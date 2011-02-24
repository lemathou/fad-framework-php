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
 * Integer (numeric)
 *
 */
class data_integer extends data_string
{

protected $empty_value = 0;
	
protected $opt = array
(
	"size"=>11,
	"numeric_signed" => true,
);

public function db_field_create()
{

$return = array
(
	"type" => "integer",
	"size" => $this->opt["size"],
	
);
if ($this->opt["numeric_signed"])
{
	$return["signed"] = true;
}

return $return;

}

/* Convert */
public function verify(&$value, $convert=false, $options=array())
{

$return = true;

if (!is_numeric($value) || (int)$value != $value)
	if ($convert)
	{
		$value = (int)$value;
		$return = false;
	}
	else
		return false;

if (isset($this->opt["numeric_signed"]) && $this->opt["numeric_signed"] == false && $value < 0)
	if ($convert)
	{
		$value = -$value;
		$return = false;
	}
	else
		return false;

return $return;

}
public function convert(&$value)
{

$value = (int)$value;
if (isset($this->opt["numeric_signed"]) && $this->opt["numeric_signed"] == false && $value < 0)
	$value = -$value;

}

function value_from_db($value)
{

if ($value === null)
	$this->value = null;
else
	$this->value = (int)$value;

}

/* View */
public function form_field_disp()
{

$attrib_size = " size=\"".($this->opt["size"]+1)."\"";
$attrib_maxlength = " maxlength=\"".($this->opt["size"]+1)."\"";

return "<input type=\"text\" name=\"$this->name\" value=\"$this->value\"$attrib_size$attrib_maxlength class=\"".get_called_class()."\" />";

}

/* Misc */
public function increment()
{

if ($this->value)
	$this->value++;
else
	$this->value = 1;

}
public function decrement()
{

if ($this->value && $this->value > 0)
	$this->value--;

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
