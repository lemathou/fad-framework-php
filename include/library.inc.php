<?

/**
  * $Id$
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

protected function query_info_more()
{

foreach ($this->list_detail as &$library)
	$library["dep_list"] = array();

$query = db()->query("SELECT `id`, `parent_id` FROM `_library_ref`");
while (list($id, $parent_id) = $query->fetch_row())
	$this->list_detail[$id]["dep_list"][] = $parent_id;

}

protected function add_more($id, $library)
{

if (isset($library["dep_list"]) && is_array($library["dep_list"]) && (count($library["dep_list"]) > 0))
{
	$query_dep_list = array();
	foreach($library["dep_list"] as $library_id) if ($this->exists($library_id))
		$query_dep_list[] = "('$library_id', '$id')";
	if (count($query_library_list)>0)
	{
		$query_string = "INSERT INTO `_library_ref` (`parent_id`, `id`) VALUES ".implode(", ",$query_dep_list);
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

protected function del_more($id)
{

db()->query("DELETE FROM `_library_ref` WHERE `id`='$id'");

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
foreach($this->list as $library)
{
	if ($library->loaded())
		$return[] = "<li>".$library->get("name")." : <b>LOADED</b></li>";
	else
		$return[] = "<li>".$library->get("name")." : NOT LOADED</li>";
}
return "<ul>".implode("\n", $return)."</ul>";

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

protected $dep_list = array();
protected $loaded = false;

/*
 * Sauvegarde/Restauration de la session
 */
function __sleep()
{

return array("id", "name", "label", "description", "dep_list");

}

protected function query_info_more()
{

$query = db()->query("SELECT `parent_id` FROM `_library_ref` WHERE `id`='".$this->id."'");
$this->dep_list=array();
while (list($id) = $query->fetch_row())
	$this->dep_list[] = $id;

}

protected function update_more($infos)
{

if (isset($infos["dep_list"]))
{
	db()->query("DELETE FROM `_library_ref` WHERE `id`='$this->id'");
	if (is_array($infos["dep_list"]) && count($infos["dep_list"]))
	{
		$query_dep_list = array();
		foreach($infos["dep_list"] as $library_id) if (library()->exists($library_id))
			$query_library_list[] = "('$library_id', '$this->id')";
		if (count($query_library_list)>0)
			db()->query("INSERT INTO `_library_ref` (`parent_id`, `id`) VALUES ".implode(", ", $query_dep_list));
	}
}

if (isset($infos["filecontent"]))
{
	$filename = "library/$this->name.inc.php";
	fwrite(fopen($filename,"w"), htmlspecialchars_decode($infos["filecontent"]));
}

}

public function load()
{

if (!$this->loaded)
{
	$filename = PATH_LIBRARY."/$this->name.inc.php";
	if (file_exists($filename))
	{
		foreach($this->dep_list as $id)
			library()->load($id);
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

/**
 * Auto loading of datamodel class definitions
 */
function __autoload($class_name)
{

if (substr($class_name, -7) == "agregat" && ($name=substr($class_name, 0, -8)) && datamodel()->exists_name($name))
{
	datamodel()->{$name};
}

}

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
