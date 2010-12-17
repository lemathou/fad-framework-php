<?php

/**
  * $Id$
  * 
  * Copyright 2008 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */

// Security
define("SECURITY_EMAIL","lemathou@free.fr");

// Base de donnée
define("DB","mysql");
define("DB_VERSION","5.1");
define("DB_PERSISTANT",true);
define("DB_CHARSET","UTF8");
define("DB_ENGINE","MYISAM");
// Identifiants BDD -> A terme les virer dés la première connexion, donc les passer en variable. A voir car ils vont se retrouver dans les fichiers de session...
define("DB_HOST","localhost");
define("DB_PORT","3306");
define("DB_USERNAME","username");
define("DB_PASSWORD","password");
define("DB_BASE","database");

// Framework Folders
define("PATH_FRAMEWORK","/home/framework");
define("PATH_ADMIN",PATH_FRAMEWORK."/admin");
define("PATH_INCLUDE",PATH_FRAMEWORK."/include");
// Project Folders
define("PATH_ROOT","/home/website");
define("PATH_LIBRARY",PATH_ROOT."/library"); // You can also store libraries in the framework path
define("PATH_TEMPLATE",PATH_ROOT."/template"); // Template files and scripts
define("PATH_PAGE",PATH_ROOT."/page"); // Page scripts
define("PATH_DATA",PATH_ROOT."/data"); // Project data
define("PATH_CACHE",PATH_ROOT."/cache"); // Template cache

// Site URL, Domain name and path
define("SITE_DOMAIN","website");
define("SITE_BASEPATH","/");
define("SITE_SSL_ENABLE",false);
define("SITE_SSL_REDIRECT",false);

// Charset
define("SITE_CHARSET","UTF-8");
// Default lang
define("SITE_MULTILANG", true);
define("SITE_LANG_DEFAULT","fr");
define("SITE_LANG_DEFAULT_ID",2); // TODO : retrieve from Database
setlocale (LC_TIME, 'fr_FR.utf8','fra'); // TODO : function to set the right LC_TIME in function of the language and the charset

// Copyright
define("SITE_COPYRIGHT","");
define("SITE_ORGANISATION","");

// Image options : Jpeg Quality
define("IMG_JPEG_QUALITY",90);

// Libraries
define("LIBRARY_AUTOLOADALL",true); // Autoload every library in the global managing object

// Pages
define("PAGE_AUTOLOADALL",false); // Autoload every library in the global managing object. Carefull if there are a lot !
define("PAGE_CACHE",false); // Used only when not logged in, for the cacheable pages.
// Menus
define("MENU_AUTOLOADALL",true); // Autoload every library in the global managing object

// APC CACHE
define("APC_CACHE",true);
define("APC_CACHE_GESTION_TTL",3600);
define("APC_CACHE_DATAMODEL_TTL",3600);		// 1h : also used for libraries and databanks
define("APC_CACHE_DATAOBJECT_TTL",300);		// 5mn : be carefull this can use a lot of memory ! TODO : use a TTL parameter for each datamodel and wether or not to use CACHE in case of specific objects
define("APC_CACHE_TEMPLATE_TTL",3600);		// 1h
define("APC_CACHE_MENU_TTL",3600);			// 1h
define("APC_CACHE_PAGE_TTL",3600);			// 1h

// Templates
define("TEMPLATE_AUTOLOADALL",false); // Autoload every template in the global managing object. Carefull if there are a lot !
// Template cache
define("TEMPLATE_CACHE",true);
define("TEMPLATE_CACHE_TYPE","file"); // options are "file" or "apc"
define("TEMPLATE_CACHE_TIME",300);
define("TEMPLATE_CACHE_MIN_TIME",10);
define("TEMPLATE_CACHE_MAX_TIME",300);
define("TEMPLATE_CACHE_LOG",true);

// DEBUG
define("DEBUG_GENTIME",false);
define("DEBUG_PERMISSION",false);
define("DEBUG_LIBRARY",false);
define("DEBUG_DATAMODEL",false);
define("DEBUG_TEMPLATE",false);
define("DEBUG_PAGE",false);
define("DEBUG_MENU",false);
define("DEBUG_LOGIN",false);
define("DEBUG_SESSION",false);
define("DEBUG_CACHE",false);

// LOG
define("LOG_ERROR",true);
define("LOG_DB_ERROR",true);
define("LOG_DB_QUERIES",true);
define("LOG_DB_ERROR_PATH",PATH_ROOT."/log/mysql_error.log");

// STATS
define("STATS_DB",false);

?>
