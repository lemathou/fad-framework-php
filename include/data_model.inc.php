<?

/**
  * $Id: data_model.inc.php 40 2008-10-01 07:37:20Z mathieu $
  * 
  * Copyright 2008 Mathieu Moulin - lemathou@free.fr
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
class datamodel_gestion extends gestion
{

protected $type = "datamodel";

protected $info_list = array("name", "library_id", "table", "perm");

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
 * Delete a datamodel
 * @param $id
 */
protected function del_more($id)
{

// Fields
db()->query("DELETE FROM `_datamodel_fields` WHERE `datamodel_id`='$id'");
db()->query("DELETE FROM `_datamodel_fields_lang` WHERE `datamodel_id`='$id'");
db()->query("DELETE FROM `_datamodel_fields_opt` WHERE `datamodel_id`='$id'");
db()->query("DELETE FROM `_datamodel_fields_opt_lang` WHERE `datamodel_id`='$id'");

}

/**

 * Create databank access functions
 */
protected function access_function_create()
{

foreach($this->list_name as $name=>$id)
{
	eval("function $name(\$id=null, \$fields=array()) { return datamodel(\"$id\", \$id, \$fields); }");
	//echo "<p>function $name(\$id=null, \$fields=array()) { return datamodel(\"$id\", \$id, \$fields); }</p>\n";
}

}

}

/**
 * Modèle de données pour remplir une maquette
 *
 */
