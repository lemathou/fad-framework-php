<?php

/**
  * $Id$
  * 
  * Copyright 2008-2011 Mathieu Moulin - lemathou@free.fr
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
class _library_gestion extends gestion
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

protected $retrieve_details = false;

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
foreach($this->list as $id=>$library)
{
	if ($library->loaded())
		$return[$id] = $library->label();
}
return $return;

}

}

/**
 * Library
 * 
 * @author mathieu
 *
 */
class _library extends object_gestion
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

if ($this->loaded === false)
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


/*
 * Specific classes for admin
 */
if (ADMIN_LOAD == true)
{
	include PATH_INCLUDE."/admin/library.inc.php";
}
else
{
	class library_gestion extends _library_gestion {};
	class library extends _library {};
}


/**
 * Access function
 */
function library($id=null)
{

if (!isset($GLOBALS["library_gestion"]))
{
	if (OBJECT_CACHE)
	{
		if (!($GLOBALS["library_gestion"]=object_cache_retrieve("library_gestion")))
			$GLOBALS["library_gestion"] = new library_gestion();
	}
	// Session
	else
	{
		if (!isset($_SESSION["library_gestion"]))
			$_SESSION["library_gestion"] = new library_gestion();
		$GLOBALS["library_gestion"] = $_SESSION["library_gestion"];
	}
	if (DEBUG_GENTIME == true)
		gentime("retrieve library()");
}

if (is_numeric($id))
	return $GLOBALS["library_gestion"]->get($id);
elseif (is_string($id))
	return $GLOBALS["library_gestion"]->get_name($id);
else
	return $GLOBALS["library_gestion"];

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
