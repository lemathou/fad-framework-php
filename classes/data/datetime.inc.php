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
 * Datetime (timestamp)
 * 
 * Associated to a datetime/timestamp DB field
 * 
 */
class data_datetime extends data_string
{

protected $empty_value = 0;
protected $opt = array
(
	"datetime_format" => "%A %d %B %G à %H:%M:%S"
);

public function db_field_create()
{

return array("type"=>"datetime");

}

/* Convert */
public function verify(&$value, $convert=false, $options=array())
{

if (!is_string($value) || !$value)
{
	if ($convert)
		$value = null;
	return false;
}

$return = true;

$e = explode(" ", $value);
if (!data_date::verify($e[0], true))
	$return = false;

if (!count($e) == 2)
{
	$e = array($e[0], "00:00:00");
	$return = false;
}
elseif (!data_time::verify($e[1], true))
	$return = false;

if ($convert)
	$value = implode(" ", $e);

return $return;

}
public function convert(&$value)
{

if (!is_string($value) || count($e=explode(" ", $value)) != 2 || (count($d=explode("/", $e[0])) != 3 && count($d=explode("-", $e[0])) != 3) || count($t=explode(":", $e[1])) != 3)
	$value = null;

}
public function convert_after(&$value)
{

if ($value !== null)
{
	$e = explode(" ", $value);
	if (count($d=explode("/", $e[0])) == 1)
		$d = array_reverse(explode("-", $e[0]));
	$t = explode(":", $e[1]);
	$value = mktime($t[0], $t[1], $t[2], $d[1], $d[0], $d[2]);
}

}
public function value_from_db($value)
{

$this->value = $value;
$this->convert($this->value);
$this->convert_after($this->value);

}
public function value_to_db()
{

return date("Y-m-d H:i:s", $this->value);

}

/* View */
public function __tostring()
{

if ($this->nonempty())
	return strftime($this->opt["datetime_format"], $this->value);
else
	return "";

}
public function form_field_disp($print=true, $options=array())
{

if ($this->nonempty())
	$value = date("d/m/Y H:i:s", $this->value);
else
	$value = "";

$return = "<input type=\"text\" name=\"".$this->name."\" value=\"$value\" size=\"19\" maxlength=\"19\" class=\"".get_called_class()."\" />";

if ($print)
	print $return;
else
	return $return;

}
public function date($str="")
{

if (!$this->value)
	return "";
elseif (!$str)
	return date("d/m/Y H:i:s", $this->value);
else
	return date($str, $this->value);

}
public function strftime($str="")
{

if (!$this->value)
	return "";
elseif (!$str)
	return strftime($this->opt["datetime_format"], $this->value);
else
	return strftime($str, $this->value);

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
