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

// Performance & Optimisation
if (DEBUG_GENTIME == true)
	include PATH_INCLUDE."/gentime.inc.php";

// Common classes inclusion
include PATH_INCLUDE."/class_autoload.inc.php";

// Database
include PATH_INCLUDE."/db.inc.php";

// Errors, exceptions, feedbacks, etc.
//include "include/error.inc.php";
//include "include/exceptions.inc.php";

// Security : IP ban, logs, etc.
//include "include/security.inc.php";

if (HEADER_LOAD == "full")
{

// Global variables
include PATH_INCLUDE."/globals.inc.php";

// Project libraries
include PATH_INCLUDE."/library.inc.php";

// Data types, data models, data banks
include PATH_INCLUDE."/data.inc.php";
include PATH_INCLUDE."/datamodel.inc.php";

// Permissions
include PATH_INCLUDE."/permission.inc.php";

// Templates
include PATH_INCLUDE."/template.inc.php";

// Menu & Pages
include PATH_INCLUDE."/page.inc.php";
include PATH_INCLUDE."/menu.inc.php";

// Lang
include PATH_INCLUDE."/lang.inc.php";

// Mise en place des fonctions associées aucx banques de donnée !!
datamodel();

}

if (SESSION_START)
{

// Login
include PATH_INCLUDE."/login.inc.php";

// Login refresh
login()->refresh();

}

?>
