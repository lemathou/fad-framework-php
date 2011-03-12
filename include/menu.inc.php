<?php

/**
  * $Id$
  * 
  * Copyright 2008-2011 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [begin]");


/**
 * Access function
 */
function menu($ref=null)
{

if (!isset($GLOBALS["_menu"]))
{
	if (DEBUG_GENTIME == true)
		gentime("retrieve menu() [begin]");
	if (CACHE)
	{
		if (!($GLOBALS["_menu"]=cache::retrieve("menu")))
			$GLOBALS["_menu"] = new _menu_manager();
	}
	else // Session
	{
		if (!isset($_SESSION["_menu"]))
			$_SESSION["_menu"] = new _menu_manager();
		$GLOBALS["_menu"] = $_SESSION["_menu"];
	}
	if (DEBUG_GENTIME == true)
		gentime("retrieve menu() [end]");
}

if (is_numeric($ref))
	return $GLOBALS["_menu"]->get($ref);
elseif (is_string($ref))
	return $GLOBALS["_menu"]->get_name($ref);
else
	return $GLOBALS["_menu"];

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
