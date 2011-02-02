<?php

/**
  * $Id: page.inc.php 76 2009-10-15 09:24:20Z mathieu $
  * 
  * Copyright 2008-2011 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * http://sourceforge.net/projects/phpfadframework/
  * Licence : http://www.gnu.org/copyleft/gpl.html  GNU General Public License
  * 
  */

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [begin]");


/**
 * Access function
 */
function page($ref=null)
{

if (!isset($GLOBALS["page_gestion"]))
{
	if (DEBUG_GENTIME == true)
		gentime("retrieve page() [begin]");
	if (CACHE)
	{
		if (!($GLOBALS["page_gestion"]=cache::retrieve("page_gestion")))
			$GLOBALS["page_gestion"] = new page_gestion();
	}
	else // Session
	{
		if (!isset($_SESSION["page_gestion"]))
			$_SESSION["page_gestion"] = new page_gestion();
		$GLOBALS["page_gestion"] = $_SESSION["page_gestion"];
	}
	if (DEBUG_GENTIME == true)
		gentime("retrieve page() [end]");
}

if (is_numeric($ref))
	return $GLOBALS["page_gestion"]->get($ref);
elseif (is_string($ref))
	return $GLOBALS["page_gestion"]->get_name($ref);
else
	return $GLOBALS["page_gestion"];

}

/**
 * Access the current page
 *
 * @return menu
 */
function page_current()
{

return page(PAGE_ID);

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
