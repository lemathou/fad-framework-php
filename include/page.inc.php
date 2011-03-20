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

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [begin]");


/**
 * Access function
 */
function pagemodel($ref=null)
{

if (!isset($GLOBALS["_pagemodel"]))
{
	if (DEBUG_GENTIME == true)
		gentime("retrieve pagemodel() [begin]");
	if (CACHE)
	{
		if (!($GLOBALS["_pagemodel"]=cache::retrieve("pagemodel")))
			$GLOBALS["_pagemodel"] = new _pagemodel_manager();
	}
	else // Session
	{
		if (!isset($_SESSION["_pagemodel"]))
			$_SESSION["_pagemodel"] = new _pagemodel_manager();
		$GLOBALS["_pagemodel"] = $_SESSION["_pagemodel"];
	}
	if (DEBUG_GENTIME == true)
		gentime("retrieve pagemodel() [end]");
}

if (is_numeric($ref))
	return $GLOBALS["_pagemodel"]->get($ref);
elseif (is_string($ref))
	return $GLOBALS["_pagemodel"]->get_name($ref);
else
	return $GLOBALS["_pagemodel"];

}

/**
 * Access function
 */
function page($ref=null)
{

if (!isset($GLOBALS["_page"]))
{
	if (DEBUG_GENTIME == true)
		gentime("retrieve page() [begin]");
	if (CACHE)
	{
		if (!($GLOBALS["_page"]=cache::retrieve("page")))
			$GLOBALS["_page"] = new _page_manager();
	}
	else // Session
	{
		if (!isset($_SESSION["_page"]))
			$_SESSION["_page"] = new _page_manager();
		$GLOBALS["_page"] = $_SESSION["_page"];
	}
	if (DEBUG_GENTIME == true)
		gentime("retrieve page() [end]");
}

if (is_numeric($ref))
	return $GLOBALS["_page"]->get($ref);
elseif (is_string($ref))
	return $GLOBALS["_page"]->get_name($ref);
else
	return $GLOBALS["_page"];

}

/**
 * Access the current page
 */
function page_current()
{

return page(PAGE_ID);

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
