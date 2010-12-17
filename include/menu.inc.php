<?

/**
  * $Id$
  * 
  * Copyright 2008 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [begin]");

/**
 * Gestion des menus
 */
class menu_gestion extends gestion
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
class menu extends object_gestion
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

public function add($page_id, $pos=null)
{

if (page()->exists($page_id))
{
	if (!is_numeric($pos) || $pos<0 || $pos>count($this->list))
		$pos = count($this->list);
	$this->list[] = $page_id;
	db()->query("INSERT INTO `_menu_page_ref` (`menu_id`, `page_id`, `pos`) VALUES ('$this->id', '$page_id', '$pos')");
}

}

public function del($pos)
{

if (isset($this->list[$pos]))
{
	unset($this->list[$pos]);
	db()->query("DELETE FROM `_menu_page_ref` WHERE `_menu_page_ref`.`menu_id` = '$this->id' AND `_menu_page_ref`.`pos`='$pos'");
	db()->query("UPDATE `_menu_page_ref` SET `pos`=`pos`-1 WHERE `_menu_page_ref`.`menu_id` = '$this->id' AND `_menu_page_ref`.`pos`>'$pos'");
}

}

public function pos_change($pos_from, $pos_to)
{

if (isset($this->list[$pos_from]) && isset($this->list[$pos_to]))
{
	$page_id = $this->list[$pos_from];
	db()->query("DELETE FROM `_menu_page_ref` WHERE `_menu_page_ref`.`menu_id` = '$this->id' AND `_menu_page_ref`.`pos`='$pos_from'");
	db()->query("UPDATE `_menu_page_ref` SET `pos`=`pos`-1 WHERE `_menu_page_ref`.`menu_id` = '$this->id' AND `_menu_page_ref`.`pos`>'$pos_from'");
	db()->query("UPDATE `_menu_page_ref` SET `pos`=`pos`+1 WHERE `_menu_page_ref`.`menu_id` = '$this->id' AND `_menu_page_ref`.`pos`>='$pos_to'");
	db()->query("INSERT INTO `_menu_page_ref` (`menu_id`, `page_id`, `pos`) VALUES ('$this->id', '$page_id', '$pos_to')");
	$this->query();
}

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

/**
 * Access the menus
 *
 * @return menu_databank or menu
 */
function menu($id=null)
{

if (!isset($GLOBALS["menu_gestion"]))
{
	// APC
	if (APC_CACHE)
	{
		if (!($GLOBALS["menu_gestion"]=apc_fetch("menu_gestion")))
		{
			$GLOBALS["menu_gestion"] = new menu_gestion();
			apc_store("menu_gestion", $GLOBALS["menu_gestion"], APC_CACHE_GESTION_TTL);
		}
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
