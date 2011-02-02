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
  * Multiple template caches are supported : APC, Memcached, DB or directly in a cache folder
  * TODO : correct APC and implement Memcached and Db (effectively MySQL)
  * 
  */

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [begin]");


/**
 * Access function
 */
function template($ref=null)
{

if (!isset($GLOBALS["template_gestion"]))
{
	if (DEBUG_GENTIME == true)
		gentime("retrieve template() [begin]");
	if (CACHE)
	{
		if (!($GLOBALS["template_gestion"]=cache::retrieve("template_gestion")))
			$GLOBALS["template_gestion"] = new template_gestion();
	}
	else // Session
	{
		if (!isset($_SESSION["template_gestion"]))
			$_SESSION["template_gestion"] = new template_gestion();
		$GLOBALS["template_gestion"] = $_SESSION["template_gestion"];
	}
	if (DEBUG_GENTIME == true)
		gentime("retrieve template() [end]");
}

if (is_numeric($ref))
	return $GLOBALS["template_gestion"]->get($ref);
elseif (is_string($ref))
	return $GLOBALS["template_gestion"]->get_name($ref);
else
	return $GLOBALS["template_gestion"];

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
