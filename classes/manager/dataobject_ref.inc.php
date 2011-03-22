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
 * Default object type for datamodel_ref
 */
class dataobject_ref
{

/**
 * datamodel_ref reference
 * @var integer
 */
protected $datamodel_ref_id;
/**
 * Identifier
 * @var integer
 */
protected $id;
/**
 * Insert date and time
 * @var timestamp
 */
protected $_insert;
/**
 * Last update date and time
 * @var timestamp
 */
protected $_update;

/**
 * @var array
 */
protected $field_values = array();
/**
 * @var array
 */
protected $fields = array();

/**
 * Options
 * @var array
 */
protected $opt = array();

public function __sleep()
{

return array("datamodel_ref_id", "id", "_insert", "_update", "field_values");

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
 * Correct the problem of fields
 */
function __clone()
{

$this->id = null;
$this->_update = time();
foreach ($this->fields as $name=>$field)
{
	$this->fields[$name] = clone $field;
}

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

if (is_string($name) && ($field=$this->datamodel_ref()->{$name}))
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

// TODO : ajouter $_insert

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

/**
 * Returns if the object has changed
 * @return boolean
 */
public function changed()
{

foreach ($this->fields as $name=>$field)
	if (!array_key_exists($name, $this->field_values) || $this->field_values[$name] !== $field->value)
		return true;

return false;

}

/**
 * Returns the list of changed fields
 * @return array
 */
public function fields_changed()
{

$list = array();
foreach ($this->fields as $name=>$field)
	if (!array_key_exists($name, $this->field_values) || $this->field_values[$name] !== $field->value)
		$list[$name] = $field;
return $list;	

}
/**
 * Returns the original values of fields
 * @return array
 */
public function fields_values()
{

return $this->field_values;	

}

/**
 * Insert data into database as a new object
 *
 * @param array $opt
 * @return boolean
 */
public function db_insert($opt=array())
{

if ($id=$this->datamodel_ref()->db_insert($this))
{
	$this->id = $id; // TODO : threat the case "true"
	$this->_insert = $this->_update = time();
	foreach ($this->fields as $name=>$field)
		$this->field_values[$name] = $field->value;
	return true;
}
else
	return false;

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
	if ($name == "_update")
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

if (count($this->datamodel_ref()->fields_key()))
{
	$ref = array();
	foreach($this->datamodel_ref()->fields_key() as $f)
		$ref[] = $fields[$f];
	$this->id = json_encode($ref);
}

}

/**
 * Insert data into database as a new object
 *
 * @param array $opt
 * @return boolean
 */
public function db_update($opt=array())
{

// TODO : Verify that the changed fields do not change the appartenance to an object field.
// TODO : Do not change the Key fields or find a good way to do it !

if ($this->datamodel_ref()->object_update($this))
{
	$this->_update = time();
	foreach ($this->fields_changed as $name=>$field)
			$this->field_values[$name] = $field->value;
	return true;
}
else
	return false;

}

}

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>