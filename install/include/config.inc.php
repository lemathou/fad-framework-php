<?php

/**
  * $Id: config.inc.php 50 2011-03-05 18:30:47Z lemathoufou $
  * 
  * Copyright 2008-2011 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * http://sourceforge.net/projects/phpfadframework/
  * Licence : http://www.gnu.org/copyleft/gpl.html  GNU General Public License
  * 
  */

// Define PATH
$path_e = explode("/", $_SERVER["SCRIPT_FILENAME"]);
array_pop($path_e);
array_pop($path_e);
define("PATH_FRAMEWORK", implode("/", $path_e));
unset($path_e);

session_start();

$php = array();
// Cache
$php["cache"]["file"] = true;
$php["cache"]["apc"] = function_exists("apc_fetch") ? true : false;
$php["cache"]["memcached"] = class_exists("Memcached") ? true : false;
// Database
$php["db"]["mysql"] = function_exists("mysql_connect") ? true : false;
$php["db"]["mysqli"] = function_exists("mysqli_connect") ? true : false;
$php["db"]["postgresql"] = function_exists("pg_connect") ? true : false;

?>