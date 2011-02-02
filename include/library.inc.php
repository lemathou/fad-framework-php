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
 * Access function
 */
function library($ref=null)
{

if (!isset($GLOBALS["library_gestion"]))
{
	if (DEBUG_GENTIME == true)
		gentime("retrieve library() [begin]");
	if (CACHE)
	{
		if (!($GLOBALS["library_gestion"]=cache::retrieve("library_gestion")))
			$GLOBALS["library_gestion"] = new library_gestion();
	}
	else // Session
	{
		if (!isset($_SESSION["library_gestion"]))
			$_SESSION["library_gestion"] = new library_gestion();
		$GLOBALS["library_gestion"] = $_SESSION["library_gestion"];
	}
	if (DEBUG_GENTIME == true)
		gentime("retrieve library() [end]");
}

if (is_numeric($ref))
	return $GLOBALS["library_gestion"]->get($ref);
elseif (is_string($ref))
	return $GLOBALS["library_gestion"]->get_name($ref);
else
	return $GLOBALS["library_gestion"];

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
