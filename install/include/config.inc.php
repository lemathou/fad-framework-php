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

// PHP capabilities
$php = array();
// Cache
$php["cache"]["file"] = true;
$php["cache"]["apc"] = function_exists("apc_fetch") ? true : false;
$php["cache"]["memcached"] = class_exists("Memcached") ? true : false;
// Database
$php["db"]["mysql"] = function_exists("mysql_connect") ? true : false;
$php["db"]["mysqli"] = function_exists("mysqli_connect") ? true : false;
$php["db"]["postgresql"] = function_exists("pg_connect") ? true : false;

// Config params
$config_list = array
(
	"PATH_FRAMEWORK"=>array("label"=>"Framework root folder", "type"=>"text", "value"=>PATH_FRAMEWORK, "onclick"=>"folder_lookup('PATH_FRAMEWORK')", "width"=>"100%"),
	"PATH_ROOT"=>array("label"=>"Project install folder", "info"=>"Default to Framework folder", "type"=>"text", "value"=>PATH_FRAMEWORK, "onclick"=>"folder_lookup('PATH_ROOT')", "width"=>"100%"),
	"SECURITY_EMAIL"=>array("label"=>"Security admin email", "type"=>"text", "value"=>"", "width"=>"100%"),
	"SITE_COPYRIGHT"=>array("label"=>"Copyright", "type"=>"text", "value"=>"", "width"=>"100%", "ok"=>true),
	"SITE_ORGANISATION"=>array("label"=>"Organisation", "type"=>"text", "value"=>"My company", "width"=>"100%", "ok"=>true),
	"SITE_DOMAIN"=>array("label"=>"Domain name", "type"=>"text", "value"=>$_SERVER["SERVER_NAME"], "width"=>"100%"),
	"SITE_BASEPATH"=>array("label"=>"Domain location", "type"=>"text", "value"=>"/", "width"=>"100%"),
	"SITE_SSL_ENABLE"=>array("label"=>"SSL enabled", "type"=>"boolean", "value"=>"0", "ok"=>true),
	"SITE_SSL_REDIRECT"=>array("label"=>"SSL force redirect", "type"=>"boolean", "value"=>"0", "ok"=>true),
	"DB_TYPE"=>array("label"=>"Database engine", "type"=>"select", "value"=>"MySQL", "select_list"=>array("mysql", "mysqli", "postgresql"), "select_control"=>&$php["db"]),
	//"DB_PERSISTANT"=>array("label"=>"Database engine", "type"=>"select", "select_list"=>array("MySQL", "MySQLi", "postgreSQL"), "select_control"=>&$php),
	"DB_HOST"=>array("label"=>"Database hostname", "type"=>"text", "value"=>"", "width"=>"100%"),
	"DB_USERNAME"=>array("label"=>"Database username", "type"=>"text", "value"=>"", "width"=>"100%"),
	"DB_PASSWORD"=>array("label"=>"Database password", "type"=>"password", "value"=>"", "width"=>"100%"),
	"DB_BASE"=>array("label"=>"Database name", "type"=>"text", "value"=>"", "width"=>"100%"),
	"SITE_MULTILANG"=>array("label"=>"Multi lang", "type"=>"boolean", "value"=>"0", "ok"=>true),
	"CACHE_TYPE"=>array("label"=>"Object cache type", "info"=>"highly recommended if available", "type"=>"select", "value"=>"", "select_list"=>array("apc", "memcached"), "select_control"=>&$php["cache"], "ok"=>true),
	"TEMPLATE_CACHE_TYPE"=>array("label"=>"Template cache", "info"=>"highly recommended", "type"=>"select", "value"=>"", "select_list"=>array("file", "apc", "memcached"), "select_control"=>&$php["cache"], "value"=>"file", "ok"=>true),
);

?>