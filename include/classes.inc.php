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
 * Auto loading of class definitions in libraries
 */
function __autoload($class_name)
{

//echo "<p>$class_name</p>\n";

$s = substr($class_name, -8);

// Datamodel
if ($s == "_agregat" && ($name=substr($class_name, 0, -8)) && datamodel()->exists_name($name))
{
	datamodel()->{$name};
	if (!class_exists($class_name))
	{
		eval("class $class_name extends dataobject {};");
	}
}
// Gestion
elseif ($s == "_gestion" && file_exists($library=PATH_CLASSES."/".substr($class_name, 0, -8).".inc.php"))
{
	include $library;
}
// Framework native classes
elseif (file_exists($library=PATH_CLASSES."/$class_name.inc.php"))
{
	include $library;
}
// Project library
elseif (file_exists($library=PATH_ROOT."/library/$class_name.inc.php"))
{
	include $library;
}

if (!class_exists($class_name))
{
	die("Class $class_name could not be loaded...");
}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
