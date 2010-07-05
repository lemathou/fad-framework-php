<?

/**
 * Les datamodels (modèles de données) sont destinés à :
 * - Modéliser des tables de base de donnée
 * - Modéliser un formulaire
 * - Modéliser plus largement un ensemble de données
 * - Remplir une maquette avec un ensemble de données typées et vérifiées
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
 * Gestion des modèles de donnée (sans forcément de banque associée)
 * 
 */
class datamodel_gestion
{

protected $list = array();
protected $list_id = array();

function __construct()
{

$query = db()->query("SELECT `id`, `name` FROM `_datamodel`");
while (list($id, $name) = $query->fetch_row())
{
	//$this->list[$datamodel["id"]] = new datamodel($datamodel["id"]);
	$this->list_id[$name] = $id;
}

}

function get($id)
{

if (isset($this->list[$id]))
{
	return $this->list[$id];
}
elseif (in_array($id, $this->list_id))
{
	$this->list[$id] = new datamodel($id);
	return $this->list[$id]; 
}
elseif (DEBUG_DATAMODEL)
{
	trigger_error("Cannot create datamodel ID#$id");
}

}

function get_name($name)
{

if (isset($this->list_id[$name]) && ($id=$this->list_id[$name]) && isset($this->list[$id]))
{
	return $this->list[$id];
}
elseif (isset($id))
{
	return $this->get($id);
}
elseif (DEBUG_DATAMODEL)
{
	trigger_error("Cannot create datamodel ID#$id");
}

}

function exists($name)
{

return isset($this->list_id[$name]);

}

function loaded($id)
{

return isset($this->list[$id]);

}

public function list_get()
{

return $this->list;

}

public function list_id_get()
{

return $this->list_id;

}

}

/**
 * Modèle de données pour remplir une maquette
 *
 */
class datamodel
{

protected $id = 0;

protected $name = "";
protected $label = "";

/**
 * Library containing required objects infos
 *
 * @var integer
 */
protected $library_id = 0;

/**
 * Data fields : model specifications
 * 
 * @var array
 */
protected $fields = array();

protected $fields_key = array();
protected $fields_required = array();
protected $fields_calculated = array();

protected $db_opt = array
(
	"table" => "",
	"index" => array(),
	"key" => array(),
	"sort" => "",
);

protected $disp_opt = array();

/*
 * Public actions / Kind of controller part of the MVC design
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

// Données à sauver en session
private $serialize_list = array("id", "name", "label", "library_id", "fields", "fields_key", "fields_required", "fields_calculated", "db_opt", "disp_opt", "action_list");
public $serialize_save_list = array();

public function __construct($id, $fields=array())
{

$this->id = $id;
$this->query_info();

/* DEPRECATED
if (is_array($fields))
	foreach ($fields as $name=>$field)
		$this->__set($name, $field);
*/

}

/*
 * Sauvegarde/Restauration de la session
 */
/*
function __sleep()
{

return session_select::__sleep($this->serialize_list);

}

function __wakeup()
{

session_select::__wakeup();

if (DEBUG_SESSION == true)
	echo "<p>WAKEUP : datamodel id#$this->id</p>\n";

}
*/

public function library()
{

return library($this->library_id);

}

public function query_info()
{

// Infos de base sur le datamodel
$query = db()->query("SELECT t1.`name`, t1.`library_id` , t1.`table` , t2.`label` FROM _datamodel as t1 LEFT JOIN _datamodel_lang as t2 ON t1.id=t2.id WHERE t1.id='".db()->string_escape($this->id)."'");
if ($query->num_rows())
{
	list($this->name, $this->library_id, $this->db_opt["table"], $this->label) = $query->fetch_row();
	//echo "<p>Datamodel $name : id $this->id</p>";
}

$this->fields = array();
$this->fields_key = array();
$this->fields_required = array();
$this->fields_calculated = array();
// Création des champs du datamodel
$query = db()->query("SELECT t1.`name` , t1.`type` , t1.`defaultvalue` , t1.`opt` , t1.`lang` , t2.`label` FROM _datamodel_fields as t1 LEFT JOIN _datamodel_fields_lang as t2 ON t1.datamodel_id=t2.datamodel_id AND t1.name=t2.fieldname WHERE t1.datamodel_id='$this->id' ORDER BY t1.pos");
while ($field=$query->fetch_assoc())
{
	if ($field["defaultvalue"] === null)
	{
		$field["defaultvalue"] = "null";
	}
	$datatype = "data_$field[type]";
	$this->add( new $datatype($field["name"], $field["defaultvalue"], $field["label"] ) , $field["opt"] );
	if ($field["lang"])
	{
		$this->fields[$field["name"]]->db_opt_set("lang",true);
	}
}
$query = db()->query("SELECT fieldname, opt_type, opt_name, opt_value FROM _datamodel_fields_opt WHERE datamodel_id='$this->id'");
while ($opt=$query->fetch_assoc())
{
	//echo "<p>$this->id : $opt[fieldname] : $opt[opt_type] : $opt[opt_name]</p>\n";
	$method="$opt[opt_type]_opt_set";
	$this->fields[$opt["fieldname"]]->$method($opt["opt_name"], json_decode($opt["opt_value"], true));
}

if (DEBUG_LIBRARY == true)
	echo "<p>Loading library ID#$this->library_id from datamodel id#$this->id query_info</p>\n";
$this->library()->load();

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
	if ($name == "key")
		$this->fields_key = $value;
	return true;
}
else
	return false;

}

public function db_opt($name)
{

if (isset($this->db_opt[$name]))
	return $this->db_opt[$name];
elseif (DEBUG_DATAMODEL)
	trigger_error("Property db_opt[$name] doesn't exist");

}

