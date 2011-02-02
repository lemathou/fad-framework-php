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
 * Accès à l'objet login (unique) 
 */
function login()
{

if (!isset($GLOBALS["login"]))
{
	if (DEBUG_GENTIME == true)
		gentime("retrieve login() [begin]");
	// Session
	{
		if (!isset($_SESSION["login"]))
			$_SESSION["login"] = new login();
		$GLOBALS["login"] = $_SESSION["login"];
	}
	if (DEBUG_GENTIME == true)
		gentime("retrieve login() [end]");
}

return $GLOBALS["login"];

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
