<?

/**
  * $Id: template.inc.php 76 2009-10-15 09:24:20Z mathieu $
  * 
  * Copyright 2008 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */


if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [begin]");

class template_gestion extends gestion
{

protected $type = "template";

protected $info_list = array("name", "type", "cache_mintime", "cache_maxtime", "login_dependant");

protected $default_id = 0;

public function get($id=0)
{

if (!$id && TEMPLATE_ID)
	$id = TEMPLATE_ID;

//echo "<p>Accessing Template ID#$id</p>\n";

if (isset($this->list[$id]))
{
	//echo "<p>Template ID#$id found in list : ".get_class($this->list[$id])."</p>\n";
	return $this->list[$id];
}
elseif (false && APC_CACHE && ($template=apc_fetch("template_$id")))
{
	return $this->list[$id] = $template;
}
elseif ($this->exists($id))
{
	if ($this->list_detail[$id]["type"] == "container")
		$template = new template_container($id, false, $this->list_detail[$id]);
	elseif (substr($this->list_detail[$id]["name"], 0, 9) == "datamodel")
		$template = new template_datamodel($id, false, $this->list_detail[$id]);
	else
		$template = new template($id, false, $this->list_detail[$id]);
	if (false && APC_CACHE)
		apc_store("template_$id", $template, APC_CACHE_TEMPLATE_TTL);
	return $this->list[$id] = $template;
}
elseif ($this->exists_name($id))
{
	return $this->get($this->list_name[$id]);
}
else
{
	return null;
}

}

protected function query_info_more()
{

// Libraries
// TODO

// Params
$query = db()->query("SELECT t1.`template_id`, t1.`order`, t1.`name`, t1.`datatype`, t1.`defaultvalue`, t2.`description` FROM `_template_params` as t1 LEFT JOIN `_template_params_lang` as t2 ON t1.template_id=t2.template_id AND t1.name=t2.name AND t2.lang_id='".SITE_LANG_DEFAULT_ID."' ORDER BY t1.template_id, t1.`order` ASC");
$param_order = array(); // temp
while ($param = $query->fetch_assoc())
{
	$this->list_detail[$param["template_id"]]["param_list_detail"][$param["order"]] = array
	(
		"name"=>$param["name"],
		"datatype"=>$param["datatype"],
		"value"=>json_decode($param["defaultvalue"], true),
		"label"=>$param["description"]
	);
	$param_order[$param["template_id"]][$param["name"]] = $param["order"];
}
$query_opt = db()->query("SELECT `template_id`, `name`, `opttype`, `optname`, `optvalue` FROM `_template_params_opt`");
while ($opt = $query_opt->fetch_assoc())
{
	if (isset($this->list_detail[$opt["template_id"]]))
		$this->list_detail[$opt["template_id"]]["param_list_detail"][$param_order[$opt["template_id"]][$opt["name"]]][$opt["opttype"]][$opt["optname"]] = json_decode($opt["optvalue"], true);
}

}

protected function add_more($id, $template)
{

// Libraries
if (isset($template["library_list"]) && is_array($template["library_list"]))
{
	$query_library_list = array();
	foreach($template["library_list"] as $library_id)
	{
		if (library()->exists($library_id))
		{
			$query_library_list[] = "($id, $library_id)";
		}
	}
	if (count($query_library_list)>0)
	{
		$query_string = "INSERT INTO `_template_library_ref` (`template_id`, `library_id`) VALUES ".implode(" , ",$query_library_list);
		db()->query($query_string);
	}
}
	
}

protected function del_more($id)
{

db()->query("DELETE FROM `_template_library_ref` WHERE `template_id` = '$id'");

}

}

/**
 * Defines the display of the page, based on database infos and a template file
 * 
 */
