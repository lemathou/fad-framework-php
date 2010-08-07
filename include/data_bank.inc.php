<?

/**
 * Gestion des banques de donnée
 * 
 * Il s'agit d'une surcouche des datamodels qui communique avec la base de donnée.
 * 
 */

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [begin]");

/**
 * Gestion des databank
 *
 */
class data_bank_gestion extends session_select
{

protected $list = array();
protected $list_id = array();

private $serialize_list = array( "list" , "list_id" );
public $serialize_save_list = array();

function __construct()
{

$this->query();

}

function query()
{

$this->list = array();
$this->list_id = array();

$this->list_id = &datamodel()->list_id_get();

$this->access_function_create();

// permissions !!!!
//$query = db()->query(" SELECT t1.id , t2.perm FROM _databank as t1 , _account_databank_perm as t2 , _databank_lang as t3 WHERE t1.id = t2.databank_id AND t2.account_id = ".login()->id()." AND t1.id = t3.id AND t3.lang = ".login()->lang()." ORDER BY t1.description");

}

function access_function_create()
{

foreach($this->list_id as $name=>$id)
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

//echo "<p>DATABANK_GESTION : accessing ID#$id</p>\n";

if (isset($this->list[$id]))
{
	return $this->list[$id];
}
elseif (in_array($id, $this->list_id))
{
	// Vérifier permissions d'accès !!
	return $this->list[$id] = new data_bank($id);
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

if (isset($this->list_id[$name]) && ($id=$this->list_id[$name]) && isset($this->list[$id]))
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

/**
 * 
 */
function list_id_get()
{

return $this->list_id;

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

protected $id = 0;
/**
 * Name of the databank = name of the datamodel
 *
 * @var string
 */
protected $name = "";
protected $label = "";

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

protected $objects = array();

private $serialize_list = array( "id", "name", "label", "perm" );
public $serialize_save_list = array();

public function __construct($id)
{

$this->id = $id;
$this->name = &datamodel($this->id)->name();
$this->label = &datamodel($this->id)->label();

// Retrieving Perm
/*
$query = db()->query("SELECT `perm` FROM `_databank_perm_ref` WHERE `databank_id` = '$this->id'");
if ($query->num_rows())
{
	list($this->perm) = $query->fetch_row();
}
*/
$query = db()->query("SELECT `perm` FROM `_account_databank_perm` WHERE `account_id` = '".login()->id()."' AND `databank_id` = '$this->id'");
if ($query->num_rows())
{
	list($this->perm) = $query->fetch_row();
}

}

public function datamodel()
{

return datamodel($this->id);

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
//$this->library_load();

if (DEBUG_SESSION == true)
	echo "<p>WAKEUP : data_bank id#$this->id</p>\n";

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

return $this->name;

}public function label()
{

return $this->label;

}
/**
 * User permissions test
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
	//print_r($this->objects[$id]);
	// Already in databank
	if (isset($this->objects[$id]))
	{
		$this->objects[$id]->db_retrieve($fields);
		return $this->objects[$id];
	}
	elseif ($object_list = datamodel($this->id)->db_get(array(array("name"=>"id", "value"=>$id)), $fields))
	{
		return $this->objects[$id] = array_pop($object_list);
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

if ($id = datamodel($this->id)->delete($params))
{
	if (isset($this->objects[$id]))
		unset($this->objects[$id]);
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

$classname = $this->name."_agregat";
$object = new $classname;
if ($fields_all_init) foreach(datamodel($this->id)->fields() as $name=>$field)
{
	if (!isset($object->{$name}))
		$object->{$name} = "";
}
return $object;

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

//echo "<p>Accessing databank : $datamodel</p>\n";
	
if (!isset($GLOBALS["databank_gestion"]))
{
	if (DEBUG_SESSION == true)
		echo "<p>Retrieving databank_gestion from _session</p>\n";
	$GLOBALS["databank_gestion"] = $_SESSION["databank_gestion"] = new data_bank_gestion();
}

if (is_numeric($datamodel_id) && (is_a($databank=$GLOBALS["databank_gestion"]->get($datamodel_id), "data_bank")))
{
	if (is_numeric($id) && $id>0)
		if (is_a($object = $databank->get($id, $fields), "data_bank_agregat"))
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