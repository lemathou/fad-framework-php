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
 * List of dataobjects
 * 
 * Set of dataobjects from the same databank
 */
class data_dataobject_list extends data_list
{

protected $value_empty = array();

protected $opt = array
(
	"datamodel_ref" => null, // datamodel_ref id or name
	"datamodel_ref_field" => null, // datamodel_ref field to retrieve
	"datamodel_ref_id" => null, // datamodel_ref field identifying this object
	"datamodel" => "databank_name", // datamodel name or ID
	"db_ref_table" => "", // linking table
	"db_ref_field" => "", // table field identifier for the object(s) to retrieve
	"db_ref_id" => "", // table field identifier for this object
	"db_order_field" => "", // order field (optionnel)
	"ref_field_disp" => "", // default field to display for __tostring() (optionnal)
);

function __construct($name, $value, $label="Object list", $options=array())
{

data::__construct($name, $value, $label, $options);

}

public function db_query_param($value, $type="=")
{

$type_list = array( "=", "LIKE", "<", ">", "<=", ">=", "NOT LIKE" );  
if (!in_array($type, $type_list))
	$type = "=";

$fieldname = $this->opt["db_ref_id"];

if (is_array($value) && count($value))
	return "`".$fieldname."` IN (".implode(", ",$this->value).")";
else
	return "`".$fieldname."` $type '".db()->string_escape($value)."'";

}

public function opt_set($name, $value)
{

parent::opt_set($name, $value);
$this->opt_update();

}

protected function opt_update()
{

if ($this->opt["datamodel_ref"] !== null && $this->opt["datamodel_ref_id"] !== null && $this->opt["datamodel_ref_field"] !== null)
{
	if ($ref = datamodel_ref($this->opt["datamodel_ref"]))
	{
		$this->opt["db_ref_table"] = $ref->name()."_ref";
		$this->opt["db_ref_field"] = $ref->__get($this->opt["datamodel_ref_field"])->db_fieldname();
		$this->opt["db_ref_id"] = $ref->__get($this->opt["datamodel_ref_id"])->db_fieldname();
	}
}

}

/**
 * Data to create the associated database 
 */
public function db_ref_create()
{

if ($this->opt["db_ref_table"])
{
	$return = array
	(
		"name" => $this->opt["db_ref_table"],
		"options" => array(),
	);
	if ($this->opt["db_order_field"])
		$return["fields"] = array
		(
			$this->opt["db_ref_id"] => array("type"=>"integer", "size"=>10, "signed"=>false, "null"=>false, "key"=>true),
			$this->opt["db_ref_field"] => array("type"=>"integer", "size"=>10, "signed"=>false, "null"=>false, "key"=>false),
			$this->opt["db_order_field"] = array("type"=>"integer", "size"=>2, "signed"=>false, "null"=>false, "key"=>true)
		);
	else
		$return["fields"] = array
		(
			$this->opt["db_ref_id"] => array("type"=>"integer", "size"=>10, "signed"=>false, "null"=>false, "key"=>true),
			$this->opt["db_ref_field"] => array("type"=>"integer", "size"=>10, "signed"=>false, "null"=>false, "key"=>true)
		);
	return $return;
}

}

/* Convert */
public function verify(&$value, $convert=false, $options=array())
{

if (!is_array($value))
{
	if ($convert)
		$value = array();
	return false;
}

$return = true;

foreach($value as $nb=>$id)
{
	if (!datamodel($this->opt["datamodel"])->exists($id))
		if ($convert)
		{
			unset($value[$nb]);
			$return = false;
		}
		else
			return false;
}

return $return;

}

public function convert_from_form(&$value)
{

if (is_array($value))
{
	foreach($value as $nb=>&$ref)
	{
		// We create an associated object
		if (is_array($ref) && ($object = datamodel($this->opt["datamodel"])->create()))
		{
			$object->update_from_form($ref);
			/*if ($this->object_id && $ref_id=$this->opt["db_ref_id"])
				$object->__set($ref_id, $this->object_id);*/
			if ($object->db_insert())
				$ref = $object->id;
			else
				unset($value["nb"]);
		}
	}
}

}

function convert(&$value)
{

if (!is_array($value))
	$value = array();

foreach($value as $nb=>$id)
{
	if (!datamodel($this->opt["datamodel"])->exists($id))
		unset($value[$nb]);
}

}

/* Update */
public function value_add($id)
{

if (!is_array($this->value))
	$this->value = array($id);
else
	$this->value[] = $id;

}

/* View */
function form_field_disp()
{

if ($this->opt["db_order_field"])
	$order = array($this->opt["db_order_field"]=>"asc");
else
	$order = array();

// Pas beaucoup de valeurs : liste simple
if (($nb=datamodel($this->opt["datamodel"])->db_count()) < 10)
{
	$query = datamodel($this->opt["datamodel"])->query();
	if ($nb<5)
		$size = $nb;
	else
		$size = 5;
	$return = "<div style=\"display:inline;\"><input name=\"$this->name\" type=\"hidden\" />";
	$return .= "<select name=\"".$this->name."[]\" title=\"$this->label\" multiple size=\"$size\" class=\"".get_called_class()."\">\n";
	if (is_array($this->value)) foreach ($this->value as $id)
			$return .= "<option value=\"$id\" selected>".datamodel($this->opt["datamodel"])->get($id)."</option>";
	foreach($query as $object)
	{
		if (!is_array($this->value) || !in_array($object->id, $this->value))
			$return .= "<option value=\"$object->id\">$object</option>";
	}
	$return .= "</select>";
	$return .= "<div><input type=\"button\" value=\"ADD\" onclick=\"datamodel_insert_form('".$this->opt["datamodel"]."', null, this.parentNode, '".$this->name."[]')\" /></div>\n";
}
// Beaucoup de valeurs : liste Ajax complexe
else
{
	$return = "<div style=\"display:inline;\">";
	$return .= "<input name=\"$this->name\" type=\"hidden\" />";
	$return .= "<input class=\"q_str\" onkeyup=\"object_list_query('".$this->opt["datamodel"]."', [{'type':$('.q_type', this.parentNode).val(),'value':this.value}], $(this).parent().get(0));\" onblur=\"object_list_hide($(this).parent().get(0))\" onfocus=\"this.select();if(this.value) object_list_query('".$this->opt["datamodel"]."', [{'type':$('.q_type', this.parentNode).val(),'value':this.value}], $(this).parent().get(0));\" />";
	$return .= "<select class=\"q_type\"><option value=\"like\">Approx.</option><option value=\"fulltext\">Precis</option></select>";
	$return .= "<div><select name=\"".$this->name."[]\" title=\"$this->label\" multiple class=\"".get_called_class()." q_id\">";
	if (is_array($this->value) && count($this->value))
	{
		datamodel($this->opt["datamodel"])->query(array(array("name"=>"id", "value"=>$this->value)));
		foreach ($this->value as $id)
			$return .= "<option value=\"$id\" selected>".datamodel($this->opt["datamodel"])->get($id)."</option>";
	}
	$return .= "</select></div>";
	$return .= "<div class=\"q_select\"></div>";
	$return .= "</div>";
	$return .= "<div><input type=\"button\" value=\"ADD\" onclick=\"datamodel_insert_form('".$this->opt["datamodel"]."', null, this.parentNode, '".$this->name."[]')\" /></div>";
}

return $return;

}

function __tostring()
{

if ($this->opt["db_order_field"])
	$order = array($this->opt["db_order_field"]=>"asc");
else
	$order = array();

if (!is_array($this->value) || !count($this->value))
{
	return "";
}
elseif ($this->opt["ref_field_disp"])
{
	$query = datamodel($this->opt["datamodel"])->query(array(array("name"=>"id", "value"=>$this->value)), true, $order);
	$return = array();
	foreach($query as $object)
	{
		$return[] = $object->{$this->opt["ref_field_disp"]};
	}
	return implode(", ", $return);
}
else
{
	return implode(", ", datamodel($this->opt["datamodel"])->query(array(array("name"=>"id", "value"=>$this->value)), true, $order));
}

}

/**
 * Returns objets in a list
 */
function object_list()
{

if ($this->opt["db_order_field"])
	$order = array($this->opt["db_order_field"]=>"asc");
else
	$order = array();

if (is_array($this->value) && count($this->value))
{
	// Retrieve objects in databank
	datamodel($this->opt["datamodel"])->query(array(array("name"=>"id", "value"=>$this->value)), true);
	// Sort by order
	$return = array();
	foreach ($this->value as $nb=>$id)
		$return[] = datamodel($this->opt["datamodel"])->get($id);
	return $return;
}
else
{
	return array();
}

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
