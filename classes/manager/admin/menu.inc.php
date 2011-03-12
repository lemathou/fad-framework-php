<?php

/**
  * $Id: menu.inc.php 27 2011-01-13 20:58:56Z lemathoufou $
  * 
  * Copyright 2008-2011 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [begin]");


class _menu_manager extends __menu_manager
{


};

class _menu extends __menu
{

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

};


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