public function __get($name)
{

if (isset($this->fields[$name]))
	return $this->fields[$name];
elseif (DEBUG_DATAMODEL)
	trigger_error("Property $name doesn't exist");

}

public function __isset($name)
{

return isset($this->fields[$name]);

}

public function __unset($name)
{

if (isset($this->fields[$name]))
	unset($this->fields[$name]);
elseif (DEBUG_DATAMODEL)
	trigger_error("Field $name doesn't exist");

}

/*
 * Add a data field
 */
public function __set($name, data $field)
{

if (is_a($field, "data"))
{
	$field->datamodel_set($this);
	return $this->fields[$name] = $field;
}
elseif (DEBUG_DATAMODEL)
	trigger_error("Field $name is not a data field object");

}
public function add(data &$field, $options="")
{

if (!is_a($field, "data"))
{
	if (DEBUG_DATAMODEL)
		trigger_error("Field $field->name is not a data field object");
}
elseif (isset($this->fields[$field->name]))
{
	if (DEBUG_DATAMODEL)
		trigger_error("Field $field->name already exists");
}
else
{
	//echo "<p>$this->name : $field->name</p>\n";
	$this->fields[$field->name] = $field;
	$field->datamodel_set($this->id);
	if ($options == "key")
		$this->fields_key[] = $field->name;
	elseif ($options == "required")
		$this->fields_required[] = $field->name;
}

}
public function field_add(data $field, $options="")
{

$this->add($field, $options);

}

public function fields()
{

return $this->fields;

}

public function name()
{

return $this->name;

}

public function label()
{

return $this->label;

}

public function id()
{

return $this->id;

}

public function field_required_add($name)
{

if ($name && isset($this->fields[$name]) && !in_array($name, $this->fields_required))
	$this->fields_required[] = $name;

}

public function fields_required()
{

return $this->fields_required;

}

public function field_key_add($name)
{

if ($name && isset($this->fields[$name]) && !in_array($name, $this->fields_key))
	$this->fields_key[] = $name;

}

public function fields_key()
{

return $this->fields_key;

}

public function field_calculated_add($name, $list)
{

if ($name && isset($this->fields[$name]) && !isset($this->fields_calculated[$name]))
{
	$this->fields_calculated[$name] = $list;
}

}

public function fields_calculated()
{

return $this->fields_calculated;

}

/**
 * Create the associated database
 *
 * @return unknown
 */
public function db_create()
{

$fields = array();
$options = array();
$fields_lang = array();
foreach ($this->fields as $name=>$field)
{
	$f = $field->db_field_create();
	if (in_array($name, $this->fields_key))
	{
		$f["null"] = false;
		$f["key"] = true;
	}
	elseif (in_array($name, $this->fields_required))
	{
		$f["null"] = false;
	}
	elseif (!isset($f["null"]))
	{
		$f["null"] = true;
	}
	if ($field->db_opt("lang"))
		$fields_lang[$name] = $f;
	else
		$fields[$name] = $f;
}

db()->table_create($this->db_opt["table"], $fields, $options);

if (count($fields_lang))
{
	foreach($fields as $i=>$j)
		if (isset($j["key"]))
		{
			if (isset($j["auto_increment"]))
				unset($j["auto_increment"]);
			$fields_lang[$i] = $j;
		}
	$fields_lang["lang_id"] = array ( "type"=>"integer" , "size"=>"3" , "key"=>true );
	db()->table_create($this->db_opt["table"]."_lang", $fields_lang, $options);
}

}

/**
 * A fignoler, il faudra par ailleurs ajouter la gestion du positionnement au datamodel...
 * 
 * @param unknown_type $fieldname
 * @param unknown_type $position
 */
public function db_field_move($fieldname, $position)
{

db()->query("ALTER TABLE `".$this->db_opt["table"]."` MODIFY COLUMN ".db()->db_field_struct($fieldname, $this->fields[$fieldname]->db_field_create())." AFTER `$position`");

}

/**
 * Update the associated database in case of updating data fields
 * or general structure
 *
 * @return unknown
 */
public function db_alter()
{

//return db()->table_update("$this->name","");

}

/**
 * Drop the associated database
 *
 * @return unknown
 */
public function db_drop()
{

//return db()->table_update("$this->name","");

}

/**
 * Empty the associated database
 *
 * @return unknown
 */
public function db_empty()
{

//return db()->table_update("$this->name","");

}

/**
 * Insert an object in database
 *
 * @param unknown_type $fields
 * @return unknown
 */
