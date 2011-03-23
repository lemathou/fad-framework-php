<?php

/**
  * $Id: file.inc.php 65 2011-03-20 12:43:50Z lemathoufou $
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

public static function retrieve($name)
{

if (DEBUG_CACHE)
	echo "<p>Retrieve $name from cache</p>";

return apc_fetch($name);

}

public static function store($name, $object, $ttl=CACHE_GESTION_TTL)
{

if (DEBUG_CACHE)
	echo "<p>Store $name in cache</p>";

return apc_store($name, $object, $ttl);

}

public static function delete($name)
{

if (DEBUG_CACHE)
	echo "<p>Delete $name in cache</p>";

return apc_delete($name);

}

public static function delete_all()
{

if (DEBUG_CACHE)
	echo "<p>Delete user cache</p>";

return apc_clear_cache("user");

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
