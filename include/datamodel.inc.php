<?php

/**
  * $Id$
  * 
  * Copyright 2008-2011 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  * location : /include : global include folder
  * 
  * Data models
  * Datamodels are designed to :
  * - Modelise tables in database
  * - Modelise a form
  * - Modelise a set of data
  * - Fill a template with a verified set of typed data
  * 
  * Il s'agit d'une liste de définitions de champs ainsi que de méthodes de travail
  * 
  * Il dispose aussi de méthodes de communication avec la base de donnée :
  * - Lecture
  * - Insertion
  * - Mise à jour
  * - Suppression
  * - Recherche simple/avancée
  * 
  */

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [begin]");


/**
 * Datamodel global managing class
 * 
 */
class _datamodel_gestion extends gestion
{

protected $type = "datamodel";

protected $info_list = array("name", "library_id", "perm");

protected $info_detail = array
(
	"name"=>array("label"=>"Nom (unique)", "type"=>"string", "size"=>64, "lang"=>false),
	"label"=>array("label"=>"Label", "type"=>"string", "size"=>128, "lang"=>true),
	"description"=>array("label"=>"Description", "type"=>"text", "lang"=>true),
	"library_id"=>array("label"=>"Librairies", "type"=>"object", "object_type"=>"library", "lang"=>false),
	"dynamic"=>array("label"=>"Dynamique", "type"=>"boolean", "lang"=>false),
);

protected $retrieve_details = false;

function __wakeup()
{

gestion::__wakeup();
$this->access_function_create();

}

protected function construct_more()
{

$this->access_function_create();

}

/**

 * Create databank access functions
 */
protected function access_function_create()
{

foreach($this->list_name as $name=>$id)
{
	$function = "function $name(\$id=null, \$fields=array()) { return datamodel(\"$id\", \$id, \$fields); }";
	//echo "<p>$function</p>\n";
	eval($function);
}

}

}

/**
 * Modèle de données pour remplir une maquette
 *
 */
