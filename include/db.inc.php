<?

/**
  * $Id: db.inc.php 59 2009-03-03 15:48:26Z mathieu $
  * 
  * Copyright 2008 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */

if (DEBUG_GENTIME == true)
	gentime(__FILE__);

include PATH_INCLUDE."/db/db.inc.php";
include PATH_INCLUDE."/db/".DB.".inc.php";

if (DEBUG_GENTIME == true)
	gentime(__FILE__);

?>
