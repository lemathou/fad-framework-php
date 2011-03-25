<?php

/**
  * $Id: cache.inc.php 65 2011-03-20 12:43:50Z lemathoufou $
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

class spell
{

static $lang = "fr";

/**
 * Enter description here ...
 * @param string $string
 * @return string
 */
function check($string)
{

exec("echo \"".addslashes(addslashes($string))."\" | aspell -d ".self::$lang." list", $result);

return $result;

}

}

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>