<?php

/**
  * $Id$
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
 * Boolean
 * 
 * Yes/No field ^^
 * 
 * Integer unsigned size 1
 * 
 */
class data_boolean extends data_string
{

protected $empty_value = null;

protected $opt = array("value_list"=>array("NO","YES"));

function __construct($name, $value, $label="Boolean", $options=array())
{

data::__construct($name, $value, $label, $options);

}

public function db_field_create()
{

return array("type"=>"boolean");

}

/* Convert */
public function convert_from_db(&$value)
{

if ($value !== null)
	$value = ($value=="1") ? true : false;

}
public function value_to_db()
{

if ($this->nonempty())
	return ($this->value) ? "1" : "0";
else
	return null;

}
public function value_to_form()
{

if ($this->nonempty())
	return ($this->value) ? "1" : "0";
else
	return "";

}
public function verify(&$value, $convert=false, $options=array())
{

if ($value !== true || $value !== false)
{
	if ($convert)
	{
		if (empty($value))
			$value = false;
		else
			$value = true;
	}
	return false;
}

return true;

}
public function convert(&$value)
{

if (empty($value))
	$value = false;
else
	$value = true;

}

/* View */
public function form_field_disp()
{

return "<input type=\"radio\" name=\"$this->name\" value=\"0\"".(($this->value === false)?" checked":"")." class=\"".get_called_class()."\" />&nbsp;".$this->opt["value_list"][0]." <input name=\"$this->name\" type=\"radio\" value=\"1\"".(($this->value === true)?" checked":"")." class=\"".get_called_class()."\" />&nbsp;".$this->opt["value_list"][1];

}
public function __tostring()
{

if ($this->value === null)
	return "";
else
	return $this->opt["value_list"][($this->value)?1:0];

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
