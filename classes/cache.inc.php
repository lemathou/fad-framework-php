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


interface cache_i
{

/**
 * Retrieve objects
 * 
 * @param name mixed (string or array)
 * @return mixed (string or array)
 * @author mathieu
 */
static function retrieve($name);

/**
 * Store objects
 * 
 * @param name string
 * @param object mixed
 * @param ttl integer
 * @return boolean
 * @author mathieu
 */
static function store($name, $object, $ttl=CACHE_GESTION_TTL);

/**
 * Remove objects
 * 
 * @param name mixed (string or array)
 * @return boolean
 * @author mathieu
 */
static function delete($name);

/**
 * Remove all objects
 * 
 * @param name mixed (string or array)
 * @return boolean
 * @author mathieu
 */
static function delete_all();

}

include PATH_CLASSES."/cache/".CACHE_TYPE.".inc.php";


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
