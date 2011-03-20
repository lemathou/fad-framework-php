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

//aaaa("URL Loaded")->fff;

/**
 * URL
 * 
 * Preg verified
 * Maxlength fixed to the standard data_string size (actually 256)
 * 
 */
class data_url extends data_string
{

protected $opt = array
(
	"regex" => '/^([a-zA-Z]+[:\/\/]+)*([A-Za-z0-9\-_]+\\.+[A-Za-z0-9\.\/%&=\?\-_]+)$/i',
	"urltype" => "http"
);

function __construct($name, $value, $label="URL", $options=array())
{

data_string::__construct($name, $value, $label, array_merge(array("url"=>array()), $options));

}

/* Convert */
public function verify(&$value, $convert=false, $options=array())
{

if (!is_string($value) || !preg_match($this->opt["regex"], $value))
{
	if ($convert)
		$value = "";
	return false;
}

return true;

}
public function convert(&$value)
{

if (!is_string($value) || !preg_match($this->opt["regex"], $value))
	$value = "";

}
public function convert_after(&$value)
{

$value = preg_replace($this->opt["regex"], "$2", $value);

}

/* View */
function link($target="_blank")
{
	if ($target)
		return "<a href=\"".$this->opt["urltype"]."://$this->value\" target=\"$target\">$this->value</a>";
	else
		return "<a href=\"".$this->opt["urltype"]."://$this->value\">$this->value</a>";
}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
