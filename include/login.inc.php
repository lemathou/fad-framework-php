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

if (!isset($GLOBALS["_login"]))
{
	if (DEBUG_GENTIME == true)
		gentime("retrieve login() [begin]");
	// Session start
	if (DEBUG_GENTIME == true)
		gentime("Session_start [begin]");
	session_start();
	if (DEBUG_GENTIME == true)
		gentime("Session_start [end]");
	// Session
	{
		if (!isset($_SESSION["_login"]))
			$_SESSION["_login"] = new _login();
		$GLOBALS["_login"] = $_SESSION["_login"];
	}
	if (DEBUG_GENTIME == true)
		gentime("retrieve login() [end]");
}

return $GLOBALS["_login"];

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
