<?

/**
  * $Id$
  * 
  * Copyright 2010 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  * Data Objects (agregat)
  * 
  * Object corresponding to a given Datamodel specification.
  * Contains the data fields of the datamodel.
  * Can be
  * - upgraded,
  * - displayed,
  * - etc.
  * 
  */

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [begin]");

/**
 * Agrégats de données
 *
 */
class data_bank_agregat
{

/**
 * Datamodel specifications
 * NEEDS to be overloaded !!
 * 
 * @var integer
 */
protected $datamodel_id=0;

/**
 * Data fields
 * 
 * @var array
 */
//protected $id = 0; TODO : change field[id] to a simple id var... lot of things to do !!
protected $fields = array();
protected $field_values = array();

/**
 * Form, display, etc. options
 * 
 * @var array
 */
protected $options = array();

public function __sleep()
{

// TODO : Find a solution if the update had not to be saved !
foreach ($this->fields as $name=>$field)
	if ($field->value !== $this->field_values[$name])
		$this->field_values[$name] = $field->value;

return array("field_values");

}
public function __wakeup()
{

$this->datamodel_find();

foreach ($this->field_values as $name=>$value)
{
	$this->fields[$name] = clone datamodel($this->datamodel_id)->{$name};
	$this->fields[$name]->value = $value;
}

}

/**
 * 
 * @param $id
 * @param $fields
 */
function __construct($id=null, $fields=array())
{

$this->datamodel_find();
$this->datamodel_set();
if (is_numeric($id) && $id>0)
{
	$this->db_retrieve(array("id"=>$id), $fields);
}

}

/**
 * Retrieve the datamodel id from the class name
 */
protected function datamodel_find()
{

$datamodel_name = substr(get_called_class(),0,-8);
if (datamodel()->exists_name($datamodel_name))
{
	$this->datamodel_id = datamodel()->get_name($datamodel_name)->id();
}

}
/**
 * Sets required fields of the object from the datamodel informations
 */
public function datamodel_set()
{

$this->fields = array();
$this->field_values = array();
// Champs par défaut :
/*
foreach($this->datamodel()->fields_key() as $name)
{
	$this->fields[$name] = clone $this->datamodel()->{$name};
	$this->field_values[$name] = $this->fields[$name]->value;
}
foreach($this->datamodel()->fields_required() as $name)
{
	$this->fields[$name] = clone $this->datamodel()->{$name};
	$this->field_values[$name] = $this->fields[$name]->value;
}
*/

}
public function datamodel()
{

return datamodel($this->datamodel_id);

}

public function __isset($name)
{

return isset($this->datamodel()->{$name});

}

public function __get($name)
{

if (isset($this->fields[$name]))
{
	return $this->fields[$name];
}
elseif (isset($this->field_values[$name]))
{
	$this->fields[$name] = clone $this->datamodel()->{$name};
	$this->fields[$name]->value = $this->field_values[$name];
	return $this->fields[$name];
}
/* Cas où l'object est en bdd => aller chercher la valeur !
elseif (isset($this->datamodel()->{$name}) && isset($this->fields["id"]))
{
	$this->db_retrieve($name);
	return $this->fields[$name];
}
*/
elseif (isset($this->datamodel()->{$name}))
{
	$this->fields[$name] = clone $this->datamodel()->{$name};
	$this->field_values[$name] = $this->fields[$name]->value;
	return $this->fields[$name];
}
elseif (DEBUG_DATAMODEL)
{
	trigger_error("Datamodel '".$this->datamodel()->name()."' agregat : Property '$name' not defined");
}

}

/**
 * Default disp value
 * Can (and SHOULD) be overloaded in datamodel library
 * 
 * @return string
 */

/**
 * Returns the ID
 * MUST be overloaded in datamodel library
 */
function __tostring()
{

return $this->datamodel()->label()." ID#".$this->fields["id"];

}

/**
 * Update a data field
 */
public function __set($name, $value)
{

if (isset($this->fields[$name]))
{
	$this->fields[$name]->value = $value;
}
elseif (isset($this->field_values[$name]))
{
	$this->fields[$name] = clone $this->datamodel()->{$name};
	$this->fields[$name]->value = $value;
}
elseif (isset($this->datamodel()->{$name}))
{
	$this->fields[$name] = clone $this->datamodel()->{$name};
	$this->field_values[$name] = $this->fields[$name]->value;
	$this->fields[$name]->value = $value;
}
elseif (DEBUG_DATAMODEL)
	trigger_error("Datamodel '$this->datamodel' agregat : Property '$name' not defined");
	
}

/**
 * Correct the problem of fields
 */
function __clone()
{

foreach ($this->fields as $name=>$field)
	$this->fields[$name] = clone $field;

}

/**
 * Returns defined field list (eventually not complete !)
 * TODO : find a solution to SIMPLY (db_retrieve_all() ??) complete the list !
 */
public function field_list()
{

return $this->fields;

}

/**
 * Set/init all fileds to default value
 * 
 */
public function init()
{

foreach ($this->datamodel()->fields() as $name=>$field)
{
	$this->fields[$name] = clone $field;
	$this->field_values[$name] = $field->value;
}

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

$query_ok = true;
$params = array();
if (!is_array($fields))
	if (is_string($fields))
		$fields = array($fields);
	else
		$fields = array();

// Verify params
foreach ($this->datamodel()->fields_key() as $name)
	if (!isset($this->fields[$name]) && !isset($this->field_values[$name]))
	{
		if (DEBUG_DATAMODEL)
			trigger_error("Datamodel '".$this->datamodel()->name()."' agregat : missing key '$name' to retrieve fields");
		$query_ok = false;
	}
	else
		$params[] = array( "name"=>$name, "type"=>"=", "value"=> $this->fields[$name]->value_to_db());

// Delete the fields we already have
if (!$force) foreach ($fields as $i=>$name)
	if (isset($this->fields[$name]))
		unset($fields[$i]);

// Effective Query
if ($query_ok && count($fields) && ($list = $this->datamodel()->db_fields($params, $fields)))
{
	if (count($list) == 1)
	{
		foreach($list[0] as $name=>$field)
		{
			if (!isset($this->fields[$name]) && !isset($this->field_values[$name]))
			{
				$this->fields[$name] = $field;
				$this->field_values[$name] = $field->value;
			}
		}
		if (APC_CACHE)
			apc_store("dataobject_".$this->datamodel_id."_".$this->fields["id"], $this);
		return true;
	}
	else
	{
		if (DEBUG_DATAMODEL)
			trigger_error("Datamodel '".$this->datamodel()->name()."' agregat : too many objects resulting from query params");
		return false;
	}
}
else
	return false;

}
/**
 * Retrieve all data fields from database
 *
 * @return unknown
 */
public function db_retrieve_all()
{

$fields = array();
foreach ($this->datamodel()->fields() as $name=>$field)
	if (!isset($this->field_values[$name]) && !isset($this->fields[$name]))
		$fields[]=$name;

if (count($fields)>0)
{
	return $this->db_retrieve($fields);
}
else
	return false;

}

/**
 * Return a view of the object, using a datamodel template
 * @param unknown_type $name
 */
public function view($name="")
{

if (!$name)
	$name = $this->datamodel()->name();

//$this->db_retrieve_all();

// C'est un mega gros mix de toutes les façons de faire... va falloir choisir à un moment !
if (template()->exists_name("datamodel/$name"))
{
	$list_name = template()->list_name_get();
	$id = $list_name["datamodel/$name"];
	$view = template($id);
	$view->object_set($this);
	return $view;
}

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
 * Return the default view
 *
 * @param unknown_type $name
 * @return unknown
 */
public function display($name="")
{

return $this->view($name);

}
/**
 * Return the default form view
 *
 * @param unknown_type $name
 * @return unknown
 */
public function form($name="")
{

$this->db_retrieve_all();

if (!$name)
	$name = $this->datamodel()->name();

if (file_exists(PATH_TEMPLATE."/datamodel/".$name.".form.tpl.php"))
{
	$view = new datamodel_display_tpl_php($this->datamodel(), $this->fields);
	$view->tplfile_set($name);
}
else
{
	$view = new datamodel_update_form($this->datamodel(), $this->fields);
}

return $view;

}

/**
 * Return the default form view
 *
 * @param unknown_type $name
 * @return unknown
 */
public function insert_form($name="")
{

$this->db_retrieve_all();

if (!$name)
	$name = $this->datamodel()->name();

if (file_exists(PATH_ROOT."/template/datamodel/".$name.".form.tpl.php"))
{
	$view = new datamodel_display_tpl_php($this->datamodel(), $this->fields);
	$view->tplfile_set($name);
}
else
{
	$view = new datamodel_insert_form($this->datamodel(), $this->fields);
}

return $view;

}
/**
 * Insert new data into database
 *
 * @param unknown_type $options
 */
public function db_insert($options=array())
{

if ($id=$this->datamodel()->db_insert($this->fields))
{
	$this->__set("id", $id);
	return true;
}
else
{
	return false;
}

}

/**
 * Update the object from a form
 * @param unknown_type $fields
 */
public function update_from_form($fields=array())
{

if (count($fields) > 0)
{
	foreach($fields as $name=>$value)
	{
		if ($field=$this->__get($name))
		{
			$field->value_from_form($value);
		}
	}
	// Champs calculés
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
	//$this->db_update();
}
//$this->form()->disp();
	
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
	if (isset($this->fields[$name]))
	{
		$this->fields[$name]->value_from_db($value);
		$this->field_values[$name] = $this->fields[$name]->value;
	}
	elseif (isset($this->field_values[$name]) || isset($this->datamodel()->{$name}))
	{
		$this->fields[$name] = clone $this->datamodel()->{$name};
		$this->fields[$name]->value_from_db($value);
		$this->field_values[$name] = $this->fields[$name]->value;
	}
}

}
/**
 * Update data into database
 *
 * @param unknown_type $options
 */
public function db_update($options=array())
{

$fields = array("id"=>$this->fields["id"]);
foreach ($this->fields as $name=>$field)
{
	if ($this->field_values[$name] !== $field->value)
		$fields[$name] = $field;
}

if ($result = $this->datamodel()->db_update($fields))
{
	//echo "INSERT INTO _databank_update ( databank_id , dataobject_id , account_id , action , datetime ) VALUES ( '".$this->datamodel->id()."' , '".$this->fields["id"]->value."' , '".login()->id()."' , 'u' , NOW() )";
	foreach ($fields as $name=>$field)
		$this->field_values[$name] = $field->value;
	db()->query("INSERT INTO _databank_update ( databank_id , dataobject_id , account_id , action , datetime ) VALUES ( '".$this->datamodel()->id()."' , '".$this->fields["id"]->value."' , '".login()->id()."' , 'u' , NOW() )");
	if (APC_CACHE)
		apc_store("dataobject_".$this->datamodel_id."_".$this->fields["id"], $this);
}

return $result;

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

$action_list = &$this->datamodel()->action_list();
if (isset($action_list[$method]) && $action=$action_list[$method]["method"])
{
	$this->$action($params);
}

}

}

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [end]");

?>
