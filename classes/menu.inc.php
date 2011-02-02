<?php

/**
  * $Id: menu.inc.php 30 2011-01-18 23:29:06Z lemathoufou $
  * 
  * Copyright 2008-2011 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * http://sourceforge.net/projects/phpfadframework/
  * Licence : http://www.gnu.org/copyleft/gpl.html  GNU General Public License
  * 
  */

if (DEBUG_GENTIME == true)
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
	include PATH_FRAMEWORK."/classes/admin/menu.inc.php";
}
else
{
	class menu_gestion extends _menu_gestion {};
	class menu extends _menu {};
}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
