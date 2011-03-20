<?php

/**
  * $Id: dataobject_ref.inc.php 30 2011-01-18 23:29:06Z lemathoufou $
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


class dataobject_ref
{

protected $datamodel_ref_id;

protected $id;
protected $_update;

protected $field_values = array();
protected $fields = array();

public function __sleep()
{

/*
foreach ($this->fields as $name=>$field)
	if ($field->value !== $this->field_values[$name])
		$this->field_values[$name] = $field->value;
*/
return array("datamodel_ref_id", "id", "_update", "field_values");

}
public function __wakeup()
{

}

/**
 * 
 * @param integer $id
 * @param array $fields
 */
function __construct()
{

}

/**
 * Sets the datamodel_ref
 */
public function datamodel_ref_set($id)
{

$this->datamodel_ref_id = $id;

}

/**
 * Returns the datamodel
 * @return _datamodel_ref
 */
public function datamodel_ref()
{

if ($this->datamodel_ref_id)
	return datamodel_ref($this->datamodel_ref_id);

}

/**
 * 
 * Enter description here ...
 * @param string $name
 * @return data
 */
protected function construct_field($name)
{

if ($field=$this->datamodel_ref()->{$name})
{
	//$field->object_set($this);
	return $field;
}

}

/**
 * 
 * Enter description here ...
 * @param string $name
 * @return boolean
 */
public function __isset($name)
{

return (array_key_exists($name, $this->fields) || array_key_exists($name, $this->field_values) || isset($this->datamodel_ref()->{$name}));

}

/**
 * Unset (to null value) a data field
 */
public function __unset($name)
{

if (array_key_exists($name, $this->fields))
{
	$this->fields[$name]->value = null;
}
else
{
	$this->fields[$name] = $this->construct_field($name);
	$this->fields[$name]->value = null;
}

}

/**
 * Update a data field
 */
public function __set(string $name, $value)
{

if ($name == "_update")
{
	if (is_numeric($value) && $value > $this->_update)
		$this->_update = (int)$value;
}
elseif (in_array($name, $this->datamodel_ref()->fields_key()))
{
	// Nothing, this is part of the Key !!
}
elseif (array_key_exists($name, $this->fields))
{
	$this->fields[$name]->value = $value;
}
elseif (array_key_exists($name, $this->field_values))
{
	$this->fields[$name] = $this->construct_field($name);
	$this->fields[$name]->value = $value;
}
elseif ($field=$this->construct_field($name))
{
	$this->field_values[$name] = $field->value = $value;
	$this->fields[$name] = $field;
}

}
/**
 * 
 * Enter description here ...
 * @param string $name
 * @return data
 */
public function __get($name)
{

if ($name == "id" || $name == "_update")
	return $this->{$name};
elseif (array_key_exists($name, $this->fields))
{
	return $this->fields[$name];
}
elseif (array_key_exists($name, $this->field_values))
{
	$this->fields[$name] = $this->construct_field($name);
	$this->fields[$name]->value = $this->field_values[$name];
	return $this->fields[$name];
}
elseif ($field=$this->construct_field($name))
{
	$this->field_values[$name] = $field->value;
	return $this->fields[$name] = $field;
}

}

/**
 * Update data fields from database
 *
 * @return unknown
 */
public function update_from_db(array $fields)
{

foreach ($fields as $name=>$value)
{
	//echo "<p>$name</p>";
	//var_dump($value);
	if ($name == "id")
	{
		$this->id = $value;
	}
	elseif ($name == "_update")
	{
		$e = explode(" ", $value);
		$d = explode("-", $e[0]);
		$t = explode(":", $e[1]);
		$this->_update = mktime($t[0], $t[1], $t[2], $d[1], $d[2], $d[0]);
	}
	elseif (array_key_exists($name, $this->fields))
	{
		$this->fields[$name]->value_from_db($value);
		$this->field_values[$name] = $this->fields[$name]->value;
	}
	elseif ($field=$this->construct_field($name))
	{
		$field->value_from_db($value);
		$this->fields[$name] = $field;
		$this->field_values[$name] = $field->value;
	}
}

$ref = array();
foreach($this->datamodel_ref()->fields_key() as $f)
	$ref[] = $fields[$f];
$this->id = json_encode($ref);

}

/**
 * Default disp value
 * Can (and SHOULD) be overloaded in datamodel library
 * 
 * @return string
 */
function __tostring()
{

return $this->datamodel_ref()->label()." ID#$this->id";

}

/**
 * Returns field list
 * @return array[int]data Data fields complete list
 */
public function fields()
{

foreach ($this->datamodel_ref()->fields() as $name=>$field)
{
	if (!array_key_exists($name, $this->fields))
	{
		$this->fields[$name] = $field;
		//$field->object_set($this);
		if (array_key_exists($name, $this->field_values))
			$this->fields[$name]->value = $this->field_values[$name];
	}
}

return $this->fields;

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>