class _datamodel extends object_gestion
{

protected $_type = "datamodel";

/**
 * Library containing required objects infos
 *
 * @var integer
 */
protected $library_id = 0;

/**
 * If objects are often updated, uses a specific _update field !
 */
protected $dynamic = 0;

/**
 * Account default permissions.
 * set : r,i,u,d,a : read, insert, update, delete, admin
 * 
 * read : get values of an object
 * insert : add an object (so you become admin of it)
 * update : update values of an object
 * delete : delete an object
 * admin : admin the databank an so set all perm values for all
 * objects in the databank and all accounts
 * 
 * A read/insert/update/admin requires at least a read value for
 * any related required databank in the info_list.
 * 
 * Complex actions are defined by the permissions over related dataobjects.
 * For example :
 * 1) if someone can both insert messages and read contact list,
 * he (she) will be able to send an email to everybody...
 * Sending an email is no more than inserting a message linked to a contact.
 * 2) if someone has access to different stock places, he can
 * move a resource from a place to another
 * 
 * To conclude, permissions defines action poeple can do on objects,
 * and must reflect reality, so that a poeple must have access to his car,
 * etc.
 * 
 * A dataobject permission is defined in order of precision :
 * 1) databank global permissions
 * 2) dataobject specific permissions
 * 3) account global permissions
 * 4) account specific permissions
 * 
 * @var string
 */
protected $perm = "";

/**
 * Data fields : model specifications
 * 
 * @var array
 */
protected $fields = array();

protected $fields_required = array();
protected $fields_calculated = array();
protected $fields_index = array();

protected $db_opt = array
(
	"index" => array(),
	"key" => array(),
	"sort" => "",
);
protected $disp_opt = array();

/*
 * Public actions / Kind of controller part of a MVC-like design
 */
protected $action_list = array
(
	"update" => array
	(
		"label" => "Mettre à jour",
		"method" => "update_from_form",
		"params" => array(),
	),
	"delete" => array
	(
		"label" => "Supprimer",
		"method" => "delete",
		"params" => array(),
	),
	"view" => array
	(
		"label" => "Afficher",
		"method" => "view",
		"params" => array(),
	),
);

/**
 * Objects
 */
protected $objects = array();

function __sleep()
{

return array("id", "name", "label", "description", "dynamic", "perm", "library_id", "fields", "fields_required", "fields_calculated", "fields_index");

}
function __wakeup()
{

$this->library_load();

}

protected function construct_more($infos)
{

$this->query_fields();
$this->library_load();

}

protected function construct_fields()
{

// TODO !

}

protected function query_info_more()
{

$this->query_fields();

}

/**
 * Retrieve informations about fields
 */
public function query_fields()
{

$this->fields = array();
$this->fields_required = array();
$this->fields_calculated = array();
$this->fields_index = array();
// Création des champs du datamodel
$query = db()->query("SELECT t1.pos, t1.`name` , t1.`type` , t1.`defaultvalue` , t1.`opt` , t1.`lang` , t1.`query` , t2.`label` FROM `_datamodel_fields` as t1 LEFT JOIN `_datamodel_fields_lang` as t2 ON t1.`datamodel_id`=t2.`datamodel_id` AND t1.`name`=t2.`fieldname` WHERE t1.`datamodel_id`='$this->id' ORDER BY t1.`pos`");
while ($field=$query->fetch_assoc())
{
	$datatype = "data_$field[type]";
	$this->fields[$field["name"]] = new $datatype($field["name"], json_decode($field["defaultvalue"], true), $field["label"]);
	$this->fields[$field["name"]]->datamodel_set($this->id);
	if ($field["opt"] == "required")
		$this->fields_required[] = $field["name"];
	if ($field["query"])
		$this->fields_index[] = $field["name"];
	if ($field["lang"])
		$this->fields[$field["name"]]->db_opt_set("lang",true);
}
$query = db()->query("SELECT `fieldname`, `opt_type`, `opt_name`, `opt_value` FROM `_datamodel_fields_opt` WHERE `datamodel_id`='$this->id'");
while ($opt=$query->fetch_assoc())
{
	//echo "<p>$this->id : $opt[fieldname] : $opt[opt_type] : $opt[opt_name]</p>\n";
	//print_r(json_decode($opt["opt_value"]));
	$method="$opt[opt_type]_opt_set";
	$this->fields[$opt["fieldname"]]->$method($opt["opt_name"], json_decode($opt["opt_value"], true));
}

$this->library_load();

}

public function __tostring()
{

return $this->label;

}

/**
 * Set database options.
 * 
 * To store the value in the database if needed.
 * 
 * @param string $name
 * @param mixed $value
 */
public function db_opt_set($name, $value)
{

if (isset($this->db_opt[$name]))
{
	$this->db_opt[$name] = $value;
	return true;
}
else
	return false;

}
public function db_opt($name)
{

if (isset($this->db_opt[$name]))
	return $this->db_opt[$name];

}

/**
 * Returns the associated library
 */
public function library()
{

if ($this->library_id)
	return library($this->library_id);
else
	return null;

}
function library_load()
{

if ($this->library_id)
{
	library($this->library_id)->load();
}

}

/**
 * Returns a data field
 * @param unknown_type $name
 */
public function __get($name)
{

if (isset($this->fields[$name]))
	return $this->fields[$name];

}

/**
 * Returns if a data field is defined
 * @param unknown_type $name
 */
public function __isset($name)
{

return isset($this->fields[$name]);

}

/**
 * Returns the complete data field list
 */
public function fields()
{

return $this->fields;

}
public function fields_required()
{

return $this->fields_required;

}
public function fields_calculated()
{

return $this->fields_calculated;

}
public function fields_index()
{

return $this->fields_index;

}

/**
 * Returns the list of associated actions
 */
public function action_list()
{

return $this->action_list;

}

/**
 * Permission for this page
 * Using global page perm, specific group page, and specific user page
 */
public function perm($type="")
{

$_type = $this->_type;

// Default perm (all)
$perm = new permission_info($this->perm);
// Specific perm
foreach(login()->perm_list() as $perm_id)
	$perm->update(permission($perm_id)->$_type($this->id));
// Specific perm for user
if ($account_perm=login()->user_perm($_type, $this->id))
	$perm->update($account_perm);

if ($type)
	return $perm->get($type);
else
	return $perm;

}

/**
 * returns an empty object
 * @param boolean $fields_all_init (depreacated ?)
 * @return object
 */
public function create($fields_all_init=false)
{

$classname = $this->name."_agregat";
$object = new $classname();
if ($fields_all_init) foreach($this->fields as $name=>$field)
	$object->{$name} = null;
return $object;

}

/**
 * Display a form to create a new object with default values
 * A better way is to create a new object and display the form using an object method 
 * @param $name
 */
function insert_form($name="")
{

if ($name)
	$datamodel_display = "$name";
else
	$datamodel_display = "datamodel_insert_form";

return new $datamodel_display($this, $this->fields);

}
/**
 * Insert an object in database
 *
 * @param array $fields
 * @return mixed integer boolean
 */
public function insert_from_form($fields)
{

if (!is_array($fields))
	return false;

foreach($fields as $name=>$value)
{
	if (!isset($this->fields[$name]))
		unset($fields[$name]);
	else
	{
		$field = clone $this->fields[$name];
		$field->value_from_form($value);
		$fields[$name] = $field;
	}
}
return $this->insert($fields);

}
public function insert($fields)
{

if ($id=$this->db_insert($fields))
	return $this->get($id);
else
	return false;

}
public function db_insert($object)
{

$fields = &$object->field_list();

// Verify required fields
//foreach ($this->fields_required as $name) if (!$fields[$name]->nonempty())
//	return false;

// Verify fields and supress keys
$query_list = array();
$query_fields = array("`id`");
$query_values = array("null");
$query_fields_lang = array();
$query_values_lang = array();
foreach ($fields as $name=>$field)
{
	// Unknown field name
	if (!isset($this->fields[$name]))
	{
		unset($fields[$name]);
	}
	// Champs complexes
	elseif ($this->fields[$name]->type == "dataobject_list")
	{
		if ($fields[$name]->nonempty())
		{
			$query_list[$name] = $field->value_to_db();
		}
	}
	elseif ($this->fields[$name]->type == "list")
	{
		if ($fields[$name]->nonempty())
		{
			$query_list[$name] = $field->value_to_db();
		}
	}
	elseif ($this->fields[$name]->type == "dataobject_select")
	{
		if (!isset($this->fields[$name]->db_opt["field"]) || !($fieldname=$this->fields[$name]->db_opt["field"]))
			$fieldname = $name;
		$query_fields[] = "`$fieldname`";
		$query_values[] = "'".db()->string_escape($field->value_to_db())."'";
	}
	// Champ simple
	else
	{
		if (!isset($this->fields[$name]->db_opt["field"]) || !($fieldname=$this->fields[$name]->db_opt["field"]))
			$fieldname = $name;
		if (isset($this->fields[$name]->db_opt["lang"]))
		{
			$query_fields_lang[] = "`$fieldname`";
			$query_values_lang[] = "'".db()->string_escape($field->value_to_db())."'";
		}
		else
		{
			$query_fields[] = "`$fieldname`";
			$query_values[] = "'".db()->string_escape($field->value_to_db())."'";
		}
	}
}

if ($this->dynamic)
{
	$datetime = time();
	$query_fields[] = "`_update`";
	$query_values[] = "'".date("Y-m-d H:i:s", $datetime)."'";
}

$query_str = "INSERT INTO `".$this->name."` (".implode(", ", $query_fields).") VALUES (".implode(", ", $query_values).")";
$query = db()->query($query_str);
// db()->insert("$this->name","");

if (!($id=$query->last_id()))
	return false;

if (count($query_fields_lang))
{
	$query_str = "INSERT INTO `".$this->name."_lang` (".implode(", ", $query_fields_lang).") VALUES (".implode(", ", $query_values_lang).")";
	$query = db()->query($query_str);
}

if (count($query_list)>0)
{
	foreach($query_list as $name=>$list)
	{
		if (in_array($this->fields[$name]->type, array("list", "dataobject_list")))
		{
			$details["query_values"] = array();
			foreach ($list as $value) if (is_string($value) || is_numeric($value))
			{
				$details["query_values"][] = "('".db()->string_escape($value)."', '$id')";
			}
			if (count($details["query_values"])>0)
			{
				$query_str = "INSERT INTO `".$this->fields[$name]->db_opt["ref_table"]."` (`".$this->fields[$name]->db_opt["ref_field"]."`, `".$this->fields[$name]->db_opt["ref_id"]."`) VALUES ".implode(", ", $details["query_values"]);
				$query = db()->query($query_str);
			}
		}
	}
}

$object->id = $id;
if ($this->dynamic)
	$object->_update = $datetime;

}

/**
 * Returns if an object exists with this ID
 * @param $id
 */
public function exists($id)
{
	
$query = db()->query("SELECT 1 FROM `".$this->name."` WHERE `id`='".db()->string_escape($id)."'");
if ($query->num_rows())
	return true;
else
	return false;

}

/**
 * Remove an object
 *
 * @param unknown_type $params
 */
public function delete($params)
{

if (is_array($list=$this->db_delete($params)))
{
	if (OBJECT_CACHE)
		$cache_delete_list = array();
	foreach($list as $id)
	{
		if (isset($this->objects[$id]))
			unset($this->objects[$id]);
		if (OBJECT_CACHE)
			$cache_delete_list[] = "dataobject_".$this->id."_".$id;
	}
	if (OBJECT_CACHE && count($cache_delete_list))
		object_cache_delete($cache_delete_list);
	return true;
}
else
	return false;

}
public function db_delete($params)
{

if (($list=$this->db_select($params, array("id"))) && count($list))
{
	// TODO : Delete other entries if needed, in cases of data_databank_list field !
	// TODO : create a function db_select_id() for simple queries !
	$delete_list_id = array();
	foreach ($list as $fields)
	{
		$delete_list_id[] = $fields["id"];
	}
	db()->query("DELETE FROM `".$this->name."` WHERE `id` IN (".implode(", ", $delete_list_id).")");
	return $return;
}
else
{
	return false;
}

}

/**
 * Update an object in database
 * 
 * @param data[] $fields
 */
public function db_update($params=array(), $fields=array(), $sort=array(), $limit=0, $start=0)
{

$query_ok = true;

$query_base = array("update"=>array(), "having"=>array(), "where"=>array(), "from"=>array("`$this->name` as n0"), "sort"=>array());
// Timestamp field
if ($this->dynamic)
{
	$datetime = time();
	$query_base["update"]["_update"] = "n0.`_update` = '".date("Y-m-d H:i:s", $datetime)."'";
}
$query_list = array();

foreach ($fields as $name=>$field)
{
	// Unknown field
	if (!isset($this->fields[$name]))
	{
		unset($fields[$name]);
	}
	// Bad field
	elseif (!is_a($field, "data") || get_class($this->fields[$name]) != get_class($field))
	{
		unset($fields[$name]);
	}
	// Extra table update : dataobject_list
	elseif ($this->fields[$name]->type == "dataobject_list")
	{
		// else only remove all dependances in the main object table
		if ($this->fields[$name]->db_opt["ref_table"])
		{
			$query_list[$name] = array();
		}
	}
	// Extra table update : dataobject_list
	elseif ($this->fields[$name]->type == "list")
	{
		if ($this->fields[$name]->db_opt["ref_table"])
		{
			$query_list[$name] = array();
		}
	}
	// Primary table update
	elseif ($this->fields[$name]->type == "dataobject_select")
	{
		//print $field->value;
		if ($field->nonempty())
		{
			$query_base["update"][$fieldname=$this->fields[$name]->db_opt["databank_field"]] = "n0.`$fieldname` = '".db()->string_escape($field->value->datamodel()->name())."'";
			$query_base["update"][$fieldname=$this->fields[$name]->db_opt["field"]] = "n0.`$fieldname` = '".db()->string_escape($field->value->id)."'";
		}
		else
		{
			$query_base["update"][$fieldname=$this->fields[$name]->db_opt["databank_field"]] = "n0.`$fieldname` = NULL";
			$query_base["update"][$fieldname=$this->fields[$name]->db_opt["field"]] = "n0.`$fieldname` = NULL";
		}
	}
	// Primary table update
	else
	{
		if (!isset($this->fields[$name]->db_opt["field"]) || !($fieldname = $this->fields[$name]->db_opt["field"]))
			$fieldname = $name;
		if (($value = $fields[$name]->value_to_db()) !== null)
		{
			if (isset($fields[$name]->db_opt["lang"]))
			{
				$query_base["lang"] = true;
				$query_base["update"][$name] = "n1.`$fieldname` = '".db()->string_escape($value)."'";
			}
			else
				$query_base["update"][$name] = "n0.`$fieldname` = '".db()->string_escape($value)."'";
		}
		else
		{
			if (isset($fields[$name]->db_opt["lang"]))
			{
				$query_base["lang"] = true;
				$query_base["update"][$name] = "n1.`$fieldname` = NULL";
			}
			else
				$query_base["update"][$name] = "n0.`$fieldname` = NULL";
		}
	}
}

$this->query_params($params, $query_base);

// Where
if (count($query_base["where"]) > 0)
	$query_where = "WHERE ".implode(" AND ",$query_base["where"]);
else
	$query_where = "";
// Having
if (count($query_base["having"]))
	$query_having = "HAVING ".implode(" AND ", $query_base["having"]);
else
	$query_having = "";
// Sort
if (count($sort)>0)
{
	foreach($sort as $name=>$order)
		if (isset($this->fields[$name]) || $name == "relevance")
		{
			if (strtolower($order) == "desc")
				$query_base["sort"][] = "`$name` DESC";
			else
				$query_base["sort"][] = "`$name` ASC";
		}
}
if (count($query_base["sort"]))
	$query_sort = "ORDER BY ".implode(",", $query_base["sort"]);
else
	$query_sort = "";
// Limit
if (is_numeric($limit) && $limit>0 && is_numeric($start) && $start>=0)
	$query_limit = " LIMIT $start, $limit";
else
	$query_limit = "";

// Test number

if (false)
{
	
}

// Primary query

$return = false;
if (count($query_base["update"])>0)
{
	$query_string = "UPDATE ".implode(", ", $query_base["from"])." SET ".implode(", ", $query_base["update"])." $query_where $query_having $query_sort $query_limit";
	//echo "<p>$query_string</p>\n";
	$query = db()->query($query_string);
	if ($query->affected_rows())
	{
		$return = true;
	}
	//echo mysql_error();
}

// Other queries

// TODO : problem if the changes to to are checked in params : the other queries will not check all the good rows (some may disappear from the whery) !!

foreach($query_list as $name=>$insert_list)
{
	if (($this->fields[$name]->type == "dataobject_list" || $this->fields[$name]->type == "list") && ($ref_field=$this->fields[$name]->db_opt("ref_field")) && ($ref_table=$this->fields[$name]->db_opt("ref_table")) && ($ref_id=$this->fields[$name]->db_opt("ref_id")))
	{
		$query_string = "DELETE `$ref_table` FROM ".implode(", ", array_merge(array("`$ref_table`"), $query_base["from"]))." WHERE ".implode(" AND ", array_merge(array("`$ref_table`.`$ref_id`=n0.`id`"), $query_base["where"]))." $query_having $query_sort $query_limit";
		//echo "<p>$query_string</p>\n";
		db()->query($query_string);
		//echo mysql_error();
		if ($fields[$name]->nonempty())
		{
			foreach($fields[$name]->value as $id)
			{
				$query_string = "INSERT INTO `$ref_table` (`$ref_id`, `$ref_field`) SELECT n0.`id`, '".db()->string_escape($id)."' FROM ".implode(", ", $query_base["from"])." $query_where $query_having $query_sort $query_limit";
				//echo "<p>$query_string</p>\n";
				db()->query($query_string);
				//echo mysql_error();
			}
			$return = true;
		}
	}
}

return $return;

}

/**
 * alias of db_get()
 * 
 * @param array $query
 * @return mixed
 */
public function query($params=array(), $fields=array(), $sort=array(), $limit=0, $start=0)
{

return $this->db_get($params, $fields, $sort, $limit, $start);

}
/**
 * Retrieve a list of objects by giving query params and fields to be retrieved
 * The function adds the required fields
 * 
 * @param unknown_type $query_where
 */
public function db_get($params=array(), $fields=array(), $sort=array(), $limit=0, $start=0)
{

if (!is_array($result=$this->db_select($params, "id", $sort, $limit, $start)) || !count($result))
{
	return array();
}

$objects_order = array();
$objects = array();
$db_retrieve_list = array();
$cache_retrieve_list = array();
//$cache_store_list = array(); // For a future

foreach($result as $nb=>$o)
{
	$id = $o["id"];
	if (isset($this->objects[$id]))
	{
		$objects[$nb] = $this->objects[$id];
	}
	elseif (OBJECT_CACHE)
	{
		$objects[$nb] = null;
		$cache_retrieve_list[$id] = "dataobject_".$this->id."_".$id;
	}
	else
	{
		$objects[$nb] = null;
		$db_retrieve_list[] = $id;
	}
	$objects_order[$id] = $nb;
}

// Retrieve from cache
if (count($cache_retrieve_list))
{
	$cache_list = object_cache_retrieve($cache_retrieve_list);
	foreach($cache_retrieve_list as $id=>$cache_id)
	{
		if (isset($cache_list[$cache_id]))
		{
			$objects[$objects_order[$id]] = $this->objects[$id] = $cache_list[$cache_id];
		}
		else
		{
			$db_retrieve_list[] = $id;
		}
	}
}

// Retrieve from database
if (count($db_retrieve_list))
{
	$params = array( array("name"=>"id", "value"=>$db_retrieve_list) );
	if (is_array($result = $this->db_select($params, true)))
	{
		foreach($result as $o)
		{
			$id = $o["id"];
			$object = $this->create();
			$object->update_from_db($o);
			$objects[$objects_order[$id]] = $this->objects[$id] = $object;
			// TODO : store all values in one time
			if (OBJECT_CACHE)
				object_cache_store("dataobject_".$this->id."_".$id, $object, OBJECT_CACHE_DATAOBJECT_TTL);
		}
	}
}

return $objects;

}
/**
 * Retrieve an objet from the datamodel.
 * Optimized version of db_get() for only one object
 * 
 * @param integer $id
 * @return mixed 
 */
public function get($id, $fields=array())
{

// Permissinon check
if (false && !$this->perm("r"))
{
	if (DEBUG_DATAMODEL)
		trigger_error("Databank $this->name : Permission error : Read access denied");
	return false;
}

// Parameters check
if (!is_numeric($id) || $id <= 0 || !is_array($fields))
	return false;

// OK
if (isset($this->objects[$id]))
{
	return $this->objects[$id];
}
elseif (OBJECT_CACHE && ($object=object_cache_retrieve("dataobject_".$this->id."_".$id)))
{
	return $this->objects[$id] = $object;
}
elseif (is_array($info_list=$this->db_select(array(array("name"=>"id", "value"=>$id)), true)) && count($info_list)==1)
{
	$o = array_pop($info_list);
	$id = $o["id"];
	$object = $this->create();
	$object->update_from_db($o);
	if (OBJECT_CACHE)
		object_cache_store("dataobject_".$this->id."_".$id, $object, OBJECT_CACHE_DATAOBJECT_TTL);
	return $this->objects[$id] = $object;
}
else
{
	return null;
}

}
/**
 * Retrieve an array containing each the one an array of fields to be retrieved by giving query params
 * Deprecated ?
 * 
 * @param unknown_type $query_where
 */
public function db_fields($params=array(), $fields=array(), $sort=array(), $limit=0, $start=0)
{

// Retrieve the resulting fields
if (is_array($result = $this->db_select($params, $fields, $sort, $limit, $start)))
{
	$return = array();
	// Fields for each object
	foreach($result as $list)
	{
		$elm = array();
		foreach($list as $name=>$value)
		{
			$elm[$name] = clone $this->fields[$name];
			$elm[$name]->value_from_db($value);
		}
		$return[] = $elm;
	}
	return $return;
}
else
	return false;

}

/**
 * Retrieve a complete field value list by giving query params and fields to be retrieved
 * 
 * @param unknown_type $query_where
 */
public function db_select($params=array(), $fields_input=array(), $sort=array(), $limit=0, $start=0)
{

if (!is_array($params))
{
	if (DEBUG_DATAMODEL)
		trigger_error("datamodel(#ID$this->id)->db_select() : incorrect params.");
	return false;
}

// Requete sur la table principale
$query_base = array("fields"=>array("n0.`id`"), "having"=>array(), "where"=>array(), "join"=>array(), "groupby"=>array(), "from"=>array("`$this->name` as n0"), "sort"=>array());
if ($this->dynamic)
	$query_base["fields"][] = "`_update`";
// Autres requetes
$query_list = array();
// Result
$return = array();
// Result params mapping
$return_params = array();

$fields_explode = array();

$fields = array();
$n = 2;
// Verify fields to be retrieved :
foreach($this->fields as $name=>$field)
{
	// Add fields
	if ($fields_input === true || (is_array($fields_input) && in_array($name, $fields_input)) || (is_string($fields_input) && $name == $fields_input))
	{
		$fields[] = $name;
		if ($field->type == "dataobject_select")
		{
			$fieldname_1 = $field->db_opt["databank_field"];
			$fieldname_2 = $field->db_opt["field"];
			$query_base["fields"][] = "CONCAT(n1.`$fieldname_1`,',',n1.`$fieldname_2`) as $name";
		}
		elseif ($field->type == "dataobject_list")
		{
			$ref_field = $field->db_opt("ref_field");
			$ref_table = $field->db_opt("ref_table");
			$ref_id = $field->db_opt("ref_id");
			$query_base["join"][] = "`$ref_table` as n$n ON n0.`id` = n$n.`$ref_id`";
			if (!count($query_base["groupby"]))
				$query_base["groupby"][] = "n0.`id`";
			$query_base["fields"][] = "CAST(GROUP_CONCAT(DISTINCT QUOTE(n$n.`$ref_field`) SEPARATOR ',') as CHAR) as $name";
			$fields_explode[] = $name;
			$n++;
		}
		elseif ($field->type == "list")
		{
			$ref_field = $field->db_opt("ref_field");
			$ref_table = $field->db_opt("ref_table");
			$ref_id = $field->db_opt("ref_id");
			$query_base["join"][] = "`$ref_table` as n$n ON n0.`id` = n$n.`$ref_id`";
			if (!count($query_base["groupby"]))
				$query_base["groupby"][] = "n0.`id`";
			$query_base["fields"][] = "CAST(GROUP_CONCAT(DISTINCT QUOTE(n$n.`$ref_field`) SEPARATOR ',') as CHAR) as $name";
			$fields_explode[] = $name;
			$n++;
		}
		else
		{
			// Rare case...
			/*
			if (isset($field->db_opt["table"]) && ($tablename = $field->db_opt["table"]))
				$query_base["join"][] = "`$tablename`";
			*/
			if (!isset($field->db_opt["field"]) || !($fieldname=$field->db_opt["field"]))
				$fieldname = $name;
			if (isset($field->db_opt["lang"]))
			{
				$query_base["lang"] = true;
				$query_base["fields"][] = "n1.`$fieldname` as `$name`";
			}
			else
				$query_base["fields"][] = "n0.`$fieldname` as `$name`";
		}
	}
}

$this->query_params($params, $query_base);

/* Primary query */

// Where
if (count($query_base["where"]) > 0)
	$query_where = "WHERE ".implode(" AND ",$query_base["where"]);
else
	$query_where = "";
// Having
if (count($query_base["having"]))
	$query_having = "HAVING ".implode(" AND ", $query_base["having"]);
else
	$query_having = "";
// Join
if (count($query_base["join"]))
	$query_join = "LEFT JOIN ".implode(" LEFT JOIN ", $query_base["join"]);
else
	$query_join = "";
// Group By
if (count($query_base["groupby"]))
	$query_groupby = "GROUP BY ".implode(", ", $query_base["groupby"]);
else
	$query_groupby = "";
// Sort
if (count($sort)>0)
{
	foreach($sort as $name=>$order) if (isset($this->fields[$name]) || $name == "relevance")
	{
		if (strtolower($order) == "desc")
			$query_base["sort"][] = "`$name` DESC";
		else
			$query_base["sort"][] = "`$name` ASC";
	}
}
if (count($query_base["sort"]))
	$query_sort = "ORDER BY ".implode(",", $query_base["sort"]);
else
	$query_sort = "";
// Limit
if (is_numeric($limit) && $limit>0 && is_numeric($start) && $start>=0)
	$query_limit = " LIMIT $start, $limit";
else
	$query_limit = "";

$query_string = "SELECT DISTINCT ".implode(" , ",$query_base["fields"])." FROM ".implode(" , ",$query_base["from"])." $query_join $query_where $query_having $query_groupby $query_sort $query_limit";
//echo "<p>$query_string</p>";

// Effective Query
$query = db()->query($query_string);

if ($query->num_rows() == 0)
	return array();

// Retrieve values
$list_id = array();
$nb = 0;
while ($row=$query->fetch_assoc())
{
	foreach($fields_explode as $name)
	{
		if ($row[$name] == "NULL")
		{
			$row[$name] = array();
		}
		else
		{
			$row[$name] = explode("','",substr($row[$name], 1, -1));
			if ($this->fields[$name]->type == "list")
				foreach($row[$name] as $i=>$value)
					$row[$name][$i] = stripslashes($value);
			elseif ($this->fields[$name]->type == "dataobject_list")
				foreach($row[$name] as $i=>$value)
					$row[$name][$i] = (int)$value;
		}
	}
	$return[$nb] = $row;
	$list_id[$nb] = $row["id"];
	$map_id[$row["id"]] = $nb;
	$nb++;
}

// Other queries

/*
foreach ($query_list as $name=>$detail)
{
	$field = $this->fields[$detail["field"]];
	if ($field->type == "list")
	{
		$ref_field = $field->db_opt("ref_field");
		$ref_table = $field->db_opt("ref_table");
		$ref_id = $field->db_opt("ref_id");
		$detail["where"][] = "`".$this->name."`.`id` = `$ref_table`.`$ref_id`";
		$detail["where"][] = "`".$this->name."`.`id` IN (".implode(", ", $list_id).")";
		$query_string = "
			SELECT `$ref_table`.`$ref_id`, `$ref_table`.`$ref_field`
			FROM `".$this->name."` , `$ref_table`
			WHERE ".implode(" AND ", $detail["where"]);
		$query = db()->query($query_string);
		if ($query->num_rows() >= 1)
		{
			while ($row=$query->fetch_row())
			{
				$return[$map_id[$row[0]]][$name][] = $row[1];
			}
		}
		foreach ($return as $nb=>$detail)
		{
			if (!isset($return[$nb][$name]))
			{
				$return[$nb][$name] = array();
			}
		}
	}
	elseif ($field->type == "dataobject_list")
	{
		//print_r($field->db_opt_list_get());
		$ref_field = $field->db_opt("ref_field");
		if ($ref_table=$field->db_opt("ref_table"))
		{
			$ref_id = $field->db_opt("ref_id");
		}
		else
		{
			$ref_table = datamodel($field->structure_opt["databank"])->name();
			$ref_id = "id";
		}
		$detail["where"][] = "`".$this->name."`.`id` = `$ref_table`.`$ref_id`";
		$detail["where"][] = "`".$this->name."`.`id` IN (".implode(", ", $list_id).")";
		// TODO : Retrieve other required fields and next step create the dependant object without other queried !
		$query_string = "SELECT `$ref_table`.`$ref_id`, `$ref_table`.`$ref_field` FROM `".$this->name."`, `$ref_table` WHERE ".implode(" AND ", $detail["where"]);
		$query = db()->query($query_string);
		if ($query->num_rows() >= 1)
		{
			while ($row=$query->fetch_row())
			{
				// Patch des fois qu'on ai des resultats en trop ^^
				if (isset($return[$map_id[$row[0]]]))
				{
					$return[$map_id[$row[0]]][$name][] = (int)$row[1];
				}
			}
		}
		foreach ($return as $nb=>$detail)
		{
			if (!isset($return[$nb][$name]))
			{
				$return[$nb][$name] = array();
			}
		}
	}
}
*/

return $return;

}

/**
 * Returns the number of objects corresponding to the given param list
 * @param unknown_type $params
 */
public function count($params=array())
{

return $this->db_count($params);

}
public function db_count($params=array())
{

if (!is_array($params))
{
	return false;
}

// Requete sur la table principale
$query_base = array ("where"=>array(), "from"=>array("`".$this->name."` as n0"), "having"=>array(), "lang"=>false);
// Autres requetes
$query_list = array();
// Result
$return = array();
// Result params mapping
$return_params = array();

$this->query_params($params, $query_base);

// Primary query

// Where
if (count($query_base["where"]) > 0)
	$query_where = "WHERE ".implode(" AND ",$query_base["where"]);
else
	$query_where = "";
// Having
if (count($query_base["having"]))
	$query_having = "HAVING ".implode(" AND ", $query_base["having"]);
else
	$query_having = "";

$query_string = "SELECT COUNT(*) FROM ".implode(", ", $query_base["from"])." $query_where $query_having";

return array_pop(db()->query($query_string)->fetch_row());

}

/**
 * Constructs the query with specific params...
 * Used in functions db_select(), db_count() and db_update().
 */
public function query_params(&$params, &$query_base)
{

if (!isset($params["_type"]) || !is_string($params["_type"]) || !in_array(strtoupper($params["_type"]), array("OR", "AND")))
	$params_type = "AND";
else
	$params_type = strtoupper($params["_type"]);

// Number and last index of alternative tables
$query_table_nb = 0;

foreach($params as $param_nb=>$param)
{
	if (!is_array($param) || !isset($param["value"]))
	{
		unset($params[$param_nb]);
	}
	elseif (isset($param["name"]) && $param["name"] == "id")
	{
		if (!isset($param["type"]))
			$param["type"] = "";
		$data_id = new data_id();
		$query_base["where"][] = "n0.".$data_id->db_query_param($param["value"], $param["type"]);
	}
	elseif (isset($param["name"]) && isset($this->fields[$param["name"]]))
	{
		if (!isset($param["type"]))
			$param["type"] = "";
		$field = $this->fields[$param["name"]];
		// Champs "spéciaux"
		if ($field->type == "dataobject_select")
		{
			$query_base["where"][] = "n0.`".$field->db_opt["databank_field"]."` = '".db()->string_escape($param["value"])."'";
		}
		elseif ($field->type == "dataobject_list")
		{
			$query_base["from"][] = "`".$field->db_opt["ref_table"]."` as t$query_table_nb";
			if (is_array($param["value"]))
				$query_base["where"][] = "t$query_table_nb.`".$field->db_opt["ref_field"]."` IN ('".implode("', '",$param["value"])."')";
			else
				$query_base["where"][] = "t$query_table_nb.`".$field->db_opt["ref_field"]."` = '".db()->string_escape($param["value"])."'";
			$query_base["where"][] = "t$query_table_nb.`".$field->db_opt["ref_id"]."` = n0.`id`";
			$query_table_nb++;
			// TODO : FAIRE UN JOIN CAR CONDITIONS PARAMS AVEC AUTRES TABLES : genre entreprise qui embauche 
		}
		elseif ($field->type == "list")
		{
			$query_base["from"][] = "`".$field->db_opt["ref_table"]."` as t$query_table_nb";
			if (is_array($param["value"])) // TODO : string_escape !
				$query_base["where"][] = "t$query_table_nb.`".$field->db_opt["ref_field"]."` IN ('".implode("', '",$param["value"])."')";
			else
				$query_base["where"][] = "t$query_table_nb.`".$field->db_opt["ref_field"]."` = '".db()->string_escape($param["value"])."'";
			$query_base["where"][] = "t$query_table_nb.`".$field->db_opt["ref_id"]."` = n0.`id`";
			$query_table_nb++;
			// TODO : Idem dataobject_list
		}
		// Champ "standard"
		else
		{
			if (isset($field->db_opt["lang"]))
			{
				$query_base["lang"] = true;
				$query_base["where"][] = "n1.".$field->db_query_param($param["value"], $param["type"]);
			}
			else
			{
				$query_base["where"][] = "n0.".$field->db_query_param($param["value"], $param["type"]);
			}
		}
	}
	// Query using fulltext index
	elseif (count($this->fields_index))
	{
		if (!isset($param["type"]))
			$param["type"] = "like";
		if ($param["type"] == "fulltext")
		{
			$query_base["fields"][] = "MATCH(n0.`".implode("`, n0.`", $this->fields_index)."`) AGAINST('".db()->string_escape($param["value"])."') as `relevance`";
			$query_base["having"][] = "relevance > 0";
		}
		else //if ($param["type"] == "like")
		{
			$l = array();
			foreach($this->fields_index as $i)
				$l[] = "`$i` LIKE '%".db()->string_escape($param["value"])."%'";
			$query_base["where"][] = "(".implode(" OR ", $l).")";
		}
	}
	// PAS DEFINI
	else
	{
		unset($params[$param_nb]);
	}
}

// Lang
if (isset($query_base["lang"]) && $query_base["lang"] == true)
{
	$query_base["from"][] = "`".$this->name."_lang` as n1";
	$query_base["where"][] = "n0.`id` = n1.`id`";
	$query_base["where"][] = "n1.`lang_id` = '".SITE_LANG_ID."'";
}

}

/**
 * 
 * Enter description here ...
 * @param array $params
 * @param mixed $fields
 * @param array $order
 * @param integer $limit
 */
function json_query($params=array(), $fields=true, $order=array(), $limit=0)
{

$query = $this->query($params, $fields, $order, $limit);

$o = array();
foreach ($query as $object)
{
	$field_list = array();
	foreach ($this->fields as $i=>$field)
	{
		if ($fields === "1" || (is_array($fields) && in_array($i, $fields)))
		{
			if ($object->{$i}->value === null)
				$field_list[] = "\"$i\":null";
			elseif ($object->{$i}->value === true)
				$field_list[] = "\"$i\":true";
			elseif ($object->{$i}->value === false)
				$field_list[] = "\"$i\":false";
			elseif (is_numeric($object->{$i}->value))
				$field_list[] = "\"$i\":".json_encode($object->{$i}->value);
			else
				$field_list[] = "\"$i\":".json_encode($object->{$i}->value);
		}
	}
	$o[] = "{\"id\":".$object->id.", \"datamodel_id\":".$this->id.", \"value\":".json_encode((string)$object).", \"fields\":{".implode(", ", $field_list)."}}";
}

return "[".implode(", ",$o)."]";

}

/**
 * Display a Select form
 * @param $params
 * @param $url
 * @param $varname
 */
function select_form($params=array(), $url="", $varname="id")
{

if (!$this->perm("l"))
{
	trigger_error("Databank $this->name : list acces denied in select form");
	return;
}

?>
<form method="get" action="" class="datamodel_form <?php echo $this->name; ?>_form">
<p>Formulaire de sélection&nbsp;:&nbsp;<select name="id" onchange="document.location.href = '<?php echo SITE_BASEPATH."/$url?$varname="; ?>'+this.value;">
<option value="">-- Choisir --</option>
<?
$objects = $this->db_get($params);
foreach ($objects as $object)
{
	if (isset($_GET["id"]) && $_GET["id"] == "$object->id")
		print "<option value=\"$object->id\" selected>$object</option>";
	else
		print "<option value=\"$object->id\">$object</option>";
}
?>
</select></p>
</form>
<?

}

}