class template extends object_gestion
{

protected $_type = "template";

protected $type = "";

/*
 * Cache related infos
 */
protected $cache_mintime = 0;
protected $cache_maxtime = 0;
protected $login_dependant = 0;

/*
 * Obsolète à terme
 */
protected $library_list = array();

/*
 * Complete list of params
 */
protected $param_list_detail = array();
protected $param_list = array();
/*
 * Effective params, using Data Objects (Yeah !)
 */
protected $param = array();

/*
 * Filename of the PHP source template file
 */
protected $tpl_filename = "";

/*
 * Calculated fields
 */
protected $cache_id = "";
protected $cache_folder = "";
protected $cache_filename = "";

protected static $infos = array("name", "type", "cache_mintime", "cache_maxtime", "login_dependant");
protected static $infos_lang = array("label", "description");

protected static $serialize_list = array("_type", "id", "name", "label", "description", "type", "cache_mintime", "cache_maxtime", "login_dependant", "library_list", "param_list_detail");

function __sleep()
{

return session_select::__sleep(self::$serialize_list);

}

function __wakeup()
{

session_select::__wakeup();

$this->tpl_filename = "template/".$this->name.".tpl.php";

$this->construct_params();

//echo "<p>template(ID#$this->id)::__wakeup()</p>\n";

}

protected function construct_more($infos)
{

$this->tpl_filename = "template/".$this->name.".tpl.php";

$this->construct_params();

}

protected function construct_params()
{

$this->param_list = array();
$this->param = array();
foreach ($this->param_list_detail as $nb=>$param)
{
	$this->param_list[$nb] = $param["name"];
	if (DEBUG_TEMPLATE)
		echo "<p>DEBUG template(ID#$this->id)::query() : param ".$param["name"]."</p>\n";
	$datatype = "data_".$param["datatype"];
	$this->param[$param["name"]] = new $datatype($param["name"], null, $param["label"]);
	if (isset($param["structure"]) && count($param["structure"]))
	{
		foreach ($param["structure"] as $i=>$j)
			$this->param[$param["name"]]->structure_opt_set($i, $j);
	}
	$this->param[$param["name"]]->value = $param["value"];
}

}

protected function query_info_more()
{

$this->tpl_filename = "template/".$this->name.".tpl.php";

// Params
$this->param_list_detail = array();
$param_order = array(); // temp
$query = db()->query("SELECT t1.`order`, t1.`name`, t1.`datatype`, t1.`defaultvalue`, t2.`description` FROM `_template_params` as t1 LEFT JOIN `_template_params_lang` as t2 ON t1.template_id=t2.template_id AND t1.name=t2.name AND t2.lang_id='".SITE_LANG_DEFAULT_ID."' WHERE t1.`template_id`='".$this->id."' ORDER BY t1.`order` ASC");
while ($param = $query->fetch_row())
{
	$this->param_list_detail[$param[0]] = array("name"=>$param[1], "datatype"=>$param[2], "value"=>json_decode($param[3], true), "label"=>$param[4]);
	$param_order[$param[1]] = $param[0];
}
$query_opt = db()->query("SELECT `name`, `opttype`, `optname`, `optvalue` FROM `_template_params_opt` WHERE `template_id`='".$this->id."'");
while ($opt = $query_opt->fetch_row())
{
	$this->param_list_detail[$param_order[$opt[0]]][$opt[1]][$opt[2]] = json_decode($opt[3], true);
}
$this->construct_params();

// Libraries
$this->library_list = array();
$query = db()->query("SELECT `library_id` FROM `_template_library_ref` WHERE `template_id`='".$this->id."'");
while (list($library_id) = $query->fetch_row())
{
	$this->library_list[] = $library_id;
}

}

protected function update_more($infos)
{

// Libraries
if (isset($infos["library_list"]) && is_array($infos["library_list"]))
{
	db()->query("DELETE FROM `_template_library_ref` WHERE `template_id`='$this->id'");
	$query_library_list = array();
	foreach ($infos["library"] as $library_id)
	{
		if (library()->exists($library_id))
		{
			$query_library_list[] = "($this->id, $library_id)";
		}
	}
	if (count($query_library_list)>0)
	{
		$query_string = "INSERT INTO `_template_library_ref` (`template_id`, `library_id`) VALUES ".implode(" , ",$query_library_list);
		db()->query($query_string);
	}
}

// Template file
if (isset($infos["filecontent"]))
{
	$filename = PATH_TEMPLATE."/$this->name.tpl.php";
	fwrite(fopen($filename,"w"), htmlspecialchars_decode($infos["filecontent"]));
}

// Template optionnal script file
if (isset($infos["script"]))
{
	$filename = PATH_TEMPLATE."/$this->name.inc.php";
	if ($infos["script"])
	{
		fwrite(fopen($filename,"w"), htmlspecialchars_decode($infos["script"]));
	}
	elseif (file_exists($filename))
	{
		unlink($filename);
	}
}

}

/**
 * Load the libraries associated to the template
 */
protected function library_load()
{

foreach ($this->library_list as $library_id)
{
	if (DEBUG_LIBRARY)
		echo "<p>template(ID#$this->id)::library_load() : ID#$library_id</p>\n";
	library($library_id)->load();
}

}

function __isset($name)
{

return isset($this->param[$name]);

}

function __get($name)
{

if (isset($this->param[$name]))
	return $this->param[$name];
else
{
	// TODO : trigger_error
	return null;
}

}

/**
 * Assignment of params from the page or parent templates
 */
public function __set($name, $value)
{

if (in_array($name, $this->param_list))
{
	if (DEBUG_TEMPLATE)
		echo "<p>DEBUG : template(ID#$this->id)::__set() : $name : ".json_encode($value)."</p>\n";
	$this->param[$name]->value_from_form($value);
}
else
{
	if (DEBUG_TEMPLATE)
		echo "<p>DEBUG : TEMPLATE($this->id)->__set NOT DEFINED : $name</p>\n";
}

}

function title()
{

return $this->label;

}

public function info($name)
{

if ($name == "library_list" || in_array($name, array_merge(self::$infos, self::$infos_lang)))
	return $this->{$name};

}

/**
 * Returns the list of params
 */
public function param_list()
{

return $this->param_list;

}

/**
 * Display template with headers
 */
public function disp()
{

$this->params_check();

/*
 * Verify if :
 * - cache is enables
 * - template is cacheable
 * - template is not login_dependant
 */
if (TEMPLATE_CACHE && ($this->cache_maxtime > 0) && !($this->login_dependant && login()->id()))
{
	//echo "<p>DISP CACHE template ID#$this->id</p>\n";
	$this->cache_id_set();
	if (isset($_GET["_page_regen"]) || !$this->cache_check())
	{
		$this->cache_generate();
	}
	$this->cache_disp();
}
else
{
	echo $this->execute();
}

}

/**
 * Identical to disp() but without headers
 * 
 */
public function __tostring()
{

$this->params_check();

/*
 * Verify if :
 * - cache is enables
 * - template is cacheable
 * - template is not login_dependant
 */
if (TEMPLATE_CACHE && ($this->cache_maxtime > 0) && !($this->login_dependant && login()->id()))
{
	$this->cache_id_set();
	if (isset($_GET["_page_regen"]) || !$this->cache_check())
	{
		$this->cache_generate();
	}
	return $this->cache_return();
}
else
{
	return $this->execute();
}

}

/**
 * Reset params to default value
 */
function params_reset()
{

//echo "<p>template(ID#$this->id)::params_reset()</p>\n";

foreach ($this->param_list_detail as $param)
{
	$this->param[$param["name"]]->value = $param["value"];
}

}

/**
 * Execute optionnal script with given params
 */
protected function params_check()
{

if (file_exists($filename=PATH_TEMPLATE."/$this->name.inc.php"))
{
	// Including references !
	extract($this->param);
	//echo "<!-- Template Script : template/$this->name.inc.php -->\n";
	include $filename;
}

}

/**
 * Returns the list of subtemplates of a given template
 */
public static function subtemplates($tpl)
{

$return = array();

if (preg_match_all("/\[\[TEMPLATE:([a-zA-Z_\/]*)(,(.*)){0,1}\]\]/", $tpl, $matches, PREG_SET_ORDER))
{
	$list_name = template()->list_name_get();
	foreach($matches as $match)
	{
		if (template()->exists_name($match[1]))
		{
			$id = $list_name[$match[1]];
			if (isset($match[3]))
			{
				//echo $match[3]." : ";
				//print_r(json_decode($match[3], true));
				$return[] = array("id"=>$id, "params"=>json_decode($match[3], true));
			}
			else
			{
				$return[] = array("id"=>$id);
			}
		}
	}
}

return $return;

}

/**
 * Apply subtemplates to a executed template.
 */
protected function subtemplates_apply($tpl)
{

if (preg_match_all("/\[\[TEMPLATE:([a-zA-Z_\/]*)(,(.*)){0,1}\]\]/", $tpl, $matches, PREG_SET_ORDER))
{
	$list_name = template()->list_name_get();
	foreach($matches as $match)
	{
		if (template()->exists_name($match[1]))
		{
			$id = $list_name[$match[1]];
			if (DEBUG_TEMPLATE)
				echo "<p>DEBUG : TEMPLATE(ID#$this->id)->cache_return() sending params to (sub)template ID#$id</p>\n";
			// TODO : VOIR SI PB DE RECURSION !!!!
			template($id)->params_reset();
			// Passage des paramètres
			if (isset($match[3]))
			{
				$param_list = template($id)->param_list();
				$params = json_decode($match[3], true);
				if ($params === true) // On tente de passer tous les paramètres
				{
					foreach($param_list as $nb=>$name)
					{
						if (isset($this->param[$name]))
						{
							if (DEBUG_TEMPLATE)
								echo "<p>--> Param : $name</p>\n";
							template($id)->__set($name, $this->param[$name]->value_to_form());
						}
					}
				}
				elseif (is_array($params)) foreach ($params as $name=>$name_from)
				{
					if (DEBUG_TEMPLATE)
						echo "<p>--> $name_from : $name</p>\n";
					if (isset($this->param[$name_from]) && in_array($name, $param_list))
					{
						if (DEBUG_TEMPLATE)
							echo "<p>--> $name_from : $name</p>\n";
						template($id)->__set($name, $this->param[$name_from]->value_to_form());
					}
				}
			}
			$tpl = str_replace($match[0], template($id), $tpl);
		}
	}
}

if (preg_match_all("/\[\[INCLUDE_DATAMODEL:(.*),(.*),(.*)\]\]/", $tpl, $matches, PREG_SET_ORDER))
{
	$list_name = template()->list_name_get();
	foreach($matches as $match)
	{
		if (template()->exists_name("datamodel/$match[1]"))
		{
			$id = $list_name["datamodel/$match[1]"];
			if (DEBUG_TEMPLATE)
				echo "<p>DEBUG : TEMPLATE(ID#$this->id)->cache_return() sending params to (sub)template ID#$id, for datamodel ID#$match[2], object ID#$match[3]</p>\n";
			template($id)->params_reset();
			template($id)->object_set(datamodel($match[2])->get($match[3], true));
			$tpl = str_replace($match[0], template($id), $tpl);
		}
	}
}

return $tpl;

}

/**
 * Execute a template
 */
protected function execute()
{

//echo "<p>template(#ID$this->id:$this->name)::execute()</p>\n";
ob_start();
extract($this->param);
include PATH_TEMPLATE."/$this->name.tpl.php";
$return = $this->subtemplates_apply(ob_get_contents());
ob_end_clean();
return $return;

}

/*
 * Set template hash from params (cache ID) and folder to save/retrieve it
 */
protected function cache_id_set()
{

if ($this->login_dependant)
		$params_str = "$this->id,".login()->id();
else
		$params_str = "$this->id,0";

foreach($this->param_list as $name)
{
	$params_str .= ",$name=".json_encode($this->param[$name]->value);
}

/*
 * Set variables
 */
$this->cache_id = md5($params_str);
$this->cache_folder = PATH_CACHE."/".substr($this->cache_id,0,1);
$this->cache_filename = "$this->cache_folder/$this->cache_id";
//echo "<p>$params_str : $this->cache_id</p>";

}

/**
 * Regenerate cache file
 * @protected // TODO
 */
public function cache_generate()
{

//echo "<p>template(ID#$this->id)::cache_generate() ".PATH_TEMPLATE."/$this->name.tpl.php : $this->cache_id</p>\n";
//print_r($this->param);
ob_start();
extract($this->param);
include PATH_TEMPLATE."/$this->name.tpl.php";
fwrite(fopen($this->cache_filename,"w"), ob_get_contents());
ob_end_clean();

}

/**
 * Verify if cache file is up to date
 */
protected function cache_check()
{

// Pas de fichier en cache
if (!file_exists($this->cache_filename))
{
	if (DEBUG_CACHE)
		echo "<p>CACHE_CHECK : Cache file does not exists -> FALSE</p>\n";
	return false;
}
// FIchier en cache trop récent
elseif ((($cache_datetime=filemtime($this->cache_filename))+TEMPLATE_CACHE_MIN_TIME) > ($time=time()))
{
	if (DEBUG_CACHE)
		echo "<p>CACHE_CHECK : Cache file recently updated -> TRUE</p>\n";
	return true;
}
// Fichier template plus récent que le cache
elseif (($tpl_datetime=filemtime($this->tpl_filename)) > $cache_datetime)
{
	if (DEBUG_CACHE)
		echo "<p>CACHE_CHECK : Template file recently updated -> FALSE</p>\n";
	return false;
}
// Fichier en cache trop vieux 
elseif (($cache_datetime+TEMPLATE_CACHE_MAX_TIME) < $time)
{
	if (DEBUG_CACHE)
		echo "<p>CACHE_CHECK : Cache filename too old -> FALSE</p>\n";
	return false;
}
// Paramètres du template modifiés
else
{
	$return = true;
	foreach($this->param_list_detail as $param)
	{
		if ($param["datatype"] == "dataobject") // TODO : add directly a query function into the dataobject, for example $param->update_datetime_get() ...
		{
			$query = db()->query("SELECT `datetime` FROM `_databank_update` WHERE databank_id='".$param["structure"]["databank"]."' AND `dataobject_id`='".$this->param[$param["name"]]->value_to_db()."' ORDER BY `datetime` DESC LIMIT 1");
			if ($query->num_rows())
			{
				list($i) = $query->fetch_row();
				if (strtotime($i)>$cache_datetime)
					$return=false;
			}
		}
	}
	if (DEBUG_CACHE)
		if ($return)
			echo "<p>CACHE_CHECK : Params not updated -> TRUE</p>\n";
		else
			echo "<p>CACHE_CHECK : Params updated -> FALSE</p>\n";
	return $return;
}

}

/**
 * Returns cached template file
 */
protected function cache_return()
{

$filesize = filesize($this->cache_filename);
$tpl = ($filesize>0) ? fread(fopen($this->cache_filename, "r"), $filesize) : "";

return $this->subtemplates_apply($tpl);

}

/**
 * Display cached template file
 */
protected function cache_disp()
{

/*
 * Faire le cumul des last-modified sur l'ensemble des templates marqués comme intervenant dans ce calcul.
 */

//header('Status: 304 Not Modified', false, 304);
//header('Last-Modified: '.gmdate('D, d M Y H:i:s',filemtime($filename)).' GMT');
//header('Expires: '.gmdate('D, d M Y H:i:s',filemtime($filename)+60).' GMT');
//header('Content-Length: '.strlen($tpl));

echo $this->cache_return();

}

}

