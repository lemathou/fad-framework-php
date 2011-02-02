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
	gentime(__FILE__);


/**
 * Database access function
 */
function db($query=null)
{

if (!isset($GLOBALS["db"]))
{
	if (DEBUG_GENTIME == true)
		gentime("retrieve db() [begin]");
	// TODO : Mettre les données de session de db() dans login()
	/*
	if (!isset($_SESSION["db"]))
		$_SESSION["db"] = new db();
	$GLOBALS["db"] = $_SESSION["db"];
	*/
	$GLOBALS["db"] = new db();
	if (DEBUG_GENTIME == true)
		gentime("retrieve db() [end]");
}

if (is_string($query))
{
	return $GLOBALS["db"]->query($query);
}
else
{
	return $GLOBALS["db"];
}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__);

?>
