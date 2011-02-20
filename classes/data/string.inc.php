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
 * String
 * 
 * With a maxlength
 * Associated to an input/text form, and a varchar db field
 *
 */
class data_string extends data
{

protected $empty_value = "";

protected $opt = array
(
	"size" => 256
);

public function db_field_create()
{

return array("type"=>"string", "size"=>$this->opt["size"]);

}

/* Verify */
public function verify(&$value, $convert=false, $options=array())
{

$return = true;

if (!is_string($value))
{
	if ($convert)
	{
		$value = $this->empty_value;
		$return = false;
	}
	else
		return false;
}

if (isset($this->opt["size"]) && ($maxlength=$this->opt["size"]) && strlen($value) > $maxlength)
{
	if ($convert)
	{
		$value = substr($value, 0, $maxlength);
		$return = false;
	}
	else
		return false;
}

if (isset($this->opt["ereg"]) && ($ereg=$this->opt["ereg"]) && !preg_match($ereg, $value))
{
	if ($convert)
	{
		$value = $this->empty_value;
		$return = false;
	}
	else
		return false;
}

return $return;

}
public function convert(&$value)
{

if (!is_string($value))
	$value = (string)$value;
if (isset($this->opt["size"]) && ($maxlength=$this->opt["size"]) && strlen($value) > $maxlength)
	$value = substr($value, 0, $maxlength);
if (isset($this->opt["ereg"]) && ($ereg=$this->opt["ereg"]) && !preg_match($ereg, $value))
	$value = null;

}
public function convert_before(&$value)
{

$value = strip_tags($value);

}

/* View */
public function __tostring()
{

if ($this->value === null)
	return "";
elseif (isset($this->opt["preg_replace"]) && is_array($opt=$this->opt["preg_replace"]) && isset($opt["pattern"]) && isset($opt["replace"]) && preg_match($opt["pattern"], $this->value))
{
	return preg_replace($opt["pattern"], $opt["replace"], (string)$this->value);
}
else
	return (string)$this->value;

}
public function form_field_disp($print=true, $options=array())
{

$attrib_size = ( !isset($this->opt["size"]) || !$this->opt["size"]) ? ""
	: ($this->opt["size"] < 32) ? " size=\"".$this->opt["size"]."\""
	: " style=\"width: 100%;\"";
$attrib_maxlength = ( isset($this->opt["size"]) && $this->opt["size"] > 0 )
	? " maxlength=\"".$this->opt["size"]."\""
	: "";

if ($this->type == "password")
	$type = "password";
else
	$type = "text";

$return = "<input type=\"$type\" name=\"$this->name\" value=\"$this->value\"$attrib_size$attrib_maxlength class=\"".get_called_class()."\" />";

if ($print)
	print $return;
else
	return $return;

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