public function db_insert($fields=array())
{

// On v�rifie les champs requis
foreach ($this->fields_required as $name)
{
	// Missing field
	if (!isset($fields[$name]))
	{
		$query_ok = false;
		if (DEBUG_DATAMODEL)
			trigger_error("Dadamodel '$this->name' db_insert() : Missing required field '$name'");
	}
}
// On v�rifie les champs et on vire les clefs

$query_list = array();

foreach ($fields as $name=>$field)
{
	// Missing field
	if (!isset($this->fields[$name]))
	{
		$query_ok = false;
		if (DEBUG_DATAMODEL)
			trigger_error("Dadamodel '$this->name' db_insert() : Undefined field '$name'");
	}
	// Clef : a modifier car un index n'est pas forc�ment auto_increment et il peut y en avoir +ieurs...
	// disons que ce sera ainsi pour une classe h�rit�e qui g�gera les dataobjects
	elseif (in_array($name, $this->fields_key))
	{
		unset($fields[$name]);
		if (DEBUG_DATAMODEL)
			trigger_error("Dadamodel '$this->name' db_insert() : Cannot insert key field '$name'");
	}
	// Champ complexe
	elseif ($this->fields[$name]->type == "dataobject_list")
	{
		if ($fields[$name]->value)
		{
			$query_list[$name] = array();
		}
	}
	elseif ($this->fields[$name]->type == "dataobject_select")
	{
		if (!isset($this->fields[$name]->db_opt["field"]) || !($fieldname = $this->fields[$name]->db_opt["field"]))
			$fieldname = $name;
		$query_fields[] = "`$fieldname`";
		$query_values[] = "'".db()->string_escape($field->value_to_db())."'";
	}
	else
	{
		if (!isset($this->fields[$name]->db_opt["field"]) || !($fieldname = $this->fields[$name]->db_opt["field"]))
			$fieldname = $name;
		$query_fields[] = "`$fieldname`";
		$query_values[] = "'".db()->string_escape($field->value_to_db())."'";
	}
}

$query_str = "INSERT INTO ".$this->db_opt["table"]." (".implode(",",$query_fields).") VALUES (".implode(",",$query_values).")";
$query = db()->query($query_str);
// db()->insert("$this->name","");
$id = $query->last_id();
//db()->query("INSERT INTO _databank_update ( databank_id , dataobject_id , account_id , action , datetime ) VALUES ( ".databank($this->name)->id()." , ".$id." , ".login()->id()." , 'i' , NOW() )");

if (count($query_list)>0)
{
	foreach($query_list as $name=>$detail)
	{
		if ($this->fields[$name]->type == "dataobject_list" && is_array($fields[$name]->value))
		{
			$details["query_values"] = array();
			foreach ($fields[$name]->value as $object)
			{
				$details["query_values"][] = "('$id','$object->id')";
			}
			if (count($details["query_values"])>0)
			{
				$query_str = "INSERT INTO `".$this->fields[$name]->db_opt["ref_table"]."` (`".$this->fields[$name]->db_opt["ref_field"]."` , `".$this->fields[$name]->db_opt["ref_id"]."`  ) VALUES ".implode(",",$details["query_values"]);
				$query = db()->query($query_str);
			}
		}
	}
}

return $id;

}

/**
 * Remove an object in database
 *
 * @param unknown_type $params
 * @return unknown
 */
public function db_delete($params)
{

//return db()->delete("$this->name","");
/*
 * Dans un premier temps on fait simple...
 */

if (is_array($params) && count($params)==1 && isset($params["id"]) && is_numeric($params["id"]))
	return db()->query("DELETE FROM `$this->name` WHERE id = '".db()->string_escape($params["id"])."'");
else
	return false;

}

/**
 * Update an object in database
 * 
 * @param data[] $fields
 */
public function db_update($fields=array())
{

$query_ok = true;
$params = array();

// On vérifie la présence des clefs et on les passe en params
foreach ($this->fields_key as $name)
{
	// Missing key
	if (!isset($fields[$name]))
	{
		$query_ok = false;
		if (DEBUG_DATAMODEL)
			trigger_error("Dadamodel db_update() : Missing key '$name'");
	}
	// Bad key type
	elseif (!is_a($fields[$name], "data") || ($class=get_class($this->fields[$name])) != get_class($fields[$name]))
	{
		if (DEBUG_DATAMODEL)
			trigger_error("Datamodel '$this->name' : Update error : key '$name' not an instance of '$class'");
	}
	// Key passed in param OK
	elseif (($value = $fields[$name]->value_to_db()) !== null)
	{
		if (!isset($this->fields[$name]->db_opt["field"]) || !($fieldname = $this->fields[$name]->db_opt["field"]))
			$fieldname = $name;
		$params[$name] = "`$fieldname` = '".db()->string_escape($value)."'";
		$insert_params[$name] = $value;
		unset($fields[$name]);
	}
}

if ($query_ok)
{
	$update_fields = array();
	$update_fields_lang = array();
	$update_query = array();
	foreach ($fields as $name=>$field)
	{
		// Field not defined
		if (!isset($this->fields[$name]))
		{
			if (DEBUG_DATAMODEL)
				trigger_error("Datamodel '$this->name' : Update error : Field '$name' not defined");
			unset($field[$name]);
		}
		// Bad field
		elseif (!is_a($field, "data") || ($class=get_class($this->fields[$name])) != get_class($field))
		{
			if (DEBUG_DATAMODEL)
				trigger_error("Datamodel '$this->name' : Update error : Field '$name' not an instance of '$class'");
			unset($field[$name]);
		}
		// Extra table update : dataobject_list
		elseif ($this->fields[$name]->type == "dataobject_list" && $this->fields[$name]->db_opt["ref_table"])
		{
			foreach($field->value as $object)
			{
				$update_query[$name][] = "('".$insert_params["id"]."','".$object->id->value_to_db()."')";
			}
		}
		// Primary table update
		elseif ($this->fields[$name]->type == "dataobject_select")
		{
			//print $field->value;
			if (is_a($field->value,"data_bank_agregat"))
			{
				$update_fields[$fieldname = $this->fields[$name]->db_opt["databank_field"]] = "`$fieldname` = '".db()->string_escape($field->value->datamodel()->name())."'";
				$update_fields[$fieldname = $this->fields[$name]->db_opt["field"]] = "`$fieldname` = '".db()->string_escape($field->value->id)."'";
			}
			else
			{
				$update_fields[$fieldname = $this->fields[$name]->db_opt["databank_field"]] = "`$fieldname` = NULL";
				$update_fields[$fieldname = $this->fields[$name]->db_opt["field"]] = "`$fieldname` = NULL";
			}
		}
		// Primary table update
		else
		{
			if (!isset($this->fields[$name]->db_opt["field"]) || !($fieldname = $this->fields[$name]->db_opt["field"]))
			{
				$fieldname = $name;
			}
			if (($value = $fields[$name]->value_to_db()) !== null)
			{
				if (isset($fields[$name]->db_opt["lang"]))
					$update_fields_lang[$name] = "`$fieldname` = '".db()->string_escape($value)."'";
				else
					$update_fields[$name] = "`$fieldname` = '".db()->string_escape($value)."'";
			}
			else
			{
				if (isset($fields[$name]->db_opt["lang"]))
					$update_fields_lang[$name] = "`$fieldname` = NULL";
				else
					$update_fields[$name] = "`$fieldname` = NULL";
			}
		}
	}
	// OK
	$return = false;
	if (count($update_fields)>0)
	{
		$query_string = "UPDATE ".$this->db_opt("table")." SET ".implode(" , ",$update_fields)." WHERE ".implode(" , ",$params);
		$query = db()->query($query_string);
		if ($query->affected_rows())
		{
			$return = true;
		}
	}
	if (count($update_fields_lang)>0)
	{
		$query_string = "UPDATE ".$this->db_opt("table")."_lang SET ".implode(" , ",$update_fields_lang)." WHERE ".implode(" , ",$params)." AND lang_id='".SITE_LANG_ID."'";
		$query = db()->query($query_string);
		if ($query->affected_rows())
		{
			$return = true;
		}
	}
	if (count($update_query)>0)
	{
		foreach($update_query as $name=>$insert_list)
		{
			// A MODIFIER CA NE FONCTIONNE QUE POUR LES DATAOBJECTS !!
			if ($this->fields[$name]->type == "dataobject_list" && ($ref_field = $this->fields[$name]->db_opt("ref_field")) && ($ref_table = $this->fields[$name]->db_opt("ref_table")) && ($ref_id = $this->fields[$name]->db_opt("ref_id")))
			{
				$query_string = "DELETE FROM `$ref_table` WHERE `$ref_field` = '".$insert_params["id"]."'";
				db()->query($query_string);
				$query_string = "INSERT INTO `$ref_table` (`$ref_field`,`$ref_id`) VALUES ".implode(",",$insert_list);
				db()->query($query_string);
				$return = true;
			}
		}
	}
	return $return;
}
else
	return false;

}

