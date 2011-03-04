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

protected $empty_value = "0000-00-00 00:00:00"; // stored as Y-m-d H:i:s
protected $opt = array
(
	"datetime_format" => "Y-m-d H:i:s", // for the value
	"disp_format" => "%A %d %B %G à %H:%M:%S", // Defined for strftime()
	"form_format" => "d/m/Y H:i:s", // Defined for date()
	"db_format" => "Y-m-d H:i:s", // Defined for date()
);

protected static $ereg_params = array("Y", "m", "d", "H", "i", "s");
protected static $ereg_default = array
(
	"Y"=>"0000",
	"m"=>"00",
	"d"=>"00",
	"H"=>"00",
	"i"=>"00",
	"s"=>"00"
);
protected static $ereg_format = array
(
	"Y"=>"([0-2][0-9]{3})",
	"m"=>"(0[0-9]|1[0-2])",
	"d"=>"([0-2][0-9]|3[0-1])",
	"H"=>"([0-1][0-9]|2[0-4])",
	"i"=>"([0-5][0-9])",
	"s"=>"([0-5][0-9])"
);

public function db_field_create()
{

return array("type"=>"datetime");

}

/* Convert */
public function verify(&$value, $convert=false, $options=array())
{

$ereg = str_replace(array_merge(self::$ereg_params, array("/")), array_merge(self::$ereg_format, array("\/")), $this->opt["datetime_format"]);
//echo "<p>$ereg</p>";
if (!preg_match("/$ereg/", $value))
{
	if ($convert)
		$this->convert($value, $options);
	return false;
}
else
	return true;

}
function convert_from_form(&$value)
{

$this->convert_format($value, $this->opt["form_format"], $this->opt["datetime_format"]);

}
function convert_from_db(&$value)
{

$this->convert_format($value, $this->opt["db_format"], $this->opt["datetime_format"]);

}
function convert_format(&$value, $format_from, $format_to)
{

$ereg = str_replace(array_merge(self::$ereg_params, array("/")), array_merge(self::$ereg_format, array("\/")), $format_from);
//echo "<p>$ereg</p>";
if (preg_match("/$ereg/", $value, $match))
{
	$pos = array();
	foreach(self::$ereg_params as $param) if (($p=strpos($format_from, $param)) !== false)
		$pos[$p] = $param;
	ksort($pos);
	$nb = 0;
	$p = array();
	foreach($pos as $i=>$j)
	{
		$nb++;
		$p[$nb] = $j;
	}
	//var_dump($p);
	$r = self::$ereg_default;
	foreach($p as $i=>$j)
		$r[$j] = $match[$i];
	//var_dump($r);
	$value = str_replace(self::$ereg_params, $r, $format_to);
}
else
	$value = $this->empty_value;

}
function value_to_form()
{

if ($this->nonempty())
{
	$value = $this->value;
	$this->convert_format($value, $this->opt["datetime_format"], $this->opt["form_format"]);
	return $value;
}
else
	return "";

}
function value_to_db()
{

if ($this->nonempty())
{
	$value = $this->value;
	$this->convert_format($value, $this->opt["datetime_format"], $this->opt["db_format"]);
	return $value;
}
else
	return null;

}

/* View */
public function __tostring()
{

return $this->view();

}
public function format($format="")
{

$value = $this->value;
$this->convert_format($value, $this->opt["datetime_format"], $format);
return $value;

}
public function view($format="")
{

return $this->strftime($format);

}
public function strftime($format="")
{

if (!$this->value)
	return "";
elseif (!$format)
	return strftime($this->opt["disp_format"], strtotime($this->value));
else
	return strftime($format, $this->timestamp());

}
public function date($format="")
{

if (!$this->value)
	return "";
elseif (!$format)
	return date($this->opt["form_format"], strtotime($this->value));
else
	return date($format, $this->timestamp());

}

public function form_field_disp($options=array())
{

return "<input type=\"text\" name=\"".$this->name."\" value=\"".$this->value_to_form()."\" size=\"19\" maxlength=\"19\" class=\"".get_called_class()."\" />";

}

/**
 * Returns the timestamp calculated from the stored value
 */
public function timestamp()
{

if ($this->nonempty())
{
	$value = $this->value;
	$this->convert_format($value, $this->opt["datetime_format"], "H-i-s-m-d-Y");
	$v = explode("-", $value);
	return mktime($v[0], $v[1], $v[2], $v[3], $v[4], $v[5]);
}
else
	return null;

}
/**
 * Compare date timestamps and returns if the stored value is larger, smaller or equal to the passed value
 * @param timestamp $value
 */
public function compare($value)
{

if ($this->value !== null)
{
	$time_1 = $this->value;
	$time_2 = $value;
	if ($time_1 < $time_2)
	{
		return "<";
	}
	elseif ($time_1 == $time_2)
	{
		return "=";
	}
	else
	{
		return ">";
	}
}
else
	return false;

}

function now()
{

$this->value = date($this->opt["datetime_format"]);

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
