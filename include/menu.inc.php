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

if (!isset($GLOBALS["menu_gestion"]))
{
	if (DEBUG_GENTIME == true)
		gentime("retrieve menu() [begin]");
	if (CACHE)
	{
		if (!($GLOBALS["menu_gestion"]=cache::retrieve("menu_gestion")))
			$GLOBALS["menu_gestion"] = new menu_gestion();
	}
	else // Session
	{
		if (!isset($_SESSION["menu_gestion"]))
			$_SESSION["menu_gestion"] = new menu_gestion();
		$GLOBALS["menu_gestion"] = $_SESSION["menu_gestion"];
	}
	if (DEBUG_GENTIME == true)
		gentime("retrieve menu() [end]");
}

if (is_numeric($ref))
	return $GLOBALS["menu_gestion"]->get($ref);
elseif (is_string($ref))
	return $GLOBALS["menu_gestion"]->get_name($ref);
else
	return $GLOBALS["menu_gestion"];

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