/**
 * Retrieve a list of objects by giving query params and fields to be retrieved
 * The function adds the required fields
 *
 * @param unknown_type $query_where
 */
public function db_get($params=array(), $fields=array(), $sort=array(), $limit=0, $start=0)
{

if (!is_array($fields))
{
	if (DEBUG_DATAMODEL)
		trigger_error("datamodel '$this->name' : incorrect field param.");
	return false;
}
else
{
	$agregat_name = $this->name."_agregat";
	// Retrieve the resulting fields
	if (is_array($result = $this->db_select($params, $fields, $sort, $limit, $start)))
	{
		// Fields for each object
		$objects = array();
		foreach($result as $o)
		{
			$object = new $agregat_name();
			foreach($o as $name=>$value)
			{
				//echo "<p>$this->name : $name : $value</p>\n";
				$object->{$name}->value_from_db($value);
			}
			$objects[] = $object;
		}
		return $objects;
	}
	else
		return false;
}

}

/**
 * Retrieve an array containing each the one an array of fields to be retrieved by giving query params
 *
 * @param unknown_type $query_where
 */
public function db_fields($params=array(), $fields=array(), $sort=array())
{

// Retrieve the resulting fields
if (is_array($result = $this->db_select($params, $fields, $sort)))
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
		trigger_error("datamodel '$this->name' : incorrect params.");
	return false;
}
elseif (!is_array($fields_input))
{
	if (DEBUG_DATAMODEL)
		trigger_error("datamodel '$this->name' : incorrect field list.");
	return false;
}
else
{
	// Requete sur la table principale
	if (!isset($field->db_opt["table"]) || !($tablename = $field->db_opt["table"]))
		$tablename = $this->db_opt["table"];
	$query_base = array ( "fields" => array(), "where" => array(), "from" => array("`".$tablename."`") );
	$query_lang = false;
	// Autres requetes
	$query_list = array();
	// Fields to be retrieved with other queries : A MODIFIER, VERIFIER LA TABLE ! CAR TABLES COMPLEMENTAIRES AUSSI POSSIBLES
	$type_special = array("list", "dataobject_list", "dataobject_select"); // "list" à rajouter !
	// Result
	$return = array();
	// Result params mapping
	$return_params = array();
	
	$fields = array();
	// Verify fields to be retrieved :
	foreach($this->fields as $name=>$field)
	{
		// Add key & required fields if needed
		if (in_array($name, array_merge($this->fields_key, $this->fields_required, $fields_input)))
		{
			$fields[] = $name;
			if (!in_array($field->type, $type_special))
			{
				if (!isset($field->db_opt["table"]) || !($tablename = $field->db_opt["table"]))
					$tablename = $this->db_opt["table"];
				else
					$query_base["join"][] = $tablename;
				if (!isset($field->db_opt["field"]) || !($fieldname = $field->db_opt["field"]))
					$fieldname = $name;
				if (isset($field->db_opt["lang"]))
				{
					$query_lang = true;
					$query_base["fields"][] = "`".$tablename."_lang`.`$fieldname` as `$name`";
				}
				else
					$query_base["fields"][] = "`$tablename`.`$fieldname` as `$name`";
			}
			elseif ($field->type == "dataobject_select")
			{
				$tablename = $this->name;
				$fieldname_1 = $field->db_opt["databank_field"];
				$fieldname_2 = $field->db_opt["field"];
				$query_base["fields"][] = "CONCAT(`$tablename`.`$fieldname_1`,',',`$tablename`.`$fieldname_2`) as $name";
			}
			elseif ($field->type == "dataobject_list")
			{
				//echo "<p>$name : ".$this->fields[$name]->db_opt("ref_table")."</p>";
				$query_list[$name] = array( "field" => $name , "table" => $this->fields[$name]->db_opt("ref_table") );
			}
			elseif ($field->type == "list")
			{
				//echo "<p>$name : ".$this->fields[$name]->db_opt("ref_table")."</p>";
				$query_list[$name] = array( "field" => $this->fields[$name]->db_opt("ref_field") , "table" => $this->fields[$name]->db_opt("ref_table") );
			}
		}
	}
	
	// Verify query params
	foreach($params as $param_nb=>$param)
	{
		if (!isset($param["type"]))
			$param["type"] = "";
		if (isset($this->fields[$param["name"]]))
		{
			$field = $this->fields[$param["name"]];
			// Champ "standard"
			if (!in_array($field->type, $type_special))
			{
				if (isset($field->db_opt["lang"]))
				{
					$query_lang = true;
					$query_base["where"][] = "`".$this->db_opt["table"]."_lang`.".$field->db_query_param($param["value"], $param["type"]);
				}
				else
					$query_base["where"][] = "`".$this->db_opt["table"]."`.".$field->db_query_param($param["value"], $param["type"]);
				// mapping for other queries
				foreach($query_list as $i=>$j)
				{
					if ($this->fields[$i]->type == "dataobject_list")
					{
						$query_list[$i]["where"][] = "`".$this->db_opt["table"]."`.".$field->db_query_param($param["value"], $param["type"]);
					}
					else
					{
						//echo "<br />BUG";
					}
				}
			}
			elseif ($field->type == "dataobject_select")
			{
				$query_base["where"][] = "`".$field->db_opt["databank_field"]."` = '".db()->string_escape($param["value"])."'";
			}
			elseif ($field->type == "dataobject_list")
			{
				$query_base["from"][] = "`".$field->db_opt["ref_table"]."`";
				$query_base["where"][] = "`".$field->db_opt["ref_table"]."`.`".$field->db_opt["ref_field"]."` = '".db()->string_escape($param["value"])."'";
				$query_base["where"][] = "`".$field->db_opt["ref_table"]."`.`".$field->db_opt["ref_id"]."` = `".$this->db_opt["table"]."`.`id`";
				// FAIRE UN JOIN CAR CONDITIONS PARAMS AVEC AUTRES TABLES : genre entreprise qui embauche 
			}
		}
		// PAS DEFINI
		else
		{
			unset($params[$param_nb]);
		}
	}
	
	// Primary query

	// Lang
	if ($query_lang)
	{
		if (!isset($field->db_opt["table"]) || !($tablename = $field->db_opt["table"]))
			$tablename = $this->db_opt["table"];
		$query_base["from"][] = "`".$tablename."_lang`";
		$query_base["where"][] = "`".$this->name."`.`id` = `".$this->name."_lang`.`id`";
		$query_base["where"][] = "`".$this->name."_lang`.`lang_id` = '".SITE_LANG_ID."'";
	}
	
	// This query is always performed to map keys with results, which is simpler for other queries
	if (count($query_base["where"]) == 0)
	{
		$query_base["where"][] = "1";
	}
	// Sort
	if (count($sort)>0)
	{
		foreach($sort as $i=>$j)
			$query_sort[] = "`$i` $j";
	}
	else
	{
		$query_sort[] = "`id` ASC";
	}
	// Limit
	if ($limit)
	{
		$query_limit = " LIMIT $start, $limit";
	}
	else
	{
		$query_limit = "";
	}
	
	$query_string = "
		SELECT ".implode(" , ",$query_base["fields"])."
		FROM ".implode(" , ",$query_base["from"])."
		WHERE ".implode(" AND ",$query_base["where"])."
		ORDER BY ".implode(",", $query_sort)."
		$query_limit";

	// Effective Query
	$query = db()->query($query_string);
	$list_id = array();
	
	if ($query->num_rows() >= 1)
	{
		while ($row=$query->fetch_assoc())
		{
			$return[$row["id"]] = $row;
			$list_id[] = $row["id"];
			// Result params mapping
			/*
			$return_params[$nb] = array();
			foreach($this->fields_key as $fieldname)
			{
				$return_params[$nb][$fieldname] = $row[$fieldname];
			}
			*/
		}
	}
	
	// Other queries
	
	foreach($query_list as $name=>$detail)
	{
		$field = $this->fields[$detail["field"]];
		if ($field->type == "list")
		{
			$ref_field = $field->db_opt("ref_field");
			$ref_table = $field->db_opt("ref_table");
			$ref_id = $field->db_opt("ref_id");
			$detail["where"][] = "`".$this->db_opt["table"]."`.`id` = `$ref_table`.`$ref_id`";
			$detail["where"][] = "`".$this->db_opt["table"]."`.`id` IN (".implode(" , ", $list_id).")";
			$query_string = "
				SELECT `$ref_table`.`$ref_id`, `$ref_table`.`$ref_field`
				FROM `".$this->db_opt["table"]."` , `$ref_table`
				WHERE ".implode(" AND ",$detail["where"]);
			$query = db()->query($query_string);
			if ($query->num_rows() >= 1)
			{
				while ($row=$query->fetch_row())
				{
					$return[$row[0]][$name][] = $row[1];
				}
			}
			foreach ($return as $id=>$detail)
			{
				if (!isset($return[$id][$name]))
				{
					$return[$id][$name] = array();
				}
			}
		}
		elseif ($field->type == "dataobject_list")
		{
			//print_r($field->db_opt_list_get());
			$ref_field = $field->db_opt("ref_field");
			if ($ref_table = $field->db_opt("ref_table"))
			{
				$ref_id = $field->db_opt("ref_id");
			}
			else
			{
				$ref_table = datamodel($field->structure_opt["databank"])->db_opt("table");
				$ref_id = "id";
			}
			$detail["where"][] = "`".$this->db_opt["table"]."`.`id` = `$ref_table`.`$ref_field`";
			$detail["where"][] = "`".$this->db_opt["table"]."`.`id` IN (".implode(" , ", $list_id).")";
			$query_string = "
				SELECT `$ref_table`.`$ref_field` , `$ref_table`.`$ref_id`
				FROM `".$this->db_opt["table"]."` , `$ref_table`
				WHERE ".implode(" AND ",$detail["where"]);
			$query = db()->query($query_string);
			if ($query->num_rows() >= 1)
			{
				while ($row=$query->fetch_row())
				{
					// Patch des fois qu'on ai des resultats en trop ^^
					if (isset($return[$row[0]]))
					{
						$return[$row[0]][$name][] = $row[1];
					}
				}
			}
			foreach ($return as $id=>$detail)
			{
				if (!isset($return[$id][$name]))
				{
					$return[$id][$name] = array();
				}
			}
		}
	}
	
	return $return;
	
}

}

