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
 * Year
 * 
 * Associated to a year DB field
 * 
 */
class data_year extends data_string
{

protected $empty_value = "0000";

protected $opt = array
(
	"size"=>4
);

public function db_field_create()
{

return array("type" => "year");

}

/* Convert */
public function verify(&$value, $convert=false, $options=array())
{

if (!is_string($value) || !preg_match("([0-9]{4})", $value))
{
	if ($convert)
		$value = $this->empty_value;
	return false;
}

return true;

}
function convert(&$value)
{

if (!is_string($value) || !preg_match("([0-9]{4})", $value))
	$value = $this->empty_value;

}

public function __tostring()
{

if ($this->nonempty())
	return $this->value;
else
	return "";

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
