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
 * Retrieve objects
 * 
 * @param name mixed (string or array)
 * @return mixed (string or array)
 * @author mathieu
 */
function object_cache_retrieve($name)
{

if (false)
	echo "<p>Retrieve $name from cache</p>";

if (OBJECT_CACHE_TYPE == "apc")
{
	return apc_fetch($name);
}

}

/**
 * Store objects
 * 
 * @param name string
 * @param object mixed
 * @param ttl integer
 * @return boolean
 * @author mathieu
 */
function object_cache_store($name, $object, $ttl=OBJECT_CACHE_GESTION_TTL)
{

if (false)
	echo "<p>Store $name in cache</p>";

if (OBJECT_CACHE_TYPE == "apc")
{
	return apc_store($name, $object, $ttl);
}

}

/**
 * Remove objects
 * 
 * @param name mixed (string or array)
 * @return boolean
 * @author mathieu
 */
function object_cache_delete($name)
{

if (false)
	echo "<p>Delete $name in cache</p>";

if (OBJECT_CACHE_TYPE == "apc")
{
	return apc_delete($name);
}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
