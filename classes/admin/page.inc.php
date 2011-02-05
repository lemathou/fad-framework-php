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

db()->query("INSERT INTO `_page_params` (`page_id`, `name`, `datatype`, `value`, `update_pos`) VALUES ('$this->id', '$name', '".db()->string_escape(json_encode(json_decode($infos["value"])))."', '".db()->string_escape($infos["datatype"])."', NULL)");

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

if (!is_string($name) || !isset($this->param_list[$name]))
	return false;

if (!is_array($infos) || !isset($infos["value"]) || !is_string($infos["value"]) || !isset($infos["update_pos"]) || !isset($infos["datatype"]))
	return false;

$query_string = "UPDATE `_page_params` SET `datatype`='".db()->string_escape($infos["datatype"])."', `value`='".db()->string_escape($infos["value"])."', `update_pos`= NULL WHERE `page_id`='$this->id' AND `name`='$name'";
db()->query($query_string);
//echo "<p>$query_string : ".mysql_error()."</p>\n";
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

if (isset($infos["opt"]))
{
	db()->query("DELETE FROM `_page_params_opt` WHERE `page_id`='$this->id' `name` = '$name'");
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

if (!is_string($name) || !isset($this->param_list[$name]))
	return false;

db()->query("DELETE FROM `_page_params` WHERE `page_id`='$this->id' AND `name`='$name'");

return true;

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