class datamodel_base extends object_gestion
{

protected $_type = "datamodel";

protected $table = "";

/**
 * Library containing required objects infos
 *
 * @var integer
 */
protected $library_id = 0;

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

protected $fields_key = array();
protected $fields_required = array();
protected $fields_calculated = array();
protected $fields_index = array();

protected $db_opt = array(); // TODO : usefull only in case of databank !
protected $disp_opt = array();

protected $action_list = array();

/**
 * Objects
 */
protected $objects = array();

// Données à sauver en session
private static $serialize_list = array("id", "name", "label", "description", "perm", "library_id", "table", "fields", "fields_key", "fields_required", "fields_calculated", "fields_index", "db_opt", "disp_opt", "action_list");

function __sleep()
{

//return session_select::__sleep(self::$serialize_list);
return array("id", "name", "label", "description", "perm", "library_id", "table", "fields", "fields_key", "fields_required", "fields_calculated", "fields_index", "db_opt", "disp_opt", "action_list");

}
function __wakeup()
{

//session_select::__wakeup();
//$this->objects=array();
$this->library_load();

if (DEBUG_SESSION == true)
	echo "<p>WAKEUP : data_bank id#$this->id</p>\n";

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
$this->fields_key = array();
$this->fields_required = array();
$this->fields_calculated = array();
// Création des champs du datamodel
$query = db()->query("SELECT t1.pos, t1.`name` , t1.`type` , t1.`defaultvalue` , t1.`opt` , t1.`lang` , t1.`query` , t2.`label` FROM `_datamodel_fields` as t1 LEFT JOIN `_datamodel_fields_lang` as t2 ON t1.`datamodel_id`=t2.`datamodel_id` AND t1.`name`=t2.`fieldname` WHERE t1.`datamodel_id`='$this->id' ORDER BY t1.`pos`");
while ($field=$query->fetch_assoc())
{
	$datatype = "data_$field[type]";
	$this->fields[$field["name"]] = new $datatype($field["name"], $field["defaultvalue"], $field["label"]);
	$this->fields[$field["name"]]->datamodel_set($this->id);
	if ($field["opt"] == "key")
		$this->fields_key[] = $field["name"];
	elseif ($field["opt"] == "required")
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

if (DEBUG_LIBRARY == true)
	echo "<p>Loading library ID#$this->library_id from datamodel id#$this->id query_info</p>\n";
$this->library()->load();

}

public function __tostring()
{

return $this->label;

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
	library($this->library_id)->load();

}

/**
 * Returns the complete data field list
 */
public function fields()
{

return $this->fields;

}

/**
 * Returns a data field
 * @param unknown_type $name
 */
public function __get($name)
{

if (isset($this->fields[$name]))
	return $this->fields[$name];
elseif (DEBUG_DATAMODEL)
	trigger_error("datamodel($this->id)::__get($name) : field doesn't exists");

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
 * Delete a data field
 * @param unknown_type $name
 */
public function __unset($name)
{

$this->field_delete($name);

}

/*
 * Add a data field
 */
public function __set($name, data $field)
{

$this->field_add($field);

}

public function field_add($field, $options="")
{

if (!login()->perm(6))
	die("ONLY ADMIN CAN UPDATE A DATAMODEL");
	// TODO : security : send an email with complete info about the one who tried to do this and blacklist him if possible ?

if (!is_array($field))
	die("datamodel(ID#$this->id)::field_add() : Invalid parameters");

if (!isset($field["type"]) || !($field["type"]) || !isset(data()->{$field["type"]}))
	die("datamodel(ID#$this->id)::field_add() : Invalid parameters : type");

if (!isset($field["name"]) || !preg_match("/([a-z])+/", $field["name"]))
	die("datamodel(ID#$this->id)::field_add() : Invalid parameters : name");

if (!isset($field["opt"]) || !isset($field["query"]) || !isset($field["lang"]) || !isset($field["label"]))
	die("datamodel(ID#$this->id)::field_add() : Invalid parameters : opt, query, lang, label");

if (!isset($field["pos"]) || !is_numeric($field["pos"]) || $field["pos"] < 0)
	die("datamodel(ID#$this->id)::field_add() : Invalid parameters : pos");

if (isset($field["defaultvalue_null"]))
	$defaultvalue = "NULL";
else
	$defaultvalue = "'".db()->string_escape($field["defaultvalue"])."'";
$pos_max = count($this->fields) + 1;

db()->query("INSERT INTO _datamodel_fields (datamodel_id, pos, name, type, defaultvalue, opt, `query`, lang) VALUES ('$this->id', '$pos_max', '".$field["name"]."', '".$field["type"]."', $defaultvalue, '".$field["opt"]."', '".$field["query"]."', '".$field["lang"]."')");
db()->query("INSERT INTO _datamodel_fields_lang (datamodel_id, lang_id, fieldname, label) VALUES ('$this->id', '".SITE_LANG_ID."', '".$field["name"]."', '".db()->string_escape($_POST["label"])."')");

// Gestion du repositionnement
if ($field["pos"] < $pos_max)
{
	db()->query("UPDATE `_datamodel_fields` SET `pos` = pos+1 WHERE `datamodel_id` = '$this->id' AND `pos` >= '".(int)$field["pos"]."'");
	db()->query("UPDATE `_datamodel_fields` SET `pos` = '".(int)$field["pos"]."' WHERE `datamodel_id` = '$this->id' AND `name` = '".$field["name"]."'");
}

$this->query_fields();

if (APC_CACHE)
{
	apc_store("datamodel_$this->id", $this, APC_CACHE_GESTION_TTL);
}

// Insertion du champ dans la tables associée
list($db_sync) = db()->query("SELECT db_sync FROM _datamodel WHERE id=$this->id")->fetch_row();
if ($db_sync)
{
	if (isset($_POST["lang"]) && $_POST["lang"])
		db()->field_create($this->table."_lang", $field["name"], $this->fields[$field["name"]]->db_field_create());
	else
		db()->field_create($this->table, $field["name"], $this->fields[$field["name"]]->db_field_create());
}

}

/**
 * Delete a field
 * @param string $name
 */
public function field_delete($name)
{

if (!login()->perm(6))
	die("ONLY ADMIN CAN UPDATE A DATAMODEL");

if (!isset($this->fields[$name]))
	die("datamodel(ID#$this->id)::field_delete() : Field $name does not exists");

db()->query("DELETE FROM `_datamodel_fields` WHERE `name`='$name' AND `datamodel_id`='$this->id'");
db()->query("DELETE FROM `_datamodel_fields_lang` WHERE `fieldname`='$name' AND `datamodel_id`='$this->id'");
db()->query("DELETE FROM `_datamodel_fields_opt` WHERE `fieldname`='$name' AND `datamodel_id`='$this->id'");
db()->field_delete($this->table, $name);

$this->query_fields();

if (APC_CACHE)
{
	apc_store("datamodel_$this->id", $this, APC_CACHE_GESTION_TTL);
}

}

/**
 * Update a field
 * @param string $name
 * @param array $field
 */
function field_update($name, $field)
{

if (!login()->perm(6))
	die("ONLY ADMIN CAN UPDATE DATAMODEL");

if (!isset($this->fields[$name]))
	die("datamodel(ID#$this->id)::field_update() : Field $name does not exists");

if (!is_array($field))
	die("datamodel(ID#$this->id)::field_update() : Invalid parmeters updating field $name");

if (!isset($field["type"]) || !($field["type"]) || !isset(data()->{$field["type"]}))
	die("datamodel(ID#$this->id)::field_update() : Invalid parmeters updating field $name");

if (!isset($field["name"]) || !preg_match("/([a-z])+/", $field["name"]))
	die("datamodel(ID#$this->id)::field_update() : Invalid parmeters updating field $name");

if (!isset($field["opt"]) || !isset($field["lang"]) || !isset($field["label"]))
	die("datamodel(ID#$this->id)::field_update() : Invalid parmeters updating field $name");

// Query infos
$query_str = "SELECT `db_sync` FROM `_datamodel` WHERE `id`='$this->id'";
list($db_sync) = db()->query($query_str)->fetch_row();
$query_str = "SELECT `pos` FROM `_datamodel_fields` WHERE `datamodel_id`='$this->id' AND name='$name'";
list($pos) = db()->query($query_str)->fetch_row();

// Création objet (par défaut)
$fieldtype = "data_$field[type]";
$datafield = new $fieldtype("field", null, null);

// Default value
if (isset($field["defaultvalue_null"]))
	$defaultvalue = "NULL";
elseif (isset($field["defaultvalue"]))
	$defaultvalue = "'".db()->string_escape($field["defaultvalue"])."'";
else
	$defaultvalue = "''";

// Update old values
$query_str = "UPDATE `_datamodel_fields` SET `name`='".$field["name"]."', `type`='".$field["type"]."', `defaultvalue`=$defaultvalue, `opt`='".db()->string_escape($field["opt"])."', `query`='".db()->string_escape($field["query"])."', `lang`='".db()->string_escape($field["lang"])."' WHERE `datamodel_id`='$this->id' AND name='$name'";
db()->query($query_str);
$query_str = "UPDATE `_datamodel_fields_lang` SET `fieldname`='".$field["name"]."', `label`='".db()->string_escape($field["label"])."' WHERE `datamodel_id`='$this->id' AND `fieldname`='$name'";
db()->query($query_str);

// Delete old option values
$query_str = "DELETE FROM `_datamodel_fields_opt` WHERE `fieldname`='$name' AND `datamodel_id`='$this->id'";
db()->query($query_str);
// Options réellement à sauver (<> opt par défaut)
if (isset($field["optlist"]))
{
	$query_opt_list = array();
	foreach($field["optlist"] as $type=>$list)
	{
		$field_type = $datafield->{$type."_opt_list_get"}();
		foreach ($list as $i=>$j)
		{
			if (!isset($field_type[$i]) || $field_type[$i] !== json_decode($j, true))
			{
				$datafield->{$type."_opt_set"}($i, json_decode($j, true));
				//$finalvalue = json_encode($datafield->{$type."_opt"}[$i]);
				$query_opt_list[] = "('$this->id' , '".$field["name"]."' , '$type' , '$i' , '".db()->string_escape($j)."' )";
			}
		}
	}
	if (count($query_opt_list))
	{
		$query_str = "INSERT INTO `_datamodel_fields_opt` (`datamodel_id`, `fieldname`, `opt_type`, `opt_name`, `opt_value`) VALUES ".implode(", ", $query_opt_list);
		db()->query($query_str);
	}
}

// Gestion du repositionnement
if ($field["pos"] < $pos)
{
	db()->query("UPDATE _datamodel_fields SET pos=pos+1 WHERE datamodel_id='$this->id' AND pos >= ".($field["pos"])." AND pos < $pos");
	db()->query("UPDATE _datamodel_fields SET pos=".($field["pos"])." WHERE datamodel_id='$this->id' AND name='".$field["name"]."'");
}
elseif ($pos < $field["pos"])
{
	db()->query("UPDATE _datamodel_fields SET pos=pos-1 WHERE datamodel_id='$this->id' AND pos > $pos AND pos <= ".($field["pos"]));
	db()->query("UPDATE _datamodel_fields SET pos=".($field["pos"])." WHERE datamodel_id='$this->id' AND name='".$field["name"]."'");
}

$this->query_fields();

if (APC_CACHE)
{
	apc_store("datamodel_$this->id", $this, APC_CACHE_GESTION_TTL);
}

// Mise à jour du champ dans la table associée
if ($db_sync)
{
	$this->query_info();
	if (isset($field["lang"]) && $field["lang"])
		db()->field_update($this->table."_lang", $name, $field["name"], $datafield->db_field_create());
	else
		db()->field_update($this->table, $name, $field["name"], $datafield->db_field_create());
}

}

public function fields_required()
{

return $this->fields_required;

}
public function fields_key()
{

return $this->fields_key;

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

function table_list($params=array(), $fields=array(), $sort=array())
{

?>
<script type="text/javascript">
function databank_list_sort(form, field)
{
	document.zeform['sort[0]'].value = field;
	databank_form_submit();
}
function databank_params_aff()
{
	element = document.getElementById('databank_params');
	if (element.style.display == 'none')
		element.style.display = 'block';
	else
		element.style.display = 'none';
}
function databank_form_submit(form)
{
	$("[name^='params']").each(function(){
		if (!$(this).val())
			$(this).attr("name", "");
	});
	document.zeform.submit();
}
</script>
<form name="zeform" action="" method="post" onsubmit="databank_form_submit(this)">
<input type="hidden" name="sort[0]" value="id" />
<input type="hidden" name="sort[1]" value="ASC" />
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
	<td><select name="fields[]" multiple><?php
	foreach ($this->fields as $name=>$field)
		if (in_array($name, $this->fields_key))
		{
			echo "<option value=\"$name\" readonly selected onclick=\"this.selected=true\" style=\"background-color:red;\">".$field->label."</option>";
		}
		elseif (in_array($name, $fields))
		{
			echo "<option value=\"$name\" selected>".$field->label."</option>";
		}
		else
		{
			echo "<option value=\"$name\">".$field->label."</option>";
		}
	?></select></td>
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
$nbmax = $this->db_count($params);
$limit = 50;

$start = 0;
$list = $this->db_get($params, $fields, $sort, $limit, $start);
foreach($list as $object)
{
	// First line
	if (!$nb)
	{
		echo "<tr>\n";
		foreach($object->field_list() as $field) if (in_array($field->name, $this->fields_key) || in_array($field->name, $fields))
		{
			if ($field->name == "id")
			{
				echo "<td><b><a href=\"javascript:;\" onclick=\"databank_list_sort('zeform','$field->name')\">".$field->label."</a></b></td>";
				echo "<td>Default display</td>";
			}
			else
				echo "<td><b><a href=\"javascript:;\" onclick=\"databank_list_sort('zeform','$field->name')\">".$field->label."</a></b></td>";
		}
		echo "</tr>\n";
	}
	echo "<tr>\n";
	foreach($object->field_list() as $field) if (in_array($field->name, $this->fields_key) || in_array($field->name, $fields))
	{
		if ($field->name == "id")
		{
			echo "<td width=\"20\"><a href=\"?datamodel_id=$this->id&object_id=$object->id\">$field</a></td>";
			echo "<td>$object</td>";
		}
		elseif ($field->type == "dataobject" && $field->value)
			echo "<td><a href=\"?datamodel_id=".$field->structure_opt("databank")."&object_id=$field->value\">$field</a></td>";
		else
			echo "<td>$field</td>";
	}
	echo "<td><a href=\"?datamodel_id=$this->id&object_id=$object->id\"><img src=\"".SITE_BASEPATH."/img/icon/icon-view.gif\" alt=\"View\" /></a></td>";
	echo "<td><a href=\"?datamodel_id=$this->id&object_id=$object->id\"><img src=\"".SITE_BASEPATH."/img/icon/icon-edit.gif\" alt=\"Update\" /></a></td>";
	echo "<td><a href=\"javascript:;\" onclick=\"if (window.confirm('Etes-vous certain de supprimer ?')) location.href='?datamodel_id=$this->id&object_del=$object->id'\"><img src=\"".SITE_BASEPATH."/img/icon/icon-delete.gif\" alt=\"Delete\" /></a></td>";
	echo "</tr>\n";
	$nb++;
}
?>
</table></div>
</form>
<?php

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

}

/**
 * 
 * DATAMODEL !!!
 * 
 * @author mathieu
 *
 */
class datamodel extends datamodel_base
{

protected $db_opt = array
(
	"table" => "",
	"index" => array(),
	"key" => array(),
	"sort" => "",
);

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

/**
 * Create the associated database
 *
 * @return unknown
 */
public function db_create()
{

$options = array();
$fields = array();
$fields_lang = array();
$fields_ref = array();
foreach ($this->fields as $name=>$field)
{
	if ($field->type != "dataobject_list")
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
		elseif (!isset($f["null"]) || $f["null"])
		{
			$f["null"] = true;
		}
		if ($field->db_opt("lang"))
			$fields_lang[$name] = $f;
		else
			$fields[$name] = $f;
	}
	else
	{
				$fields_ref[] = $field->db_create();
	}
}

db()->table_create($this->table, $fields, $options);

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
	db()->table_create($this->table."_lang", $fields_lang, $options);
}

if (count($fields_ref))
{
	foreach($fields_ref as $table)
	{
		db()->table_create($table["name"], $table["fields"], $table["options"]);
	}
}

if (count($this->fields_index))
	db()->query("ALTER TABLE `".$this->table."` ADD FULLTEXT `FULLTEXT` (`".implode("`, `",$this->fields_index)."`)");

}

/**
 * A fignoler, il faudra par ailleurs ajouter la gestion du positionnement au datamodel...
 * 
 * @param unknown_type $fieldname
 * @param unknown_type $position
 */
public function db_field_move($fieldname, $position)
{

db()->query("ALTER TABLE `".$this->table."` MODIFY COLUMN ".db()->db_field_struct($fieldname, $this->fields[$fieldname]->db_field_create())." AFTER `$position`");

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
 * @param array $fields
 * @return mixed integer boolean
 */
public function insert($fields)
{

if ($id=$this->db_insert($fields))
	return $this->get($id);
else
	return false;

}
public function db_insert($fields=array())
{

// Verify required fields
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

// Verify fields and supress keys
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
	// TODO : Keys : a modifier car un index n'est pas forc�ment auto_increment et il peut y en avoir +ieurs...
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

$query_str = "INSERT INTO ".$this->table." (".implode(",",$query_fields).") VALUES (".implode(",",$query_values).")";
$query = db()->query($query_str);
// db()->insert("$this->name","");
$id = $query->last_id();
//db()->query("INSERT INTO _databank_update ( databank_id , dataobject_id , account_id , action , datetime ) VALUES ( ".databank($this->name)->id()." , ".$id." , ".login()->id()." , 'i' , NOW() )");

if (count($query_list)>0)
{
	foreach($query_list as $name=>$detail)
	{
		//echo "<p>$name : ".implode(", ",$fields[$name]->value)."</p>";
		if ($this->fields[$name]->type == "dataobject_list" && is_array($fields[$name]->value) && count($fields[$name]->value))
		{
			$details["query_values"] = array();
			foreach ($fields[$name]->value as $object_id)
			{
				$details["query_values"][] = "('$object_id', '$id')";
			}
			if (count($details["query_values"])>0)
			{
				$query_str = "INSERT INTO `".$this->fields[$name]->db_opt["ref_table"]."` (`".$this->fields[$name]->db_opt["ref_field"]."`, `".$this->fields[$name]->db_opt["ref_id"]."`) VALUES ".implode(", ",$details["query_values"]);
				$query = db()->query($query_str);
			}
		}
	}
}

return $id;

}

public function insert_from_form($fields)
{

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

/**
 * Remove an object
 *
 * @param unknown_type $params
 */
public function delete($params)
{

if (is_array($list=$this->db_delete($params)))
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
public function db_delete($params)
{

if (($list=$this->db_select($params)) && is_array($list))
{
	// TODO : Delete other entries if needed, in cases of data_databank_list field !
	// TODO : create a function db_select_id() for simple queries !
	$return = array();
	foreach ($list as $id=>$fields)
	{
		db()->query("DELETE FROM `".$this->table."` WHERE `id` = '".db()->string_escape($id)."'");
		$return[] = $id;
	}
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
			unset($fields[$name]);
		}
		// Bad field
		elseif (!is_a($field, "data") || ($class=get_class($this->fields[$name])) != get_class($field))
		{
			if (DEBUG_DATAMODEL)
				trigger_error("Datamodel '$this->name' : Update error : Field '$name' not an instance of '$class'");
			unset($fields[$name]);
		}
		// Extra table update : dataobject_list
		elseif ($this->fields[$name]->type == "dataobject_list")
		{
			if ($this->fields[$name]->db_opt["ref_table"])
			{
				$update_query[$name] = array();
				foreach($field->value as $id)
				{
					$update_query[$name][] = "('".$insert_params["id"]."','$id')";
				}
			}
		}
		// Primary table update
		elseif ($this->fields[$name]->type == "dataobject_select")
		{
			//print $field->value;
			if ($field->nonempty())
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
		$query_string = "UPDATE ".$this->table." SET ".implode(" , ",$update_fields)." WHERE ".implode(" , ",$params);
		$query = db()->query($query_string);
		if ($query->affected_rows())
		{
			$return = true;
		}
		//echo mysql_error();
	}
	if (count($update_fields_lang)>0)
	{
		$query_string = "UPDATE ".$this->table."_lang SET ".implode(" , ",$update_fields_lang)." WHERE ".implode(" , ",$params)." AND lang_id='".SITE_LANG_ID."'";
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
			if ($this->fields[$name]->type == "dataobject_list" && ($ref_field=$this->fields[$name]->db_opt("ref_field")) && ($ref_table=$this->fields[$name]->db_opt("ref_table")) && ($ref_id=$this->fields[$name]->db_opt("ref_id")))
			{
				$query_string = "DELETE FROM `$ref_table` WHERE `$ref_id` = '".$insert_params["id"]."'";
				db()->query($query_string);
				if (count($insert_list))
				{
					$query_string = "INSERT INTO `$ref_table` (`$ref_id`, `$ref_field`) VALUES ".implode(", ",$insert_list);
					db()->query($query_string);
				}
				$return = true;
			}
		}
	}
	return $return;
}
else
	return false;

}

public function create($fields_all_init=false)
{

$agregat_name = $this->name."_agregat";
$object = new $agregat_name();
if ($fields_all_init) foreach($this->fields as $name=>$field)
{
	if (!isset($object->{$name}))
		$object->{$name} = null;
}
return $object;

}

/**
 * Search objects into the databank
 * 
 * @param array $query
 * @return mixed
 */
public function query($params=array(), $fields=array(), $sort=array(), $limit=0, $start=0)
{

//echo "<p>Databank ID#$this->id : query()</p>\n";

if (is_array($result=$this->db_get($params, $fields, $sort, $limit, $start)))
{
	foreach($result as $object)
		$this->objects[$object->id->value] = $object;
	return $result;
}
else
	return false;

}
/**
 * Retrieve an objet from the datamodel.
 * 
 * @param integer $id
 * @return mixed 
 */
public function get($id, $fields=array())
{

if (false && !$this->perm("r"))
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
	elseif (is_array($object_list=$this->db_get(array(array("name"=>"id", "value"=>$id)), $fields)) && count($object_list))
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
 * Retrieve a list of objects by giving query params and fields to be retrieved
 * The function adds the required fields
 *
 * @param unknown_type $query_where
 */
public function db_get($params=array(), $fields=array(), $sort=array(), $limit=0, $start=0)
{

// Retrieve the resulting fields
if (is_array($result = $this->db_select($params, $fields, $sort, $limit, $start)))
{
	// Fields for each object
	$objects = array();
	foreach($result as $o)
	{
		$object = $this->create();
		foreach($o as $name=>$value)
		{
			//echo "<p>$this->name : $name : $value</p>\n";
			// TODO : gérer de façon à dégager les champs inutiles en amont
			if (isset($this->fields[$name]))
				$object->{$name}->value_from_db($value);
		}
		$objects[] = $object;
		if (APC_CACHE)
			apc_store("dataobject_".$this->id."_".$object->id, $object, APC_CACHE_DATAOBJECT_TTL);

	}
	return $objects;
}
else
	return false;

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
/*
elseif (!is_array($fields_input) && !isset($this->fields[$fields_input]) && $fields_input !== true)
{
	if (DEBUG_DATAMODEL)
		trigger_error("datamodel '$this->name' : incorrect field param.");
	return false;
}
*/
else
{
	// Requete sur la table principale
	$query_base = array ( "fields" => array(), "having" => array(), "where" => array(), "from" => array("`".$this->table."`") );
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
		if (in_array($name, $this->fields_key) || in_array($name, $this->fields_required) || $fields_input === true || (is_array($fields_input) && in_array($name, $fields_input)) || (is_string($fields_input) && $name == $fields_input))
		{
			$fields[] = $name;
			if (!in_array($field->type, $type_special))
			{
				if (!isset($field->db_opt["table"]) || !($tablename = $field->db_opt["table"]))
					$tablename = $this->table;
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
		if (isset($param["name"]) && isset($this->fields[$param["name"]]))
		{
			if (!isset($param["type"]))
				$param["type"] = "";
			$field = $this->fields[$param["name"]];
			// Champ "standard"
			if (!in_array($field->type, $type_special))
			{
				if (isset($field->db_opt["lang"]))
				{
					$query_lang = true;
					$query_base["where"][] = "`".$this->table."_lang`.".$field->db_query_param($param["value"], $param["type"]);
				}
				else
					$query_base["where"][] = "`".$this->table."`.".$field->db_query_param($param["value"], $param["type"]);
				// mapping for other queries
				foreach($query_list as $i=>$j)
				{
					if ($this->fields[$i]->type == "dataobject_list")
					{
						$query_list[$i]["where"][] = "`".$this->table."`.".$field->db_query_param($param["value"], $param["type"]);
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
				$query_base["where"][] = "`".$field->db_opt["ref_table"]."`.`".$field->db_opt["ref_id"]."` = `".$this->table."`.`id`";
				// FAIRE UN JOIN CAR CONDITIONS PARAMS AVEC AUTRES TABLES : genre entreprise qui embauche 
			}
		}
		// Query using fulltext index
		elseif (isset($param["type"]) && isset($param["value"]) && count($this->fields_index))
		{
			if ($param["type"] == "fulltext")
			{
				$query_base["fields"][] = "MATCH(`".implode("`, `", $this->fields_index)."`) AGAINST('".db()->string_escape($param["value"])."') as `relevance`";
				$query_base["having"][] = "relevance > 0";
			}
			elseif ($param["type"] == "like")
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
	
	// Primary query

	// Lang
	if ($query_lang)
	{
		$query_base["from"][] = "`".$this->table."_lang`";
		$query_base["where"][] = "`".$this->table."`.`id` = `".$this->table."_lang`.`id`";
		$query_base["where"][] = "`".$this->table."_lang`.`lang_id` = '".SITE_LANG_ID."'";
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
		{
			if (strtolower($j) == "desc")
				$query_sort[] = "`".db()->string_escape($i)."` DESC";
			else
				$query_sort[] = "`".db()->string_escape($i)."` ASC";
		}
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
	
	if (count($query_base["having"]))
		$query_having = "HAVING ".implode(" AND ", $query_base["having"]);
	else
		$query_having = "";
	
	$query_string = "
		SELECT DISTINCT ".implode(" , ",$query_base["fields"])."
		FROM ".implode(" , ",$query_base["from"])."
		WHERE ".implode(" AND ",$query_base["where"])."
		$query_having
		ORDER BY ".implode(",", $query_sort)."
		$query_limit";
	//echo "<p>$query_string</p>";
	// Effective Query
	$query = db()->query($query_string);
	
	if ($query->num_rows() >= 1)
	{
		
		$list_id = array();
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
		
		// Other queries
		
		foreach($query_list as $name=>$detail)
		{
			$field = $this->fields[$detail["field"]];
			if ($field->type == "list")
			{
				$ref_field = $field->db_opt("ref_field");
				$ref_table = $field->db_opt("ref_table");
				$ref_id = $field->db_opt("ref_id");
				$detail["where"][] = "`".$this->table."`.`id` = `$ref_table`.`$ref_id`";
				$detail["where"][] = "`".$this->table."`.`id` IN (".implode(" , ", $list_id).")";
				$query_string = "
					SELECT `$ref_table`.`$ref_id`, `$ref_table`.`$ref_field`
					FROM `".$this->table."` , `$ref_table`
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
				$detail["where"][] = "`".$this->table."`.`id` = `$ref_table`.`$ref_id`";
				$detail["where"][] = "`".$this->table."`.`id` IN (".implode(" , ", $list_id).")";
				// TODO : Retrieve other required fields and next step create the dependant object without other queried !
				$query_string = "
					SELECT `$ref_table`.`$ref_id`, `$ref_table`.`$ref_field`
					FROM `".$this->table."` , `$ref_table`
					WHERE ".implode(" AND ",$detail["where"]);
				$query = db()->query($query_string);
				if ($query->num_rows() >= 1)
				{
					while ($row=$query->fetch_row())
					{
						// Patch des fois qu'on ai des resultats en trop ^^
						if (isset($return[$row[0]]))
						{
							$return[$row[0]]
							[$name][] = $row[1];
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
	}
	
	return $return;
	
}

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
else
{
	// Requete sur la table principale
	$query_base = array ("where"=>array(), "from"=>array("`".$this->table."`"), "having"=>array());
	// Autres requetes
	$query_list = array();
	// Fields to be retrieved with other queries : A MODIFIER, VERIFIER LA TABLE ! CAR TABLES COMPLEMENTAIRES AUSSI POSSIBLES
	$type_special = array("list", "dataobject_list", "dataobject_select");
	// Langue
	$query_lang = false;
	// Result
	$return = array();
	// Result params mapping
	$return_params = array();
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
					$query_base["where"][] = "`".$this->table."_lang`.".$field->db_query_param($param["value"], $param["type"]);
				}
				else
					$query_base["where"][] = "`".$this->table."`.".$field->db_query_param($param["value"], $param["type"]);
				// mapping for other queries
				foreach($query_list as $i=>$j)
				{
					if ($this->fields[$i]->type == "dataobject_list")
					{
						$query_list[$i]["where"][] = "`".$this->table."`.".$field->db_query_param($param["value"], $param["type"]);
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
				$query_base["where"][] = "`".$field->db_opt["ref_table"]."`.`".$field->db_opt["ref_id"]."` = `".$this->table."`.`id`";
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
			$tablename = $this->table;
		$query_base["from"][] = "`".$tablename."_lang`";
		$query_base["where"][] = "`".$this->table."`.`id` = `".$this->table."_lang`.`id`";
		$query_base["where"][] = "`".$this->table."_lang`.`lang_id` = '".SITE_LANG_ID."'";
	}
	// This query is always performed to map keys with results, which is simpler for other queries
	if (count($query_base["where"]) == 0)
		$query_base["where"][] = "1";
	$query_string = " SELECT COUNT(*) FROM ".implode(", ", $query_base["from"])." WHERE ".implode(" AND ",$query_base["where"]);
	return array_pop(db()->query($query_string)->fetch_row());
}

}

/**
 * Returns if an object exists with this ID
 * @param $id
 */
public function exists($id)
{
	
$query = db()->query("SELECT 1 FROM `".$this->table."` WHERE `id`='".db()->string_escape($id)."'");
if ($query->num_rows())
	return true;
else
	return false;

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
}
else
{
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

function json_query($params, $fields)
{

$query = $this->query($params, $fields, array(), 10);

echo "[\n";
if (count($query)) foreach ($query as $object)
{
	$field_list = array();
	foreach ($fields as $i)
	{
		if (isset($object->{$i}))
		{
			if ($object->{$i}->value === null)
				$field_list[] = "$i:null";
			elseif ($object->{$i}->value === true)
				$field_list[] = "$i:true";
			elseif ($object->{$i}->value === false)
				$field_list[] = "$i:false";
			elseif (is_numeric($object->{$i}->value))
				$field_list[] = "$i:".$object->{$i}->value;
			else
				$field_list[] = "$i:".json_encode($object->{$i}->value)."";
		}
	}
	echo "	{id:$object->id, value:'".addslashes("$object")."', fields:{".implode(", ", $field_list)."}},\n";
}
echo "]\n";

}

}

/**
 * Datamodel access function
 */
function datamodel($datamodel_id=null, $object_id=null, $fields=array())
{

if (!isset($GLOBALS["datamodel_gestion"]))
{
	// APC
	if (APC_CACHE)
	{
		if (!($GLOBALS["datamodel_gestion"]=apc_fetch("datamodel_gestion")))
		{
			$GLOBALS["datamodel_gestion"] = new datamodel_gestion();
			apc_store("datamodel_gestion", $GLOBALS["datamodel_gestion"], APC_CACHE_GESTION_TTL);
		}
	}
	// Session
	else
	{
		if (!isset($_SESSION["datamodel_gestion"]))
			$_SESSION["datamodel_gestion"] = new datamodel_gestion();
		$GLOBALS["datamodel_gestion"] = $_SESSION["datamodel_gestion"];
	}
}

if ($datamodel_id && $GLOBALS["datamodel_gestion"]->exists($datamodel_id))
{
	$datamodel = $GLOBALS["datamodel_gestion"]->get($datamodel_id);
	//var_dump($datamodel);
	if ($object_id)
	{
		if (is_a($object=$datamodel->get($object_id, $fields), "data_bank_agregat"))
			return $object;
		else
			return false;	
	}
	else
		return $datamodel;
}
else
	return $GLOBALS["datamodel_gestion"];

}

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [end]");

?>
