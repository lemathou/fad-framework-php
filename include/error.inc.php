<?

/**
  * $Id: error.inc.php 59 2009-03-03 15:48:26Z mathieu $
  * 
  * Copyright 2008 Mathieu Moulin - iProspective - lemathou@free.fr
  * 
  * This file is part of FTNGroupWare.
  * 
  */

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__);

class error
{

protected $list = array();

function add($cat, $description)
{

$this->list[$cat][] = $description;

}

function disp()
{

while (list($cat, $list)=each($this->list))
{
	print "<p>$cat</p>";
	print "<ul>";
	while (list(,$description)=each($list))
		print "<li>$description</li>";
	print "</ul>";
}

}

}

// Acc�s � l'objet

function error()
{

return $GLOBALS["error"];

}

$GLOBALS["error"] = new error();

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__);

?>