<?php

/**
  * $Id: template.inc.php 27 2011-01-13 20:58:56Z lemathoufou $
  * 
  * Copyright 2008-2011 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [begin]");


class template_gestion extends _template_gestion
{

protected function del_more($id)
{

db()->query("DELETE FROM `_template_params` WHERE template_id='$id'");
db()->query("DELETE FROM `_template_params_lang` WHERE template_id='$id'");
db()->query("DELETE FROM `_template_params_opt` WHERE template_id='$id'");

}

}


class template extends _template
{

/**
 * Add, remove and update template parameters
 */
public function param_add($name, $info)
{

if (!login()->perm(6))
	die("ONLY ADMIN CAN ADD TEMPLATE PARAMS");
if (!is_string($name) || !preg_match("/^([a-zA-Z_-]*)$/", $name) || isset($this->param[$name]) || !is_array($info) || !isset($info["datatype"]) || !data()->exists_name($info["datatype"]))
	return false;
if (!isset($info["defaultvalue"]) || !is_string($info["defaultvalue"]))
	$info["defaultvalue"] = "null";
if (!isset($info["description"]) || !is_string($info["description"]))
	$info["description"] = "";

list($info["order"]) = db()->query("SELECT COUNT(*) FROM `_template_params` WHERE `template_id`='$this->id'")->fetch_row();
db()->query("INSERT INTO `_template_params` (`template_id`, `order`, `datatype`, `name`, `defaultvalue`) VALUES ('$this->id', '".$info["order"]."', '".$info["datatype"]."', '".$name."', '".db()->string_escape($info["defaultvalue"])."' )");
db()->query("INSERT INTO `_template_params_lang` (`template_id`, `lang_id`, `name`, `description`) VALUES ('$this->id', '".SITE_LANG_ID."', '".db()->string_escape($name)."', '".db()->string_escape($info["description"])."' )");

$this->query_info();
template()->query_info();

return true;

}
public function param_del($name)
{

if (!login()->perm(6))
	die("ONLY ADMIN CAN ADD TEMPLATE PARAMS");
if (!is_string($name) || !isset($this->param[$name]))
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

if (!login()->perm(6))
	die("ONLY ADMIN CAN ADD TEMPLATE PARAMS");
if (!is_string($name) || isset($this->param[$name]) || !is_array($info))
	return false;

$update_list = array();
$update_lang_list = array();
if (isset($info["datatype"]))
	if (!data()->exists_name($info["datatype"]))
		return false;
	else
		$update_list[] = "`datatype`='".$info["datatype"]."'";
if (isset($info["defaultvalue"]))
	if (!is_string($info["defaultvalue"]))
		return false;
	else
		$update_list[] = "`defaultvalue`='".db()->string_escape($info["defaultvalue"])."'";
if (isset($info["description"]))
	if (!is_string($info["description"]))
		return false;
	else
		$update_lang_list[] = "`description`='".db()->string_escape($info["description"])."'";
list($posmax) = db()->query("SELECT COUNT(*) FROM `_template_params` WHERE `template_id`='$this->id'")->fetch_row();
if (isset($info["order"]))
	if (!is_numeric($info["order"]) || $info["order"] < 0 || $info["order"] > $pos_max)
		return false;

if (count($update_list))
{
	db()->query("UPDATE `_template_params` SET ".implode($update_list)." WHERE `template_id`='$this->id' AND `name`='".$name."'");
}
if (count($update_lang_list))
{
	db()->query("UPDATE `_template_params_lang` SET ".implode($update_lang_list)." WHERE `template_id`='$this->id' AND `lang_id`='".SITE_LANG_ID."' AND `name`='".$name."'");
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
