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

$s = substr($class_name, -8);

// Gestion
if ($s == "_gestion")
{
	if (file_exists($library=PATH_CLASSES."/".substr($class_name, 0, -8).".inc.php"))
		include $library;
}
// Data
elseif ($class_name == "data")
{
	include PATH_CLASSES."/data/_data.inc.php";
}
elseif (substr($class_name, 0, 5) == "data_")
{
	if ($name=substr($class_name, 5))
	{
		if (file_exists($library=PATH_CLASSES."/data/$name.inc.php"))
			include $library;
		elseif (file_exists($library=PATH_ROOT."/classes/data/$name.inc.php"))
			include $library;
		if (!class_exists($class_name, false))
			eval("class $class_name extends data {};");
	}
}
// Datamodel / Dataobject
elseif ($class_name == "dataobject")
{
	include PATH_CLASSES."/dataobject.inc.php";
}
// Framework native classes
elseif (file_exists($library=PATH_CLASSES."/$class_name.inc.php"))
{
	include $library;
}
// Project library
elseif (file_exists($library=PATH_LIBRARY."/$class_name.inc.php"))
{
	include $library;
}
elseif (datamodel()->exists_name($class_name))
{
	if (file_exists($library=PATH_DATAMODEL."/$class_name.inc.php"))
	{
		include $library;
	}
	if (!class_exists($class_name, false))
	{
		eval("class $class_name extends dataobject {};");
	}
}

/*
if (!class_exists($class_name, false))
{
	die("Class $class_name could not be loaded...");
}
*/

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
