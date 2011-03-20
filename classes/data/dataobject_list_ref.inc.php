<?php

/**
  * $Id: dataobject_list_ref.inc.php 32 2011-01-24 07:13:42Z lemathoufou $
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
 * List of fields
 * 
 * Set of fields from a datamodel_ref bank
 */
class data_dataobject_list_ref extends data
{

protected $value_empty = array();

protected $opt = array
(
	"datamodel_ref" => null, // datamodel_ref id or name
	"datamodel_ref_field" => null, // datamodel_ref field to retrieve
	"datamodel_ref_id" => null, // datamodel_ref field identifying this object
);

function __construct($name, $value, $label="Field list", $options=array())
{

data::__construct($name, $value, $label, $options);

}

function datamodel_ref()
{

return datamodel_ref($this->opt["datamodel_ref"]);

}

public function db_query_param($value, $type="=")
{

$type_list = array( "=", "LIKE", "<", ">", "<=", ">=", "NOT LIKE" );  
if (!in_array($type, $type_list))
	$type = "=";

$fieldname = $this->datamodel_ref()->__get($this->opt["datamodel_ref_id"])->db_fieldname();

if (is_array($value) && count($value))
	return "`".$fieldname."` IN (".implode(", ",$this->value).")";
else
	return "`".$fieldname."` $type '".db()->string_escape($value)."'";

}

public function opt_set($name, $value)
{

parent::opt_set($name, $value);

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

public function retrieve_all()
{

$this->value = array();
foreach($this->datamodel_ref()->query(array()) as $object)
{
	$this->value[] = $object->id;
}

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
 * Returns specific objet fields in a list (usefull to retrieve easily associated objects)
 */
function field_list($name)
{

if (!$this->datamodel_ref()->__exists($name))
	return array();

$return = array();
foreach ($this->value as $ref)
	$return[] = $this->datamodel_ref($ref)->__get($name);

return $return;

}

/**
 * Returns objets in a list
 */
function object_list()
{

$return = array();
foreach ($this->value as $ref)
	$return[] = $this->datamodel_ref($ref);

return $return;

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>