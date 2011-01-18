<?php

/**
  * $Id: gestion.inc.php 27 2011-01-13 20:58:56Z lemathoufou $
  * 
  * Copyright 2008-2011 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * http://sourceforge.net/projects/phpfadframework/
  * 
  * Licence : http://www.gnu.org/copyleft/gpl.html  GNU General Public License
  * 
  */

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [begin]");


/**
 * Database gestion
 * Default object data bank
 * @author mathieu
 */
abstract class _gestion
{

protected $type = "";

protected $list = array();
protected $list_detail = array();
protected $list_name = array();

// Data in main database table
protected $info_list = array("name"); // Keep at least name ! This is a unique string key
// Data in lang database table
protected $info_lang_list = array("label", "description"); // Keep at least label
// Detailled data (with type, etc.)
protected $info_detail = array // Keep at least name and label !
(
	"name"=>array("label"=>"Nom (unique)", "type"=>"string", "size"=>64, "lang"=>false),
	"label"=>array("label"=>"Label", "type"=>"string", "size"=>128, "lang"=>true),
	"description"=>array("label"=>"Description", "type"=>"text", "lang"=>true)
);
// Required info
protected $info_required = array("name", "label");

// Retrieve all objects on first load
protected $retrieve_objects = false;
// Keep all non required info after first load
protected $retrieve_details = true;

public function info_list()
{

return $this->info_list;

}
public function info_lang_list()
{

return $this->info_lang_list;

}
public function info_detail_list()
{

return $this->info_detail;

}

/**
 * Sauvegarde/Restauration de la session
 */
function __sleep()
{

if ($this->retrieve_objects)
	return array("list_detail", "list");
else
	return array("list_detail");

}
function __wakeup()
{

foreach($this->list_detail as $id=>$info)
	$this->list_name[$info["name"]] = $id;

}

function __construct()
{

$this->query_info();
$this->construct_more();

}
protected function construct_more()
{

// To be extended if needed !

}

/**
 * Query required info
 */
function query_info($retrieve_objects=false)
{

$this->list = array();
$this->list_name = array();
$this->list_detail = array();

$query_objects = array();
$query_fields = array("`t1`.`id`");
foreach ($this->info_detail as $name=>$field)
{
	if (isset($field["lang"]))
	{
		if ($field["lang"])
			$query_fields[] = "`t2`.`$name`";
		else
			$query_fields[] = "`t1`.`$name`";
	}
	elseif ($field["type"] == "object_list")
	{
		$query_objects[] = $name;
	}
}

$query_string = "SELECT ".implode(", ", $query_fields)." FROM `_$this->type` as t1 LEFT JOIN `_".$this->type."_lang` as t2 ON t1.`id`=t2.`id` AND t2.`lang_id`='".SITE_LANG_ID."'";
$query = db()->query($query_string);
while($info = $query->fetch_assoc())
{
	$this->list_detail[$info["id"]] = $info;
	$this->list_name[$info["name"]] = $info["id"];
	foreach ($query_objects as $name)
		$this->list_detail[$info["id"]][$name] = array();
}

foreach ($query_objects as $name)
{
	$field = $this->info_detail[$name];
	$query = db()->query("SELECT `$field[db_id]`, `$field[db_field]` FROM `$field[db_table]`");
	while (list($id, $object_id)=$query->fetch_row())
	{
		if (isset($this->list_detail[$id]))
			$this->list_detail[$id][$name][] = $object_id;
	}
}

$this->query_info_more();

// TEMPORARY HACK so retrieve_objects() does not become silly ^^
if (!$this->retrieve_details)
{
	$rd = false;
	$this->retrieve_details = true;
}
else
	$rd = true;

if ($retrieve_objects || $this->retrieve_objects)
{
	$this->retrieve_objects();
}
// generate objects in cache in some cases to optimise website
if (!$this->retrieve_objects && !$rd && OBJECT_CACHE)
{
	foreach($this->list_detail as $id=>$info)
	{
		object_cache_store($this->type."_$id", $this->construct_object($id), OBJECT_CACHE_GESTION_TTL);
	}
}
if (!$rd)
{
	$this->retrieve_details = false;
	foreach ($this->list_detail as $id=>$info)
	{
		$this->list_detail[$id] = array();
		foreach ($this->info_required as $name)
			$this->list_detail[$id][$name] = $info[$name];
	}
}

if (OBJECT_CACHE)
	object_cache_store($this->type."_gestion", $this, OBJECT_CACHE_GESTION_TTL);

}
protected function query_info_more()
{

// To be extended if needed !

}

/**
 * Constructs an object
 */
protected function construct_object($id)
{

$type = $this->type;

if ($this->retrieve_details)
	return new $type($id, false, $this->list_detail[$id]);
else
	return new $type($id, true);

}

/**
 * Retrieve all objects
 */
public function retrieve_objects()
{

$type = $this->type;
foreach ($this->list_detail as $id=>$info)
{
	if (!isset($this->list[$id]))
		$this->list[$id] = $this->construct_object($id);
}

}

/**
 * Returns an object using its ID
 * @param int $id
 */
function get($id)
{

if (!is_numeric($id))
	return null;

//echo $id;

if (isset($this->list[$id]))
{
	return $this->list[$id];
}
elseif (!$this->retrieve_objects && OBJECT_CACHE && ($object=object_cache_retrieve($this->type."_$id")))
{
	return $this->list[$id] = $object;
}
elseif (!$this->retrieve_objects && isset($this->list_detail[$id]))
{
	$object = $this->construct_object($id);
	if (OBJECT_CACHE)
		object_cache_store($this->type."_$id", $object, OBJECT_CACHE_GESTION_TTL);
	return $this->list[$id] = $object;
}
else
{
	return null;
}

}

/**
 * Retrieve an object using its unique name
 * @param unknown_type $name
 */
function __get($name)
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
function get_name($name)
{

return $this->__get($name);

}

/**
 * Returns if an object exists
 * @param int $id
 */
function exists($id)
{

return isset($this->list_detail[$id]);

}
/**
 * 
 * Returns if an object exists using its unique name
 * @param string $name
 */
function __isset($name)
{

return isset($this->list_name[$name]);

}
function exists_name($name)
{

return $this->__isset($name);

}

/**
 * Returns the list
 */
public function list_get()
{

return $this->list;

}
public function list_name_get($name=null)
{

if ($name)
	return $this->list_name[$name];
else
	return $this->list_name;

}
public function list_detail_get($id=null)
{

if ($id)
	return $this->list_detail[$id];
else
	return $this->list_detail;

}

}

