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

if (!isset($GLOBALS["_template"]))
{
	if (DEBUG_GENTIME == true)
		gentime("retrieve template() [begin]");
	if (CACHE)
	{
		if (!($GLOBALS["_template"]=cache::retrieve("template")))
			$GLOBALS["_template"] = new _template_manager();
	}
	else // Session
	{
		if (!isset($_SESSION["_template"]))
			$_SESSION["_template"] = new _template_manager();
		$GLOBALS["_template"] = $_SESSION["_template"];
	}
	if (DEBUG_GENTIME == true)
		gentime("retrieve template() [end]");
}

if (is_numeric($ref))
	return $GLOBALS["_template"]->get($ref);
elseif (is_string($ref))
	return $GLOBALS["_template"]->get_name($ref);
else
	return $GLOBALS["_template"];

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
