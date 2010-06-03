<?

/**
  * $Id: session_start.inc.php 76 2009-10-15 09:24:20Z mathieu $
  * 
  * Copyright 2008 Mathieu Moulin - iProspective - lemathou@free.fr
  * 
  * This file is part of FTNGroupWare.
  * 
  */

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [begin]");

session_start();

// Récupération des variables de session
$session_vars = array("db", "lang", "globals", "library_gestion", "datamodel_gestion", "databank_gestion", "page_gestion", "menu_gestion", "login");
foreach ($session_vars as $name)
{
	if (isset($_SESSION[$name]))
	{
		if (DEBUG_SESSION == true)
			echo "<p>Retrieving session var : $name</p>\n";
		${$name} = $_SESSION[$name];
	}
}
unset($session_vars);

login()->refresh();

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>