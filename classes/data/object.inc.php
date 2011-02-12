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
 * Object
 * 
 */
class data_object extends data
{

protected $opt = array
(
	"object_type" => "objecttype",
	"db_table" => "",
	"db_fieldname" => "",
	"db_format" => ""
);

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
