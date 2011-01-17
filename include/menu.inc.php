<?

/**
  * $Id$
  * 
  * Copyright 2008-2011 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [begin]");

/**
 * Gestion des menus
 */
class _menu_gestion extends gestion
{

protected $type = "menu";

protected function query_info_more()
{

$query = db()->query("SELECT `menu_id`, `pos`, `page_id` FROM `_menu_page_ref` ORDER BY `menu_id`, `pos`");
while (list($menu_id, $pos, $page_id)=$query->fetch_row())
{
	$this->list_detail[$menu_id]["list"][$pos] = $page_id;
}

}

}

/**
 * Menu
 */
class _menu extends object_gestion
{

protected $_type = "menu";

protected $list = array();

protected function query_info_more()
{

$this->list = array();
$query = db()->query("SELECT `_menu_page_ref`.`pos`, `_menu_page_ref`.`page_id` FROM `_menu_page_ref` WHERE `_menu_page_ref`.`menu_id` = '$this->id' ORDER BY `_menu_page_ref`.`pos`");
while (list($pos, $page_id)=$query->fetch_row())
{
	$this->list[$pos] = $page_id;
}

}

public function list_get()
{

return $this->list;

}

public function disp($method="", $options=array())
{

if (isset($options["popup"]))
	$popup = true;
else
	$popup = false;

if ($method == "table")
	$tagsep = "td";
elseif ($method == "ul")
	$tagsep = "li";
elseif ($method == "div")
	$tagsep = "span";
else // $method == "span"
	$tagsep = ""; // direct links

$return = "";
foreach ($this->list as $page_id) if (page()->exists($page_id))
{
	if ($popup)
		$link = "<a href=\"".page($page_id)->url()."\" onclick=\"info_show('".page($page_id)->url()."'); return false;\">".page($page_id)->info("shortlabel")."</a>";
	else
		$link = page($page_id)->link();
	if ($tagsep)
		$return .= "<$tagsep>$link</$tagsep>";
}

if ($method == "table")
	return "<table class=\"menu menu_$this->id\"><tr>$return</tr></table>";
elseif ($method == "ul")
	return "<ul class=\"menu menu_$this->id\">$return</ul>";
elseif ($method == "div")
	return "<div class=\"menu menu_$this->id\">$return</div>";
else // $method == "span"
	return "<span class=\"menu menu_$this->id\">$return</span>";

}

}


/*
 * Specific classes for admin
 */
if (defined("ADMIN_LOAD"))
{
	include PATH_INCLUDE."/admin/menu.inc.php";
}
else
{
	class menu_gestion extends _menu_gestion {};
	class menu extends _menu {};
}


/**
 * Access the menus
 *
 * @return menu_databank or menu
 */
function menu($id=null)
{

if (!isset($GLOBALS["menu_gestion"]))
{
	if (OBJECT_CACHE)
	{
		if (!($GLOBALS["menu_gestion"]=object_cache_retrieve("menu_gestion")))
			$GLOBALS["menu_gestion"] = new menu_gestion();
	}
	// Session
	else
	{
		if (!isset($_SESSION["menu_gestion"]))
			$_SESSION["menu_gestion"] = new menu_gestion();
		$GLOBALS["menu_gestion"] = $_SESSION["menu_gestion"];
	}
}

if ($id)
	return $GLOBALS["menu_gestion"]->get($id);
else
	return $GLOBALS["menu_gestion"];

}

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [end]");

?>
