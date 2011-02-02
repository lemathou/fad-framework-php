<?php

/**
  * $Id: apc.inc.php 30 2011-01-18 23:29:06Z lemathoufou $
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


class cache implements cache_i
{

/**
 * Retrieve objects
 * 
 * @param name mixed (string or array)
 * @return mixed (string or array)
 * @author mathieu
 */
public static function retrieve($name)
{

if (false)
	echo "<p>Retrieve $name from cache</p>";

return apc_fetch($name);

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
public static function store($name, $object, $ttl=CACHE_GESTION_TTL)
{

if (false)
	echo "<p>Store $name in cache</p>";

return apc_store($name, $object, $ttl);

}

/**
 * Remove objects
 * 
 * @param name mixed (string or array)
 * @return boolean
 * @author mathieu
 */
public static function delete($name)
{

if (false)
	echo "<p>Delete $name in cache</p>";

return apc_delete($name);

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [begin]");

?>	