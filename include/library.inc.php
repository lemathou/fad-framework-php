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

/**
 * Retrieve infos from database
 */
public function query_info()
{

$this->list = array();
$this->list_detail = array();
$this->list_name = array();
$query = db()->query("SELECT `_library`.`id` , `_library`.`name` , `_library`.`description`, `_library_lang`.`label` FROM `_library` LEFT JOIN `_library_lang` ON `_library`.`id`=`_library_lang`.`id` AND `_library_lang`.`lang_id`='".SITE_LANG_ID."'");
while ($library = $query->fetch_assoc())
{
	$this->list_detail[$library["id"]] = $library;
	$this->list_name[$library["name"]] = $library["id"];
/*
	if (LIBRARY_AUTOLOADALL)
		$this->list[$library["id"]] = new library($library["id"], false, $library); // Masq only if there is too much unused libraries in many pages
*/
}

}

/**
 * Add a library
 * @param array $library
 */
public function add($library)
{

if (!login()->perm(6))
	die("ONLY ADMIN CAN ADD LIBRARY");

$query_string = "INSERT INTO `_library` (`name`, `description`) VALUES ('".db()->string_escape($library["name"])."', '".db()->string_escape($library["description"])."')";
$query = db()->query($query_string);

if (($id=$query->last_id()))
{
	$query_string = "INSERT INTO `_library_lang` (`id`, `lang_id`, `label`) VALUES ('$id', '".SITE_LANG_ID."', '".db()->string_escape($library["label"])."')";
	$query = db()->query($query_string);
	if (isset($library["library_list"]) && is_array($library["library_list"]) && (count($library["library_list"]) > 0))
	{
		$query_perm_list = array();
		foreach($library["library_list"] as $library_id) if ($this->exists($library_id))
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
	$this->query_info();
	if (APC_CACHE)
	{
		apc_store("library_gestion", $this, APC_CACHE_GESTION_TTL);
	}
	return $id;
}
else
{
	return null;
}

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
class library extends session_select
{

protected $id=0;
protected $name="";
protected $label="";
protected $description="";

protected $list=array();

protected $loaded=false;

protected static $infos = array("name", "description");
protected static $infos_lang = array("label");

// Données à sauver en session
private static $serialize_list = array("id", "name", "label", "description", "list");
public $serialize_save_list = array();

function __construct($id, $query=true, $infos=array())
{

$this->id = $id;

foreach($infos as $i=>$j)
	$this->{$i} = $j;

$this->query_dep();

}

/**
 * Update library infos from a form
 * @param $infos
 */
function update($infos)
{

foreach ($infos as $name=>$value)
{
	if (in_array($name, array_merge(self::$infos, self::$infos_lang)))
	{
		$this->{$name} = $value;
	}
}

if (isset($infos["library_list"]) && is_array($infos["library_list"]))
{
	$this->list = array();
	foreach($infos["library_list"] as $library_id)
	{
		if (library()->exists($library_id))
			$this->list[] = $library_id;
	}
}

if (isset($infos["filecontent"]))
{
	$filename = "library/$this->name.inc.php";
	fwrite(fopen($filename,"w"), htmlspecialchars_decode($infos["filecontent"]));
}

$this->db_update();

}

/**
 * Update library info in database
 */
function db_update()
{

if (APC_CACHE)
{
	if (LIBRARY_AUTOLOADALL)
		apc_store("library_gestion", library(), APC_CACHE_GESTION_TTL);
	else
		apc_store("library_$this->id", $this, APC_CACHE_GESTION_TTL);
}

db()->query("UPDATE `_library` SET `name`='".addslashes($this->name)."', `description`='".addslashes($this->description)."' WHERE `id`='$this->id'");
db()->query("UPDATE `_library_lang` SET `label`='".addslashes($this->label)."' WHERE `id`='$this->id' AND `lang_id`='".SITE_LANG_ID."'");
db()->query("DELETE FROM `_library_ref` WHERE `id`='$this->id'");
if (count($this->list))
{
	$query_library_list = array();
	foreach($this->list as $library_id)
		$query_library_list[] = "('$library_id', '$this->id')";
	if (count($query_library_list)>0)
		db()->query("INSERT INTO `_library_ref` (`parent_id`, `id`) VALUES ".implode(", ",$query_library_list));
}

}

public function query_dep($infos = array())
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

public function __get($name)
{

return $this->{$name};

}

public function loaded()
{
	
if ($this->loaded)
	return true;
else
	return false;

}

function __tostring()
{

return "$this->name : $this->description";

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
$this->loaded=false;

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