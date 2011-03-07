<?php

/**
  * $Id: _session_refresh.js.php 28 2011-01-17 07:50:38Z lemathoufou $
  * 
  * Copyright 2008-2011 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * http://sourceforge.net/projects/phpfadframework/
  * 
  * Licence : http://www.gnu.org/copyleft/gpl.html  GNU General Public License
  * 
  */

include PATH_INCLUDE."/header.inc.php";

/* ACTION */

function action()
{

foreach($_GET as $i=>$j)
	$_POST[$i] = $j;

$login_id = login()->id();

$return = array();

if (isset($_POST["login"]))
{
	if ($login_id)
		$return[] = "\"login\": {\"id\": $login_id, \"name\": ".json_encode(login()->info("email"))."}";
	else
		$return[] = "\"login\": {\"id\": $login_id, \"name\": \"\"}";
}

if ($login_id && isset($_POST["messages"]))
{
	list($nb) = db()->query("SELECT COUNT(`id`) FROM `email` WHERE `compte_to`='$login_id' AND read_datetime='0000-00-00 00:00:00'")->fetch_row();
	$return[] =  "\"messages\": $nb";
}

if ($login_id && isset($_POST["coupons"]))
{
	list($nb) = db()->query("SELECT COUNT(`coupon_id`) FROM `coupon_recherche_results` WHERE `account_id`='$login_id'")->fetch_row();
	$return[] =  "\"coupons\": $nb";
}

echo implode(", ",$return);

}

?>
{<? action(); ?>}
