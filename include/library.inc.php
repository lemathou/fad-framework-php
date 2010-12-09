<?

/**
  * $Id: library.inc.php 71 2009-03-18 18:09:34Z mathieu $
  * 
  * Copyright 2008 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [begin]");

/**
 * Library gestion
 *
 */
class library_gestion extends gestion
{

protected $type = "library";

protected $retrieve_all = true;

public function add_more($id, $library)
{

if (isset($library["library_list"]) && is_array($library["library_list"]) && (count($library["library_list"]) > 0))
{
	$query_library_list = array();
	if (isset($library["library_list"]) == is_array($library["library_list"])) foreach($library["library_list"] as $library_id) if ($this->exists($library_id))
		$query_library_list[] = "('$library_id', '$id')";
	if (count($query_library_list)>0)
	{
		$query_string = "INSERT INTO `_library_ref` (`parent_id`, `id`) VALUES ".implode(", ",$query_library_list);
		db()->query($query_string);
	}
}
if (isset($library["filecontent"]))
{
	$filename = "library/$library[name].inc.php";
	if (!$library["filecontent"])
		$library["filecontent"] = "<?php\n\n\n?>";
	fwrite(fopen($filename,"w"), htmlspecialchars_decode($library["filecontent"]));
}
	
}

function del_more($id)
{

db()->query("DELETE FROM `_library_ref` WHERE `id` = '$id'");

$filename = "library/".$this->list_detail[$id]["name"].".inc.php";
if (file_exists($filename))
	unset($filename);

}

/**
 * Load a library
 * @param unknown_type $id
 */
public function load($id)
{

if (isset($this->list_detail[$id]))
	return $this->get($id)->load();
else
	return false;

}

/**
 * List of loaded libraries
 */
public function loaded_list()
{

$return = array();
while (list(,$library)=each($this->list))
{
	if ($library->loaded())
		$return[] = "<li>".$library->get("name")." : <b>LOADED</b></li>";
	else
		$return[] = "<li>".$library->get("name")." : NOT LOADED</li>";
}
return "<ul>".implode("\n",$return)."</ul>";

}

}

/**
 * Library
 * 
 * @author mathieu
 *
 */
class library extends object_gestion
{

protected $_type = "library";

protected $list = array();
protected $loaded = false;

// Données à sauver en session
private static $serialize_list = array("id", "name", "label", "description", "list");

function construct_more($infos)
{

$this->list = array();
if (isset($infos["library_list"]) && is_array($infos["library_list"]))
	foreach($infos["library_list"] as $id)
		$this->list[] = $id;

}

/**
 * Update library infos from a form
 * @param $infos
 */
function update_more($infos)
{

if (isset($infos["library_list"]) && is_array($infos["library_list"]))
{
	db()->query("DELETE FROM `_library_ref` WHERE `id`='$this->id'");
	$query_library_list = array();
	foreach($infos["library_list"] as $library_id) if (library()->exists($library_id))
		$query_library_list[] = "('$library_id', '$this->id')";
	if (count($query_library_list)>0)
		db()->query("INSERT INTO `_library_ref` (`parent_id`, `id`) VALUES ".implode(", ", $query_library_list));
}

if (isset($infos["filecontent"]))
{
	$filename = "library/$this->name.inc.php";
	fwrite(fopen($filename,"w"), htmlspecialchars_decode($infos["filecontent"]));
}

}

public function query_info_more()
{

$query = db()->query("SELECT `parent_id` FROM `_library_ref` WHERE `id`='".$this->id."'");
$this->list=array();
while (list($id) = $query->fetch_row())
	$this->list[] = $id;

}

public function load()
{

if (!$this->loaded)
{
	$filename = PATH_LIBRARY."/$this->name.inc.php";
	if (file_exists($filename))
	{
		foreach($this->list as $ref => $id)
		{
			library()->load($id);
		}
		include($filename);
		$this->loaded = true;
		if (DEBUG_LIBRARY == true)
			echo "<p>Library $this->name loaded</p>";
	}
	else
	{
		die("Library $this->name : file not found");
	}
}

}

public function loaded()
{
	
return $this->loaded;

}

/*
 * Sauvegarde/Restauration de la session
 */
function __sleep()
{

return session_select::__sleep(self::$serialize_list);

}

function __wakeup()
{

session_select::__wakeup();
$this->loaded = false;

if (DEBUG_SESSION == true)
	echo "<p>WAKEUP : library id#$this->id</p>\n";

//$this->load();

}

}


/**
 * Access function
 */
function library($id=0)
{

if (!isset($GLOBALS["library_gestion"]))
{
	// APC
	if (APC_CACHE)
	{
		if (!($GLOBALS["library_gestion"]=apc_fetch("library_gestion")))
		{
			$GLOBALS["library_gestion"] = new library_gestion();
			apc_store("library_gestion", $GLOBALS["library_gestion"], APC_CACHE_GESTION_TTL);
		}
	}
	// Session
	else
	{
		if (!isset($_SESSION["library_gestion"]))
			$_SESSION["library_gestion"] = new library_gestion();
		$GLOBALS["library_gestion"] = $_SESSION["library_gestion"];
	}
}

if ($id)
	return $GLOBALS["library_gestion"]->get($id);
else
	return $GLOBALS["library_gestion"];

}

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
