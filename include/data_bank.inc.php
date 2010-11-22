<?

/**
 * Data Banks management
 * 
 * This is a layer upon datamodels which communicates with database and cache (APC)
 * to fast and simply retrieve and store objects,
 * using many functions
 * 
 */

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [begin]");

/**
 * Databank global managing class
 *
 */
class data_bank_gestion extends session_select
{

protected $list = array();

private $serialize_list = array("list");
public $serialize_save_list = array();

function __construct()
{

$this->query();

}

function query()
{

$this->list = array();

$this->access_function_create();

// permissions !!!!
//$query = db()->query(" SELECT t1.id , t2.perm FROM _databank as t1 , _account_databank_perm as t2 , _databank_lang as t3 WHERE t1.id = t2.databank_id AND t2.account_id = ".login()->id()." AND t1.id = t3.id AND t3.lang = ".login()->lang()." ORDER BY t1.description");

}

function access_function_create()
{

foreach(datamodel()->list_name_get() as $name=>$id)
{
	eval("function $name(\$id=null, \$fields=array()) { return databank(\"$id\", \$id, \$fields); }");
}

}

/**
 * Accéder à une banque de donnée
 * 
 * @param int $id
 */
function get($id)
{

if (isset($this->list[$id]))
{
	// TODO : Vérifier permissions d'accès !!
	return $this->list[$id];
}
elseif (APC_CACHE && ($databank=apc_fetch("databank_$id")))
{
	return $this->list[$id] = $databank;
}
elseif (in_array($id, datamodel()->list_name_get()))
{
	$databank = new data_bank($id);
	if (APC_CACHE)
		apc_store("databank_$id", $databank, APC_CACHE_DATAMODEL_TTL);
	return $this->list[$id] = $databank;
}
else
{
	trigger_error("Databank id $id not found.");
	return false;
}

}

/**
 * 
 * @param string $name
 */
function get_name($name)
{

$list_id = &datamodel()->list_name_get();
if (isset($list_id[$name]) && ($id=$list_id[$name]) && isset($this->list[$id]))
{
	return $this->list[$id];
}
elseif (isset($id))
{
	return $this->get($id);
}
else
{
	trigger_error("Databank '$name' not found.");
	return false;
}

}

/*
 * Sauvegarde/Restauration de la session
 */
function __sleep()
{

return session_select::__sleep($this->serialize_list);

}
function __wakeup()
{

session_select::__wakeup();
$this->access_function_create();

if (DEBUG_SESSION == true)
	echo "<p>WAKEUP : data_bank_gestion</p>\n";

}

}

/**
 * DATA BANK
 */