public function db_count($params=array())
{

if (!is_array($params))
{
	return false;
}
else
{
	// Requete sur la table principale
	$query_base = array ( "fields" => array(), "where" => array() );
	// Autres requetes
	$query_list = array();
	// Fields to be retrieved with other queries : A MODIFIER, VERIFIER LA TABLE ! CAR TABLES COMPLEMENTAIRES AUSSI POSSIBLES
	$type_special = array("list", "dataobject_list", "dataobject_select");
	// Result
	$return = array();
	// Result params mapping
	$return_params = array();
	foreach($params as $name=>$value)
	{
		if (isset($this->fields[$name]))
		{
			$field = $this->fields[$name];
			if (!in_array($this->fields[$name]->type, $type_special))
			{
				$query_base["where"][$name] = $this->fields[$name]->db_query_param($value);
				// mapping for other queries
				foreach($query_list as $i=>$j)
				{
					if ($this->fields[$i]->type == "dataobject_list" && ($ref_field=$this->fields[$i]->db_opt("ref_field")))
					{
						$query_list[$i]["where"][$name] = "`$ref_field` = '".db()->string_escape($value)."'";
					}
					elseif ($this->fields[$i]->type == "dataobject_list")
					{
						$query_list[$i]["where"][$name] = "`".$this->fields[$i]->db_opt("ref_id")."` = '".db()->string_escape($value)."'";
					}
				}
			}
			elseif ($this->fields[$name]->type == "dataobject_select")
			{
				$query_base["where"][$name] = "`".$this->fields[$name]->db_opt["databank_field"]."` = '".db()->string_escape($value)."'";
			}
			elseif ($this->fields[$name]->type == "dataobject_list")
			{
				// FAIRE UN JOIN CAR CONDITIONS PARAMS AVEC AUTRES TABLES : genre entreprise qui embauche 
			}
		}
		else
			unset($params[$name]);
	}
	// Primary query
	// This query is always performed to map keys with results, which is simpler for other queries
	if (count($query_base["where"]) == 0)
		$query_base["where"][] = "1";
	$query_string = " SELECT count(*) FROM $this->name WHERE ".implode(" AND ",$query_base["where"]);
	return array_pop(db()->query($query_string)->fetch_row());
}

}

