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

public function __construct()
{

$query = db()->query("SELECT id, name FROM _template");
while (list($id, $name)=$query->fetch_row())
{
	$this->list_id[$name] = $id;
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
function query($infos=array())
{

// Global infos
list($this->name, $this->container, $this->cache_mintime, $this->cache_maxtime, $this->login_dependant) =
	db()->query("SELECT `name`, `container`, `cache_mintime`, `cache_maxtime`, `login_dependant` FROM `_template` WHERE `id`='".$this->id."'")->fetch_row();
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
	$this->param_list_detail[$param[1]]["datatype"] = $param[2];
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
		foreach ($this->params as $name=>$value)
			${$name} = $value;
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
		foreach ($this->params as $name=>$value)
			${$name} = $value;
		ob_start();
		include "template/".$this->name.".tpl.php";
		$return = ob_get_contents();
		ob_end_clean();
		return $return;
}

}

/**
 * Execute scripts on params
 */
protected function params_check()
{

foreach ($this->param_list as $name)
	${$name} = &$this->params[$name];

foreach ($this->script_list as $script)
	if (file_exists("template/scripts/$script.inc.php"))
		include "template/scripts/$script.inc.php";

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

foreach ($this->params as $name=>$value)
	${$name} = $value;

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

$filesize = filesize($this->cache_filename);
$tpl = fread(fopen($this->cache_filename, "r"), ($filesize>0) ? $filesize : 1);

if (preg_match_all("/\[\[INCLUDE_TEMPLATE:(.*)\]\]/", $tpl, $matches, PREG_SET_ORDER))
{
	foreach($matches as $match)
	{
		if (DEBUG_CACHE)
			echo "<p>DEBUG : TEMPLATE(ID#$this->id)->cache_disp() : Found subtemplate $match[1]</p>";
		if ($id=template()->exists_name($match[1]))
		{
			if (DEBUG_CACHE)
				echo "<p>DEBUG : TEMPLATE(ID#$this->id)->cache_disp() : sending params to (sub)template ID#$id ($match[1])</p>\n"; 
			foreach($this->params as $name=>$value)
			{
				if (DEBUG_CACHE)
					echo "<p>--> $name : $value</p>\n";
				template($id)->__set($name, $value);
			}
			$tpl = str_replace($match[0], template($id), $tpl);
		}
	}
}

/*
 * Faire le cumul des last-modified sur l'ensemble des templates marqués comme intervenant dans ce calcul.
 */

//header('Status: 304 Not Modified', false, 304);
//header('Last-Modified: '.gmdate('D, d M Y H:i:s',filemtime($filename)).' GMT');
//header('Expires: '.gmdate('D, d M Y H:i:s',filemtime($filename)+60).' GMT');
header('Content-Length: '.strlen($tpl));
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
 * Special configuration optimized for datamodels.
 * @author mathieu
 *
 */
class template_datamodel extends template
{

protected $datamodel_id = 0;
protected $dataobject_id = 0;

function datamodel_set($id)
{

$this->datamodel_id = $id;

}

function object_set($id)
{

$this->dataobject_id = $id;

}

function object_retrieve_values()
{

foreach($this->param_list as $name)
{
	$this->params[$name] = databank($this->datamodel_id)->get($this->dataobject_id)->{$name};
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
