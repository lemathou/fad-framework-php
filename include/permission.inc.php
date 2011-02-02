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
function permission($ref=null)
{

if (!isset($GLOBALS["permission_gestion"]))
{
	if (DEBUG_GENTIME == true)
		gentime("retrieve permission() [begin]");
	if (CACHE)
	{
		if (!($GLOBALS["permission_gestion"]=cache::retrieve("permission_gestion")))
			$GLOBALS["permission_gestion"] = new permission_gestion();
	}
	else // Session
	{
		if (!isset($_SESSION["permission_gestion"]))
			$_SESSION["permission_gestion"] = new permission_gestion();
		$GLOBALS["permission_gestion"] = $_SESSION["permission_gestion"];
	}
	if (DEBUG_GENTIME == true)
		gentime("retrieve permission() [end]");
}

if (is_numeric($ref))
	return $GLOBALS["permission_gestion"]->get($ref);
elseif (is_string($ref))
	return $GLOBALS["permission_gestion"]->get_name($ref);
else
	return $GLOBALS["permission_gestion"];

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
