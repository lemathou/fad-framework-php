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

class permission_gestion extends gestion
{

protected $type = "permission";

protected $retrieve_all = true;

protected function query_info_more()
{

$query = db()->query("SELECT `perm_id`, `library_id`, `perm` FROM `_library_perm_ref`");
if ($query->num_rows())
{
	while (list($perm_id, $library_id, $perm) = $query->fetch_row())
		$this->list_detail[$perm_id]["library_perm"][$library_id] = $perm;
}

$query = db()->query("SELECT `perm_id`, `datamodel_id`, `perm` FROM `_datamodel_perm_ref`");
if ($query->num_rows())
{
	while (list($perm_id, $datamodel_id, $perm) = $query->fetch_row())
		$this->list_detail[$perm_id]["datamodel_perm"][$datamodel_id] = $perm;
}

$query = db()->query("SELECT `perm_id`, `datamodel_id`, `object_id`, `perm` FROM `_dataobject_perm_ref`");
if ($query->num_rows())
{
	while (list($perm_id, $datamodel_id, $object_id, $perm) = $query->fetch_row())
		$this->list_detail[$perm_id]["dataobject_perm"][$datamodel_id][$object_id] = $perm;
}

$query = db()->query("SELECT `perm_id`, `template_id`, `perm` FROM `_template_perm_ref`");
if ($query->num_rows())
{
	while (list($perm_id, $template_id, $perm) = $query->fetch_row())
		$this->list_detail[$perm_id]["template_perm"][$template_id] = $perm;
}

$query = db()->query("SELECT `perm_id`, `page_id`, `perm` FROM `_page_perm_ref`");
if ($query->num_rows())
{
	while (list($perm_id, $page_id, $perm) = $query->fetch_row())
		$this->list_detail[$perm_id]["page_perm"][$page_id] = $perm;
}

$query = db()->query("SELECT `perm_id`, `menu_id`, `perm` FROM `_menu_perm_ref`");
if ($query->num_rows())
{
	while (list($perm_id, $menu_id, $perm) = $query->fetch_row())
		$this->list_detail[$perm_id]["menu_perm"][$menu_id] = $perm;
}

}

}

/**
 * Permissions
 */
class permission extends object_gestion
{

protected $_type = "permission";

//protected $list = array();
protected $library_perm = array();
protected $datamodel_perm = array();
protected $dataobject_perm = array();
protected $template_perm = array();
protected $page_perm = array();
protected $menu_perm = array();

protected static $serialize_list = array("id", "name", "label", "description", "library_perm", "datamodel_perm", "dataobject_perm", "template_perm", "page_perm", "menu_perm");

function __sleep()
{

return session_select::__sleep(self::$serialize_list);

}

protected function query_info_more()
{

$this->library_perm = array();
$query = db()->query("SELECT `library_id`, `perm` from `_library_perm_ref` WHERE `perm_id` = '$this->id'");
if ($query->num_rows())
{
	while (list($library_id, $perm) = $query->fetch_row())
		$this->library_perm[$library_id] = $perm;
}

$this->datamodel_perm = array();
$query = db()->query("SELECT `datamodel_id`, `perm` from `_datamodel_perm_ref` WHERE `perm_id` = '$this->id'");
if ($query->num_rows())
{
	while (list($datamodel_id, $object_id, $perm) = $query->fetch_row())
		$this->datamodel_perm[$datamodel_id][$object_id] = $perm;
}

$this->dataobject_perm = array();
$query = db()->query("SELECT `datamodel_id`, `object_id`, `perm` from `_dataobject_perm_ref` WHERE `perm_id` = '$this->id'");
if ($query->num_rows())
{
	while (list($datamodel_id, $object_id, $perm) = $query->fetch_row())
		$this->dataobject_perm[$datamodel_id][$object_id] = $perm;
}

$this->template_perm = array();
$query = db()->query("SELECT `template_id`, `perm` from `_template_perm_ref` WHERE `perm_id` = '$this->id'");
if ($query->num_rows())
{
	while (list($template_id, $perm) = $query->fetch_row())
		$this->template_perm[$template_id] = $perm;
}

$this->page_perm = array();
$query = db()->query("SELECT `page_id`, `perm` from `_page_perm_ref` WHERE `perm_id` = '$this->id'");
if ($query->num_rows())
{
	while (list($page_id, $perm) = $query->fetch_row())
		$this->page_perm[$page_id] = $perm;
}

$this->menu_perm = array();
$query = db()->query("SELECT `menu_id`, `perm` from `_menu_perm_ref` WHERE `perm_id` = '$this->id'");
if ($query->num_rows())
{
	while (list($menu_id, $perm) = $query->fetch_row())
		$this->menu_perm[$menu_id] = $perm;
}

}

function datamodel($id)
{

if (isset($this->datamodel_perm[$id]))
	return $this->datamodel_perm[$id];
else
	return false;

}

function dataobject($datamodel_id, $object_id)
{

if (isset($this->dataobject_perm[$datamodel_id][$object_id]))
	return $this->dataobject_perm[$datamodel_id][$object_id];
else
	return false;

}

function template($id)
{

if (isset($this->template_perm[$id]))
	return $this->template_perm[$id];
else
	return false;

}

function page($id)
{

if (isset($this->page_perm[$id]))
	return $this->page_perm[$id];
else
	return false;

}

function menu($id)
{

if (isset($this->menu_perm[$id]))
	return $this->menu_perm[$id];
else
	return false;

}

}

/**
 * Object used to retrieve permissions
 */
class permission_info
{

protected $list = array
(
	"i"=>false,
	"l"=>false,
	"r"=>false,
	"u"=>false,
	"d"=>false,
	"a"=>false
);

function get($name)
{

if (isset($this->list[$name]))
	return $this->list[$name];
else
	return null;

}

function __construct($list=null)
{

$this->update($list);

}

function update($list)
{

if (is_array($list))
{
	foreach($list as $i=>$j)
	{
		if (isset($this->list[$i]))
		{
			if ($j)
				$this->list[$i] = true;
			else
				$this->list[$i] = false;
		}
	}
}
elseif (is_string($list))
{
	foreach($this->list as $i=>$j)
	{
		if (strpos($list, $i) !== false)
			$this->list[$i] = true;
	}
}

}
function update_str($list)
{

if (is_string($list))
	foreach($this->list as $i=>$j)
		if (strpos($list, "+$i") !== false)
			$this->list[$i] = true;
		elseif (strpos($list, "-$i") !== false)
			$this->list[$i] = false;

}

function __tostring()
{

$return = "";
foreach ($this->list as $i=>$j)
{
	if ($j)
		$return .= "$i";
}
return $return;

}

function perm_list()
{

return $this->list;

}

}

/**
 * Global access function
 */
function permission($id=0)
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
