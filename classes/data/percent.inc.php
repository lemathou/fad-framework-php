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
 * Percent
 */
class data_percent extends data_measure
{

protected $opt = array
(
	"numeric_signed"=>true,
	"size"=>6,
	"numeric_precision"=>2,
	"numeric_type"=>"%"
);

protected $empty_value = null;

function __construct($name, $value, $label="Percent", $options=array())
{

data_float::__construct($name, $value, $label, $options);

}

function convert_before(&$value)
{

$value = $value/100;

}

function __tostring()
{

if ($this->nonempty())
	return ($this->value*100)." ".$this->opt["numeric_type"];
else
	return "";

}

public function verify(&$value, $convert=false, $options=array())
{

if (!is_numeric($value) || $value < 0 || $value > 1)
{
	if ($convert)
	{
		if ($value)
			$value = 1;
		else
			$value = 0;
	}
	return false;
}

$value = floatval($value);

return true;

}
public function convert(&$value)
{

if ($value)
	$value = 1;
else
	$value = 0;

}
function value_to_db()
{

if ($this->value === null)
	return null;
else
	return $this->value*100;

}
function value_from_db($value)
{

if ($value === null)
	$this->value = null;
else
	$this->value = $value/100;

}

public function form_field_disp($print=true, $options=array())
{

if ($this->value === null)
	$value = "";
else
	$value = $this->value*100;

$return = "<input type=\"text\" name=\"$this->name\" value=\"$value\" size=\"4\" maxlength=\"5\" class=\"".get_called_class()."\" />";

if ($print)
	print $return;
else
	return $return;

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
