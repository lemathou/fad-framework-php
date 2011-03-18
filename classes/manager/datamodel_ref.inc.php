<?php

/**
  * $Id: datamodel_ref.inc.php 30 2011-01-18 23:29:06Z lemathoufou $
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
 * Object references
 * 
 */
class _datamodel_ref_manager extends _manager
{

protected $type = "datamodel_ref";

protected $info_detail = array
(
	"name"=>array("label"=>"Nom (unique)", "type"=>"string", "size"=>32, "lang"=>false),
	"label"=>array("label"=>"Label", "type"=>"string", "size"=>128, "lang"=>true),
);

}


/**
 * Object references
 * 
 */
class _datamodel_ref extends _object
{

protected $_type = "datamodel_ref";

protected $id;
protected $name;
protected $label;

/**
 * Data fields : model specifications
 * 
 * @var array
 */
protected $fields_detail = array();
protected $fields = array();

protected $fields_calculated = array();
protected $fields_key = array();

/**
 * Objects
 */
protected $objects = array();
protected $objects_exists = array();

function __sleep()
{

return array("id", "name", "label", "fields_detail", "fields_key");

}

protected function construct_more($infos)
{

$this->label = $this->name;
$this->query_fields();

}

protected function query_info_more()
{

$this->query_fields();

}

public function query_fields()
{

$this->fields_detail = array();
$this->fields = array();
$this->fields_key = array();
$query = db("SELECT `name`, `type`, `value`, `key` FROM `_datamodel_ref_fields` WHERE `datamodel_ref_id`='$this->id'");
while ($row=$query->fetch_assoc())
{
	$this->fields_detail[$row["name"]] = array("type"=>$row["type"], "value"=>json_decode($row["value"]), "opt"=>array());
	if ($row["key"])
		$this->fields_key[] = $row["name"];
}
$query = db("SELECT `name`, `optname`, `optvalue` FROM `_datamodel_ref_fields_opt` WHERE `datamodel_ref_id`='$this->id'");
while ($row=$query->fetch_assoc())
{
	$this->fields_detail[$row["name"]]["opt"][$row["name"]] = json_decode($row["optvalue"]);
}

}

public function __tostring()
{

return $this->label;

}

protected function construct_field($name)
{

if (!array_key_exists($name, $this->fields_detail))
	return false;
else
{
	if (!array_key_exists($name, $this->fields))
	{
		$field = $this->fields_detail[$name];
		$datatype = "data_$field[type]";
		$this->fields[$name] = new $datatype($name, $field["value"], $field["label"]);
		foreach($field["opt"] as $i=>$j)
			$this->fields[$name]->opt_set($i, $j);
	}
	return $this->fields[$name];
}

}

/**
 * Returns a data field
 * @param unknown_type $name
 */
public function __get($name)
{

if (array_key_exists($name, $this->fields))
	return clone $this->fields[$name];
elseif (array_key_exists($name, $this->fields_detail))
{
	$this->construct_field($name);
	return clone $this->fields[$name];
}

}
/**
 * Returns if a data field is defined
 * @param unknown_type $name
 */
public function __isset($name)
{

return array_key_exists($name, $this->fields_detail);

}

/**
 * Returns the complete data field list
 */
public function fields()
{

foreach($this->fields_detail as $name=>$field)
	if (!array_key_exists($name, $this->fields))
		$this->construct_field($name);
return $this->fields;

}
public function fields_key()
{

return $this->fields_key;

}

}


?>