/**
 * Specific for container (primary) templates
 */
class template_container extends template
{

public function __set($name, $value)
{

if (DEBUG_TEMPLATE)
	echo "<p>DEBUG : template_container(ID#$this->id)::__set() : $name : ".json_encode($value)."</p>\n";

if (isset($this->param[$name]))
	$this->param[$name]->value = $value;
else
	$this->param[$name] = new data($name, $value, $name);

}

public function cache_id_set()
{

$params_str = "$this->id,".PAGE_ID;

// Each param sent to the template is used, with precision of its name
foreach($this->param as $name=>$value)
{
	$params_str .= ",$name='".addslashes(json_encode($value->value))."'";
}

/*
 * Set variables
 */
$this->cache_id = md5($params_str);
$this->cache_folder = PATH_CACHE."/".substr($this->cache_id,0,1);
$this->cache_filename = "$this->cache_folder/$this->cache_id";

//echo fread(fopen($this->cache_filename, "r"), filesize($this->cache_filename));

}

public function params_reset()
{

$this->param = array();
foreach($this->param_list_detail as $param)
{
	$this->param[$param["name"]] = new data($param["name"], $param["value"], $param["label"]);
}

}

}

/**
 * Special configuration optimized for datamodels.
 * @author mathieu
 *
 */