function select_form($params=array(), $url="", $varname="id")
{
?>
<form method="get" action="" style="margin:0px;padding:0px;">
<p>Formulaire de sélection&nbsp;:&nbsp;<select name="id" onchange="document.location.href = '<?php  echo SITE_BASEPATH."/$url?$varname="; ?>'+this.value;">
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

function insert_form($name="")
{

if ($name)
	$datamodel_display = "$name";
else
	$datamodel_display = "datamodel_insert_form";

return new $datamodel_display($this, $this->fields);

}

function table_list($params=array(), $fields=array(), $sort=array())
{

?>
<script type="text/javascript">
function databank_list_sort(form, field)
{
	document.zeform.sort.value = field;
	document.zeform.submit();
}
function databank_params_aff()
{
	element = document.getElementById('databank_params');
	if (element.style.display == 'none')
		element.style.display = 'block';
	else
		element.style.display = 'none';
}
</script>
<form name="zeform" action="" method="post">
<input type="hidden" name="sort" value="id" />
<div style="margin:5px 0px;border:1px black solid;padding: 4px;margin-right: 400px;">
<p><a href="javascript:;" onclick="databank_params_aff()">Paramètres de sélection</a></p>
<div id="databank_params" style="display:none;">
<table cellspacing="0" cellspacing="0" cellpadding="0" width="100%">
<tr>
	<td valign="top"><h3>Sélection :</h3></td>
	<td><?php
	foreach ($this->fields as $field)
	{
		if ($field->type == "select")
		{
			echo "<p>\n";
			echo "$field->name : ";
			$field->form_field_select_disp(true, (isset($params[$field->name])) ? $params[$field->name] : "");
			echo "</p>\n";
		}
		elseif ($field->type == "dataobject_select")
		{
			echo "<p>\n";
			echo "$field->name : ";
			$field->form_field_select_disp(true, (isset($params[$field->name])) ? $params[$field->name] : "");
			echo "</p>\n";
		}
	}
	?></td>
</tr>
<tr>
	<td valign="top"><h3>Afficher les colonnes :</h3></td>
	<td><select name="fields[]" multiple>
<?php
foreach ($this->fields as $name=>$field)
	if (in_array($name, $this->fields_key))
	{
		echo "<option value=\"$name\" selected onclick=\"this.selected=true\" style=\"background-color:red;\">".$field->disp_opt("label")."</option>";
	}
	elseif (in_array($name, $this->fields_required))
	{
		echo "<option value=\"$name\" selected onclick=\"this.selected=true\" style=\"background-color:blue;\">".$field->disp_opt("label")."</option>";
	}
	elseif (in_array($name, $fields))
	{
		echo "<option value=\"$name\" selected>".$field->disp_opt("label")."</option>";
	}
	else
	{
		echo "<option value=\"$name\">".$field->disp_opt("label")."</option>";
	}
?>
	</select></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><input type="submit" value="Afficher" /></td>
</tr>
</table>
</div>
</div>
<div><table cellspacing="0" cellpadding="2" border="1">
<?php
$nb=0;
$list = $this->db_get($params, $fields, $sort);
foreach($list as $object)
{
	if (!$nb)
	{
		echo "<tr>\n";
		foreach($object->field_list() as $field)
			echo "<td><b><a href=\"javascript:;\" onclick=\"databank_list_sort('zeform','$field->name')\">".$field->disp_opt["label"]."</a></b></td>";
		echo "</tr>\n";
	}
	echo "<tr>\n";
	foreach($object->field_list() as $field)
		if ($field->name == "id")
			echo "<td width=\"20\"><a href=\"".SITE_BASEPATH."/".$this->name."/$field/\">$field</a></td>";
		elseif ($field->type == "dataobject" && $field->value)
			echo "<td><a href=\"/".$field->structure_opt("databank")."/".$field->value->id."/\">$field</a></td>";
		else
			echo "<td>$field</td>";
	echo "<td><a href=\"".SITE_BASEPATH."/".$this->name."/$object->id/\"><img src=\"".SITE_BASEPATH."/img/icon/icon-view.gif\" alt=\"View\" /></a></td>";
	echo "<td><a href=\"".SITE_BASEPATH."/".$this->name."/$object->id/update\"><img src=\"".SITE_BASEPATH."/img/icon/icon-edit.gif\" alt=\"Update\" /></a></td>";
	echo "<td><a href=\"javascript:;\" onclick=\"if (window.confirm('Etes-vous certain de supprimer ?')) location.href='".SITE_BASEPATH."/".$this->name."/$object->id/delete'\"><img src=\"".SITE_BASEPATH."/img/icon/icon-delete.gif\" alt=\"Delete\" /></a></td>";
	echo "</tr>\n";
	$nb++;
}
?>
</table></div>
</form>
<?php

}

public function action_list()
{

return $this->action_list;

}

}

