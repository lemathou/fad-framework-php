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
 * Float (numeric)
 *
 */
class data_float extends data_integer
{

protected $opt = array
(
	"size"=>11,
	"numeric_signed"=>true,
	"numeric_precision"=>2
);

public function db_field_create()
{

$return = array
(
	"type" => "float",
	"size" => $this->opt["size"],
	"precision" => $this->opt["numeric_precision"],
	
);
if ($this->opt["numeric_signed"])
{
	$return["signed"]=true;
}

return $return;

}

/* Convert */
public function verify(&$value, $convert=false, $options=array())
{

var_dump($value);

$return = true;

if (!is_numeric($value) || (float)$value != $value)
	if ($convert)
	{
		$value = str_replace(",", ".", $value);
		$value = floatval($value);
		$return = false;
	}
	else
		return false;

if (isset($this->opt["numeric_precision"]) && ($precision=$this->opt["numeric_precision"]) && $value != round($value, $precision))
	if ($convert)
	{
		$value = round($value, $precision);
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
function convert(&$value)
{

if (!preg_match('/^?[-]?([0-9]*)(\.([0-9]*)){0,1}$/', $value))
	$value = null;

}

function convert_from_db(&$value)
{

if ($value !== null)
	$value = floatval($value);

}

public function form_field_disp()
{

$attrib_size = " size=\"".($this->opt["size"]+2)."\"";
$attrib_maxlength = " maxlength=\"".($this->opt["size"]+2)."\"";

return "<input type=\"text\" name=\"$this->name\" value=\"$this->value\"$attrib_size$attrib_maxlength class=\"".get_called_class()."\" />";

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
