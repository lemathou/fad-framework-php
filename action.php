<?php

/**
  * $Id: action.php 28 2011-01-17 07:50:38Z lemathoufou $
  * 
  * Copyright 2008-2011 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */

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
