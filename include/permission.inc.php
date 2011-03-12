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

if (!isset($GLOBALS["_permission"]))
{
	if (DEBUG_GENTIME == true)
		gentime("retrieve permission() [begin]");
	if (CACHE)
	{
		if (!($GLOBALS["_permission"]=cache::retrieve("permission")))
			$GLOBALS["_permission"] = new _permission_manager();
	}
	else // Session
	{
		if (!isset($_SESSION["_permission"]))
			$_SESSION["_permission"] = new _permission_manager();
		$GLOBALS["_permission"] = $_SESSION["_permission"];
	}
	if (DEBUG_GENTIME == true)
		gentime("retrieve permission() [end]");
}

if (is_numeric($ref))
	return $GLOBALS["_permission"]->get($ref);
elseif (is_string($ref))
	return $GLOBALS["_permission"]->get_name($ref);
else
	return $GLOBALS["_permission"];

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