class data_bank extends session_select
{

protected $id=0;
/**
 * Name of the databank = name of the datamodel
 *
 * @var string
 */
protected $name="";
protected $label="";

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
protected $perm="";
protected $perm_type=array();

/**
 * List of retrieved objects, kept only during the page display
 * @var unknown_type
 */
protected $objects=array();

private $serialize_list = array( "id", "name", "label", "perm", "perm_type" );
public $serialize_save_list = array();

public function __construct($id)
{

$this->id = $id;

$this->perm_query();
$this->library_load();

}

/*
 * Sauvegarde/Restauration de la session
 */
function __sleep()
{

return session_select::__sleep($this->serialize_list);

}
function __wakeup()
{

session_select::__wakeup();
$this->objects=array();
$this->library_load();

if (DEBUG_SESSION == true)
	echo "<p>WAKEUP : data_bank id#$this->id</p>\n";

}

public function datamodel()
{

return datamodel($this->id);

}

/**
 * Databank Id
 *
 * @return integer
 */
public function id()
{

return $this->id;

}
public function name()
{

return datamodel($this->id)->name();

}
public function label()
{

return datamodel($this->id)->label();

}
/**
 * User permissions test
 * Uses user account and perm list.
 *
 * @return boolean
 */
public function perm($type)
{

// MIS A TRUE PENDANT LES TESTS
if (true || is_numeric(strpos($this->perm, $type)))
	return true;
else
	return false;

}
/**
 * User permissions list
 *
 * @return string
 */
public function perm_list()
{

return $this->perm;

}

function perm_query()
{

$this->perm = "";
$this->perm_type = array();

// Retrieving Perm
$query = db()->query("SELECT `perm` FROM `_databank_perm` WHERE `datamodel_id`='$this->id'");
if ($query->num_rows())
{
	list($this->perm) = $query->fetch_row();
}
// Retrieving Perm
$query = db()->query("SELECT `perm_id`, `perm` FROM `_databank_perm_ref` WHERE `databank_id`='$this->id'");
if ($query->num_rows())
	while(list($perm_id, $perm) = $query->fetch_row())
		$this->perm_type[$perm_id] = $perm;

}

function library_load()
{

if (is_a($library=datamodel($this->id)->library(), "library"))
	$library->load();

}

/**
 * Retrieve an objet from the datamodel.
 * 
 * @param integer $id
 * @return mixed 
 */
public function get($id, $fields=array())
{

if (!$this->perm("r"))
{
	if (DEBUG_DATAMODEL)
		trigger_error("Databank $this->name : Permission error : Read access denied");
	return false;
}
elseif (is_numeric($id) && $id>0)
{
	// TODO : hack : Retrieve a maximum of data by default but might be better...
	if (isset($this->objects[$id]))
	{
		if (count($fields) || $fields === true)
			$this->objects[$id]->db_retrieve($fields);
		return $this->objects[$id];
	}
	elseif (APC_CACHE && ($object=apc_fetch("dataobject_".$this->id."_".$id)))
	{
		return $this->objects[$id] = $object;
	}
	elseif (is_array($object_list=datamodel($this->id)->db_get(array(array("name"=>"id", "value"=>$id)), $fields)) && ($object=array_pop($object_list)))
	{
		if (APC_CACHE)
			apc_store("dataobject_".$this->id."_".$id, $object, APC_CACHE_DATAOBJECT_TTL);
		return $this->objects[$id] = $object;
	}
	// Retrieve error
	else
	{
		if (DEBUG_DATAMODEL)
			trigger_error("Databank $this->name : Object id $id does nos exists");
		return NULL;
	}
}
// $id not ok
else
	return false;

}

/**
 * Retrieve infos about objects with params
 * 
 * @param integer $id
 * @param array $infos
 * @return mixed 
 */
public function query($params=array(), $fields=array(), $sort=array(), $limit=0, $start=0)
{

//echo "<p>Databank ID#$this->id : query()</p>\n";

if (is_array($result=datamodel($this->id)->db_get($params, $fields, $sort, $limit, $start)))
{
	foreach($result as $object)
		$this->objects["$object->id"] = $object;
	return $result;
}
else
	return false;

}

/**
 * Compte
 */
public function count($params=array())
{

return datamodel($this->id)->db_count($params);

}

/**
 * Add an object in the bank
 *
 * @param unknown_type $fields
 * 
 * @return agregat
 */
public function insert($fields)
{

if ($id = datamodel($this->id)->db_insert($fields))
{
	return $this->get($id);
}
else
	return false;

}

public function insert_from_form($fields)
{

foreach($fields as $name=>$value)
{
	if (!isset($this->datamodel->{$name}))
		unset($fields[$name]);
	else
	{
		$field = clone datamodel($this->id)->{$name};
		$field->value_from_form($value);
		$fields[$name] = $field;
	}
}
return $this->insert($fields);

}

/**
 * Remove an object
 *
 * @param unknown_type $params
 */
public function delete($params)
{

if (is_array($list=datamodel($this->id)->db_delete($params)))
{
	foreach($list as $id)
	{
		if (isset($this->objects[$id]))
			unset($this->objects[$id]);
		if (APC_CACHE)
			apc_delete("dataobject_".$this->id."_".$id);
	}
	return true;
}
else
	return false;

}

/**
 * Search objects into the databank
 * 
 * @param array $query
 * @return mixed
 */
public function search($params=array(), $fields=array())
{

/*
 * Dans certains cas, par ex. clients et fournisseurs, il faut pouvoir aller chercher
 * dans la databank d'objets liés, ici la databank entreprise.
 * 
 */

// User read permission
if (!$this->perm("r"))
{
	trigger_error("Databank $this->name : read acces denied in search function");
	return false;
}
else
{
	return datamodel($this->id)->search($params, $fields);
}

}

/**
 * Form to select and edit from a list with params
 *
 * @param array $params
 */
public function select_form($params=array(), $url="", $varname="")
{

// User read permission test
if (!$this->perm("r"))
{
	trigger_error("Databank $this->name : read acces denied in select form");
}
else
{
	datamodel($this->id)->select_form($params, $url, $varname);
}

}

/**
 * Form to add an object
 *
 */
public function insert_form()
{

// User read permission test
if (!$this->perm("r"))
{
	trigger_error("Databank $this->name : read acces denied in select form");
}
else
{
	datamodel($this->id)->insert_form()->disp();
}

}

/**
 * Form to add an object
 *
 */
public function table_list($params=array(), $fields=array(), $sort=array())
{

// User read permission test
if (!$this->perm("r"))
{
	trigger_error("Databank $this->name : read acces denied in select form");
}
else
{
	datamodel($this->id)->table_list($params, $fields, $sort);
}

}

/**
 * Create a new object
 * 
 */
public function create($fields_all_init=false)
{

$classname = datamodel($this->id)->name()."_agregat";
$object = new $classname;
if ($fields_all_init) foreach(datamodel($this->id)->fields() as $name=>$field)
{
	if (!isset($object->{$name}))
		$object->{$name} = "";
}
return $object;

}

/**
 * Returns if an object exists
 * @param unknown_type $id
 */
public function exists($id)
{

return $this->datamodel()->exists($id);

}

}


