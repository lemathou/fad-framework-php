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

if (!defined("PATH_INCLUDE"))
	die("Config file not loaded");

// Variables globales, constructeurs généraux, classes générales, fonctions diverses, etc.
include PATH_INCLUDE."/header.inc.php";

function action($action)
{

if (strpos(".", $action) === null && strpos("/", $action) === null && file_exists($filename=PATH_ROOT."action/$action.inc.php"))
{
	include $filename;
}

}

if (isset($_GET["action"]) && is_string($_GET["action"]))
{
	action($_GET["action"]);
}

?>
