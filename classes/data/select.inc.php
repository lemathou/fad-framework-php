<?php

/**
  * $Id: data.inc.php 32 2011-01-24 07:13:42Z lemathoufou $
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
 * Select from a list
 * 
 * An element from an exhaustive given list
 *
 */
class data_select extends data_string
{

protected $empty_value = "";

protected $opt = array
(
	"value_list" => array(),
);

public function db_field_create()
{

$value_list = array();
foreach($this->opt["value_list"] as $name=>$label)
	$value_list[] = $name;
return array("type" => "select", "value_list" => $value_list);

}

/* Convert */
public function verify(&$value, $convert=false, $options=array())
{

if (!isset($this->opt["value_list"][$value]))
{
	if ($convert)
		$value = "";
	return false;
}

return true;

}
function convert(&$value)
{

if (!isset($this->opt["value_list"][$value]))
	$value = "";

}

/* View */
public function form_field_disp($print=true, $options=array())
{

$return = "<select name=\"$this->name\" class=\"".get_called_class()."\">";
$return .= "<option value=\"\"></option>";
foreach ($this->opt["value_list"] as $i=>$j)
	if ($this->value == $i)
		$return .= "<option value=\"$i\" selected=\"selected\">$j</option>";
	else
		$return .= "<option value=\"$i\">$j</option>";
$return .= "</select>";

if ($print)
	echo $return;
else
	return $return;

}
public function form_field_select_disp($print=true, $options=array())
{

$return = "<select name=\"$this->name\">";
$return .= "<option value=\"\"></option>";
foreach ($this->opt["value_list"] as $i=>$j)
	if ($options == $i)
		$return .= "<option value=\"$i\" selected=\"selected\">$j</option>";
	else
		$return .= "<option value=\"$i\">$j</option>";
$return .= "</select>";

if ($print)
	print $return;
else
	return $return;

}
function __tostring()
{

if ($this->value && isset($this->opt["value_list"][$this->value]))
	return "".$this->opt["value_list"][$this->value];
else
	return "";

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
