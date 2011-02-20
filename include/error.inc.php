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


/*
 * Access function
 */
function error()
{

if (!isset($GLOBALS["_error"]))
	$GLOBALS["_error"] = new _error();

return $GLOBALS["_error"];

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
