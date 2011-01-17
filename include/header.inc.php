<?

/**
  * $Id$
  * 
  * Copyright 2008-2010 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */

// Performance & Optimisation
if (DEBUG_GENTIME == true)
	include PATH_INCLUDE."/gentime.inc.php";


// Common classes
include PATH_INCLUDE."/classes.inc.php";
// Forms
//include "include/forms.inc.php";
// text
//include "include/text.inc.php";
// email
//include "include/email.inc.php";
// ...

// Database
include PATH_INCLUDE."/db.inc.php";

// Object cache
include PATH_INCLUDE."/cache.inc.php";

// Object gestion classes
include PATH_INCLUDE."/gestion.inc.php";

// Project libraries
include PATH_INCLUDE."/library.inc.php";

// Data types, data models, data banks
include PATH_INCLUDE."/data.inc.php";
include PATH_INCLUDE."/datamodel.inc.php";
include PATH_INCLUDE."/data_display.inc.php";
include PATH_INCLUDE."/data_bank.inc.php";

// GLobal variables
include PATH_INCLUDE."/globals.inc.php";

// Errors, exceptions, feedbacks, etc.
//include "include/error.inc.php";
//include "include/exceptions.inc.php";

// Security : IP ban, logs, etc.
//include "include/security.inc.php";

// URL rewriting : actually depreacated, but...
//include "include/rewriting.inc.php";

// Permissions
include PATH_INCLUDE."/permission.inc.php";

// Menu & Pages
include PATH_INCLUDE."/page.inc.php";
include PATH_INCLUDE."/menu.inc.php";

// Login
include PATH_INCLUDE."/login.inc.php";

// Templates
include PATH_INCLUDE."/template.inc.php";


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