/**
 * Agregat pour databank
 *
 */
abstract class data_bank_agregat extends agregat
{

protected $datamodel_id = 0; // NEEDS to be overloaded !!

/**
 * 
 * @param $id
 * @param $fields
 */
function __construct($id=null, $fields=array())
{

agregat::__construct(datamodel($this->datamodel_id));
if (is_numeric($id) && $id>0)
{
	$this->db_retrieve(array("id"=>$id), $fields);
}

}

/**
 * Returns the ID
 * MUST be overloaded in datamodel library
 */
function __tostring()
{

return (string)$this->fields["id"];

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
	$fields = array($fields);
// Verify params
foreach ($this->datamodel()->fields_key() as $name)
	if (!isset($this->fields[$name]))
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
			$this->fields[$name] = $field;
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
 * Update data into database
 *
 * @param unknown_type $options
 */
public function db_update($options=array())
{

if ($result = $this->datamodel()->db_update($this->fields))
{
	//echo "INSERT INTO _databank_update ( databank_id , dataobject_id , account_id , action , datetime ) VALUES ( '".$this->datamodel->id()."' , '".$this->fields["id"]->value."' , '".login()->id()."' , 'u' , NOW() )";
	db()->query("INSERT INTO _databank_update ( databank_id , dataobject_id , account_id , action , datetime ) VALUES ( '".$this->datamodel()->id()."' , '".$this->fields["id"]->value."' , '".login()->id()."' , 'u' , NOW() )");
	if (APC_CACHE)
		apc_store("dataobject_".$this->datamodel_id."_".$this->fields["id"], $this);
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

if ($id = $this->datamodel()->db_insert($this->fields))
{
	$this->fields["id"]->value_from_form($id);
	return true;
}
else
{
	return false;
}

}

}

/**
 * Databank Access
 *
 * @param string $datamodel
 * @param integer $id
 * @param array $fields
 * @return mixed
 */
function databank($datamodel_id=null, $id=null, $fields=array())
{

if (DEBUG_DATAMODEL)
	echo "<p>DEBUG : Accessing databank : $datamodel</p>\n";

/*
 * Retrieve or create the managing databank object
 */
if (!isset($GLOBALS["databank_gestion"]))
{
	// APC
	if (APC_CACHE)
	{
		if (!($GLOBALS["databank_gestion"]=apc_fetch("databank_gestion")))
		{
			$GLOBALS["databank_gestion"] = new data_bank_gestion();
			apc_store("databank_gestion", $GLOBALS["databank_gestion"], APC_CACHE_GESTION_TTL);
		}
	}
	// Session
	else
	{
		if (!isset($_SESSION["databank_gestion"]))
			$_SESSION["databank_gestion"] = new data_bank_gestion();
		$GLOBALS["databank_gestion"] = $_SESSION["databank_gestion"];
	}
}

if (is_numeric($datamodel_id) && (is_a($databank=$GLOBALS["databank_gestion"]->get($datamodel_id), "data_bank")))
{
	if ($id)
		if (is_a($object=$databank->get($id, $fields), "data_bank_agregat"))
			return $object;
		else
			return false;	
	else
		return $databank;
}
else
	return $GLOBALS["databank_gestion"];

}

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [end]");

?>
