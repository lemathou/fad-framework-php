<?

/**
  * $Id: permission.inc.php 59 2009-03-03 15:48:26Z mathieu $
  * 
  * Copyright 2008 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */

/**
 * Global managing object for permissions
 * 
 * @author mathieu
 *
 */
class permission_gestion
{

protected $list = array();

function __construct()
{
}

}

/**
 * Permissions
 */
class permission
{

protected $id = null;
protected $name = "";
protected $label = "";

protected $list = array();

/**
 * 
 * @param int $id
 * @param bool $query
 * @param array $fields
 */
function __construct($id, $query=true, $fields=array())
{

$this->id = $id;

if ($query === true)
{
	$this->query_infos();
}

}

function query_infos()
{

$query = db()->query("SELECT t1.name, t2.label FROM _perm as t1 LEFT JOIN _perm_lang as t2 ON t1.id=t2.id AND t2.lang_id=".SITE_LANG_ID." WHERE t1.id = '$this->id'");
list($this->name , $this->label) = $query->fetch_row();

}

}

/**
 * Global access function
 */
function permission()
{

if (!isset($GLOBALS["permission_gestion"]))
{
	// APC
	if (APC_CACHE)
	{
		if (!($GLOBALS["permission_gestion"]=apc_fetch("permission_gestion")))
		{
			$GLOBALS["permission_gestion"] = new permission_gestion();
			apc_store("permission_gestion", $GLOBALS["permission_gestion"], APC_CACHE_GESTION_TTL);
		}
	}
	// Session
	else
	{
		if (!isset($_SESSION["permission_gestion"]))
			$_SESSION["permission_gestion"] = new permission_gestion();
		$GLOBALS["permission_gestion"] = $_SESSION["permission_gestion"];
	}
}

if ($id)
	return $GLOBALS["permission_gestion"]->get($id);
else
	return $GLOBALS["permission_gestion"];

}

?>