/*
 * Specific classes for admin
 */
if (ADMIN_LOAD == true)
{
	include PATH_INCLUDE."/admin/datamodel.inc.php";
}
else
{
	class datamodel_gestion extends _datamodel_gestion {};
	class datamodel extends _datamodel {};
}


/**
 * Datamodel access function
 */
function datamodel($datamodel_id=null, $object_id=null, $fields=array())
{

if (!isset($GLOBALS["datamodel_gestion"]))
{
	if (OBJECT_CACHE)
	{
		if (!($GLOBALS["datamodel_gestion"]=object_cache_retrieve("datamodel_gestion")))
			$GLOBALS["datamodel_gestion"] = new datamodel_gestion();
	}
	// Session
	else
	{
		if (!isset($_SESSION["datamodel_gestion"]))
			$_SESSION["datamodel_gestion"] = new datamodel_gestion();
		$GLOBALS["datamodel_gestion"] = $_SESSION["datamodel_gestion"];
	}
}

if ($datamodel_id === null)
{
	return $GLOBALS["datamodel_gestion"];
}

if ( !(is_numeric($datamodel_id) && ($datamodel=$GLOBALS["datamodel_gestion"]->get($datamodel_id))) && !(is_string($datamodel_id) && ($datamodel=$GLOBALS["datamodel_gestion"]->get_name($datamodel_id))))
{
	return null;
}

if ($object_id === null)
	return $datamodel;

if ($object=$datamodel->get($object_id))
	return $object;

return null;	

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