/**
 * Agrégats de donnée
 * 
 * Un agrégat est une liste de champs répondant à un datamodel donné.
 * Les agrégats contiennent des chanps modifiables, clones à partir du datamodel.
 * 
 */

/**
 * Agrégats de données
 *
 */
class agregat
{

protected $name = "";
protected $label = "";

/**
 * Datamodel specifications
 * 
 * @var array
 */
protected $datamodel;

/**
 * Data fields
 * 
 * @var array
 */
protected $fields = array();

protected $form_opt = array
(
	"action" => "",
	"method" => "POST",
);

public function __construct($datamodel=null, $fields=array())
{

if ($datamodel !== null && is_a($datamodel, "datamodel"))
	$this->datamodel_set($datamodel);

}

public function datamodel_set(datamodel $datamodel)
{

$this->fields = array();

$this->datamodel = $datamodel;
$this->name = &$this->datamodel->name();
$this->label = &$this->datamodel->label();

// Champs par défaut :
foreach($this->datamodel->fields_key() as $name)
	$this->fields[$name] = clone $this->datamodel->{$name};
foreach($this->datamodel->fields_required() as $name)
	$this->fields[$name] = clone $this->datamodel->{$name};

}

public function __isset($name)
{

return isset($this->fields[$name]);

}

public function __get($name)
{

if (isset($this->fields[$name]))
{
	return $this->fields[$name];
}
elseif (isset($this->datamodel->{$name}))
{
	/*
	$query_ok = true;
	foreach ($this->datamodel->db_opt("key") as $key)
		if (!isset($this->fields[$key]))
		{
			$query_ok = false;
			trigger_error("Datamodel '$this->name' agregat : Missing index key '$key' to retrieve field '$name'");
		}
		else
		{
			$params[$key] = $this->fields[$key]->value_to_db();
		}
	if ($query_ok)
	{
		$this->db_retrieve(array($name));
	return $this->fields[$name];
	}
	else
	*/
	{
		return $this->fields[$name] = clone $this->datamodel->{$name};
	}
}
elseif (DEBUG_DATAMODEL)
{
	trigger_error("Datamodel '$this->name' agregat : Property '$name' not defined");
}

}

/**
 * Default disp value
 *
 * @return string
 */
public function __tostring()
{

return $this->name;

}

/*
 * Update a data field
 */
public function __set($name, $value)
{

if (isset($this->datamodel->{$name}))
{
	if (!isset($this->fields[$name]))
	{
		$this->fields[$name] = clone $this->datamodel->{$name};
	}
	$this->fields[$name]->value = $value;
}
elseif (DEBUG_DATAMODEL)
	trigger_error("Datamodel '$this->datamodel' agregat : Property '$name' not defined");
	
}

public function field_list()
{

return $this->fields;

}

public function update($data_name, data $data)
{

//$this->data[$data_name]);

}


/*
 * Get Datamodel
 */
public function datamodel()
{

return $this->datamodel;
	
}

/**
 * Set/init all fileds to default value
 * 
 */
public function init()
{

foreach ($this->datamodel->fields() as $name=>$field)
	$this->fields[$name] = clone $field;

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
	$name = $this->datamodel->name();

if (file_exists(PATH_ROOT."/template/datamodel/".$name.".form.tpl.php"))
{
	$view = new datamodel_display_tpl_php($this->datamodel, $this->fields);
	$view->tplfile_set($name);
}
else
{
	$view = new datamodel_update_form($this->datamodel, $this->fields);
}

return $view;

}

public function view($name="")
{

if (!$name)
	$name = $this->datamodel->name();

$this->db_retrieve_all();

// C'est un mega gros mix de toutes les façons de faire... va falloir choisir à un moment !
if (file_exists(PATH_ROOT."/template/datamodel/".$name.".tpl.php"))
{
	$view = new datamodel_display_tpl_php($this->datamodel, $this->fields);
	$view->tplfile_set($name);
}
else
{
	$view = new datamodel_display($this->datamodel, $this->fields);
}

return $view;

}

public function disp($name="")
{

$this->display($name)->disp();

}

public function action_list()
{

return $this->datamodel->action_list();

}

public function action($method, $params)
{

$action_list = &$this->datamodel->action_list();
if (isset($action_list[$method]) && $action=$action_list[$method]["method"])
{
	$this->$action($params);
}

}

public function update_from_form($fields=array())
{

if (count($fields) > 0)
{
	foreach($fields as $name=>$value)
	{
		if ($this->__get($name))
		{
			$this->__get($name)->value_from_form($value);
		}
	}
	// Champs calculés
	$calculate = array();
	$retrieve = array();
	foreach($this->datamodel->fields_calculated() as $name=>$list)
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
	// Mise à jour en base de donnée
	//$this->db_update();
}
//$this->form()->disp();
	
}

}

