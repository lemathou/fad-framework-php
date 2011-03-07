<?php

/**
  * $Id: template.inc.php 30 2011-01-18 23:29:06Z lemathoufou $
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


class _template_gestion extends __template_gestion
{

protected function del_more($id)
{

db()->query("DELETE FROM `_template_params` WHERE template_id='$id'");
db()->query("DELETE FROM `_template_params_lang` WHERE template_id='$id'");
db()->query("DELETE FROM `_template_params_opt` WHERE template_id='$id'");

}

}


class _template extends __template
{

/**
 * Add, remove and update template parameters
 */
public function param_add($name, $info)
{

if (!login()->perm(1))
	die("ONLY ADMIN CAN ADD TEMPLATE PARAMS");

if (!is_string($name) || !preg_match("/^([a-zA-Z_-]*)$/", $name) || isset($this->param[$name]) || !is_array($info))
	return false;
if (!isset($info["datatype"]) || !data()->exists_name($info["datatype"]))
	$info["datatype"] = "";
if (!isset($info["value"]) || !is_string($info["value"]))
	$info["value"] = "null";
if (!isset($info["label"]) || !is_string($info["label"]))
	$info["label"] = "";

db()->query("INSERT INTO `_template_params` (`template_id`, `order`, `datatype`, `name`, `value`) VALUES ('$this->id', '".count($this->param)."', '".$info["datatype"]."', '".$name."', '".db()->string_escape($info["value"])."' )");
db()->query("INSERT INTO `_template_params_lang` (`template_id`, `lang_id`, `name`, `label`) VALUES ('$this->id', '".SITE_LANG_ID."', '".$name."', '".db()->string_escape($info["label"])."' )");

if (isset($info["opt"]) && is_array($info["opt"]))
{
	foreach ($info["opt"] as $i=>$j) if (is_string($i) && is_string($j))
		db()->query("INSERT INTO `_template_params_opt` (template_id, name, optname, optvalue) VALUES ('$this->id', '$name', '".db()->string_escape($i)."', '".db()->string_escape(json_encode(json_decode($j)))."')");
}

$this->query_info();
template()->query_info();

return true;

}
public function param_del($name)
{

if (!login()->perm(1))
	die("ONLY ADMIN CAN ADD TEMPLATE PARAMS");

if (!is_string($name) || !array_key_exists($name, $this->param))
	return false;

db()->query("DELETE FROM `_template_params` WHERE template_id='$this->id' AND name='$name'");
db()->query("DELETE FROM `_template_params_lang` WHERE template_id='$this->id' AND name='$name'");
db()->query("DELETE FROM `_template_params_opt` WHERE template_id='$this->id' AND name='$name'");

$this->query_info();
template()->query_info();

return true;

}
public function param_update($name, $info)
{

//var_dump($info);

if (!login()->perm())
	die("ONLY ADMIN CAN ADD TEMPLATE PARAMS");

if (!is_string($name) || !array_key_exists($name, $this->param) || !is_array($info))
	return false;

$update_list = array();
$update_lang_list = array();
if (isset($info["datatype"]))
	if (!data()->exists_name($info["datatype"]))
		return false;
	else
		$update_list[] = "`datatype`='".$info["datatype"]."'";
if (isset($info["value"]))
	if (!is_string($info["value"]))
		return false;
	else
		$update_list[] = "`value`='".db()->string_escape($info["value"])."'";
if (isset($info["label"]))
	if (!is_string($info["label"]))
		return false;
	else
		$update_lang_list[] = "`label`='".db()->string_escape($info["label"])."'";
if (isset($info["name"]))
	if (!is_string($info["name"]) || ($name != $info["name"] && array_key_exists($info["name"], $this->param)))
		return false;
	elseif ($name != $info["name"])
	{
		$update_list[] = "`name`='".db()->string_escape($info["name"])."'";
		$update_lang_list[] = "`name`='".db()->string_escape($info["name"])."'";
	}
if (isset($info["order"]))
	if (!is_numeric($info["order"]) || $info["order"] < 0 || $info["order"] > count($this->param))
		return false;

if (count($update_list))
{
	$query_string = "UPDATE `_template_params` SET ".implode(", ", $update_list)." WHERE `template_id`='$this->id' AND `name`='".$name."'";
	db()->query($query_string);
	//echo "<p>DEBUG : $query_string</p>\n";
}
if (count($update_lang_list))
{
	db()->query("UPDATE `_template_params_lang` SET ".implode(", ", $update_lang_list)." WHERE `template_id`='$this->id' AND `lang_id`='".SITE_LANG_ID."' AND `name`='".$name."'");
}

if (isset($info["opt"]))
{
	db()->query("DELETE FROM `_template_params_opt` WHERE `template_id`='$this->id' AND `name`='$name'");
	if (isset($info["name"]))
		$name = $info["name"];
	if (is_array($info["opt"])) foreach ($info["opt"] as $i=>$j) if (is_string($i) && is_string($j))
		db()->query("INSERT INTO `_template_params_opt` (template_id, name, optname, optvalue) VALUES ('$this->id', '$name', '".db()->string_escape($i)."', '".db()->string_escape(json_encode(json_decode($j)))."')");
}

$this->query_info();
template()->query_info();

return true;

}

}

//class template_container extends _template_container {};
//class template_datamodel extends _template_datamodel {};

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
