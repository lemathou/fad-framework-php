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
  * Data Objects
  * 
  * Object corresponding to a given Datamodel specification.
  * Contains the data fields of the datamodel.
  * Can be
  * - upgraded,
  * - displayed,
  * - etc.
  * 
  */

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [begin]");


/**
 * Default object type for datamodel
 */
class dataobject
{

/**
 * Datamodel reference
 * @var integer
 */
protected $datamodel_id;
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

foreach ($this->fields as $name=>$field)
	if ($field->value !== $this->field_values[$name])
		$this->field_values[$name] = $field->value;

return array("datamodel_id", "id", "_insert", "_update", "field_values");

}
public function __wakeup()
{

//$this->datamodel_find();

}

/**
 * 
 * @param integer $id
 * @param array $fields
 */
function __construct($id=null, $fields=array())
{

$this->datamodel_find();
//$this->init();

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
 * Retrieve the datamodel id from the class name
 * TODO : warning ! in the future memorise it for better performance
 */
protected function datamodel_find()
{

if ($datamodel=datamodel(get_called_class()))
{
	$this->datamodel_id = $datamodel->id();
}

}

/**
 * Returns the datamodel
 * @return _datamodel
 */
public function datamodel()
{

if ($this->datamodel_id)
	return datamodel($this->datamodel_id);

}

/**
 * 
 * Enter description here ...
 * @param string $name
 * @return data
 */
protected function construct_field($name)
{

if ($field=$this->datamodel()->{$name})
{
	$field->object_set($this);
	return $field;
}

}

/**
 * returns if a data field exists
 * @param string $name
 * @return boolean
 */
public function __isset($name)
{

return (is_string($name) && (array_key_exists($name, $this->fields) || array_key_exists($name, $this->field_values) || isset($this->datamodel()->{$name})));

}

/**
 * Unset (to null value) a data field
 * @param string $name
 */
public function __unset($name)
{

if (!is_string($name))
	return;

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
 * Reset (to default value) a data field
 * @param string $name
 */
public function reset($name)
{

if (!is_string($name))
	return;

$this->fields[$name] = $this->construct_field($name);

}

/**
 * Update a data field
 * @param string $name
 * @param mixed $value
 */
public function __set($name, $value)
{

if (!is_string($name))
	return;

if (array_key_exists($name, $this->fields))
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
	$this->field_values[$name] = null; // TODO : verify this is a good way
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

if (!is_string($name))
	return;

if ($name == "id" || $name == "_update" || $name == "_insert")
{
	return $this->{$name};
}
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
	$this->field_values[$name] = null; // TODO : verify this is a good way, as for __set()
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

return $this->datamodel()->label()." ID#$this->id";

}

/**
 * Returns field list
 * @return array[int]data Data fields complete list
 */
public function fields()
{

foreach ($this->datamodel()->fields() as $name=>$field)
{
	if (!array_key_exists($name, $this->fields))
	{
		$this->fields[$name] = $field;
		$field->object_set($this);
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
 * Set/init all fileds to default value
 * 
 */
public function init()
{

//$this->id = 0;
$this->_update = time();
foreach($this->fields as $name=>$field)
	$field->value = $this->datamodel()->{$name}->value;

}
/**
 * Retrieve fields from database
 *
 * @param array $fields
 * @param boolean $force
 * @return boolean
 */
public function db_retrieve($fields, $force=false)
{

if (!$this->id)
	return false;

$params[] = array("name"=>"id", "value"=>$this->id);

if (is_string($fields))
	$fields = array($fields);
elseif (!is_array($fields))
	$fields = true;

// Delete the fields we already have if we don't want all fields
if (!$force)
{
	foreach ($fields as $i=>$name)
		if (array_key_exists($name, $this->fields) || array_key_exists($name, $this->field_list))
			unset($fields[$i]);
}

// Effective Query
if (count($fields) && is_array($list=$this->datamodel()->db_select($params, $fields)) && count($list) == 1)
{
	foreach(array_pop($list) as $name=>$field)
	{
		if (!array_key_exists($name, $this->fields) && !array_key_exists($name, $this->field_values))
		{
			$this->fields[$name] = $field;
			$this->field_values[$name] = $field->value;
		}
	}
	$this->_update = time();
	if (CACHE)
		cache::store("dataobject_".$this->datamodel_id."_".$this->id, $this, CACHE_DATAOBJECT_TTL);
	return true;
}
else
{
	if (DEBUG_DATAMODEL)
		trigger_error("dataobject('".$this->datamodel()->name()."')::db_retrieve() : 0 or too many objects resulting from query params");
	return false;
}

}
/**
 * Retrieve all data fields from database
 * @return boolean
 */
public function db_retrieve_all()
{

$fields = array();
foreach ($this->datamodel()->fields() as $name=>$field)
	if (!array_key_exists($name, $this->field_values) && !array_key_exists($name, $this->fields))
		$fields[] = $name;

if (count($fields) > 0)
{
	return $this->db_retrieve($fields);
}
else
	return false;

}

/**
 * Return a view of the object, using a datamodel template
 * @param unknown_type $name
 * @return _template
 */
public function view($name="")
{

if (!$name)
	$name = $this->datamodel()->name();

if ($view=template("datamodel/$name"))
{
	$view->object_set($this);
	return $view;
}

}
public function display($name="")
{

return $this->view($name);

}
/**
 * Display
 * @param unknown_type $name
 */
public function disp($name="")
{

echo $this->display($name);

}
/**
 * Return the default form view
 *
 * @param unknown_type $name
 * @return unknown
 */
public function form($name="")
{

if (!$name)
	$name = $this->datamodel()->name();

{
	$view = new datamodel_update_form($this);
}

return $view;

}
public function update_form($name="")
{

if (!$name)
	$name = $this->datamodel()->name();

{
	$view = new datamodel_update_form($this);
}

return $view;

}
public function insert_form($name="")
{

if (!$name)
	$name = $this->datamodel()->name();

{
	$view = new datamodel_insert_form($this);
}

return $view;

}

/**
 * Insert data into database as a new object
 *
 * @param unknown_type $options
 */
public function db_insert($opt=array())
{

if ($id=$this->datamodel()->object_insert($this))
{
	$this->id = $id;
	$this->_update = time();
	foreach ($this->fields as $name=>$field)
		$this->field_values[$name] = $field->value;
	if (CACHE)
		cache::store("dataobject_".$this->datamodel_id."_".$this->id, $this, CACHE_DATAOBJECT_TTL);
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
public function update_from_db($fields=array())
{

foreach ($fields as $name=>$value)
{
	//echo "<p>$name</p>";
	//var_dump($value);
	if ($name == "id")
	{
		$this->id = (int)$value;
	}
	elseif ($name == "_update")
	{
		$e = explode(" ", $value);
		$d = explode("-", $e[0]);
		$t = explode(":", $e[1]);
		$this->_update = mktime($t[0], $t[1], $t[2], $d[1], $d[2], $d[0]);
	}
	elseif ($name == "_insert")
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

}
/**
 * Update the object from a form
 * @param unknown_type $fields
 */
public function update_from_form($fields=array(), $db_update=false)
{

//var_dump($fields);
if (is_array($fields) && count($fields) > 0)
{
	foreach($fields as $name=>$value)
	{
		//echo "<p>$name : $value</p>\n";
		if ($name == "id")
		{
			$this->__set("id", $value);
		}
		elseif ($field=$this->__get($name))
		{
			$field->value_from_form($value);
		}
	}
	// Calculated fields
	// TODO : UPDATE
	//var_dump($this->fields);
	if (count($this->datamodel()->fields_calculated()))
	{
		$calculate = array();
		$retrieve = array();
		foreach($this->datamodel()->fields_calculated() as $name=>$list)
		{
			// On parcours les champs utiles dans un calcul
			foreach($list as $value)
				// Si le champ a �t� modifi� on doit le mettre � jour
				if (isset($fields[$value]))
					if (!isset($calculate[$name]))
						$calculate[$name] = $list;
		}
		// Récupération des champs manquant
		foreach($calculate as $name=>$list)
		{
			foreach ($list as $value)
				if (!isset($fields[$value]) && !in_array($value, $retrieve))
					$retrieve[] = $value;
			if (!isset($fields[$value]) && !in_array($name, $retrieve))
				$retrieve[] = $name;
		}
		if (count($retrieve)>0)
		{
			//print_r($retrieve);
			$this->db_retrieve($retrieve);
		}
		// Calculs
		foreach($calculate as $name=>$list)
		{
			$function = "calculate_$name";
			$this->$function();
		}
	}
	// Mise à jour en base de donnée
	if ($db_update)
		$this->db_update();
}
	
}
/**
 * Update data into database
 *
 * @param array $opt
 * @return boolean
 */
public function db_update($opt=array())
{

// Permission verification
if (false)
{
	die("NOT ALLOWED TO UPDATE !");
}

if ($this->datamodel()->object_update($this))
{
	if (false) foreach ($fields as $name=>$field)
	{
		// Update linked objects
		if (get_class($field) == "data_dataobject_list" && ($datamodel=datamodel($field->opt("datamodel"))))
		{
			if (is_array($field->value)) foreach($field->value as $id)
			{
				if (!array_key_exists($name, $this->field_values) || (is_array($this->field_values[$name]) && !in_array($id, $this->field_values[$name])))
				{
					if ($object=$datamodel->get($id))
						$object->db_retrieve(true, true);
				}
			}
			if (isset($this->field_values[$name]) && is_array($this->field_values[$name])) foreach($this->field_values as $id)
			{
				if (is_array($field->value) && !in_array($id, $field->value))
				{
					if ($object=$datamodel->get($id))
						$object->db_retrieve(true, true);
				}
			}
		}
		$this->field_values[$name] = $field->value;
	}
	$this->_update = time();
	//db()->query("INSERT INTO `_datamodel_update` (`datamodel_id`, `dataobject_id`, `account_id`, `action`, `datetime`) VALUES ('".$this->datamodel()->id()."', '".$this->id."', '".login()->id()."', 'u', NOW())");
	if (CACHE)
		cache::store("dataobject_".$this->datamodel_id."_".$this->id, $this, CACHE_DATAOBJECT_TTL);
	return true;
}
else
{
	return false;
}

}

/**
 * Delete the object
 * @return boolean
 */
public function db_delete()
{

if ($this->datamodel()->object_delete($this))
{
	$this->id = null;
	return true;
}
else
{
	return false;
}

}

/**
 * Returns the details for Javascript control functions
 * @return string
 */
public function js()
{

$list = array();
foreach($this->fields() as $field)
{
	$list[] = "\"$field->name\":".$field->js();
}

return "{\"datamodel\":".$this->datamodel_id.", \"id\":$this->id, \"label\":\"".json_encode($this->__tostring())."\", \"fields\":{\n".implode(",\n", $list)."\n} }";

}

/**
 * Returns the datamodel action list
 */
public function action_list()
{

return $this->datamodel()->action_list();

}

/**
 * Execute an action
 * @param unknown_type $method
 * @param unknown_type $params
 */
public function action($method, $params)
{

// TODO : great potential with this concept !
// datamodel()::action_exists()
// datamodel()::action_get()
// etc.

$action_list = &$this->datamodel()->action_list();
if (isset($action_list[$method]) && $action=$action_list[$method]["method"])
{
	$this->$action($params);
}

}

/**
 * The most simple action...
 * Create an (almost) empty linked object
 * @param string $datamodel_name
 * @param array $data_fields
 * @param string $ref_id
 * @param string $ref_field
 * @param string $trigger_function
 * @return dataobject
 */
public function ref_create($datamodel_name, $ref_id=null, $ref_field=null, $trigger_function=null, $data_fields=array(), $data_update_method="")
{

if ($datamodel=datamodel($datamodel_name))
{
	// Create object
	$object = $datamodel->create();
	// Update fields
	if (is_array($data_fields)) foreach($data_fields as $name=>$field)
	{
		if (is_a($field, "data"))
			$object->__set($name, $field->value);
		else
			$object->__set($name, $field);
	}
	// Set ID
	if (($ref_id && $datamodel->__isset($ref_id)) || $datamodel->__isset($ref_id=$this->datamodel()->name()))
		$object->__set($ref_id, $this->id);
	// Trigger function
	if (is_string($trigger_function))
		if (method_exists($this, "action_$trigger_function"))
			$ok = call_user_func(array($this, "action_$trigger_function"), $object);
		else
			$ok = false;
	else
		$ok = true;
	// Insert in Database and returns object
	if  ($ok && $object->db_insert())
	{
		if ((($ref_field && ($field=$this->__get($ref_field))) || ($field=$this->__get($ref_field=$datamodel_name))) && $field->opt("datamodel") == $datamodel_name)
		{
			if (is_a($field, "data_dataobject_list"))
				$field->value_add($object->id);
			elseif (is_a($field, "data_dataobject"))
				$field->value = $object->id;
		}
		return $object;
	}
}

}

public function ref_delete($datamodel_name, $id)
{

// TODO

}

public function ref_link($datamodel_name, $ref_id=null, $ref_field=null, $trigger_function=null, $id)
{

// Verify Datamodel & retrieve object
if (($datamodel=datamodel($datamodel_name)) && ($object=$datamodel->get($id)))
{
	// Set ID
	if (($ref_id && $datamodel->__isset($ref_id)) || $datamodel->__isset($ref_id=$this->datamodel()->name()))
		$object->__set($ref_id, $this->id);
	// Trigger function
	if (is_string($trigger_function))
		if (method_exists($this, "action_$trigger_function"))
			$ok = call_user_func(array($this, "action_$trigger_function"), $object);
		else
			$ok = false;
	else
		$ok = true;
	// Insert in Database and returns object
	if  ($ok && ($object->db_update()))
	{
		if ($ref_field && ($field=$this->__get($ref_field)) && $field->opt("datamodel") == $datamodel_name)
		{
			if (is_a($field, "data_dataobject_list"))
				$field->value_add($object->id);
			elseif (is_a($field, "data_dataobject"))
				$field->value = $object->id;
		}
		return $object;
	}
}

}

public function ref_unlink($datamodel_name, $id)
{

// TODO


}

public function ref_change($datamodel_name, $blahblah)
{

// TODO


}

/**
 * Returns the default string for url rewriting
 * @return string
 */
function url_str()
{

if (($field=$this->__get("url")) !== null)
	return $field;
elseif (($field=$this->__get("label")) !== null)
	return text::rewrite_ref((string)$field);
elseif (($field=$this->__get("name")) !== null)
	return text::rewrite_ref((string)$field);
else
	return text::rewrite_ref($this->__tostring());

}

/**
 * Returns default URL to the view page
 * @return string
 */
public function url()
{

if ($page=page(get_class($this)))
	return $page->url(array($this->id), $this->url_str());
else
	return "#";

}

/**
 * Returns default LINK to the view page, using default URL and default display
 * @return string
 */
public function link()
{

return "<a href=\"".$this->url()."\">".$this->__tostring()."</a>";

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
