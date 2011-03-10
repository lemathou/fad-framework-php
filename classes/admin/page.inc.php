<?php

/**
  * $Id: page.inc.php 76 2009-10-15 09:24:20Z mathieu $
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


class _page_gestion extends __page_gestion
{



}

class _page extends __page
{

/**
 * Add a param
 * @param unknown_type $name
 * @param unknown_type $infos
 */
public function param_add($name, $infos)
{

if (!is_string($name) || isset($this->param_list[$name]))
	return false;

if (!is_array($infos))
	$infos = array();
elseif (!isset($infos["value"]) || !is_string($infos["value"]))
	$infos["value"] = null;
elseif (!isset($infos["update_pos"]) || !is_string($infos["update_pos"]))
	$infos["update_pos"] = null;
elseif (!isset($infos["datatype"]) || !is_string($infos["datatype"]))
	$infos["datatype"] = null;

db()->query("INSERT INTO `_page_params` (`page_id`, `name`, `datatype`, `value`, `update_pos`) VALUES ('$this->id', '$name', '".db()->string_escape($infos["datatype"])."', '".db()->string_escape(json_encode(json_decode($infos["value"])))."', NULL)");

// Position
if (is_numeric($pos=$infos["update_pos"]))
{
	$pos_max = count($this->params_url);
	if ($pos < 0 || $pos > $pos_max)
		$pos = $pos_max;
	if ($pos < $pos_max)
		db()->query("UPDATE `_page_params` SET `update_pos`=`update_pos`+1 WHERE `page_id`='$this->id' AND `update_pos` >= $pos");
	db()->query("UPDATE `_page_params` SET `update_pos`='$pos' WHERE `page_id`='$this->id' AND `name`='$name'");
}

$this->query_params();
$this->construct_params();

return true;

}
/**
 * Update a param
 * @param unknown_type $name
 * @param unknown_type $infos
 */
public function param_update($name, $infos)
{

//var_dump($infos);

if (!is_string($name) || !array_key_exists($name, $this->param_list))
	return false;
if (!is_array($infos))
	$infos = array();

$l = array("value", "update_pos", "datatype");
$update_list = array();
foreach($infos as $i=>$j) if(in_array($i, $l) && is_string($j))
{
	if ($i == "update_pos")
		$update_list[] = "`$i`=NULL";
	else
		$update_list[] = "`$i`='".db()->string_escape($j)."'";
}

if (count($update_list))
{
	$query_string = "UPDATE `_page_params` SET ".implode(", ", $update_list)." WHERE `page_id`='$this->id' AND `name`='$name'";
	db()->query($query_string);
	//echo "<p>$query_string : ".mysql_error()."</p>\n";
}
if ($n=array_search($name, $this->params_url))
{
	db()->query("UPDATE `_page_params` SET `update_pos`=`update_pos`-1 WHERE `page_id`='$this->id' AND `update_pos` >= $n");
}
if (isset($infos["update_pos"]) && is_numeric($n=$infos["update_pos"]))
{
	db()->query("UPDATE `_page_params` SET `update_pos`=`update_pos`+1 WHERE `page_id`='$this->id' AND `update_pos` >= $n");
	db()->query("UPDATE `_page_params` SET `update_pos`='$n' WHERE `page_id`='$this->id' AND `name`='$name'");
}

if (isset($infos["opt"]))
{
	db()->query("DELETE FROM `_page_params_opt` WHERE `page_id`='$this->id' AND `name` = '$name'");
	if (is_array($infos["opt"])) foreach($infos["opt"] as $i=>$j)
	{
		db()->query("INSERT INTO `_page_params_opt` (`page_id`, `name`, `optname`, `optvalue`) VALUES ('$this->id', '$name', '$i', '".db()->string_escape($j)."')");
	}
}

$this->query_params();
$this->construct_params();
page()->query_info();

return true;

}
/**
 * Delete a param
 * @param $name
 */
public function param_del($name)
{

if (!is_string($name) || !array_key_exists($name, $this->param_list))
	return false;

db()->query("DELETE FROM `_page_params` WHERE `page_id`='$this->id' AND `name`='$name'");
db()->query("DELETE FROM `_page_params_lang` WHERE `page_id`='$this->id' AND `name`='$name'");
db()->query("DELETE FROM `_page_params_opt` WHERE `page_id`='$this->id' AND `name`='$name'");

$this->query_params();
$this->construct_params();
page()->query_info();

return true;

}

public function view_add($name, $infos)
{


if (!is_string($name) || isset($this->view_list[$name]))
	return false;

if (!is_array($infos))
	$infos = array();
elseif (!isset($infos["template_id"]) || !is_numeric($infos["template_id"]))
	$infos["template_id"] = null;
elseif (!isset($infos["params"]) || !is_string($infos["params"]))
	$infos["params"] = null;

db()->query("INSERT INTO `_page_view` (`page_id`, `name`, `template_id`, `params_map`) VALUES ('$this->id', '$name', '".db()->string_escape($infos["template_id"])."', '".db()->string_escape(json_encode(json_decode($infos["params"])))."')");

$this->query_view();

return true;

}

public function view_update($name, $infos)
{


if (!is_string($name) || !isset($this->view_list[$name]))
	return false;

if (!is_array($infos))
	$infos = array();
elseif (!isset($infos["template_id"]) || !is_numeric($infos["template_id"]))
	$infos["template_id"] = null;
elseif (!isset($infos["params"]) || !is_array($infos["params"]))
	$infos["params"] = null;

db()->query("UPDATE `_page_view` SET `template_id`='".db()->string_escape($infos["template_id"])."', `params_map`='".db()->string_escape(json_encode($infos["params"]))."' WHERE `page_id`='$this->id' AND `name`='$name'");

$this->query_view();

return true;

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
