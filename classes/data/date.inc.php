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
 * Date
 * 
 * using strftime() for the displaying
 * Stored in french format but can be changed
 * Associated to a jquery datepickerUI form and a date DB field
 * 
 */
class data_date extends data_string
{

protected $empty_value = "00/00/0000";

protected $opt = array
(
	"ereg" => '/^(0?[1-9]|[12][0-9]|3[01])[\/](0?[1-9]|1[0-2])[\/](19|20)\d{2}$/',
	"date_format" => "%A %d %B %G", // Defined for strftime()
	"size" => 10,
);

public function db_field_create()
{

return array("type" => "date");

}

/* Convert */
function value_to_db()
{

if ($this->value === null)
	return null;
else
	return implode("-",array_reverse(explode("/",$this->value)));

}
function value_to_form()
{

if ($this->value === null)
	return "";
else
	return $this->value;
	
}
function value_from_db($value)
{

if ($value !== null)
	$this->value = implode("/",array_reverse(explode("-",$value)));
else
	$this->value = null;

}

function view($style="")
{

if (!$style)
	$style = $this->opt["date_format"];

if ($this->nonempty())
	return strftime($style, $this->timestamp());
else
	return "";

}
public function __tostring()
{

if ($this->nonempty())
	return strftime($this->opt["date_format"], $this->timestamp());
else
	return "";

}

/**
 * Returns the timestamp calculated from the stored value
 */
public function timestamp()
{

if ($this->nonempty())
{
	$date_e = explode("/", $this->value);
	return mktime(0, 0, 0, $date_e[1], $date_e[0], $date_e[2]);
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
	$time_1 = $this->timestamp();
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

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
