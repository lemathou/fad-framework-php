<?php

/**
  * $Id: data.inc.php 32 2011-01-24 07:13:42Z lemathoufou $
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
 * Password
 * 
 * Can handle different encryption types
 * Associated to a input/password form
 *
 */
class data_password extends data_string
{

protected $opt = array
(
	"size" => 64,
	"enctype" => "md5"
);

/* TODO : penser à la conversion en md5=>voir comment modifier par la suite
 * le mieux est peut-être de stocker directement à l'insertion en md5
 * pour pouvoir plus aisément comparer... a voir !!
 */

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
