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

if (!isset($GLOBALS["globals"]))
{
	if (!isset($_SESSION["globals"]))
		$_SESSION["globals"] = new globals();
	$GLOBALS["globals"] = $_SESSION["globals"];
}

return $GLOBALS["globals"];
	
}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