/**
 * Agregat pour databank
 *
 */
class data_bank_agregat extends agregat
{

function __construct(datamodel $datamodel, $id=null, $fields=array())
{

agregat::__construct($datamodel);
if (is_numeric($id))
{
	$this->db_retrieve(array("id"=>$id), $fields);
}

}

function __tostring()
{

return (string)$this->fields["id"];

}

/**
 * Retrieve fields from database
 *
 * @param unknown_type $params
 * @param unknown_type $fields
 * @return unknown
 */
public function db_retrieve($fields, $force=false)
{

$query_ok = true;
$params = array();
// Verify params
foreach ($this->datamodel->fields_key() as $name)
	if (!isset($this->fields[$name]))
	{
		if (DEBUG_DATAMODEL)
			trigger_error("Datamodel '$this->name' agregat : missing key '$name' to retrieve fields");
		$query_ok = false;
	}
	else
		$params[] = array( "name"=>$name, "type"=>"=", "value"=> $this->fields[$name]->value_to_db());

// Delete the fields we already have
if (!$force) foreach ($fields as $i=>$name)
	if (isset($this->fields[$name]))
		unset($fields[$i]);

// Effective Query
if ($query_ok && count($fields) && ($list = $this->datamodel->db_fields($params, $fields)))
{
	if (count($list) == 1)
	{
		foreach($list[0] as $name=>$field)
		{
			$this->fields[$name] = $field;
		}
		return true;
	}
	else
	{
		if (DEBUG_DATAMODEL)
			trigger_error("Datamodel '$this->name' agregat : too many objects resulting from query params");
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
foreach ($this->datamodel->fields() as $name=>$field)
	if (!isset($this->fields[$name]))
		$fields[]=$name;

if (count($fields)>0)
{
	return $this->db_retrieve($fields);
}
else
	return false;

}

/**
 * Alias of db_retrieve_all()
 *
 * @param unknown_type $options
 */
public function db_get($params)
{

$this->db_retrieve($params);

}

/**
 * Update data into database
 *
 * @param unknown_type $options
 */
public function db_update($options=array())
{

if ($result = $this->datamodel->db_update($this->fields))
{
	//echo "INSERT INTO _databank_update ( databank_id , dataobject_id , account_id , action , datetime ) VALUES ( '".$this->datamodel->id()."' , '".$this->fields["id"]->value."' , '".login()->id()."' , 'u' , NOW() )";
	db()->query("INSERT INTO _databank_update ( databank_id , dataobject_id , account_id , action , datetime ) VALUES ( '".$this->datamodel->id()."' , '".$this->fields["id"]->value."' , '".login()->id()."' , 'u' , NOW() )");
}

return $result;

}

/**
 * Insert new data into database
 *
 * @param unknown_type $options
 */
public function db_insert($options=array())
{

if ($id = $this->datamodel->db_insert($this->fields))
{
	$this->id->value_from_form($id);
	return true;
}
else
{
	return false;
}

}

}

/**
 * Datamodel access function
 */
function datamodel($id=null)
{

if (!isset($GLOBALS["datamodel_gestion"]))
{
	$GLOBALS["datamodel_gestion"] = $_SESSION["datamodel_gestion"] = new datamodel_gestion();
}

if (is_numeric($id) && $id>0)
	return $GLOBALS["datamodel_gestion"]->get($id);
else
	return $GLOBALS["datamodel_gestion"];

}

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [end]");

?>
