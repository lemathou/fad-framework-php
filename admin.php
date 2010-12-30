<?

/**
  * $Id$
  * 
  * Copyright 2008-2010 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */

define("SITE_LANG",SITE_LANG_DEFAULT);
define("SITE_LANG_ID",SITE_LANG_DEFAULT_ID);

include PATH_INCLUDE."/header.inc.php";

// Session refresh
session_start();
login()->refresh();

// Logged as super-admin
if (login()->perm(6))
{

define("ADMIN_OK",true);
// Display admin panel
include PATH_ADMIN."/index.inc.php";

}

// Otherwise... bye bye!
else
{

header("HTTP/1.0 401 Unavailable");
// Display admin login
include PATH_ADMIN."/index_login.inc.php";

}


?>