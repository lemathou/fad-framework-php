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
 * Year
 * 
 * Associated to a year DB field
 * 
 */
class data_year extends data_datetime
{

protected $empty_value = "0000";

protected $opt = array
(
	"datetime_format" => "Y", // for the value
	"disp_format" => "%G", // Defined for strftime()
	"form_format" => "Y", // Defined for date()
	"db_format" => "Y", // Defined for date()
);

public function db_field_create()
{

return array("type" => "year");

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