/**
 * Default object type
 */
abstract class _object_gestion
{

protected $_type = "";

protected $id=0;
protected $name="";
protected $label="";
protected $description="";

public function __construct($id, $query=true, $infos=array())
{

$type = $this->_type;
$this->id = $id;

foreach ($infos as $name=>$value)
	if (isset($this->{$name}))
		$this->{$name} = $value;

$this->construct_more($infos);

if ($query)
	$this->query_info();

}
protected function construct_more($infos)
{

// To be extended if needed !

}

/**
 * Query required info
 */
function query_info()
{

$type = $this->_type;
$info_list = $type()->info_list();
$info_lang_list = $type()->info_lang_list();

$query_fields = array("`t1`.`id`");
foreach($info_list as $field)
	$query_fields[] = "`t1`.`$field`";
foreach($info_lang_list as $field)
	$query_fields[] = "`t2`.`$field`";

$query_string = "SELECT ".implode(", ", $query_fields)." FROM `_$type` as t1 LEFT JOIN `_".$type."_lang` as t2 ON t1.`id`=t2.`id` AND t2.`lang_id`='".SITE_LANG_ID."' WHERE t1.id='$this->id'";
$query = db()->query($query_string);
while($infos = $query->fetch_assoc())
{
	foreach($infos as $name=>$value)
		$this->{$name} = $value;
}

$this->query_info_more();

if (OBJECT_CACHE)
	object_cache_store($this->_type."_$this->id", $this, OBJECT_CACHE_GESTION_TTL);

}
protected function query_info_more()
{

// To be extended if needed

}

function __tostring()
{

return "[$this->type][ID#$this->id] $this->name : $this->label";

}
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

return $this->label;

}
public function info($name)
{

if (isset($this->{$name}))
	return $this->{$name};

}

}

/*
 * Specific classes for admin
 */
if (defined("ADMIN_LOAD"))
{
	include PATH_INCLUDE."/admin/gestion.inc.php";
}
else
{
	class gestion extends _gestion {};
	class object_gestion extends _object_gestion {};
}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>