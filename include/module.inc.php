<?php

/**
  * $Id$
  * 
  * Copyright 2008 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * http://sourceforge.net/projects/phpfadframework/
  * Licence : http://www.gnu.org/copyleft/gpl.html  GNU General Public License
  * 
  */

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [begin]");


// Access

function module($name="")
{


if (!isset($GLOBALS["modules"]))
{
	$GLOBALS["modules"] = new modules();
}

if ($name)
{
	return $GLOBALS["modules"]->get($name);
}
else
{
	return $GLOBALS["modules"];
}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>