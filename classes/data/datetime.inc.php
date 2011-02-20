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
	"disp_format" => "%A %d %B %G à %H:%M:%S", // Defined for strftime()
	"form_format" => "d/m/Y H:i:s", // Defined for date()
	"db_format" => "Y-m-d H:i:s", // Defined for date()
);

public function db_field_create()
{

return array("type"=>"datetime");

}

/* Convert */
public function verify(&$value, $convert=false, $options=array())
{

if (!ereg("/([0-2][0-9]{3})-(0[0-9]|1[0-2])-([0-2][0-9]|3[0-1]) ([0-1][0-9]|2[0-4]):([0-5][0-9]):([0-5][0-9])/", $value))
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

$this->convert_from_format($value, $this->opt["form_format"]);

}
function convert_from_db(&$value)
{

$this->convert_from_format($value, $this->opt["db_format"]);

}
function convert_from_format(&$value, $format)
{

$params = array("Y", "m", "d", "H", "i", "s");
$pos = array();
foreach($params as $param) if (($p=strpos($format, $param)) !== false)
	$pos[$p] = $param;
ksort($pos);
$nb = 0;
$p = array();
foreach($pos as $i=>$j)
{
	$nb++;
	$p[$nb] = $j;
}
$ereg = str_replace(array_merge($params, array("/")), array("([0-2][0-9]{3})", "(0[1-9]|1[0-2])", "(0[1-9]|[1-2][0-9]|3[0-1])", "(0[0-9]|1[0-9]|2[0-4])", "([0-5][0-9])" ,"([0-5][0-9])", "\/"), $format);
//echo "<p>$ereg</p>";
if (preg_match("/$ereg/", $value, $match))
{
	$r = array("Y"=>"0000", "m"=>"00", "d"=>"00", "H"=>"00", "i"=>"00", "s"=>"00");
	foreach($p as $i=>$j)
		$r[$j] = $match[$i];
	//var_dump($r);
	$value = str_replace($params, $r, $format);
}
else
	$value = $this->empty_value;

}
function value_to_form()
{

if ($this->nonempty())
{
	$e = explode(" ", $this->value);
	$d = explode("-", $e[0]);
	$t = explode(":", $e[1]);
	return str_replace(array("Y", "m", "d", "H", "i", "s"), array_merge($d, $t), $this->opt["form_format"]);
}
else
	return "";

}

/* View */
public function __tostring()
{

return $this->view();

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
	return strftime($this->opt["disp_format"], $this->value);
else
	return strftime($format, $this->value);

}
public function date($format="")
{

if (!$this->value)
	return "";
elseif (!$format)
	return date($this->opt["form_format"], $this->value);
else
	return date($format, $this->value);

}

public function form_field_disp($options=array())
{

return "<input type=\"text\" name=\"".$this->name."\" value=\"$this->value".$this->value_to_form()."\" size=\"19\" maxlength=\"19\" class=\"".get_called_class()."\" />";

}

/**
 * Returns the timestamp calculated from the stored value
 */
public function timestamp()
{

if ($this->nonempty())
	return $this->value;
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

$this->value = date($this->opt["value_format"]);

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
