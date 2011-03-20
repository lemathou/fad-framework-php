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
 * Rich Text (XHTML)
 * 
 * Can limit the use of tags to a list.
 * 
 */
class data_richtext extends data_text
{

protected $opt = array
(
	"string_tag_attrib_authorized" => array("b"=>array(), "i"=>array(), "u"=>array(), "strong"=>array(), "a"=>array("href"), "p"=>array(), "img"=>array("src", "alt")),
	"string_tag_authorized" => "<p><b><i><u><font><strong><a><img><ul><li>"
);

public function db_field_create()
{

return array("type" => "richtext");

}

/* Conversion */
public function convert_before(&$value)
{

$value = strip_tags($value, $this->opt["string_tag_authorized"]);

}

/* View */
public function __tostring()
{

if ($this->value === null)
	return "";
else
	return (string)$this->value;

}
public function form_field_disp()
{

return"<textarea name=\"$this->name\" class=\"".get_called_class()."\">$this->value</textarea>";

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
