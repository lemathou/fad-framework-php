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
 * List (array)
 * 
 * Can be indexed or ordered
 * 
 * The content is a set en elements of the same type
 * TODO : Use the data_list_mixed to put different datatypes inside
 * 
 * Displaying uses jquery, and data is stored in json in a text DB field
 *
 */
class data_list extends data
{

protected $opt = array
(
	"db_ref_field" => "", // champ à récupérer
	"db_ref_table" => "", // table de liaison
	"db_ref_id" => "", // champ de liaison
);

/* Convert */
public function verify(&$value, $convert=false, $options=array())
{

if (!is_array($value))
{
	if ($convert)
		$value = array();
	return false;
}

return true;

}
public function convert(&$value)
{

if (!is_array($value))
	$value = array();

}
public function value_from_db($value)
{

if (is_array($value))
	$this->value = $value;
else
	$this->value = null;

}
public function value_to_db()
{

if (is_array($this->value))
	return $this->value;
else
	return null;

}

/* View */
public function __tostring()
{

if (is_array($this->value))
	return (string)implode(", ", $this->value);
else
	return "";

}
function form_field_disp()
{

if (isset($this->opt["db_order_field"]))
	$order = array($this->opt["db_order_field"]=>"asc");
else
	$order = array();

// Pas beaucoup de valeurs : liste simple
if (!is_array($this->value) || count($this->value) == 0)
{
	$return = "NADA";
}
elseif (($nb=count($this->value)) < 20)
{
	if ($nb<10)
		$size = $nb;
	else
		$size = 5;
	$return = "<input name=\"$this->name\" type=\"hidden\" />";
	$return .= "<select name=\"".$this->name."[]\" title=\"$this->label\" multiple size=\"$size\" class=\"".get_called_class()."\">\n";
	if (is_array($this->value)) foreach ($this->value as $id)
			$return .= "<option value=\"$id\" selected>$id</option>";
	$return .= "</select>\n";
}
else
{
	// TODO : liste ajax
}

return $return;

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
