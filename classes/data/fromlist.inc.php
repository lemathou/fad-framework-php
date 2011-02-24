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
 * Select multiple like
 * 
 * A set of elements from a given exhaustive list
 * Associated to a select multiple form, and a 'set' DB field
 *
 */
class data_fromlist extends data_list
{

protected $opt = array
(
	"value_list" => array(),
);

public function db_field_create()
{

return array("type" => "fromlist", "value_list" => array_keys($this->opt["value_list"]));

}

/* Convert */
public function verify(&$value, $convert=false, $options=array())
{

if (!is_array($value))
{
	if ($convert)
		$value = array();
	return false;
}

$return = true;

foreach ($value as $nb=>$i)
{
	if (!isset($this->opt["value_list"][$i]))
	{
		if ($convert)
		{
			unset($value[$nb]);
			$return = false;
		}
		else
			return false;
	}
}

return $return;

}
public function convert(&$value)
{

if (!is_array($value))
	$value = array();

foreach ($value as $i=>$j)
	if (!isset($this->opt["value_list"][$j]))
		unset($value[$i]);

}

/* View */
public function __tostring()
{

if (is_array($this->value))
	return implode(", ", $this->value);
else
	return "";

}
public function form_field_disp()
{

$return = "<select name=\"".$this->name."[]\" multiple class=\"".get_called_class()."\">";
foreach ($this->opt["value_list"] as $i=>$j)
	if (is_array($this->value) && in_array($i, $this->value))
		$return .= "<option value=\"$i\" selected=\"selected\">$j</option>";
	else
		$return .= "<option value=\"$i\">$j</option>";
$return .= "</select>";

return $return;

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
