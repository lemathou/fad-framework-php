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

// TODO : simplifier le chargemenbt dynamique de classe !!!!
//echo "<p>__autoload('$class_name')</p>\n";

$s = substr($class_name, -8);

// Framework Managing classes
if ($class_name != "_gestion" && substr($class_name, 0, 1) == "_" && $s == "_gestion")
{
	if (file_exists($filename=PATH_CLASSES."/".substr($class_name, 1, -8).".inc.php"))
		include $filename;
}
elseif (substr($class_name, 0, 1) == "_")
{
	if (file_exists($filename=PATH_CLASSES."/".substr($class_name, 1).".inc.php"))
		include $filename;
}
// Dataobject native class
elseif ($class_name == "dataobject")
{
	include PATH_CLASSES."/dataobject.inc.php";
}
// Permission native class
elseif ($class_name == "permission_info")
{
	include PATH_CLASSES."/permission.inc.php";
}
// Data fields
elseif ($class_name == "data")
{
	include PATH_CLASSES."/data/_data.inc.php";
}
elseif (substr($class_name, 0, 5) == "data_")
{
	if ($name=substr($class_name, 5))
	{
		if (file_exists($filename=PATH_CLASSES."/data/$name.inc.php"))
			include $filename;
		elseif (file_exists($filename=PATH_CLASSES."/data/$name.inc.php"))
			include $filename;
		// TODO : Useful or dumb ..?
		if (!class_exists($class_name, false))
			eval("class $class_name extends data {};");
	}
}
// Framework native classes
elseif (file_exists($filename=PATH_CLASSES."/$class_name.inc.php"))
{
	include $filename;
}
// Project Datamodel
elseif (datamodel()->exists_name($class_name))
{
	if (file_exists($filename=PATH_DATAMODEL."/$class_name.inc.php"))
	{
		include $filename;
	}
	if (!class_exists($class_name, false))
	{
		eval("class $class_name extends dataobject {};");
	}
}
// Project library
elseif (file_exists($filename=PATH_LIBRARY."/$class_name.inc.php"))
{
	include $filename;
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
