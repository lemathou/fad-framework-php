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

public function db_table()
{

return $this->name."_ref";

}

public function query_fields()
{

$this->fields_detail = array();
$this->fields = array();
$this->fields_key = array();
$query = db("SELECT `name`, `type`, `value`, `key` FROM `_datamodel_ref_fields` WHERE `datamodel_ref_id`='$this->id'");
while ($row=$query->fetch_assoc())
{
	$this->fields_detail[$row["name"]] = array("label"=>$row["name"], "type"=>$row["type"], "value"=>json_decode($row["value"], true), "opt"=>array());
	if ($row["key"])
		$this->fields_key[] = $row["name"];
}
$query = db("SELECT `name`, `optname`, `optvalue` FROM `_datamodel_ref_fields_opt` WHERE `datamodel_ref_id`='$this->id'");
while ($row=$query->fetch_assoc())
{
	$this->fields_detail[$row["name"]]["opt"][$row["optname"]] = json_decode($row["optvalue"], true);
}

}

public function __tostring()
{

return $this->label;

}

protected function construct_field($name)
{

if (!is_string($name))
	return false;
if (!array_key_exists($name, $this->fields_detail))
	return false;

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

/**
 * Returns a data field
 * @param string $name
 * @return data
 */
public function __get($name)
{

if (!is_string($name))
	return null;

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
 * @param string $name
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

/**
 * 
 * Enter description here ...
 * @param array|string $id Identifier in json or (ordered) key params
 */
public function get($id)
{

if (is_array($id))
{
	foreach ($this->fields_key as $nb=>$name)
	{
		if ((isset($id[$name]) && ($r=$id[$name])) || (isset($id[$nb]) && ($r=$id[$nb])))
			$ref[] = $r;
		else
			return null;
	}
	$id = json_encode($ref);
}
elseif (!is_string($id))
{
	return null;
}

if (isset($this->objects[$id]))
{
	return $this->objects[$id];
}
elseif (CACHE && ($object=cache::retrieve("dataobject_ref_".$id)))
{
	return $this->objects[$id] = $object;
}
else
{
	if (!isset($ref))
		$ref = json_decode($id);
	$params = array();
	foreach($this->fields_key as $nb=>$name)
		$params[$name] = $ref[$nb];
	if (count($query=$this->query($params)) == 1)
		return array_pop($query);
	else
		return null;	
}

}

/**
 * 
 * Enter description here ...
 * @param array $params
 * @return array
 */
public function query(array $params=array())
{

$list = $this->db_select($params, array("id"));

$return = array();
$cache_retrieve = array();
$db_retrieve = array();

foreach($list as $fields)
{
	$ref = array();
	foreach($this->fields_key as $f)
		$ref[] = $fields[$f];
	$id = json_encode($ref);
	if (array_key_exists($id, $this->objects))
		$return[] = $this->objects[$id];
	elseif (CACHE)
		$cache_retrieve[$id] = $ref;
	else
		$db_retrieve[$id] = $ref;
}

foreach ($cache_retrieve as $id=>$ref)
{
	if ($object=cache::retrieve("dataobject_ref_".$id))
		$return[] = $this->objects[$id] = $object;
	else
		$db_retrieve[$id] = $ref;
}

foreach($db_retrieve as $id=>$ref)
{
	$params = array();
	foreach($this->fields_key as $nb=>$name)
		$params[$name] = $ref[$nb];
	$fields = array_pop($this->db_select($params, true));
	$object = new dataobject_ref();
	$object->datamodel_ref_set($this->id);
	$object->update_from_db($fields);
	if (CACHE)
		cache::store("dataobject_ref_".$id, $object, CACHE_DATAOBJECT_TTL);
	$return[] = $this->objects[$id] = $object;
}

return $return;

}

public function db_select(array $params, $fields=true)
{

$query = array("from"=>array("`".$this->db_table()."`"), "fields"=>array(), "params"=>array());

foreach($params as $name=>$value)
	if (array_key_exists($name, $this->fields_detail))
		$query["params"][] = "`".$this->__get($name)->db_fieldname()."`='".db()->string_escape($value)."'";

foreach ($this->fields_key as $name)
{
	$query["fields"][] = "`".$this->__get($name)->db_fieldname()."` as $name";
}
foreach ($this->fields() as $name=>$field)
{
	if (($fields === true || in_array($name, $fields)) && !in_array($name, $this->fields_key))
	{
		$query["fields"][] = "`".$field->db_fieldname()."` as $name";
	}
}
if (count($query["params"]))
	$query_params = "WHERE ".implode(" AND ", $query["params"]);
else
	$query_params = "";

$return = array();

$query_string = "SELECT ".implode(", ", $query["fields"])." FROM ".implode(", ", $query["from"])." $query_params";
//echo "<p>$query_string</p>";
$query = db()->query($query_string);
while ($fields=$query->fetch_assoc())
{
	$return[] = $fields;
}

return $return;
	
}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>