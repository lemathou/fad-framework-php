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
 * Text
 * 
 * Plain text which can contains everything
 * Usefull for long text
 *
 */
class data_text extends data_string
{

protected $opt = array();

public function db_field_create()
{

return array("type"=>"string");

}

/* View */
public function form_field_disp($print=true, $options=array())
{

return "<textarea name=\"$this->name\" class=\"".get_called_class()."\">$this->value</textarea>";

}
public function form_field_select_disp($print=true, $options=array())
{

return data_string::form_field_disp();

}
public function __tostring()
{

if ($this->value === null)
	return "";
else
	return nl2br((string)$this->value, true);

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
