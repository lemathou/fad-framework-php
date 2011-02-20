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

if (!isset($GLOBALS["_db"]))
{
	if (DEBUG_GENTIME == true)
		gentime("retrieve db() [begin]");
	// TODO : Mettre les données de session de db() dans login()
	$GLOBALS["_db"] = new _db();
	if (DEBUG_GENTIME == true)
		gentime("retrieve db() [end]");
}

if (is_string($query))
{
	return $GLOBALS["_db"]->query($query);
}
else
{
	return $GLOBALS["_db"];
}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__);

?>
