<?php

/**
  * $Id: page.inc.php 76 2009-10-15 09:24:20Z mathieu $
  * 
  * Copyright 2008-2010 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */

class page_gestion extends _page_gestion
{



}

class page extends _page
{

/**
 * Add a param
 * @param unknown_type $name
 * @param unknown_type $infos
 */
public function param_add($name, $infos)
{

if (!is_string($name) || isset($this->params_list[$name]))
	return false;

if (!is_array($infos))
	$infos = array("value"=>null, "update_pos"=>null);
elseif (!isset($infos["value"]))
	$infos["value"] = null;
elseif (!isset($infos["update_pos"]))
	$infos["update_pos"] = null;

db()->query("INSERT INTO `_page_params` (`page_id`, `name`, `value`, `update_pos`) VALUES ('$this->id', '$name', '".db()->string_escape(json_encode(json_decode($infos["value"])))."', NULL)");

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

if (!is_string($name) || !isset($this->params_list[$name]))
	return false;

if (!is_array($infos) || !isset($infos["value"]) || !is_string($infos["value"]) || !isset($infos["update_pos"]))
	return false;

db()->query($query_string = "UPDATE `_page_params` SET `value`='".db()->string_escape($infos["value"])."', `update_pos`= NULL WHERE `page_id`='$this->id' AND `name`='$name'");
if ($n=array_search($name, $this->params_url))
{
	unset($this->params_url[$n]);
	for ($i=$n+1;$i=count($this->params_url)-1;$i++)
	{
		$this->params_url[$i-1] = $this->params_url[$i];
		unset($this->params_url[$i]);
	}
	db()->query("UPDATE `_page_params` SET `update_pos`=`update_pos`-1 WHERE `page_id`='$this->id' AND `update_pos` >= $n");
}

if (is_numeric($n=$infos["update_pos"]))
{
	for ($i=count($this->params_url)-1;$i=$n;$i--)
	{
		$this->params_url[$i+1] = $this->params_url[$i];
		unset($this->params_url[$i]);
	}
	$this->params_url[$n] = $name;
	db()->query("UPDATE `_page_params` SET `update_pos`=`update_pos`+1 WHERE `page_id`='$this->id' AND `update_pos` >= $n");
	db()->query("UPDATE `_page_params` SET `update_pos`='$n' WHERE `page_id`='$this->id' AND `name`='$name'");
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

if (!is_string($name) || !isset($this->params_list[$name]))
	return false;

db()->query("DELETE FROM `_page_params` WHERE `page_id`='$this->id' AND `name`='$name'");

return true;

}

}

?>