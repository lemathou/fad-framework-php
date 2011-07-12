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

if (!defined("ADMIN_LOAD"))
	define("ADMIN_LOAD",false);
if (!defined("HEADER_LOAD"))
	define("HEADER_LOAD","basic");
if (!defined("SESSION_START"))
	define("SESSION_START",false);

// Security
define("SECURITY_EMAIL",${SECURITY_EMAIL});

// Copyright
define("SITE_COPYRIGHT",${SITE_COPYRIGHT});
define("SITE_ORGANISATION",${SITE_ORGANISATION});

// Site URL, Domain name and path
define("SITE_DOMAIN",${SITE_DOMAIN});
define("SITE_BASEPATH",${SITE_BASEPATH});
define("SITE_SSL_ENABLE",${SITE_SSL_ENABLE});
define("SITE_SSL_REDIRECT",${SITE_SSL_REDIRECT});

// Database
define("DB_TYPE",${DB_TYPE});
define("DB_PERSISTANT",false);
define("DB_CHARSET","UTF8");
// Login
define("DB_HOST",${DB_HOST});
define("DB_PORT","3306");
define("DB_USERNAME",${DB_USERNAME});
define("DB_PASSWORD",${DB_PASSWORD});
// Framework shared database (datamodel, pagemodel, templates, etc.) 
define("DB_FW_SHARED_BASE",${DB_BASE});
// Framework project specifics database (page, menu, account, etc.)
define("DB_FW_PROJECT_BASE",${DB_BASE});
// Project Data database (datamodel specific tables)
define("DB_PJ_DATA_BASE",${DB_BASE});
// Default database (depeacated)
define("DB_BASE",${DB_BASE});

// Framework Folders
define("PATH_FRAMEWORK",${PATH_FRAMEWORK});
define("PATH_ADMIN",PATH_FRAMEWORK."/admin");
define("PATH_INCLUDE",PATH_FRAMEWORK."/include");
define("PATH_CLASSES",PATH_FRAMEWORK."/classes");
// Project Folders
define("PATH_ROOT",${PATH_ROOT});
define("PATH_LIBRARY",PATH_ROOT."/classes/library"); // You can also store libraries in the framework path
define("PATH_DATAMODEL",PATH_ROOT."/classes/datamodel"); // You can also store libraries in the framework path
define("PATH_TEMPLATE",PATH_ROOT."/template"); // Template files and scripts
define("PATH_PAGE",PATH_ROOT."/page"); // Page scripts
define("PATH_DATA",PATH_ROOT."/data"); // Project data
define("PATH_CACHE",PATH_ROOT."/cache"); // Template cache
define("PATH_LOG",PATH_ROOT."/log");

// Charset
define("SITE_CHARSET","UTF-8");
// Multi lang
define("SITE_MULTILANG",${SITE_MULTILANG});
// Default lang
define("SITE_LANG_DEFAULT","fr");
define("SITE_LANG_DEFAULT_ID",2);
// TODO : function to set the right LC_TIME using language and charset
setlocale (LC_TIME, "fr_FR.utf8", "fra");

// Image options : Jpeg Quality
define("IMG_JPEG_QUALITY",90);

// Object cache
define("CACHE",${CACHE});
define("CACHE_TYPE",${CACHE_TYPE});
// TODO : use a TTL parameter for each datamodel and...
define("CACHE_GESTION_TTL",3600);
define("CACHE_DATAOBJECT_TTL",300); // 5mn : be carefull this can use a lot of memory !

// Template cache
define("TEMPLATE_CACHE",${TEMPLATE_CACHE});
define("TEMPLATE_CACHE_TYPE",${TEMPLATE_CACHE_TYPE}); // options are "file" and soon "apc", "memcached", etc.
define("TEMPLATE_CACHE_TIME",300);
define("TEMPLATE_CACHE_MIN_TIME",10);
define("TEMPLATE_CACHE_MAX_TIME",300);
define("TEMPLATE_CACHE_LOG",true);

// Debug
define("DEBUG_GENTIME",false); // For Gentime statistics
define("DEBUG_PERMISSION",false);
define("DEBUG_LIBRARY",false);
define("DEBUG_DATAMODEL",false);
define("DEBUG_TEMPLATE",false);
define("DEBUG_PAGE",false);
define("DEBUG_MENU",false);
define("DEBUG_LOGIN",false);
define("DEBUG_SESSION",false);
define("DEBUG_CACHE",false);

// Logging
define("LOG_ERROR",true);
define("LOG_DB_ERROR",true);
define("LOG_DB_QUERIES",true);
define("LOG_DB_ERROR_PATH",PATH_LOG."/mysql_error.log");

// Stats
define("STATS_DB",false);

// Default pages
define("PAGE_DEFAULT_ID",1);
define("PAGE_UNDEFINED_ID",1);
define("PAGE_UNAUTHORIZED_ID",1);

?>
