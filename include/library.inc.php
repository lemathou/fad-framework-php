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

protected $info_detail = array
(
	"name"=>array("label"=>"Nom (unique)", "type"=>"string", "size"=>64, "lang"=>false),
	"label"=>array("label"=>"Label", "type"=>"string", "size"=>128, "lang"=>true),
	"description"=>array("label"=>"Description", "type"=>"text", "lang"=>true),
	"dep_list"=>array("label"=>"Dépendances", "type"=>"object_list", "object_type"=>"library", "db_table"=>"_library_ref", "db_id"=>"id", "db_field"=>"parent_id"),
	"script"=>array("label"=>"Script", "type"=>"script", "folder"=>PATH_LIBRARY, "filename"=>"{name}.inc.php")
);

protected $retrieve_all = true;

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
