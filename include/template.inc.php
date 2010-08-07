<?

/**
  * $Id: template.inc.php 76 2009-10-15 09:24:20Z mathieu $
  * 
  * Copyright 2008 Mathieu Moulin - iProspective - lemathou@free.fr
  * 
  * This file is part of FTNGroupWare.
  * 
  */


if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [begin]");

class template_gestion
{

protected $default_id = 0;
protected $list = array();
protected $list_id = array();
protected $list_name = array();

public function __construct()
{

$query = db()->query("SELECT id, name FROM _template");
while (list($id, $name)=$query->fetch_row())
{
	$this->list_id[$name] = $id;
	$this->list_name[$id] = $name;
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
elseif ($this->exists($id))
{
	if (substr($this->list_name[$id], 0, 9) == "datamodel")
		return $this->list[$id] = new template_datamodel($id);
	else
		return $this->list[$id] = new template($id);
}
else
{
	return false;
}

}

function exists($id)
{

if (in_array($id, $this->list_id))
	return true;
else
	return false;

}
function exists_name($name)
{

if (isset($this->list_id[$name]))
	return $this->list_id[$name];
else
	return false;

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
 * Effective values of params
 */
protected $params = array();

/*
 * Filename of the PHP source template file
 */
protected $tpl_filename = "";

/*
 * Ordered list of scripts to be executed before displaying the template
 * For example :
 * - to control values and change if wrong,
 * - to set a message to be displayed,
 * - etc.
 * A good control can save a lot of space in cache folder, preventing from sending inoperant params.
 */
protected $script_list = array();

/*
 * Acceptation de TOUTES les variables (attention danger)
 * Utiliser avec parcimonie (voire pas du tout) les variables dans ce type de template,
 * et préférer leur utilisation dans des sous-templates dédiés à des tâches (header, contenu, footer, etc.) 
 */
protected $container = 0;

/*
 * Cache related infos
 */
protected $cache_id = "";
protected $cache_folder = "";
protected $cache_filename = "";
protected $cache_mintime = 0;
protected $cache_maxtime = 0;
protected $login_dependant = 0;

function __construct($id, $query=true, $params=array())
{

$this->id = $id;

if ($query)
	$this->query();
if (is_array($params) && count($params)>0)
	foreach ($params as $name=>$value)
			$this->params[$name] = $value;

// Voir si c'est bien utile, je pense que oui pour certaines biblio de fonctions
//$this->library_query();
//$this->library_load();

}

/**
 * Query required infos on template before using it
 * @param unknown_type $infos
 */
protected function query($infos=array())
{

// Global infos
list($this->type, $this->name, $this->container, $this->cache_mintime, $this->cache_maxtime, $this->login_dependant) =
	db()->query("SELECT `type`, `name`, `container`, `cache_mintime`, `cache_maxtime`, `login_dependant` FROM `_template` WHERE `id`='".$this->id."'")->fetch_row();
$this->tpl_filename = "template/".$this->name.".tpl.php";
// Params
$this->param_list = array();
$this->param_list_detail = array();
$this->params = array();
$query = db()->query("SELECT `order`, `name`, `datatype`, `defaultvalue` FROM `_template_params` WHERE `template_id`='".$this->id."' ORDER BY `order` ASC");
while ($param = $query->fetch_row())
{
	if (DEBUG_TEMPLATE)
		echo "<p>TEMPLATE->QUERY ID#$this->id : PARAM $param[1]</p>\n";
	$this->param_list[$param[0]] = $param[1];
	$this->param_list_detail[$param[1]] = array("datatype"=>$param[2], "value"=>$param[3]);
	$this->params[$param[1]] = $param[3];
}
$query = db()->query("SELECT `name`, `opttype`, `optname`, `optvalue` FROM `_template_params_opt` WHERE `template_id`='".$this->id."'");
while ($param = $query->fetch_row())
{
	$this->param_list_detail[$param[0]][$param[1]][$param[2]]=$param[3];
}
// Script list
$this->script_list = array();
$query = db()->query("SELECT `name` FROM `_template_scripts` WHERE `template_id`='".$this->id."'");
while (list($name) = $query->fetch_row())
{
	$this->script_list[] = $name;
}

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
 * Load the libraries associated to the template
 */
function library_load()
{

foreach ($this->library_list as $library_id)
{
	if (DEBUG_LIBRARY == true)
		echo "<p>Loading library ID#$library_id from template ID#$this->id</p>\n";
	library($library_id)->load();
}

}

/**
 * Assignment of params
 */
public function __set($name, $value)
{
	if ($this->container || in_array($name, $this->param_list))
	{
		if (DEBUG_TEMPLATE)
			echo "<p>DEBUG : TEMPLATE($this->id)->__set : $name : $value</p>\n";
		$this->params[$name] = $value;
	}
	else
	{
		if (DEBUG_TEMPLATE)
			echo "<p>DEBUG : TEMPLATE($this->id)->__set NOT DEFINED : $name</p>\n";
	}
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
		foreach ($this->params as $_name=>$_value)
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
		foreach ($this->params as $_name=>$_value)
			${$_name} = $_value;
		ob_start();
		include "template/".$this->name.".tpl.php";
		$return = ob_get_contents();
		ob_end_clean();
		return $return;
}

}

/**
 * Reset params to default value
 */
function params_reset()
{

$this->params = array();
foreach($this->param_list_detail as $name=>$param)
{
	//echo "<p>$name</p>";
	$this->params[$name] = $param["value"];
}

}

/**
 * Execute scripts on params
 */
protected function params_check()
{

if (count($this->script_list))
{
	foreach ($this->param_list as $_name)
		${$_name} = $this->params[$_name];
	foreach ($this->script_list as $_script)
	{
		//echo "<p>TEMPLATE ID#$this->id : INCLUDING script \"$script\"</p>";
		if (file_exists("template/scripts/$_script.inc.php"))
			include "template/scripts/$_script.inc.php";
	}
	foreach ($this->param_list as $_name)
		$this->params[$_name] = ${$_name};
}

}

/*
 * Set template hash from params (cache ID) and folder to save/retrieve it
 */
protected function cache_id_set()
{

$params_str = "'$this->id'";
if ($this->container)
{
	if ($this->login_dependant)
		$params_str .= ",_account='".login()->id()."'";
	foreach($this->params as $name=>$value)
	{
		$params_str .= ",$name='".addslashes(json_encode($value))."'";
	}
}
else
{
	if ($this->login_dependant)
		$params_str .= ",_account='".login()->id()."'";
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
$this->cache_folder = "cache/".substr($this->cache_id,0,1);
$this->cache_filename = "$this->cache_folder/$this->cache_id";

}

/**
 * Regenerate cache file
 */
protected function cache_generate()
{

foreach ($this->params as $_name=>$_value)
{
	${$_name} = $_value;
}

ob_start();
include $this->tpl_filename;
fwrite(fopen($this->cache_filename,"w"), ob_get_contents());
ob_end_clean();

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
	$return = true;
	foreach($this->param_list_detail as $name=>$param)
	{
		if ($param["datatype"] == "dataobject")
		{
			$query = db()->query("SELECT `datetime` FROM `_databank_update` WHERE databank_id='".$param["structure"]["databank"]."' AND `dataobject_id`='".$this->params[$name]."' ORDER BY `datetime` DESC LIMIT 1");
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

$filesize = filesize($this->cache_filename);
$tpl = fread(fopen($this->cache_filename, "r"), ($filesize>0) ? $filesize : 1);

if (preg_match_all("/\[\[INCLUDE_TEMPLATE:(.*)\]\]/", $tpl, $matches, PREG_SET_ORDER))
{
	foreach($matches as $match)
	{
		if ($id=template()->exists_name($match[1]))
		{
			if (DEBUG_TEMPLATE)
				echo "<p>DEBUG : TEMPLATE(ID#$this->id)->cache_return() sending params to (sub)template ID#$id</p>\n";
			// TODO : VOIR SI PB DE RECURSION !!!!
			template($id)->params_reset();
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

if (preg_match_all("/\[\[INCLUDE_DATAMODEL:(.*),(.*),(.*)\]\]/", $tpl, $matches, PREG_SET_ORDER))
{
	foreach($matches as $match)
	{
		if ($id=template()->exists_name("datamodel/$match[1]"))
		{
			if (DEBUG_TEMPLATE)
				echo "<p>DEBUG : TEMPLATE(ID#$this->id)->cache_return() sending params to (sub)template ID#$id</p>\n";
			template($id)->params_reset();
			template($id)->object_set(databank($match[2])->get($match[3], true));
			$tpl = str_replace($match[0], template($id), $tpl);
		}
	}
}

return $tpl;

}

function get($name)
{

if (isset($this->params[$name]))
{
	return $this->params[$name];
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
	if ($this->login_dependant)
		$params_str .= ",_account='".login()->id()."'";
	foreach($this->params as $name=>$value)
	{
		$params_str .= ",$name='".addslashes(json_encode($value))."'";
	}
}
else
{
	if ($this->login_dependant)
		$params_str .= ",_account='".login()->id()."'";
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
protected function cache_generate()
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
class template_emcached extends template
{



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

function object_retrieve_values()
{
	
$this->params = array();
foreach(datamodel($this->object->datamodel()->id())->fields() as $field)
{
	$this->params[$field->name] = $this->object->{$field->name};
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
		foreach ($this->params as $_name=>$_value)
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
		foreach ($this->params as $_name=>$_value)
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
 * Accès aux templates
 * 
 * @param integer $id
 */
function template($id=0)
{

if (!isset($GLOBALS["template_gestion"]))
{
	$GLOBALS["template_gestion"] = $_SESSION["template_gestion"] = new template_gestion();
}

if (is_numeric($id) && $id>0)
	return $GLOBALS["template_gestion"]->get($id);
else
	return $GLOBALS["template_gestion"];

}

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [end]");

?>