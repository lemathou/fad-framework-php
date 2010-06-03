<?

/**
  * $Id: databank.inc.php 59 2009-03-03 15:48:26Z mathieu $
  * 
  * � Copyright 2008 Mathieu Moulin - iProspective - lemathou@free.fr �
  * 
  * This file is part of FTNGroupWare.
  * 
  * Databank and Dataobjects classes
  * 
  * 
  * 
  */

/**
 * databank interface
 *
 */
interface databank_i
{

// infos relative aux objets
public function infos($name);

// récup d'un objet de la liste
public function get($id);

// suppression d'un objet de la liste
public function remove($id);

// suppression d'un objet de la liste
public function add($infos);

// recherche d'un objet de la liste en fct des critères passés
public function search($query=array());

// permission test
public function perm($type);

// permission list
public function perm_list();

}

/**
 * Classe abstraite pour les types de donnée
 *
 */
abstract class databank implements databank_i
{

/**
 * Id of the databank
 * Required to retrieve perm infos from database
 *
 * @var integer
 */
protected $id = 0;
/**
 * Name of the databank
 * this is also the name of the objects class,
 * of the principal table name and
 * the function name to access the databank.
 *
 * @var string
 */
protected $name = "";

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
 * Complete properties list and details for the objects in the databank
 * 
 * label : nom du champ pour l'affichage
 * 
 * type : integer, float, string, array(, link... a voir)
 * size : optionnel, taille du champ en base de donn�e
 * 
 * ref_databank : banque de liaison �ventuelle
 * 
 * ref_table : table de liaison en cas de besoin
 * ref_option : options de la table de liaison
 * ref_id : id de liaison
 * 
 * ref_type : integer, float, string, object
 * ref_name : champ de liaison pour l'affichage en cas de liste de valeurs
 * 
 * @var array
 */
protected $infos_list = array ();
/**
 * Default properties of the objects
 * 
 * @var array
 */
protected $infos_defaultlist = array ();
/**
 * Default database table for the objects
 *
 * @var string
 */
protected $infos_table = "";

/**
 * Reference lists for complex properties
 *
 * @var array
 */
protected $ref_list = array ();

/**
 * Class of the objects in the databank
 *
 * @var string
 */
protected $object_class = "";

/**
 * List of the objects in the databank
 *
 * @var array $list
 */
protected $list = array();


public function __construct()
{

//print "<p>$this->name</p>";

// Init the reference tables used for complex properties
foreach($this->infos_list as $name => $info)
{
	if (isset($info["ref_table"]) && $info["type"] != "array" && isset($info["ref_name"]))
	{
		//print "<p>Fetching ref $name : SELECT $info[ref_id] , $info[ref_name] FROM $info[ref_table] ORDER BY $info[ref_name] </p>\n";
		$query = db()->query(" SELECT $info[ref_id] , $info[ref_name] FROM $info[ref_table] ORDER BY $info[ref_name] ");
		while (list($i,$j)=$query->fetch_row())
		{
			$this->ref_list[$name][$i] = $j;
		}
	}
}

// Retrieving database Id
$query = db()->query(" SELECT id FROM _databank WHERE name = '$this->name' ");
list($this->id) = $query->fetch_row();

// Retrieving account global permissions
$this->perm_retrieve();

}

/**
 * Retrieve permissions
 */
public function perm_retrieve()
{
	
$query = db()->query(" SELECT perm FROM _account_databank_perm WHERE account_id = ".login()->id()." AND databank_id = $this->id ");
if ($query->num_rows() == 1)
{
	list($this->perm) = $query->fetch_row();
}

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

/**
 * Retrieve an objet from the bank.
 * 
 * @param integer $id
 * @return mixed 
 */
public function get($id)
{

// Retrieving object
if (is_numeric($id) && !isset($this->list[$id]))
{
	$object_class = $this->name;
	$query = db()->query(" SELECT ".implode(" , ",array_keys($this->infos_defaultlist))." FROM `".$this->name."` WHERE `id` = '$id'" );
	if (!$query->num_rows())
	{
		return NULL;
	}
	elseif (!($infos = $query->fetch_assoc()) || !($object = new $object_class($id, $infos)) || !$object->perm("r"))
	{
		return NULL;
	}
	else
		return $this->list[$id] = $object;
}
// Object in list, we try it again if user permission has change (we use update datetime)
elseif ($this->list[$id]->perm("r"))
	return $this->list[$id];
else
{
	print "<p>ERROR : Cannot GET in databank : $this->name</p>";
	return false;
}
	
}

/**
 * Retrieve infos about an object
 * 
 * @param integer $id
 * @param array $infos
 * @return mixed 
 */
protected function query($id, $infos=array())
{

if (is_numeric($id) && ($query = db()->query("SELECT ".implode(" , ",array_keys($this->infos_defaultlist))." FROM `".$this->infos_table."` WHERE `id` = $id")) && ($query->num_rows()))
	return $query->fetch_assoc();
else
	return NULL;

}

/**
 * Delete an object from the databank
 * 
 * @param integer $id
 * @return mixed 
 */
public function remove($id)
{

// A CORRIGER CAR CELA CHARGE L'OBJET CA NE DOIT PAS SI PAS LES DROITS
// CELA PEUT GENERER D'AUTERS REQUETES LIEES DONANNT DES ACCES
// Retrieve object
if (!$this->get($id))
{
	return NULL;
}
// User delete permission
elseif (!$this->get($id)->perm("d"))
{
	unset($this->list[$id]);
	print "<p>ERROR : Cannot REMOVE in databank : $this->name</p>";
	return false;
}
// OK
else
{
	unset($this->list[$id]);
	$query = db()->query("DELETE FROM `".$this->infos_table."` WHERE `id` = $id");
	return $query->affected_rows();
	// autres requ�tes �ventuelles li�es...
}

}

/**
 * Add an object into the databank
 * 
 * @param array $infos
 * @return mixed 
 */
public function add($infos)
{

// User insert permission
if (!$this->perm("i"))
{
	print "<p>ERROR : Cannot ADD in databank : $this->name</p>";
	return false;
}
elseif (is_array($infos))
{
	$query_values = array();
	while (list($name,$value) = each($infos))
	{
		if (isset($this->infos_list[$name]))
			if ($this->$infos_list[$name]["type"] == "integer" && is_numeric($value))
				$query_values["`$name`"] = "$value";
			else
				$query_values["`$name`"] = "'$value'";
	}
	if (count($query_values) > 0)
	{
		db()->query("INSERT INTO ".$this->infos_table." ( ".implode(" , ",array_keys($query_values))." ) VALUES ( ".implode(" , ",$query_values)." )");
		return $this->get(db()->last_id());
	}
	else
		return NULL;
}
// autres requ�tes li�es..?

}

/**
 * Search objects into the databank
 * 
 * @param array $query
 * @return mixed
 */
public function search($query=array())
{

/*
 * Dans certains cas, par ex. clients et fournisseurs, il faut pouvoir aller chercher
 * dans la databank d'objets link�s, ici la databank entreprise.
 * 
 */

// User read permission
if (!$this->perm("r"))
{
	return false;
}
else
{
	$query_where_list = array();
	while (list($name,$value)=each($query))
		if (isset($this->infos_list[$name]))
			if ($this->infos_list[$name]["type"] == "integer")
			{
				$query_where_list[] = "`$name` = ".(int)$value;
			}
			elseif ($this->infos_list[$name]["type"] == "enum")
			{
				$query_where_list[] = "`$name` = '".addslashes($value)."'";
			}
			elseif ($this->infos_list[$name]["type"] == "array")
			{
				$query_where_list[] = "`$name` = '".addslashes($value)."'";
			}
	return db()->query("SELECT ".implode(" , ",array_keys($this->infos_defaultlist))." FROM $this->infos_table WHERE ".implode(" AND ",$query_where_list));
}

}

/**
 * User permissions test
 *
 * @return boolean
 */
public function perm($type)
{

if (is_numeric(strpos($this->perm,$type)))
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
 * Tests if a parameter or a field of parameters is set
 *
 * @param string $name
 * @return boolean
 */
public function infos_isset($name)
{

return isset($this->infos_list[$name]);

}

/**
 * Returns the details of a parameters if exists, otherwise false
 *
 * @param string $name
 * @return mixed
 */
public function infos($name)
{

if (isset($this->infos_list[$name]))
	return $this->infos_list[$name];
else
	return false;

}

/**
 * Returns the complete liste of parameters
 *
 * @return array
 */
public function infos_list()
{

return $this->infos_list;

}

/**
 * Returns a reference if exists, otherwise false
 *
 * @param string $name
 * @return mixed
 */
public function ref($name)
{

if (isset($this->ref_list[$name]))
	return $this->ref_list[$name];
else
	return false;

}

/**
 * Returns a the default list of parameters with default values
 *
 * @return array
 */
public function infos_defaultlist()
{

return $this->infos_defaultlist;

}

/**
 * Returns a the default table of parameters
 *
 * @return string
 */
public function infos_table()
{

return $this->infos_table;

}

public function select_form()
{

// User read permission test
if ($this->perm("r"))
{
?>
<form method="get" action="">
<select name="id" onchange="submit()">
<option value="">-- Choisir --</option>
<?
$query = db()->query(" SELECT id FROM $this->name ORDER BY id ");
while (list($id) = $query->fetch_row())
{
	$name = (string)$this->get($id);
	if (isset($_GET["id"]) && $_GET["id"] == $id)
		print "<option value=\"$id\" selected>$name</option>";
	else
		print "<option value=\"$id\">$name</option>";
}
?>
</select>
</form>
<?
}

}

}

/**
 * dataobject interface
 *
 */
interface dataobject_i
{

// No comment...
function __construct($id, $infos=array());

// Afficher une valeur
function __get($name);

// Modifier une valeur
function __set($name, $value);

// Afficher une (liste de) valeur(s)
function get($infos);

// Modifier une (liste de) valeur(s)
function update($infos);

// Simple Update form
function update_form();

}

/**
 * dataobject class
 *
 */
abstract class dataobject implements dataobject_i
{

/**
 * Gestion d'un objet de donn�es standard
 *
 * @var integer $id
 * @var array $infos
 * 
 */
protected $id;
protected $infos=array();

protected $perm="";

/**
 * Gestion d'un objet de donn�es standard
 *
 * @param integer $id
 * @param mixed[] $infos
 */
public function __construct($id, $infos=array())
{

$this->id = $id;

$databank = $this->databank;

// Test des champs requis et requ�te �ventuelle
$query_list = array();
foreach ($databank()->infos_defaultlist() as $name => $value)
	if (!isset($infos[$name]))
		$query_list[] = $name;
if (count($query_list) > 0)
	$this->query($query_list);

// Param�tres mis "� la main" : attention si diff�rences avec l'objet en base de donn�e...
foreach ($infos as $name => $value)
	if ($databank()->infos_isset($name))
		$this->infos[$name] = $value;

// Default user permissions
$this->perm = $databank()->perm_list();
$query = db()->query(" SELECT perm FROM _account_dataobject_perm WHERE account_id = ".login()->id()." AND databank_id = ".$databank()->id()." AND dataobject_id = $this->id ");
if ($query->num_rows())
{
	list($this->perm) = $query->fetch_row();
}

}

/**
 * User permission test
 *
 * @return boolean
 */
public function perm($type)
{

if (is_numeric(strpos($this->perm,$type)))
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
 * Retrieve informations about the object from database
 * 
 * @param array $infos
 */
protected function query($infos=array())
{

$databank = $this->databank;

if (count($infos))
{
	//print " SELECT ".implode(" , ",$infos)." FROM ".$databank()->infos_table()." WHERE id = ".$this->id;
	$query = db()->query(" SELECT ".implode(" , ",$infos)." FROM ".$databank()->infos_table()." WHERE id = ".$this->id );
	if ($query->num_rows())
	{
		foreach ($query->fetch_assoc() as $name => $value)
		{
			$this->infos[$name] = $value;
		}
		return true;
	}
	else
		return false;
}

}

/**
 * Get a property defined in $infos_list in the databank
 *
 * @param string $name
 * @return mixed
 */
public function __get($name)
{

$databank = $this->databank;

// User read permission test
if (!$this->perm("r"))
{
	return false;
}
// Field already set
elseif (($field = $databank()->infos($name)) && isset($this->infos[$name]))
{
	// Liste parmis un tableau de valeurs pr�charg�
	if ($field["type"] == "array" && isset($field["ref_table"]) && ($ref = $databank()->ref($name)))
		return $this->infos[$name];
	// Liste parmis un tableau d'objets
	if ($field["type"] == "array" && isset($field["ref_databank"]))
		return $this->infos[$name];
	// Li� � une Banque de donn�es => on renvoie l'objet
	elseif ($field["type"] == "integer" && isset($field["ref_databank"]))
		if ($this->infos[$name] && ($object = $field["ref_databank"]()->get($this->infos[$name])))
			return $object;
		else
			return "";
	// Parmis un tableau de valeurs pr�charg�
	elseif (isset($field["ref_table"]))
		if (isset($ref[$this->infos[$name]]))
			return $ref[$this->infos[$name]];
		else
			return "";
	// Enum parmis un tableau de valeurs pr�charg�
	elseif ($field["type"] == "enum")
		if (isset($ref[$this->infos[$name]]))
			return $ref[$this->infos[$name]];
		else
			return "";
	// Champ sans formatage particulier
	else
		return $this->infos[$name];
}
// Retrieve field
elseif ($field)
{
	if ($field["type"] == "array" && isset($field["ref_name"]))
	{
		$this->infos[$name] = array();
		$query = db()->query(" SELECT `$field[ref_id]` , `$field[ref_name]` FROM `$field[ref_table]` WHERE `$field[ref_id]` = $this->id ");
		while (list($i,$j)=$query->fetch_row())
			$this->infos[$name][$i] = $j;
		return $this->__get($name);
	}
	elseif ($field["type"] == "array" && isset($field["ref_databank"]) && isset($field["ref_table_array_index"]))
	{
		$this->infos[$name] = array();
		$query = db()->query(" SELECT `$field[ref_id]` , `$field[ref_table_array_index]` FROM `$field[ref_table]` WHERE `$field[ref_link_id]` = $this->id ORDER BY `$field[ref_table_array_index]` ");
		while (list($i,$j)=$query->fetch_row())
			$this->infos[$name][$j] = $field["ref_databank"]($i);
		return $this->__get($name);
	}
	elseif ($field["type"] == "array" && isset($field["ref_databank"]))
	{
		$this->infos[$name] = array();
		$query = db()->query(" SELECT `$field[ref_id]` FROM `$field[ref_table]` WHERE `$field[ref_link_id]` = $this->id ");
		while (list($i)=$query->fetch_row())
			$this->infos[$name][$i] = $field["ref_databank"]($i);
		return $this->__get($name);
	}
	elseif ($field["type"] == "object") // Renvoi l'objet associ� en databank
	{
		$this->infos[$name] = $field["object_datatype"]($this->__get($field["object_id"]));
		return $this->__get($name);
	}
	else
	{
		//print " SELECT `$name` FROM ".$databank()->infos("table")." WHERE `id` = $this->id ";
		list($this->infos[$name]) = db()->query(" SELECT `$name` FROM ".$databank()->infos_table()." WHERE `id` = $this->id ")->fetch_row();
		return $this->__get($name);
	}
}
// EXISTE PAS
else
{
	return NULL;
}

}

/**
 * Update a property defined in $infos_list in the databank
 *
 * @param string $name
 * @param mixed $value
 * @return mixed
 */
public function __set($name, $value)
{

// Verify permissions.
$databank = $this->databank;

// User update permission test
if (!$this->perm("u"))
{
	return false;
}
// Field exists
if ($field = $databank()->infos($name))
{
	if ($field["type"] == "integer" && is_numeric($value))
		db()->query(" UPDATE ".$field["table"]." SET `$name`= $value WHERE `id` = ".$this->id );
	elseif ($field["type"] == "array" && isset($field["ref_databank"]))
	{
		// Suppression des anciennes r�f�rences
		db()->query(" DELETE FROM ".$field["ref_table"]." WHERE `".$field["ref_link_id"]."` = $this->id");
		// Ajout des nouvelles r�f�rences
		foreach($value as $index=>$val)
		{
			$query_row_list = "( $this->id , $index , $val )";
		}
		db()->query(" INSERT INTO ".$field["ref_table"]." ( `".$field["ref_link_id"]."` , `".$field["ref_id"]."` , `".$field["ref_table_array_index"]."` ) VALUES ".implode(" , ",$query_row_list));
	}
	elseif ($field["type"] != "array" && $field["type"] != "object")
		db()->query(" UPDATE ".$field["table"]." SET `$name`='".addslashes($value)."' WHERE `id` = $this->id ");
	return $this->infos[$name] = $value;
}
// Field doesn't exists
else
{
	return NULL;
}

}

// A voir si cette fonction a encore de l'int�r�t
public function get($infos)
{

$databank = $this->databank;

// User read permission test
if (!$this->perm("r"))
{
	return false;
}
elseif (is_array($infos))
{
	$return = array();
	while (list(,$name)=each($infos))
		if ($databank()->infos_isset($name))
			$return[$name] = $this->__get($name);
	if (count($return)>0)
		return $return;
	else
		return NULL;
}
else
	return $this->__get($infos);

}

/**
 * Update the object in database
 *
 * @param array $infos
 * @return mixed
 */
public function update($infos)
{

$databank = $this->databank;

// User update permission test
if (!$this->perm("u"))
{
	print "<p>ERROR : Databank $databank : Permission Update</p>";
	return false;
}
elseif (is_array($infos))
{
	$updated = 0;
	$query_update = array();
	foreach ($infos as $name=>$value)
	{
		if ($field = $databank()->infos($name))
		{
			if ($field["type"] == "enum" && isset($field["multiple"]) && is_array($value))
			{
				$query_update[] = "`$name` = '".implode(",",$value)."'";
				$this->infos[$name] = implode(",",$value);
			}
			elseif ($field["type"] == "integer")
			{
				$query_update[] = "`$name` = '$value'";
				$this->infos[$name] = $value;
			}
			elseif ($field["type"] == "array" && isset($field["ref_databank"]) && is_array($value))
			{
				db()->query("DELETE FROM `$field[ref_table]` WHERE `$field[ref_link_id]` = $this->id");
				$query_update_array=array();
				while (list($index, $id)=each($value))
					$query_update_array[] = "( $this->id , $id , $index )";
				if (count($query_update_array)>0)
					if(db()->query(" INSERT INTO `$field[ref_table]` ( `$field[ref_link_id]` , `$field[ref_id]` , `$field[ref_table_array_index]` ) VALUES ".implode(" , ",$query_update_array))->affected_rows())
						$updated = 1;
			}
			elseif ($field["type"] != "array" && $field["type"] != "object")
			{
				$query_update[] = "`$name` = '$value'";
				$this->infos[$name] = stripslashes($value);
			}
		}
	}
	if (count($query_update) > 0)
	{
		$query_string = " UPDATE `".$databank()->infos_table()."` SET ".implode(" , ",$query_update)." WHERE `id` = ".$this->id ;
		//print $query_string;
		if (db()->query($query_string)->affected_rows() == 1 && !$updated)
			$updated = 1;
		return $updated;
	}
	else
	{
		return false;
	}
}
else
{
	return false;
}

}

/**
 * Insert a new referenced object, if rights
 *
 * @param string $name
 * @param array $infos
 * 
 * @return boolean
 */
public function ref_insert($name, $infos)
{

// 
if ($field["type"] == "integer" && isset($field["ref_databank"]))
{
	if ($field["ref_databank"]->perm("i"))
	{
		$object = $field["ref_databank"]->add($infos);
		$this->infos[$name] = $object->id();
		return true;
	}
	else
		return false;
}
else
	return null;

}

/**
 * Render a complex field
 *
 * @param string $name
 * @param array $field
 */
public function field_render($name, $field=array())
{

$databank = $this->databank;
$field = $databank()->infos($name);

}

/**
 * Returns the update form via the form class
 *
 */
public function update_form_bis()
{

// User update permission test
if (!$this->perm("u"))
{
	return false;
	break;
}

$databank = $this->databank;

$form = new form("update_form" , array("method"=>"POST","action"=>""));
foreach ($databank()->infos_list() as $name => $field) if ($field["type"] != "object")
{
	/*
	 * Liste d'objets reli�e par une table tiers
	 */
	if ($field["type"] == "array" && isset($field["ref_databank"]))
	{
	}
	/*
	 * Champ mapp� sur une banque de donn�es
	 */
	if ($field["type"] == "integer" && isset($field["render"]) && $field["render"] == "databank")
	{
		$form->field_add($name,"text",array("label"=>$field["label"]));
	}
	/*
	 * Champ mapp� sur une liste "raisonnable" pr�charg�e en databank � sa construction.
	 * Pour de longues listes (disons � partir d'une centaine d'�l�ments)
	 * on pr�f�rera utiliser des objets et un moteur de recherche, comme au dessous.
	 */
	elseif ($field["type"] == "integer" && isset($field["ref_table"]) && !isset($field["ref_datatype"]))
	{
		$form->field_add($name,"text",array("label"=>$field["label"]));
	}
	/*
	 * Champ mapp� sur une famille d'objets
	 */
	elseif ($field["type"] == "integer" && isset($field["ref_databank"]))
	{
		$form->field_add($name,"text",array("label"=>$field["label"]));
	}
}

return $form;

}

/**
 * Display the update form
 *
 */
public function update_form()
{

$databank = $this->databank;

// User update permission test
if (!$this->perm("r"))
{
	return false;
}
else
{

print "<form method=\"POST\" style=\"margin:0px;padding:0px;\"><table border=\"1\" cellspacing=\"2\" cellpadding=\"2\" style=\"border:1px red solid;\">\n";

foreach ($databank()->infos_list() as $name => $field) if ($field["type"] != "object")
{
	print "<tr>\n";
	print "<td>".$field["label"]."&nbsp;:</td>\n";
	/*
	 * Champ sans mappage, �ventuellement avec quelque mise en forme l�g�re.
	 * On utilisera enum et set uniquement lorsqu'on aura besoin d'une liste
	 * d�finitivement statique de valeurs.
	 * 
	 * integer et float => input
	 * string => input ou bien textarea suivant la longueur (>127)
	 * enum => select ou select multiple
	 */
	/*
	 * Liste d'objets reli�e par une table tiers
	 */
	if ($field["type"] == "array" && isset($field["ref_databank"]))
	{
		$this->__get($name);
		print "<td><div id=\"result_$name\" style=\"float: left;margin-right: 5px;\">";
		$nb=0;
		foreach ($this->infos[$name] as $i => $j)
		{
			$nb++;
			if ($nb > 1)
				print "<br/><input type=\"hidden\" name=\"update[$name][]\" value=\"".$i."\"/><input readonly=\"true\" value=\"".$j."\"/> <a style=\"color:red;text-decoration:none;\" href=\"javascript:field_value_delete('$name','$i');\">X</a>";
			else
				print "<input type=\"hidden\" name=\"update[$name][]\" value=\"".$i."\"/><input readonly=\"true\" value=\"".$j."\"/> <a style=\"color:red;text-decoration:none;\" href=\"javascript:field_value_delete('$name','$i');\">X</a>";
		}
		if ($nb>0)
		{
			print "<br/><a href=\"javascript:field_value_add('$name');\">Ajouter</a>";
		}
		print "</div></td>\n";
	}
	/*
	 * Champ mapp� sur une banque de donn�es
	 */
	if ($field["type"] == "integer" && isset($field["render"]) && $field["render"] == "databank")
	{
		$this->__get($name);
		print "<td>";
		$query = db()->query(" SELECT id , name FROM _databank WHERE name IN ('".implode("','",$field["render_values"])."') ");
		while (list($i,$j)=$query->fetch_row())
		{
			if ($i == $this->infos[$name])
				print "<input type=\"radio\" name=\"update[$name]\" value=\"".$i."\" checked=\"checked\"/> $j";
			else
				print "<input type=\"radio\" name=\"update[$name]\" value=\"".$i."\"/> $j";
		}
		print "</td>\n";
	}
	/*
	 * Champ mapp� sur une liste "raisonnable" pr�charg�e en databank � sa construction.
	 * Pour de longues listes (disons � partir d'une centaine d'�l�ments)
	 * on pr�f�rera utiliser des objets et un moteur de recherche, comme au dessous.
	 */
	elseif ($field["type"] == "integer" && isset($field["ref_table"]) && !isset($field["ref_datatype"]))
	{
		$this->__get($name);
		print "<td><select name=\"update[$name]\">";
		print "<option value=\"0\">-- choisir --</option>";
		foreach ($databank()->ref($name) as $i => $j)
		{
			if ($i == $this->infos[$name])
				print "<option value=\"$i\" selected=\"selected\">$j</option>";
			else
				print "<option value=\"$i\">$j</option>";
		}
		print "</select></td>\n";
	}
	/*
	 * Champ mapp� sur une famille d'objets
	 */
	elseif ($field["type"] == "integer" && isset($field["ref_databank"]))
	{
		$this->__get($name);
		print "<td>";
		print "<input type=\"hidden\" name=\"update[$name]\" value=\"".$this->infos[$name]."\"/>";
		print "<script type=\"text/javascript\"> field_value_$name = '".$this->infos[$name]."'; field_aff_$name = '".$this->__get($name)."'; </script>";
		print "<table width=\"100%\" cellspacing=\"0\"><tr>";
		print "<td><div id=\"result_$name\" style=\"float: left;margin-right: 5px;\"><input readonly=\"true\" value=\"".$this->__get($name)."\"/></div></td>";
		print "<td align=\"right\">Query : <input type=\"text\" value=\"\" id=\"lookup_$name\" /> <input type=\"button\" value=\"OK\" onclick=\"lookup('$field[ref_databank]', document.getElementById('lookup_$name').value, '$name', document.getElementById('result_$name'))\" /></td>";
		print "</tr></table></td>";
	}
	/*
	 * Champ batard qui sera remplace par integer + databank
	 */
	elseif ($field["type"] != "array" && isset($field["ref_table"]))
	{
		$this->__get($name);
		print "<td><input type=\"hidden\" name=\"update[$name]\" value=\"".$this->infos[$name]."\"/><div id=\"result_$name\" style=\"float: left;margin-right: 5px;\"><input readonly=\"true\" value=\"".$this->__get($name)."\"/></div> Query : <input type=\"text\" value=\"\" onchange=\"lookup('$field[ref_databank]', this.value, '$name', document.getElementById('result_$name'))\" /></td>";
	}
	/*
	 * Champs sans r�f�rence externe
	 */
	else
	{
		$this->__get($name);
		if ($field["type"] == "enum")
		{
			// Type MySQL set
			if (isset($field["multiple"]))
			{
				$values = explode(",",$this->infos[$name]);
				print "<td><select name=\"update[$name][]\" multiple>";
				foreach ($field["values"] as $i => $j)
				{
					if (in_array($i,$values))
						print "<option value=\"$i\" selected=\"selected\">$j</option>";
					else
						print "<option value=\"$i\">$j</option>";
				}
				print "</select></td>\n";
			}
			// Type MySQL enum
			else
			{
				print "<td><select name=\"update[$name]\">";
				print "<option value=\"\">-- choisir --</option>";
				foreach ($field["values"] as $i => $j)
				{
					if ($i == $this->infos[$name])
						print "<option value=\"$i\" selected=\"selected\">$j</option>";
					else
						print "<option value=\"$i\">$j</option>";
				}
				print "</select></td>\n";
			}
		}
		// Type MySQL date
		elseif ($field["type"] == "date")
			print "<td><input type=\"text\" name=\"update[$name]\" value=\"".$this->infos[$name]."\" size=\"10\" maxlength=\"10\" /></td>\n";
		// Type MySQL varchar / text / char / tinytext ...
		elseif ($field["type"] == "string" && $field["size"] > 127)
			print "<td><textarea name=\"update[$name]\" maxlength=\"".$field["size"]."\" cols=\"60\" rows=\"4\">".$this->infos[$name]."</textarea></td>\n";
		elseif (isset($field["size"]))
			if ($field["size"] >= 32)
				print "<td><input type=\"text\" name=\"update[$name]\" value=\"".$this->infos[$name]."\" size=\"32\" maxlength=\"".$field["size"]."\" /></td>\n";
			else
				print "<td><input type=\"text\" name=\"update[$name]\" value=\"".$this->infos[$name]."\" size=\"".$field["size"]."\" maxlength=\"".$field["size"]."\" /></td>\n";
		else
			print "<td><input type=\"text\" name=\"update[$name]\" value=\"".$this->infos[$name]."\" /></td>\n";
	}
print "</tr>\n";
}

print "<tr>";
print "<td>&nbsp;</td>";
print "<td><input type=\"submit\" value=\"Mettre � jour\" /></td>";
print "</tr>\n";

print "</table></form>\n";

}

}

/**
 * Default value
 * Cette m�thode DOIT �tre surcharg�e pour afficher une information convenable,
 * mais elle est d�finie d�s � pr�sent car elle est utilis�e dans cette classe
 * 
 * @method public
 * @return integer
 */
public function __tostring()
{

return (string)$this->id;

}

/**
 * id link
 *
 * @return string
 */
public function link()
{

return "id=$this->id";

}

/**
 * id
 *
 * @return integer
 */
public function id()
{

return $this->id;

}

}

/**
 * Extension of databank for many many datatypes
 * When a data needs a name and a description,
 * then it's a "simple dataobject"...
 * The __tosing() value returns the name.
 * 
 */
abstract class databank_simple extends databank
{

protected $infos_list = array
(
	"name" => array ( "label" => "Nom" , "type" => "string" , "size" => "64" ),
	"description" => array ( "label" => "Description" , "type" => "string" , "size" => "256" ),
);
protected $infos_defaultlist = array
(
	"name" => "",
);

}

/**
 * Extension of dataobject for many many datatypes
 *
 */
abstract class dataobject_simple extends dataobject
{

function __tostring()
{

return $this->infos["name"];

}

}


?>