class template_datamodel extends template
{

protected $object = null;

function object_set(agregat $object)
{

$this->object = $object;

$this->object_retrieve_values();

}

function object()
{

return $this->object;

}


function object_retrieve_values()
{

//echo "<p>TEMPLATE_DATAMODEL()::object_retrieve_values()</p>";

$this->params = array();
foreach($this->object->datamodel()->fields() as $field)
{
	$this->param[$field->name] = $this->object->{$field->name};
}

}

protected function cache_id_set()
{

if ($this->object)
	$params_str = "'$this->id','".$this->object->datamodel()->id()."','".$this->object->id->value."'";
else
	$params_str = "'$this->id','0','0'";

/*
 * Set variables
 */
$this->cache_id = md5($params_str);
$this->cache_folder = "cache/".substr($this->cache_id,0,1);
$this->cache_filename = "$this->cache_folder/$this->cache_id";

}

/**
 * Vérifie l'obsolescence du fichier en cache
 */
protected function cache_check()
{

// Pas de fichier en cache
if (!file_exists($this->cache_filename))
{
	if (DEBUG_CACHE)
		echo "<p>CACHE_CHECK : Cache file does not exists -> FALSE</p>\n";
	return false;
}
// FIchier en cache trop récent
elseif ((($cache_datetime=filemtime($this->cache_filename))+TEMPLATE_CACHE_MIN_TIME) > ($time=time()))
{
	if (DEBUG_CACHE)
		echo "<p>CACHE_CHECK : Cache file recently updated -> TRUE</p>\n";
	return true;
}
// Fichier template plus récent que le cache
elseif (($tpl_datetime=filemtime($this->tpl_filename)) > $cache_datetime)
{
	if (DEBUG_CACHE)
		echo "<p>CACHE_CHECK : Template file recently updated -> FALSE</p>\n";
	return false;
}
// Fichier en cache trop vieux 
elseif (($cache_datetime+TEMPLATE_CACHE_MAX_TIME) < $time)
{
	if (DEBUG_CACHE)
		echo "<p>CACHE_CHECK : Cache filename too old -> FALSE</p>\n";
	return false;
}
// Paramètres du template modifiés
else
{
	$query = db()->query("SELECT `datetime` FROM `_databank_update` WHERE databank_id='".$this->object->datamodel()->id()."' AND `dataobject_id`='".$this->object->id->value."' ORDER BY `datetime` DESC LIMIT 1");
	if ($query->num_rows())
	{
		list($i) = $query->fetch_row();
		if (strtotime($i)>$cache_datetime)
			$return=false;
		else
			$return=true;
	}
	else
		$return=true;
	if (DEBUG_CACHE)
		if ($return)
			echo "<p>CACHE_CHECK : Params not updated -> TRUE</p>\n";
		else
			echo "<p>CACHE_CHECK : Params updated -> FALSE</p>\n";
	return $return;
}

}

/**
 * Display template with headers
 */
public function disp()
{

$this->params_check();

if (TEMPLATE_CACHE && $this->object && $this->object->id->value && ($this->cache_maxtime > 0) && !($this->login_dependant && login()->id()))
{
	$this->cache_id_set();
	if (isset($_GET["_page_regen"]) || !$this->cache_check())
	{
		$this->cache_generate();
	}
	$this->cache_disp();
}
else
{
		extract($this->param);
		ob_start();
		include PATH_TEMPLATE."/".$this->name.".tpl.php";
		$return = ob_get_contents();
		ob_end_clean();
		echo $return;
}

}

/**
 * Identical to disp() but without headers
 * 
 */
public function __tostring()
{

$this->params_check();

if (TEMPLATE_CACHE && $this->object && $this->object->id->value && ($this->cache_maxtime > 0) && !($this->login_dependant && login()->id()))
{
	$this->cache_id_set();
	if (isset($_GET["_page_regen"]) || !$this->cache_check())
	{
		$this->cache_generate();
	}
	return $this->cache_return();
}
else
{
		foreach ($this->param as $_name=>$_value)
			${$_name} = $_value;
		ob_start();
		include PATH_TEMPLATE."/".$this->name.".tpl.php";
		$return = ob_get_contents();
		ob_end_clean();
		return $return;
}

}

}

