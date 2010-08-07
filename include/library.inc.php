<?

/**
  * $Id: library.inc.php 71 2009-03-18 18:09:34Z mathieu $
  * 
  * Copyright 2008 Mathieu Moulin - iProspective - lemathou@free.fr
  * 
  * This file is part of FTNGroupWare.
  * 
  */

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [begin]");

/**
 * Library gestion
 *
 */
class library_gestion
{

protected $list = array();

protected static $info_list = array ( "name" , "description" );

public function __construct()
{

$this->query();

}

protected function query()
{

$this->list = array();
$query = db()->query("SELECT `id` , `name` , `description` FROM `_library`");
while ($library = $query->fetch_assoc())
	$this->list[$library["id"]] = new library($library["id"], false, $library);

}


public function get($id)
{

if (isset($this->list[$id]))
{
	return $this->list[$id];
}
else
{
	die("TRYING TO ACCESS A INCONSISTENT LIBRARY");
}

}

public function load($id)
{

if (isset($this->list[$id]))
{
	$this->list[$id]->load();
}

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

public function list_get()
{

return $this->list;

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
protected $description="";

protected $list=array();

protected $loaded=false;

// Données à sauver en session
private $serialize_list = array( "id" , "name" , "description" , "list" );
public $serialize_save_list = array();

function __construct($id, $query=true, $infos=array())
{

$this->id = $id;

if (is_array($infos) && count($infos))
	while (list($i,$j) = each($infos))
		$this->{$i} = $j;

if ($query)
	$this->query;

}

public function query($infos = array())
{

$query = db()->query("SELECT `name` , `description` FROM `_library` WHERE `id`='".$this->id."'");
$infos = $query->fetch_array();
while (list($i,$j) = each($infos))
	$this->{$i} = $j;

$query = db()->query("SELECT `parent_id` FROM `_library_ref` WHERE `id`='".$this->id."'");
$this->list=array();
while (list($id) = $query->fetch_row())
	$this->list[] = $id;

}

public function load()
{

if (!$this->loaded)
{
	$filename = "library/$this->name.inc.php";
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

return session_select::__sleep($this->serialize_list);

}

function __wakeup()
{

session_select::__wakeup();
$this->loaded=false;

if (DEBUG_SESSION == true)
	echo "<p>WAKEUP : library id#$this->id</p>\n";

$this->load();

}

}

// Accès

function library($id=null)
{

if (!isset($GLOBALS["library_gestion"]) && !isset($_SESSION["library_gestion"]))
{
	if (DEBUG_SESSION == true)
		echo "<p>Creating library_gestion</p>\n";
	$GLOBALS["library_gestion"] = $_SESSION["library_gestion"] = new library_gestion();
}

if (is_numeric($id) && $id>0)
	return $GLOBALS["library_gestion"]->get($id);
else
	return $GLOBALS["library_gestion"];

}

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [end]");

?>