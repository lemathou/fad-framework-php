<?php

/**
  * $Id$
  * 
  * Copyright 2008-2011 Mathieu Moulin - iProspective - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * http://sourceforge.net/projects/phpfadframework/
  * Licence : http://www.gnu.org/copyleft/gpl.html  GNU General Public License
  * 
  */

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [begin]");


// Access function
function globals()
{

if (!isset($GLOBALS["_globals"]))
{
	if (!isset($_SESSION["_globals"]))
		$_SESSION["_globals"] = new _globals();
	// TODO : pas en session !
	$GLOBALS["_globals"] = $_SESSION["_globals"];
}

return $GLOBALS["_globals"];
	
}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
