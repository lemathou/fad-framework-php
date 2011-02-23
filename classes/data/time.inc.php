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
 * Time
 * 
 */
class data_time extends data_datetime
{

protected $empty_value = "00:00:00"; // stored as H:i:s

protected $opt = array
(
	"datetime_format" => "H:i:s", // for the value
	"disp_format" => "%H:%M:%S", // Defined for strftime()
	"form_format" => "H:i:s", // Defined for date()
	"db_format" => "H:i:s", // Defined for date()
);

public function db_field_create()
{

return array("type" => "time");

}

public function form_field_disp($options=array())
{

return "<input type=\"text\" name=\"".$this->name."\" value=\"".$this->value_to_form()."\" size=\"8\" maxlength=\"8\" class=\"".get_called_class()."\" />";

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