/**
 * Version with MySQL Cache (TODO)
 * @author mathieu
 *
 */
class template_mysql extends template
{

protected $cache=null;

/*
 * Set template hash from params (cache ID) and folder to save/retrieve it
 */
protected function cache_id_set()
{

$params_str = "";
if ($this->container)
{
	foreach($this->params as $name=>$value)
	{
		$params_str .= ",$name='".addslashes(json_encode($value))."'";
	}
}
else
{
	foreach($this->param_list as $name)
	{
		$params_str .= ",$name";
		if (isset($this->params[$name]))
			$params_str .= "='".addslashes(json_encode($this->params[$name]))."'";
	}
}

/*
 * Set variables
 */
$this->cache_id = md5($params_str);

}

/**
 * Regenerate cache file
 */
public function cache_generate()
{

ob_start();
extract($this->params);
include PATH_TEMPLATE."/$this->name.tpl.php";
db()->query("REPLACE INTO `_template_cache` (`template_id`, `hash`, `datetime`, `content`) VALUES ( '$this->id', '$this->cache_id', NOW(), '".(addslashes($tpl=ob_get_contents()))."')");
$this->cache = array("time"=>time(), "content"=>$tpl);
ob_end_clean();

}

/**
 * Vérifie l'obsolescence du fichier en cache
 */
protected function cache_check()
{

$query = db()->query("SELECT UNIX_TIMESTAMP(`datetime`) as `time`, `content` FROM `_template_cache` WHERE `template_id`='$this->id' AND `hash`='$this->cache_id'");
if ($nb=$query->num_rows())
	$this->cache=$query->fetch_assoc();
 
// Pas de fichier en cache
if (!$nb)
{
	if (DEBUG_CACHE)
		echo "<p>CACHE_CHECK (ID#$this->id) : Cache file does not exists -> FALSE</p>\n";
	return false;
}
// Fichier en cache trop récent
elseif (($this->cache["time"]+TEMPLATE_CACHE_MIN_TIME) > ($time=time()))
{
	if (DEBUG_CACHE)
		echo "<p>CACHE_CHECK (ID#$this->id) : Cache file recently updated -> TRUE</p>\n";
	return true;
}
// Fichier template plus récent que le cache
elseif (($tpl_datetime=filemtime($this->tpl_filename)) > $this->cache["time"])
{
	if (DEBUG_CACHE)
		echo "<p>CACHE_CHECK (ID#$this->id) : Template file recently updated -> FALSE</p>\n";
	return false;
}
// Fichier en cache trop vieux 
elseif (($this->cache["time"]+TEMPLATE_CACHE_MAX_TIME) < $time)
{
	if (DEBUG_CACHE)
		echo "<p>CACHE_CHECK (ID#$this->id) : Cache filename too old -> FALSE</p>\n";
	return false;
}
// Paramètres du template modifiés
else
{
	$return = true;
	foreach($this->param_list_detail as $param)
	{
		if ($return == true && $param["datatype"] == "dataobject")
		{
			$query = db()->query("SELECT `datetime` FROM `_databank_update` WHERE databank_id='".$param["structure"]["databank"]."' AND `dataobject_id`='".$this->params[$param["name"]]."' ORDER BY `datetime` DESC LIMIT 1");
			if ($query->num_rows())
			{
				list($i) = $query->fetch_row();
				if (strtotime($i)>$this->cache["time"])
					$return=false;
			}
		}
	}
	if (DEBUG_CACHE)
		if ($return)
			echo "<p>CACHE_CHECK (ID#$this->id) : Params not updated -> TRUE</p>\n";
		else
			echo "<p>CACHE_CHECK (ID#$this->id) : Params updated -> FALSE</p>\n";
	return $return;
}

}

}

/**
 * Version with memcached Cache (TODO)
 * @author mathieu
 *
 */
class template_memcached extends template
{



}

/**
 * Accès aux templates
 * 
 * @param integer $id
 */
function template($id=0)
{

if (!isset($GLOBALS["template_gestion"]))
{
	// APC
	if (APC_CACHE)
	{
		if (!($GLOBALS["template_gestion"]=apc_fetch("template_gestion")))
		{
			$GLOBALS["template_gestion"] = new template_gestion();
			apc_store("template_gestion", $GLOBALS["template_gestion"], APC_CACHE_GESTION_TTL);
		}
	}
	// Session
	else
	{
		if (!isset($_SESSION["template_gestion"]))
			$_SESSION["template_gestion"] = new template_gestion();
		$GLOBALS["template_gestion"] = $_SESSION["template_gestion"];
	}
}

if ($id)
	return $GLOBALS["template_gestion"]->get($id);
else
	return $GLOBALS["template_gestion"];

}

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [end]");

?>
