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

if (!isset($GLOBALS["_library"]))
{
	if (DEBUG_GENTIME == true)
		gentime("retrieve library() [begin]");
	if (CACHE)
	{
		if (!($GLOBALS["_library"]=cache::retrieve("library")))
			$GLOBALS["_library"] = new _library_gestion();
	}
	else // Session
	{
		if (!isset($_SESSION["_library"]))
			$_SESSION["_library"] = new _library_gestion();
		$GLOBALS["_library"] = $_SESSION["_library"];
	}
	if (DEBUG_GENTIME == true)
		gentime("retrieve library() [end]");
}

if (is_numeric($ref))
	return $GLOBALS["_library"]->get($ref);
elseif (is_string($ref))
	return $GLOBALS["_library"]->get_name($ref);
else
	return $GLOBALS["_library"];

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
