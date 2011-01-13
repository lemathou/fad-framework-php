<?

/**
  * $Id$
  * 
  * Copyright 2008-2010 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  * Many template caches are supported : APC, Memcached or directly in a cache folder
  * 
  */


if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [begin]");

class template_gestion extends gestion
{

protected $type = "template";

protected $info_list = array("name", "type", "cache_mintime", "cache_maxtime", "login_dependant");

protected $info_detail = array
(
	"name"=>array("label"=>"Nom (unique)", "type"=>"string", "size"=>64, "lang"=>false),
	"label"=>array("label"=>"Label", "type"=>"string", "size"=>128, "lang"=>true),
	"description"=>array("label"=>"Description", "type"=>"text", "lang"=>true),
	"type"=>array("label"=>"Type", "type"=>"select", "lang"=>false, "default"=>"page", "select_list"=> array('container'=>"Conteneur principal",'inc'=>"Inclusion fréquente",'page'=>"Contenu de page",'datamodel'=>"Vue de datamodel")),
	"cache_mintime"=>array("label"=>"Durée minimum du cache", "lang"=>false, "default"=>TEMPLATE_CACHE_MIN_TIME, "type"=>"integer"),
	"cache_maxtime"=>array("label"=>"Durée maximum du cache", "lang"=>false, "default"=>TEMPLATE_CACHE_MAX_TIME, "type"=>"integer"),
	"login_dependant"=>array("label"=>"Dépendant du login", "lang"=>false, "default"=>"0", "type"=>"boolean"),
	"library_list"=>array("label"=>"Librairies", "type"=>"object_list", "object_type"=>"library", "db_table"=>"_template_library_ref", "db_id"=>"template_id", "db_field"=>"library_id"),
	"tplfile"=>array("label"=>"Template", "type"=>"script", "folder"=>PATH_TEMPLATE, "filename"=>"{name}.tpl.php"),
	"script"=>array("label"=>"Script", "type"=>"script", "folder"=>PATH_TEMPLATE, "filename"=>"{name}.inc.php")
);

protected $info_required = array("name", "label", "type");

protected $retrieve_details = false;

protected $default_id = 0;

public function get($id=0)
{

if (!$id && TEMPLATE_ID)
	$id = TEMPLATE_ID;

return gestion::get($id);

}

protected function construct_object($id)
{

if ($this->retrieve_details)
{
	if ($this->list_detail[$id]["type"] == "container")
		return new template_container($id, false, $this->list_detail[$id]);
	elseif (substr($this->list_detail[$id]["type"], 0, 9) == "datamodel")
		return new template_datamodel($id, false, $this->list_detail[$id]);
	else
		return new template($id, false, $this->list_detail[$id]);
}
else
{
	if ($this->list_detail[$id]["type"] == "container")
		return new template_container($id, true);
	elseif (substr($this->list_detail[$id]["type"], 0, 9) == "datamodel")
		return new template_datamodel($id, true);
	else
		return new template($id, true);
}

}

protected function query_info_more()
{

// Params
$param_order = array(); // temp
$query = db()->query("SELECT t1.`template_id`, t1.`order`, t1.`name`, t1.`datatype`, t1.`defaultvalue`, t2.`description` FROM `_template_params` as t1 LEFT JOIN `_template_params_lang` as t2 ON t1.template_id=t2.template_id AND t1.name=t2.name AND t2.lang_id='".SITE_LANG_DEFAULT_ID."' ORDER BY t1.template_id, t1.`order` ASC");
while ($param = $query->fetch_assoc())
{
	$this->list_detail[$param["template_id"]]["param_list_detail"][$param["order"]] = array
	(
		"name"=>$param["name"],
		"datatype"=>$param["datatype"],
		"value"=>json_decode($param["defaultvalue"], true),
		"label"=>$param["description"],
		"opt"=>array()
	);
	$param_order[$param["template_id"]][$param["name"]] = $param["order"];
}
$query_opt = db()->query("SELECT `template_id`, `name`, `optname`, `optvalue` FROM `_template_params_opt`");
while ($opt = $query_opt->fetch_assoc())
{
	if (isset($this->list_detail[$opt["template_id"]]))
		$this->list_detail[$opt["template_id"]]["param_list_detail"][$param_order[$opt["template_id"]][$opt["name"]]]["opt"][$opt["optname"]] = json_decode($opt["optvalue"], true);
}

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
// Effective params, using data fields
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

function __sleep()
{

return array("id", "name", "label", "description", "type", "cache_mintime", "cache_maxtime", "login_dependant", "library_list", "param_list_detail");

}
function __wakeup()
{

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
	$datatype = "data_".$param["datatype"];
	$this->param[$param["name"]] = new $datatype($param["name"], $param["value"], $param["label"]);
	foreach ($param["opt"] as $i=>$j)
		$this->param[$param["name"]]->structure_opt_set($i, $j);
}

}

protected function query_info_more()
{

$this->tpl_filename = "template/".$this->name.".tpl.php";

$this->query_params();

}
protected function query_params()
{

// Params
$this->param_list_detail = array();
$param_order = array(); // temp
$query = db()->query("SELECT t1.`order`, t1.`name`, t1.`datatype`, t1.`defaultvalue`, t2.`description` FROM `_template_params` as t1 LEFT JOIN `_template_params_lang` as t2 ON t1.template_id=t2.template_id AND t1.name=t2.name AND t2.lang_id='".SITE_LANG_DEFAULT_ID."' WHERE t1.`template_id`='".$this->id."' ORDER BY t1.`order` ASC");
while ($param = $query->fetch_row())
{
	$this->param_list_detail[$param[0]] = array("name"=>$param[1], "datatype"=>$param[2], "value"=>json_decode($param[3], true), "label"=>$param[4], "opt"=>array());
	$param_order[$param[1]] = $param[0];
}
$query_opt = db()->query("SELECT `name`, `optname`, `optvalue` FROM `_template_params_opt` WHERE `template_id`='".$this->id."'");
while ($opt = $query_opt->fetch_row())
{
	$this->param_list_detail[$param_order[$opt[0]]]["opt"][$opt[1]] = json_decode($opt[2], true);
}
$this->construct_params();

}

/**
 * Add, remove and update template parameters
 */
public function param_add($name, $info)
{

if (!login()->perm(6))
	die("ONLY ADMIN CAN ADD TEMPLATE PARAMS");
if (!is_string($name) || !preg_match("/^([a-zA-Z_-]*)$/", $name) || isset($this->param[$name]) || !is_array($info) || !isset($info["datatype"]) || !data()->exists_name($info["datatype"]))
	return false;
if (!isset($info["defaultvalue"]) || !is_string($info["defaultvalue"]))
	$info["defaultvalue"] = "null";
if (!isset($info["description"]) || !is_string($info["description"]))
	$info["description"] = "";

list($info["order"]) = db()->query("SELECT COUNT(*) FROM `_template_params` WHERE `template_id`='$this->id'")->fetch_row();
db()->query("INSERT INTO `_template_params` (`template_id`, `order`, `datatype`, `name`, `defaultvalue`) VALUES ('$this->id', '".$info["order"]."', '".$info["datatype"]."', '".$name."', '".db()->string_escape($info["defaultvalue"])."' )");
db()->query("INSERT INTO `_template_params_lang` (`template_id`, `lang_id`, `name`, `description`) VALUES ('$this->id', '".SITE_LANG_ID."', '".db()->string_escape($name)."', '".db()->string_escape($info["description"])."' )");

$this->query_info();
template()->query_info();

return true;

}
public function param_del($name)
{

if (!login()->perm(6))
	die("ONLY ADMIN CAN ADD TEMPLATE PARAMS");
if (!is_string($name) || !isset($this->param[$name]))
	return false;

db()->query("DELETE FROM `_template_params` WHERE template_id='$this->id' AND name='$name'");
db()->query("DELETE FROM `_template_params_lang` WHERE template_id='$this->id' AND name='$name'");
db()->query("DELETE FROM `_template_params_opt` WHERE template_id='$this->id' AND name='$name'");

$this->query_info();
template()->query_info();

return true;

}
public function param_update($name, $info)
{

if (!login()->perm(6))
	die("ONLY ADMIN CAN ADD TEMPLATE PARAMS");
if (!is_string($name) || isset($this->param[$name]) || !is_array($info))
	return false;

$update_list = array();
$update_lang_list = array();
if (isset($info["datatype"]))
	if (!data()->exists_name($info["datatype"]))
		return false;
	else
		$update_list[] = "`datatype`='".$info["datatype"]."'";
if (isset($info["defaultvalue"]))
	if (!is_string($info["defaultvalue"]))
		return false;
	else
		$update_list[] = "`defaultvalue`='".db()->string_escape($info["defaultvalue"])."'";
if (isset($info["description"]))
	if (!is_string($info["description"]))
		return false;
	else
		$update_lang_list[] = "`description`='".db()->string_escape($info["description"])."'";
list($posmax) = db()->query("SELECT COUNT(*) FROM `_template_params` WHERE `template_id`='$this->id'")->fetch_row();
if (isset($info["order"]))
	if (!is_numeric($info["order"]) || $info["order"] < 0 || $info["order"] > $pos_max)
		return false;

if (count($update_list))
{
	db()->query("UPDATE `_template_params` SET ".implode($update_list)." WHERE `template_id`='$this->id' AND `name`='".$name."'");
}
if (count($update_lang_list))
{
	db()->query("UPDATE `_template_params_lang` SET ".implode($update_lang_list)." WHERE `template_id`='$this->id' AND `lang_id`='".SITE_LANG_ID."' AND `name`='".$name."'");
}

$this->query_info();
template()->query_info();

return true;

}

/**
 * Returns the list of params
 */
public function param_list()
{

return $this->param_list;

}
public function param_list_detail()
{

return $this->param_list_detail;

}

/**
 * Usage of params from the page or parent template
 */
function __isset($name)
{

return isset($this->param[$name]);

}
function __get($name)
{

if (isset($this->param[$name]))
	return $this->param[$name];
else
	return null;

}
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

/**
 * Display template with headers
 */
public function disp()
{

/*
 * Faire le cumul des last-modified sur l'ensemble des templates marqués comme intervenant dans ce calcul.
 */
//header('Status: 304 Not Modified', false, 304);
//header('Last-Modified: '.gmdate('D, d M Y H:i:s',filemtime($filename)).' GMT');
//header('Expires: '.gmdate('D, d M Y H:i:s',filemtime($filename)+60).' GMT');
//header('Content-Length: '.strlen($tpl));

echo $this->__tostring();

}

/**
 * Returns the template to display, applying params
 */
public function __tostring()
{

$this->params_check();

//echo "<p>template($this->id)::__tostring()</p>\n";

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
 * Reset params to default value and empty the calculated template.
 */
function reset()
{

//echo "<p>template(ID#$this->id)::reset()</p>\n";
$this->tpl = "";
$this->cache_id = "";
$this->params_reset();

}
function params_reset()
{

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
			$template = template($id);
			if (DEBUG_TEMPLATE)
				echo "<p>DEBUG : TEMPLATE(ID#$this->id)->cache_return() sending params to (sub)template ID#$id ".$match[1]."</p>\n";
			// TODO : VOIR SI PB DE RECURSION !!!!
			$template->reset();
			// Passage des paramètres
			if (isset($match[3]))
			{
				$param_list = $template->param_list();
				$params = json_decode($match[3], true);
				if ($params === true) // On tente de passer tous les paramètres
				{
					foreach($param_list as $nb=>$name)
					{
						if (isset($this->param[$name]))
						{
							if (DEBUG_TEMPLATE)
								echo "<p>--> Param : $name</p>\n";
							$template->__set($name, $this->param[$name]->value_to_form());
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
						$template->__set($name, $this->param[$name_from]->value_to_form());
					}
				}
			}
			$tpl = str_replace($match[0], $template, $tpl);
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
			$template = template($id);
			if (DEBUG_TEMPLATE)
				echo "<p>DEBUG : TEMPLATE(ID#$this->id)->cache_return() sending params to (sub)template ID#$id, for datamodel ID#$match[2], object ID#$match[3]</p>\n";
			$template->reset();
			$template->object_set(datamodel($match[2])->get($match[3]));
			$tpl = str_replace($match[0], $template, $tpl);
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
extract($this->param);
ob_start();
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
if (TEMPLATE_CACHE_TYPE == "file")
{
	$this->cache_folder = PATH_CACHE."/".substr($this->cache_id,0,1);
	$this->cache_filename = "$this->cache_folder/$this->cache_id";
}

}

/**
 * Regenerate cache file
 * @protected // TODO
 */
public function cache_generate()
{

$_time = time();

//echo "<p>template(ID#$this->id)::cache_generate() ".PATH_TEMPLATE."/$this->name.tpl.php : $this->cache_id</p>\n";
extract($this->param);
ob_start();
include PATH_TEMPLATE."/$this->name.tpl.php";
if (APC_CACHE && TEMPLATE_CACHE_TYPE == "apc")
	apc_store("tpl_$this->cache_id", $this->tpl="<!-- $_time -->".ob_get_contents(), TEMPLATE_CACHE_MAX_TIME);
else
	fwrite(fopen($this->cache_filename,"w"), ob_get_contents());
ob_end_clean();

fwrite(fopen(PATH_ROOT."/log/cache.txt","a"), date("Y-m-d H:i:s", $_time)." : Write tpl($this->id) params $this->cache_id\n");

}

/**
 * Verify if cache file is up to date
 */
protected function cache_check()
{

$time = time();

if (APC_CACHE && TEMPLATE_CACHE_TYPE == "apc")
{
	//echo "<p>Fetching tpl_$this->cache_id : ".substr(apc_fetch("tpl_$this->cache_id"), 5, 10)."\n";
	if (!($this->tpl=apc_fetch("tpl_$this->cache_id")))
	{
		if (DEBUG_CACHE)
			echo "<p>template($this->id)::cache_check() : Failed retrieving cache file</p>\n";
		return false;
	}
	// Fichier en cache trop récent
	elseif (!($cache_datetime=substr($this->tpl, 5, 10)) || ($cache_datetime+TEMPLATE_CACHE_MIN_TIME) < $time)
	{
		if (DEBUG_CACHE)
			echo "<p>template($this->id)::cache_check() : Cache file recently updated -> TRUE</p>\n";
		return true;
	}
	// Fichier template plus récent que le cache
	elseif (($tpl_datetime=filemtime($this->tpl_filename)) > $cache_datetime)
	{
		if (true || DEBUG_CACHE)
			echo "<p>template($this->id)::cache_check() : Template file recently updated ($tpl_datetime) -> $cache_datetime</p>\n";
		return false;
	}
	else
	{
		$return = true;
		foreach($this->param_list_detail as $param)
		{
			if ($param["datatype"] == "dataobject") // TODO : add directly a query function into the dataobject, for example $param->update_datetime_get() ...
			{
				$query = db()->query("SELECT `datetime` FROM `_databank_update` WHERE databank_id='".$param["opt"]["databank"]."' AND `dataobject_id`='".$this->param[$param["name"]]->value."' ORDER BY `datetime` DESC LIMIT 1");
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
				echo "<p>template($this->id)::cache_check() : Params not updated -> TRUE</p>\n";
			else
				echo "<p>template($this->id)::cache_check() : Params updated -> FALSE</p>\n";
		return $return;
	}
}
else
{
	// Pas de fichier en cache
	if (!file_exists($this->cache_filename))
	{
		if (DEBUG_CACHE)
			echo "<p>template($this->id)::cache_check() : Cache file does not exists -> FALSE</p>\n";
		return false;
	}
	// Fichier en cache trop récent
	elseif ((($cache_datetime=filemtime($this->cache_filename))+TEMPLATE_CACHE_MIN_TIME) > ($time=time()))
	{
		if (DEBUG_CACHE)
			echo "<p>template($this->id)::cache_check() : Cache file recently updated -> TRUE</p>\n";
		return true;
	}
	// Fichier template plus récent que le cache
	elseif (($tpl_datetime=filemtime($this->tpl_filename)) > $cache_datetime)
	{
		if (DEBUG_CACHE)
			echo "<p>template($this->id)::cache_check() : Template file recently updated -> FALSE</p>\n";
		return false;
	}
	// Fichier en cache trop vieux 
	elseif (($cache_datetime+TEMPLATE_CACHE_MAX_TIME) < $time)
	{
		if (DEBUG_CACHE)
			echo "<p>template($this->id)::cache_check() : Cache filename too old -> FALSE</p>\n";
		return false;
	}
	// Paramètres du template modifiés
	{
		$return = true;
		foreach($this->param_list_detail as $param)
		{
			if ($param["datatype"] == "dataobject") // TODO : add directly a query function into the dataobject, for example $param->update_datetime_get() ...
			{
				$query = db()->query("SELECT `datetime` FROM `_databank_update` WHERE databank_id='".$param["opt"]["databank"]."' AND `dataobject_id`='".$this->param[$param["name"]]->value_to_db()."' ORDER BY `datetime` DESC LIMIT 1");
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
				echo "<p>template($this->id)::cache_check() : Params not updated -> TRUE</p>\n";
			else
				echo "<p>template($this->id)::cache_check() : Params updated -> FALSE</p>\n";
		return $return;
	}
}

}

/**
 * Returns cached template file using $cache_id
 */
protected function cache_return()
{

if ($this->tpl)
{
	return $this->subtemplates_apply($this->tpl);
}
else
{
	if (APC_CACHE && TEMPLATE_CACHE_TYPE == "apc")
	{
		//echo "<p>template($this->id)::cache_return() using APC $this->cache_id</p>\n";
		return $this->subtemplates_apply(apc_fetch("tpl_$this->cache_id"));
	}
	else
	{
		//echo "<p>template($this->id)::cache_return() using cache file $this->cache_filename</p>\n";
		$filesize = filesize($this->cache_filename);
		$tpl = ($filesize>0) ? fread(fopen($this->cache_filename, "r"), $filesize) : "";
		return $this->subtemplates_apply($tpl);
	}
}

}

}

/**
 * Specific configuration for container (alias primary) templates
 * @author mathieu
 * 
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
if (TEMPLATE_CACHE_TYPE == "file")
{
	$this->cache_folder = PATH_CACHE."/".substr($this->cache_id,0,1);
	$this->cache_filename = "$this->cache_folder/$this->cache_id";
}

}

public function params_reset()
{

// We need to delete all params because in this type of template we don't know how many there are
$this->param = array();
foreach($this->param_list_detail as $param)
{
	$this->param[$param["name"]] = new data($param["name"], $param["value"], $param["label"]);
}

}

}

/**
 * Specific configuration for datamodel templates.
 * @author mathieu
 * 
 */
class template_datamodel extends template
{

protected $datamodel_id = null;
protected $object_id = null;
protected $object = null;

function object_set(data_bank_agregat $object)
{

$this->object_id = $object->id->value;
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
if (TEMPLATE_CACHE_TYPE == "file")
{
	$this->cache_folder = "cache/".substr($this->cache_id,0,1);
	$this->cache_filename = "$this->cache_folder/$this->cache_id";
}

}

/**
 * Vérifie l'obsolescence du fichier en cache
 */
protected function cache_check()
{

if (APC_CACHE && TEMPLATE_CACHE_TYPE == "apc")
{
	if (!($this->tpl=apc_fetch("tpl_$this->cache_id")))
	{
		return false;
	}
	// Fichier en cache trop récent
	elseif (!($cache_datetime=substr($this->tpl, 5, 10)) || ($cache_datetime+TEMPLATE_CACHE_MIN_TIME) < $time)
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
else
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

}

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
	if (OBJECT_CACHE)
	{
		if (!($GLOBALS["template_gestion"]=object_cache_retrieve("template_gestion")))
			$GLOBALS["template_gestion"] = new template_gestion();
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
