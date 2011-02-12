<?php

/**
  * $Id: memcached.inc.php 30 2011-01-18 23:29:06Z lemathoufou $
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

protected static $m = null;

public static function init()
{

self::$m = new Memcached();
self::$m->addServer('localhost', 11211);

}

public static function retrieve($name)
{

if (DEBUG_CACHE)
	echo "<p>Retrieve $name from cache</p>";

if (is_array($name))
	return self::$m->get($name);
else
	return self::$m->getMulti($name);

}

public static function store($name, $object, $ttl=CACHE_GESTION_TTL)
{

if (DEBUG_CACHE)
	echo "<p>Store $name in cache</p>";

return self::$m->add($name, $object, $ttl);

}

public static function delete($name)
{

if (DEBUG_CACHE)
	echo "<p>Delete $name in cache</p>";

return self::$m->delete($name);

}

public static function delete_all()
{

if (DEBUG_CACHE)
	echo "<p>Delete user cache</p>";

return self::$m->flush();

}

}

cache::init();

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
