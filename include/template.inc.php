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

class template_gestion
{

protected $default_id = 0;
protected $list = array();
protected $list_name = array();
protected $list_detail = array();

public function __construct()
{

$query = db()->query("SELECT `_template`.`id`, `_template`.`name`, `_template`.`type`, `_template_lang`.`title` FROM `_template` LEFT JOIN `_template_lang` ON `_template`.`id`=`_template_lang`.`id` AND `_template_lang`.`lang_id`='".SITE_LANG_ID."'");
while (list($id, $name, $type, $label)=$query->fetch_row())
{
	$this->list_name[$name] = $id;
	$this->list_detail[$id] = array ("name"=>$name, "type"=>$type, "label"=>$label);
}

}

public function get($id=0)
{

if (!$id && TEMPLATE_ID)
	$id = TEMPLATE_ID;

if (isset($this->list[$id]))
{
	return $this->list[$id];
}
elseif (APC_CACHE && ($template=apc_fetch("template_$id")))
{
	return $this->list[$id] = $template;
}
elseif ($this->exists($id))
{
	if ($this->list_detail[$id]["type"] == "container")
		$template = new template_container($id);
	elseif (substr($this->list_detail[$id]["name"], 0, 9) == "datamodel")
		$template = new template_datamodel($id);
	else
		$template = new template($id);
	if (APC_CACHE)
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

/**
 * Retrieve a page using its (unique) name
 * @param unknown_type $name
 */
public function __get($name)
{

if (isset($this->list_name[$name]))
{
	return $this->get($this->list_name[$name]);
}
else
{
	return null;
}

}

function exists($id)
{

return isset($this->list_detail[$id]);

}
function exists_name($name)
{

if (isset($this->list_name[$name]))
	return $this->list_name[$name];
else
	return false;

}

/**
 * Add a template
 * @param array $template
 */
public function add($template)
{

if (!(is_array($template) && isset($template["name"]) && isset($template["cache_mintime"]) && isset($template["cache_maxtime"]) && isset($template["title"]) && isset($template["description"]) && isset($template["details"])))
{
	return false;
}
else
{
	$query_string = "INSERT INTO `_template` (`name`, `type`, `cache_mintime`, `cache_maxtime`) VALUES ('".$template["name"]."', 'page', '".$template["cache_mintime"]."', '".$template["cache_maxtime"]."')";
	$query = db()->query($query_string);
	if ($id = $query->last_id())
	{
		$query_string = "INSERT INTO `_template_lang` (`id`, `lang_id`, `title`, `description`, `details`) VALUES ('$id', '".SITE_LANG_ID."', '".addslashes($template["title"])."', '".addslashes($template["description"])."', '".addslashes($template["details"])."')";
		$query = db()->query($query_string);
		if (isset($template["library"]) && is_array($template["library"]) && (count($template["library"]) > 0))
		{
			$query_library_list = array();
			foreach($template["library"] as $library_id)
			{
				if (library()->get($library_id))
				{
					$query_library_list[] = "($id, $library_id)";
				}
			}
			if (count($query_library_list)>0)
			{
				$query_string = " INSERT INTO `_template_library_ref` (`template_id`, `library_id`) VALUES ".implode(" , ",$query_library_list);
				db()->query($query_string);
			}
		}
		$this->list_detail[$id] = array("name"=>$template["name"], "type"=>'page');
		$this->list_name[$template["name"]] = $id;
		if (APC_CACHE == true)
		{
			$this->list = array();
			apc_store("template_gestion", $this, APC_CACHE_GESTION_TTL);
		}
		return $id;
	}
	else
	{
		return false;
	}
}

}

public function list_detail()
{

return $this->list_detail;

}

}

/**
 * Defines the display of the page, based on database infos and a template file
 * 
 */
class template
{

protected $id=0;

protected $type="";

protected $name="";
protected $title="";
protected $description="";
protected $details="";

/*
 * Obsolète à terme
 */
protected $library_list = array();

/*
 * Complete list of params
 */
protected $param_list = array();
protected $param_list_detail = array();
/*
 * Effective params, using Data Objects (Yeah !)
 */
protected $param = array();

/*
 * Filename of the PHP source template file
 */
protected $tpl_filename = "";

/*
 * Cache related infos
 */
protected $cache_id = "";
protected $cache_folder = "";
protected $cache_filename = "";
protected $cache_mintime = 0;
protected $cache_maxtime = 0;
protected $login_dependant = 0;

protected static $infos = array("type", "name", "cache_mintime", "cache_maxtime", "login_dependant");
protected static $infos_lang = array("title", "description", "details");

function __construct($id, $query=true, $params=array())
{

$this->id = $id;

if ($query)
	$this->query();
if (is_array($params) && count($params)>0)
	foreach ($params as $name=>$value)
			$this->params[$name] = $value;

}

/**
 * Query required infos on template before using it
 * @param unknown_type $infos
 */
public function query()
{

// Global infos
list($this->type, $this->name, $this->cache_mintime, $this->cache_maxtime, $this->login_dependant, $this->title, $this->description, $this->details) =
	db()->query("SELECT `_template`.`type`, `_template`.`name`, `_template`.`cache_mintime`, `_template`.`cache_maxtime`, `_template`.`login_dependant`, `_template_lang`.`title`, `_template_lang`.`description`, `_template_lang`.`details` FROM `_template`, `_template_lang` WHERE `_template`.`id`=`_template_lang`.`id` AND `_template_lang`.`lang_id`=".SITE_LANG_DEFAULT_ID." AND `_template`.`id`='".$this->id."'")->fetch_row();

$this->tpl_filename = "template/".$this->name.".tpl.php";

// Params
$this->param_list = array();
$this->param_list_detail = array();
$this->param = array();
$query = db()->query("SELECT t1.`order`, t1.`name`, t1.`datatype`, t1.`defaultvalue`, t2.`description` FROM `_template_params` as t1 LEFT JOIN `_template_params_lang` as t2 ON t1.template_id=t2.template_id AND t1.name=t2.name AND t2.lang_id='".SITE_LANG_DEFAULT_ID."' WHERE t1.`template_id`='".$this->id."' ORDER BY t1.`order` ASC");
while ($param = $query->fetch_row())
{
	$this->param_list[$param[0]] = $param[1];
	$this->param_list_detail[$param[1]] = array("datatype"=>$param[2], "value"=>json_decode($param[3], true), "structure"=>array(), "label"=>$param[4]);
}
$query_opt = db()->query("SELECT `name`, `opttype`, `optname`, `optvalue` FROM `_template_params_opt` WHERE `template_id`='".$this->id."'");
while ($opt = $query_opt->fetch_row())
{
	$this->param_list_detail[$opt[0]][$opt[1]][$opt[2]]=json_decode($opt[3], true);
}
foreach ($this->param_list_detail as $name=>$param)
{
	if (DEBUG_TEMPLATE)
		echo "<p>DEBUG template(ID#$this->id)::query() : param $name</p>\n";
	$datatype = "data_".$param["datatype"];
	$this->param[$name] = new $datatype($name, null, $param["label"]);
	if (count($param["structure"]))
	{
		foreach ($param["structure"] as $i=>$j)
			$this->param[$name]->structure_opt_set($i, $j);
	}
	$this->param[$name]->value = $param["value"];
}

// Script list
/*
$this->script_list = array();
$query = db()->query("SELECT `name` FROM `_template_scripts` WHERE `template_id`='".$this->id."'");
while (list($name) = $query->fetch_row())
{
	$this->script_list[] = $name;
}
*/

}

/**
 * Retrieve list of required libraries
 */
protected function library_query()
{

$this->library_list = array();
$query = db()->query("SELECT `library_id` FROM `_template_library_ref` WHERE `template_id`='".$this->id."'");
while (list($library_id) = $query->fetch_row())
{
	$this->library_list[] = $library_id;
}

}

/**
 * Update template (warning, there is a dedicated function to update each param)
 * 
 * @param integer $id
 * @param array $template
 */
public function update($infos)
{

foreach(self::$infos as $name)
	if (isset($infos[$name]))
		$this->{$name} = $infos[$name];
foreach(self::$infos_lang as $name)
	if (isset($infos[$name]))
		$this->{$name} = $infos[$name];

if (isset($infos["library"]) && is_array($infos["library"]))
{
	$this->library_list = array();
	foreach ($infos["library"] as $library_id)
	{
		if (library()->exists($library_id))
		{
			$this->library_list[] = $library_id;
		}
	}
}

// Template file
if (isset($infos["filecontent"]))
{
	$filename = "template/$this->name.tpl.php";
	fwrite(fopen($filename,"w"), htmlspecialchars_decode($infos["filecontent"]));
}

// Template optionnal script file
if (isset($infos["script"]))
{
	echo $filename = "template/scripts/$this->name.inc.php";
	if ($infos["script"])
	{
		fwrite(fopen($filename,"w"), htmlspecialchars_decode($infos["script"]));
	}
	elseif (file_exists($filename))
	{
		unlink($filename);
	}
}

$this->db_update();

}
/**
 * Update template base infos in database
 */
public function db_update()
{

// Base infos
$l = array();
foreach (self::$infos as $name)
	$l[] = "`_template`.`$name`='".db()->string_escape($this->{$name})."'";
// Language infos
$l = array();
foreach (self::$infos_lang as $name)
	$l[] = "`_template_lang`.`$name`='".db()->string_escape($this->{$name})."'";

db()->query("UPDATE `_template`, `_template_lang` SET ".implode(", ", $l)." WHERE `_template`.`id`='$this->id' AND `_template`.`id`=`_template_lang`.`id` AND `_template_lang`.`lang_id`=".SITE_LANG_DEFAULT_ID);

// Libraries dependences
db()->query("DELETE FROM `_template_library_ref` WHERE `template_id`='$this->id'");
$query_library_list = array();
foreach($this->library_list as $library_id)
{
	$query_library_list[] = "($this->id, $library_id)";
}
if (count($query_library_list)>0)
{
	$query_string = "INSERT INTO `_template_library_ref` (`template_id`, `library_id`) VALUES ".implode(" , ",$query_library_list);
	db()->query($query_string);
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

if (isset($this->param[$name]))
	return true;
else
	return false;

}

function __get($name)
{

if (isset($this->param[$name]))
	return $this->param[$name];
else
	return null;

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

/**
 * Returns usefull infos
 */
public function id()
{

return $this->id;

}
public function name()
{

return $this->name;

}
public function label()
{

return $this->title;

}
function title()
{

return $this->title;

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

return $this->param_list_detail;

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

foreach($this->param_list_detail as $_name=>$_param)
{
	$this->param[$_name]->value_from_form($_param["value"]);
}

}

/**
 * Execute scripts on params
 */
protected function params_check()
{

if (file_exists("template/scripts/$this->name.inc.php"))
{
	// References !
	//echo "<!-- Template Script : template/scripts/$this->name.inc.php -->\n";
	foreach ($this->param as $_name=>$_value)
		${$_name} = $_value;
	include "template/scripts/$this->name.inc.php";
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
	foreach($matches as $match)
	{
		if ($id=template()->exists_name($match[1]))
		{
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
	foreach($matches as $match)
	{
		if ($id=template()->exists_name($match[1]))
		{
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
					foreach($param_list as $name=>$detail)
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
					if (isset($this->param[$name_from]) && isset($param_list[$name]))
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
	foreach($matches as $match)
	{
		if ($id=template()->exists_name("datamodel/$match[1]"))
		{
			if (DEBUG_TEMPLATE)
				echo "<p>DEBUG : TEMPLATE(ID#$this->id)->cache_return() sending params to (sub)template ID#$id, for datamodel ID#$match[2], object ID#$match[3]</p>\n";
			template($id)->params_reset();
			template($id)->object_set(databank($match[2])->get($match[3], true));
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
	//echo "<!-- template($this->name):__tostring() -->";
	foreach ($this->param as $_name=>$_value)
		${$_name} = $_value;
	ob_start();
	include "template/".$this->name.".tpl.php";
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
	$params_str .= ",$name='".addslashes(json_encode($this->param[$name]->value_to_db()))."'";
}

/*
 * Set variables
 */
$this->cache_id = md5($params_str);
//echo "<p>$params_str : $this->cache_id</p>";
$this->cache_folder = "cache/".substr($this->cache_id,0,1);
$this->cache_filename = "$this->cache_folder/$this->cache_id";

}

/**
 * Regenerate cache file
 * @protected // TODO
 */
public function cache_generate()
{

foreach ($this->param as $_name=>$_value)
{
	${$_name} = $_value;
}

ob_start();
include $this->tpl_filename;
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
	foreach($this->param_list_detail as $name=>$param)
	{
		if ($param["datatype"] == "dataobject") // TODO : add directly a query function into the dataobject, for example $param->update_datetime_get() ...
		{
			$query = db()->query("SELECT `datetime` FROM `_databank_update` WHERE databank_id='".$param["structure"]["databank"]."' AND `dataobject_id`='".$this->param[$name]->value_to_db()."' ORDER BY `datetime` DESC LIMIT 1");
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

echo $this->cache_return();

}

}

class template_container extends template
{

public function __set($name, $value)
{

if (DEBUG_TEMPLATE)
	echo "<p>DEBUG : template(ID#$this->id)::__set() : $name : ".json_encode($value)."</p>\n";
if (isset($this->param[$name]))
	$this->param[$name]->value_from_form($value);
else
	$this->param[$name] = new data($name, $value, $name);

}

public function cache_id_set()
{

$params_str = "$this->id,".PAGE_ID;

// Each param sent to the template is used, with precision of its name
foreach($this->param as $name=>$value)
{
	$params_str .= ",$name='".addslashes(json_encode($value))."'";
}

/*
 * Set variables
 */
$this->cache_id = md5($params_str);
$this->cache_folder = "cache/".substr($this->cache_id,0,1);
$this->cache_filename = "$this->cache_folder/$this->cache_id";

}

public function params_reset()
{

$this->param = array();

foreach($this->param_list_detail as $_name=>$_param)
{
	$this->param[$_name] = new data($_name, $_param["value"], $_name);
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
	
$this->params = array();
foreach(datamodel($this->object->datamodel()->id())->fields() as $field)
{
	$this->param[$field->name] = $this->object->{$field->name};
}

/*
databank($this->datamodel_id)->get($this->object_id,$this->param_list);
foreach($this->param_list as $name)
{
	echo "<p>$name</p>";
	$this->params[$name] = databank($this->datamodel_id)->get($this->object_id)->{$name};
}
*/
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
		foreach ($this->param as $_name=>$_value)
			${$_name} = $_value;
		ob_start();
		include "template/".$this->name.".tpl.php";
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
		include "template/".$this->name.".tpl.php";
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

foreach ($this->params as $_name=>$_value)
	${$_name} = $_value;

ob_start();
include $this->tpl_filename;
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
	foreach($this->param_list_detail as $name=>$param)
	{
		if ($return == true && $param["datatype"] == "dataobject")
		{
			$query = db()->query("SELECT `datetime` FROM `_databank_update` WHERE databank_id='".$param["structure"]["databank"]."' AND `dataobject_id`='".$this->params[$name]."' ORDER BY `datetime` DESC LIMIT 1");
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

/**
 * Display cached template file
 */
protected function cache_disp()
{

$tpl = $this->cache_return();

/*
 * Faire le cumul des last-modified sur l'ensemble des templates marqués comme intervenant dans ce calcul.
 */

//header('Status: 304 Not Modified', false, 304);
//header('Last-Modified: '.gmdate('D, d M Y H:i:s',filemtime($filename)).' GMT');
//header('Expires: '.gmdate('D, d M Y H:i:s',filemtime($filename)+60).' GMT');
//header('Content-Length: '.strlen($tpl));

echo $tpl;

}

/**
 * Returns cached template file
 */
protected function cache_return()
{

$tpl = $this->cache["content"];

if (preg_match_all("/\[\[INCLUDE_TEMPLATE:(.*)\]\]/", $tpl, $matches, PREG_SET_ORDER))
{
	foreach($matches as $match)
	{
		if ($id=template()->exists_name($match[1]))
		{
			if (DEBUG_TEMPLATE)
				echo "<p>DEBUG : TEMPLATE(ID#$this->id)->cache_return() sending params to (sub)template ID#$id</p>\n"; 
			foreach($this->params as $name=>$value)
			{
				if (DEBUG_TEMPLATE)
					echo "<p>--> $name : $value</p>\n";
				template($id)->__set($name, $value);
			}
			$tpl = str_replace($match[0], template($id), $tpl);
		}
	}
}

return $tpl;

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
