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


/**
 * Image/Picture
 *
 */
class data_image extends data_file
{

static protected $format_list = array("jpg"=>"image/jpeg", "png"=>"image/png", "gif"=>"image/gif");

protected $opt = array("fileformat"=>"jpg", "imgquality"=>90);

/*
 * A TRAVAILLER... PAS EVIDENT
 * On doit pouvoir forcer un format en entrée, convertir en un format donné au besoin.
 */

function format_convert($format)
{

if (isset(self::$format_list[$format]))
	$this->format = $format;

}

/* View */
function form_field_disp($print=true, $options=array())
{

$return = "<input name=\"$this->name\" size=\"16\" value=\"".$this->value."\" /> <input name=\"$this->name\" type=\"file\" />";
if ($this->datamodel_id && $this->object_id)
	$return .= "<img src=\"/data/".$this->datamodel()->name()."/$this->object_id/$this->value\" alt=\"$this->value\" />";

return $return;

}
function __tostring()
{

if ($this->nonempty())
	return "<img src=\"/data/".$this->datamodel()->name()."/1/$this->value\" alt=\"$this->value\" />";
else
	return "";

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
