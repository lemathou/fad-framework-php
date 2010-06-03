<?

/**
  * $Id: db.inc.php 59 2009-03-03 15:48:26Z mathieu $
  * 
  * � Copyright 2008 Mathieu Moulin - iProspective - lemathou@free.fr �
  * 
  * This file is part of FTNGroupWare.
  * 
  */

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__);

include "include/db/db.inc.php";
include "include/db/".DB.".inc.php";

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__);

?>
