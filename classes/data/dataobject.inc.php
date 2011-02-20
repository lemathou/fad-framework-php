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
 * Dataobject/Databank
 * 
 * Dataobjects a specific agregats of class data_bank_agregat,
 * corresponding to a datamodel associated to a database.
 * Thoses objects needs only an ID to be retrieved
 */
class data_dataobject extends data
{

protected $empty_value = 0;

protected $opt = array
(
	"datamodel" => 0,
	"ref_field_disp" => "", // field to display if needed
);

function __construct($name, $value, $label="Object", $options=array())
{

data::__construct($name, $value, $label, $options);

}

public function db_field_create()
{

return array("type"=>"integer", "size"=>10, "signed"=>false);

}

public function verify(&$value, $convert=false, $options=array())
{

if (!is_numeric($value) || !datamodel($this->opt["datamodel"])->exists($value))
{
	if ($convert)
		$value = null;
	return false;
}

$value = (int)$value;
return true;

}

function convert(&$value)
{

if (!is_numeric($value) || !datamodel($this->opt["datamodel"])->exists($value))
	$value = null;
else
	$value = (int)$value;

}

function convert_from_db(&$value)
{

if (is_numeric($value))
	$value = (int)$value;

}

function form_field_disp($print=true, $option=array())
{

// Pas beaucoup de valeurs : liste simple
if (($databank=datamodel($this->opt["datamodel"])) && (($nb=$databank->count()) <= 50))
{
	if (isset($option["order"]))
		$query = $databank->query(array(), array(), $option["order"]);
	else
		$query = $databank->query();

	$return = "<select name=\"$this->name\" title=\"$this->label\" class=\"".get_called_class()."\">\n";
	$return .= "<option></option>";
	foreach($query as $object)
	{
		if ($this->value == $object->id)
		{
			$return .= "<option value=\"$object->id\" selected=\"selected\">$object</option>";
		}
		else
			$return .= "<option value=\"$object->id\">$object</option>";
	}
	$return .= "</select>\n";
}
// Beaucoup de valeurs : liste Ajax complexe
else
{
	$return = "<div style=\"display:inline;\"><input name=\"$this->name\" value=\"$this->value\" type=\"hidden\" class=\"q_id\" />";
	if ($this->nonempty())
		$value = (string)datamodel()->get($this->opt["datamodel"], $this->value, true);
	else
		$value = "";
	$return .= "<select class=\"q_type\"><option value=\"like\">Approx.</option><option value=\"fulltext\">Precis</option></select><input class=\"q_str\" value=\"$value\" onkeyup=\"object_list_query(".$this->opt["datamodel"].", [{'type':$('.q_type', this.parentNode).val(),'value':this.value}], $(this).parent().get(0));\" onblur=\"object_list_hide($(this).parent().get(0))\" onfocus=\"this.select();if(this.value) object_list_query(".$this->opt["datamodel"].", [{'type':$('.q_type', this.parentNode).val(),'value':this.value}], $(this).parent().get(0));\" />";
	$return .= "<div class=\"q_select\"></div>";
	$return .= "</div>";
}

if ($print)
	echo $return;
else
	return $return;

}

function form_field_disp_all($print=true)
{

$return = "<div id=\"".$this->name."_list\">\n";
$return .= "<select name=\"".$this->name."\" title=\"$this->label\" class=\"".get_called_class()."\">\n";

$query = datamodel($this->opt["databank"])->query();
foreach ($query as $object)
{
	if ($this->value == "$object->id")
		$return .= "<option value=\"$object->id\" selected=\"selected\">$object</option>";
	else
		$return .= "<option value=\"$object->id\">$object</option>";
}

$return .= "</select>\n";
$return .= "</div>\n";


if ($print)
	echo $return;
else
	return $return;

}

function __tostring()
{

if ($this->nonempty() && ($datamodel=datamodel($this->opt["datamodel"])) && ($object=$datamodel->get($this->value)))
{
	if (($fieldname=$this->opt["ref_field_disp"]) && isset($datamodel->{$fieldname}))
	{
		return (string)$object->{$fieldname};
	}
	else
		return (string)$object;
}
else
	return "";

}

/**
 * Returns the object
 */
function object()
{

if ($this->nonempty())
	return datamodel($this->opt["datamodel"])->get($this->value);
else
	return null;

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
