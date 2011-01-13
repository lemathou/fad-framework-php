<?

/**
  * $Id$
  * 
  * Copyright 2008-2010 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [begin]");

/**
 * Database gestion
 * Default object data bank
 * @author mathieu
 */
abstract class gestion
{

protected $type = "";

protected $list = array();
protected $list_detail = array();
protected $list_name = array();

// Data in main database table
protected $info_list = array("name"); // Keep at least name ! This is a unique string key
// Data in lang database table
protected $info_lang_list = array("label", "description"); // Keep at least label
// Detailled data (with type, etc.)
protected $info_detail = array // Keep at least name and label !
(
	"name"=>array("label"=>"Nom (unique)", "type"=>"string", "size"=>64, "lang"=>false),
	"label"=>array("label"=>"Label", "type"=>"string", "size"=>128, "lang"=>true),
	"description"=>array("label"=>"Description", "type"=>"text", "lang"=>true)
);
// Required info
protected $info_required = array("name", "label");

// Retrieve all objects on first load
protected $retrieve_objects = false;
// Keep all non required info after first load
protected $retrieve_details = true;

public function info_list()
{

return $this->info_list;

}
public function info_lang_list()
{

return $this->info_lang_list;

}
public function info_detail_list()
{

return $this->info_detail;

}

/**
 * Sauvegarde/Restauration de la session
 */
function __sleep()
{

if ($this->retrieve_objects)
	return array("list_detail", "list");
else
	return array("list_detail");

}
function __wakeup()
{

foreach($this->list_detail as $id=>$info)
	$this->list_name[$info["name"]] = $id;

}

function __construct()
{

$this->query_info();
$this->construct_more();

}
protected function construct_more()
{

// To be extended if needed !

}

/**
 * Query required info
 */
function query_info($retrieve_objects=false)
{

$this->list = array();
$this->list_name = array();
$this->list_detail = array();

$query_objects = array();
$query_fields = array("`t1`.`id`");
foreach ($this->info_detail as $name=>$field)
{
	if (isset($field["lang"]))
	{
		if ($field["lang"])
			$query_fields[] = "`t2`.`$name`";
		else
			$query_fields[] = "`t1`.`$name`";
	}
	elseif ($field["type"] == "object_list")
	{
		$query_objects[] = $name;
	}
}

$query_string = "SELECT ".implode(", ", $query_fields)." FROM `_$this->type` as t1 LEFT JOIN `_".$this->type."_lang` as t2 ON t1.`id`=t2.`id` AND t2.`lang_id`='".SITE_LANG_ID."'";
$query = db()->query($query_string);
while($info = $query->fetch_assoc())
{
	$this->list_detail[$info["id"]] = $info;
	$this->list_name[$info["name"]] = $info["id"];
	foreach ($query_objects as $name)
		$this->list_detail[$info["id"]][$name] = array();
}

foreach ($query_objects as $name)
{
	$field = $this->info_detail[$name];
	$query = db()->query("SELECT `$field[db_id]`, `$field[db_field]` FROM `$field[db_table]`");
	while (list($id, $object_id)=$query->fetch_row())
	{
		if (isset($this->list_detail[$id]))
			$this->list_detail[$id][$name][] = $object_id;
	}
}

$this->query_info_more();

// TEMPORARY HACK so retrieve_objects() does not become silly ^^
if (!$this->retrieve_details)
{
	$rd = false;
	$this->retrieve_details = true;
}
else
	$rd = true;

if ($retrieve_objects || $this->retrieve_objects)
{
	$this->retrieve_objects();
}
// generate objects in cache in some cases to optimise website
if (!$this->retrieve_objects && !$rd && OBJECT_CACHE)
{
	foreach($this->list_detail as $id=>$info)
	{
		object_cache_store($this->type."_$id", $this->construct_object($id), OBJECT_CACHE_GESTION_TTL);
	}
}
if (!$rd)
{
	$this->retrieve_details = false;
	foreach ($this->list_detail as $id=>$info)
	{
		$this->list_detail[$id] = array();
		foreach ($this->info_required as $name)
			$this->list_detail[$id][$name] = $info[$name];
	}
}

if (OBJECT_CACHE)
	object_cache_store($this->type."_gestion", $this, OBJECT_CACHE_GESTION_TTL);

}
protected function query_info_more()
{

// To be extended if needed !

}

/**
 * Constructs an object
 */
protected function construct_object($id)
{

$type = $this->type;

if ($this->retrieve_details)
	return new $type($id, false, $this->list_detail[$id]);
else
	return new $type($id, true);

}

/**
 * Retrieve all objects
 */
public function retrieve_objects()
{

$type = $this->type;
foreach ($this->list_detail as $id=>$info)
{
	if (!isset($this->list[$id]))
		$this->list[$id] = $this->construct_object($id);
}

}

/**
 * Returns an object using its ID
 * @param int $id
 */
function get($id)
{

if (!is_numeric($id))
	return null;

//echo $id;

if (isset($this->list[$id]))
{
	return $this->list[$id];
}
elseif (!$this->retrieve_objects && OBJECT_CACHE && ($object=object_cache_retrieve($this->type."_$id")))
{
	return $this->list[$id] = $object;
}
elseif (!$this->retrieve_objects && isset($this->list_detail[$id]))
{
	$object = $this->construct_object($id);
	if (OBJECT_CACHE)
		object_cache_store($this->type."_$id", $object, OBJECT_CACHE_GESTION_TTL);
	return $this->list[$id] = $object;
}
else
{
	return null;
}

}

/**
 * Retrieve an object using its unique name
 * @param unknown_type $name
 */
function __get($name)
{

if (isset($this->list_name[$name]))
{
	return $this->get($this->list_name[$name]);
}
else
{
	return null;
}

}
function get_name($name)
{

return $this->__get($name);

}

/**
 * Returns if an object exists
 * @param int $id
 */
function exists($id)
{

return isset($this->list_detail[$id]);

}
/**
 * 
 * Returns if an object exists using its unique name
 * @param string $name
 */
function __isset($name)
{

return isset($this->list_name[$name]);

}
function exists_name($name)
{

return $this->__isset($name);

}

/**
 * Returns the list
 */
public function list_get()
{

return $this->list;

}
public function list_name_get($name=null)
{

if ($name)
	return $this->list_name[$name];
else
	return $this->list_name;

}
public function list_detail_get($id=null)
{

if ($id)
	return $this->list_detail[$id];
else
	return $this->list_detail;

}

/**
 * Delete an object
 * @param int $id
 */
public function del($id)
{

if (!login()->perm(6)) // TODO : send email to admin
	die("ONLY ADMIN CAN DELETE $this->type");

if (!$this->retrieve_objects)
	if (!isset($this->list_detail[$id]))
		return false;
	else
		$o_name = $this->list_detail[$id]["name"];
elseif ($this->retrieve_objects)
	if (!isset($this->list[$id]))
		return false;
	else
		$o_name = $this->list[$id]->name();

db()->query("DELETE FROM `_$this->type` WHERE `id`='$id'");
db()->query("DELETE FROM `_".$this->type."_lang` WHERE `id`='$id'");
foreach ($this->info_detail as $name=>$info)
{
	if ($info["type"] == "script")
	{
		$filename = "$info[folder]/".str_replace("{name}", $o_name, $info["filename"]);
		if (file_exists($filename))
			unlink($filename);
	}
	elseif ($info["type"] == "object_list")
	{
		db()->query("DELETE FROM `$info[db_table]` WHERE `$info[db_id]`='$id'");
	}
}
$this->del_more($id);
$this->query_info();

return true;

}
protected function del_more($id)
{

// To be extended if needed !

}

/**
 * Add an object
 * @param array $infos
 */
public function add($infos)
{

if (!login()->perm(6))
	die("ONLY ADMIN CAN ADD $this->type");

$query_fields = array();
$query_values = array();
$query_fields_lang = array();
$query_values_lang = array();
$query_objects = array();

if (!is_array($infos) || !isset($infos["name"]) || !is_string($infos["name"]) || !preg_match("/^([a-zA-Z_\/0-9-]+)$/", $infos["name"]))
	return false;
if ($this->exists_name($infos["name"]))
	return false;

foreach ($this->info_detail as $name=>$field_info)
{
	if (isset($field_info["lang"]) && !$field_info["lang"] && isset($infos[$name]) && is_string($infos[$name]))
	{
		$query_fields[] = "`$name`";
		$query_values[] = "'".db()->string_escape($infos[$name])."'";
	}
	elseif (isset($field_info["lang"]) && $field_info["lang"] && isset($infos[$name]) && is_string($infos[$name]))
	{
		$query_fields_lang[] = "`$name`";
		$query_values_lang[] = "'".db()->string_escape($infos[$name])."'";
	}
	elseif ($field_info["type"] == "script" && isset($infos[$name]) && is_string($infos[$name]))
	{
		$filename = $field_info["folder"]."/".str_replace("{name}", $infos["name"], $field_info["filename"]);
		fwrite(fopen($filename,"w"), htmlspecialchars_decode($infos[$name]));
	}
	elseif ($field_info["type"] == "object_list" && isset($infos[$name]))
	{
		$query_objects[] = $name;
	}
}

db()->query("INSERT INTO `_".$this->type."` (".implode(", ", $query_fields).") VALUES (".implode(", ", $query_values).")");
$id = db()->last_id();
db()->query("INSERT INTO `_".$this->type."_lang` (`id`, `lang_id`, ".implode(", ", $query_fields_lang).") VALUES ('$id', '".SITE_LANG_ID."', ".implode(", ", $query_values_lang).")");

foreach ($query_objects as $name)
{
	$field_info = $this->info_detail[$name];
	$object_type = $field_info["object_type"];
	db()->query("DELETE FROM `$field_info[db_table]` WHERE `$field_info[db_id]`='$id'");
	$query_object_list = array();
	if (is_array($infos[$name])) foreach($infos[$name] as $object_id) if ($object_type()->exists($object_id))
		$query_object_list[] = "('$object_id', '$id')";
	if (count($query_object_list)>0)
		db()->query("INSERT INTO `$field_info[db_table]` (`$field_info[db_field]`, `$field_info[db_id]`) VALUES ".implode(", ", $query_object_list));
}

$this->add_more($id, $infos);

$this->query_info();

}
/**
 * Specific fields for an object
 * @param integer $id
 * @param array $infos
 */
protected function add_more($id, $infos)
{

// To be extended if needed !

}

/**
 * Display a list
 * @param array params : filtering parameters
 * @param array field_list : fields to display (automatically adds id and name)
 */
public function table_list($params=array(), $field_list=true)
{

if (!login()->perm(6))
	die("ONLY ADMIN CAN SHOW LIST OF $this->type");

$this->retrieve_objects();

?>
<table width="100%" cellspacing="1" border="1" cellpadding="1" class="object_list">
<tr class="label">
	<td>[id] name</td>
	<td>label</td>
<?
foreach ($this->info_detail as $name=>$field_info) if (($field_list === true || in_array($name, $field_list)) && $name != "name" && $name != "label" && $field_info["type"] != "script")
	echo "<td>".$field_info["label"]."</td>\n";
?>
</tr>
<?
foreach ($this->list as $id=>$object) if (true || $params === true)
{
	echo "<tr>\n";
			echo "<td><a href=\"?id=$id\">[$id] ".$object->name()."</a></td>\n";
			echo "<td>".$object->label()."</td>\n";
	foreach ($this->info_detail as $name=>$field_info) if (($field_list === true || in_array($name, $field_list)) && $name != "name" && $name != "label" && $field_info["type"] != "script")
	{
		if (in_array($field_info["type"], array("string", "text")))
		{
			echo "<td>".$object->info($name)."</td>\n";
		}
		elseif ($field_info["type"] == "integer")
		{
			echo "<td align=\"right\">".$object->info($name)."</td>\n";
		}
		elseif ($field_info["type"] == "boolean")
		{
			if ($object->info($name))
				echo "<td>OUI</td>\n";
			else
				echo "<td>NON</td>\n";
		}
		elseif ($field_info["type"] == "select")
		{
			if ($object->info($name))
				echo "<td>".$field_info["select_list"][$object->info($name)]."</td>\n";
			else
				echo "<td>".$info[$name]."</td>\n";
		}
		elseif ($field_info["type"] == "object")
		{
			$object_type = $field_info["object_type"];
			if ($object_type()->exists($id=$object->info($name)))
				echo "<td>".$object_type()->get($id)->label()."</td>";
			else
				echo "<td><i>undefined</i></td>\n";
		}
		elseif ($field_info["type"] == "object_list")
		{
			$object_type = $field_info["object_type"];
			$object_list = array();
			if (count($object->info($name))) foreach ($object->info($name) as $id) if ($object_type()->exists($id))
			{
				$object_list[] = $object_type()->get($id)->label();
			}
			echo "<td>".implode(", ", $object_list)."</td>\n";
		}
		else
			echo "<td>".json_encode($object->info($name))."</td>\n";
	}
	echo "</tr>\n";
}
?>
</table>
<?

}

public function insert_form($action="")
{

if (!login()->perm(6))
	die("ONLY ADMIN CAN ADD $this->type");

?>
<form action="<?php echo $action; ?>" method="post" class="object_form">
<table cellspacing="0" cellpadding="1">
<?php
foreach ($this->info_detail as $name=>$info)
{
?>
<tr>
	<td class="label"><label for="<?php echo $name; ?>"><?php echo $info["label"]; ?> :</label></td>
<?php
	if ($info["type"] == "string") { ?>
	<td><input name="<?php echo $name; ?>" size="32" maxlength="<?php echo $info["size"]; ?>" value="<?php if (isset($info["default"])) echo $info["default"]; ?>" class="data_string" /></td>
<?php } elseif ($info["type"] == "text") { ?>
	<td><textarea name="<?php echo $name; ?>" class="data_text"><?php if (isset($info["default"])) echo $info["default"]; ?></textarea></td>
<?php } elseif ($info["type"] == "integer") { ?>
	<td><input name="<?php echo $name; ?>" size="10" maxlength="10" value="<?php if (isset($info["default"])) echo $info["default"]; ?>" class="data_integer" /></td>
<?php } elseif ($info["type"] == "boolean") { ?>
	<td><input name="<?php echo $name; ?>" type="radio" value="1"<?php if ($info["default"] == "1") echo " checked"; ?> /> OUI <input name="<?php echo $name; ?>" type="radio" value="0"<?php if ($info["default"] == "0") echo " checked"; ?> /> NON</td>
<?php } elseif ($info["type"] == "select") { ?>
	<td><select name="<?php echo $name; ?>" class="data_select"><option value=""></option><?
	foreach($info["select_list"] as $i=>$j)
		if ($info["default"] == $i)
			echo "<option value=\"$i\" selected>$j</option>";
		else
			echo "<option value=\"$i\">$j</option>";
	?></select></td>
<?php } elseif ($info["type"] == "script") { ?>
	<td><textarea id="<?php echo $name; ?>" name="<?php echo $name; ?>" class="data_script"><?php if (isset($info["default"])) echo $info["default"]; ?></textarea></td>
<?php } elseif ($info["type"] == "object") { $object_type = $info["object_type"]; ?>
	<td><select name="<?php echo $name; ?>" class="data_select"><option value=""></option><?
	foreach($object_type()->list_detail_get() as $i=>$j)
		echo "<option value=\"$i\">$j[label]</option>";
	?></select></td>
<?php } elseif ($info["type"] == "object_list") { $object_type = $info["object_type"]; ?>
	<td><input name="<?php echo $name; ?>" type="hidden" /><select name="<?php echo $name; ?>[]" size="10" multiple class="data_fromlist"><?
	foreach($object_type()->list_detail_get() as $i=>$j)
		echo "<option value=\"$i\">$j[label]</option>";
	?></select></td>
<?php } else { ?>
	<td><textarea name="<?php echo $name; ?>" style="width:100%;" rows="5"></textarea></td>
<?php } ?>
</tr>
<?php
}
?>
<tr>
	<td>&nbsp;</td>
	<td><input type="submit" name="_insert" value="Ajouter" /></td>
</tr>
</table>
</form>
<?php
}


}

/**
 * Default object type
 */
abstract class object_gestion
{

protected $_type = "";

protected $id=0;
protected $name="";
protected $label="";
protected $description="";

public function __construct($id, $query=true, $infos=array())
{

$type = $this->_type;
$this->id = $id;

foreach ($infos as $name=>$value)
	if (isset($this->{$name}))
		$this->{$name} = $value;

$this->construct_more($infos);

if ($query)
	$this->query_info();

}
protected function construct_more($infos)
{

// To be extended if needed !

}

/**
 * Query required info
 */
function query_info()
{

$type = $this->_type;
$info_list = $type()->info_list();
$info_lang_list = $type()->info_lang_list();

$query_fields = array("`t1`.`id`");
foreach($info_list as $field)
	$query_fields[] = "`t1`.`$field`";
foreach($info_lang_list as $field)
	$query_fields[] = "`t2`.`$field`";

$query_string = "SELECT ".implode(", ", $query_fields)." FROM `_$type` as t1 LEFT JOIN `_".$type."_lang` as t2 ON t1.`id`=t2.`id` AND t2.`lang_id`='".SITE_LANG_ID."' WHERE t1.id='$this->id'";
$query = db()->query($query_string);
while($infos = $query->fetch_assoc())
{
	foreach($infos as $name=>$value)
		$this->{$name} = $value;
}

$this->query_info_more();

if (OBJECT_CACHE)
	object_cache_store($this->_type."_$this->id", $this, OBJECT_CACHE_GESTION_TTL);

}
protected function query_info_more()
{

// To be extended if needed

}

/**
 * Update the object
 * @param array $infos
 */
public function update($infos)
{

if (!login()->perm(6))
	die("ONLY ADMIN CAN UPDATE $this->_type");

if (!is_array($infos))
	$infos = array();

$type = $this->_type;
$info_detail = $type()->info_detail_list();

$query_info = array();
$query_info_lang = array();
$query_objects = array();

// unique name
if (isset($infos["name"]) && (!is_string($infos["name"]) || !preg_match("/^([a-zA-Z0-9_\/-]+)$/",$infos["name"]) || $type()->exists($infos["name"])))
	unset($infos["name"]);

foreach ($info_detail as $name=>$field_info)
{
	if (isset($field_info["lang"]) && !$field_info["lang"] && isset($infos[$name]))
	{
		$query_info[] = "`$name`='".db()->string_escape($infos[$name])."'";
	}
	elseif (isset($field_info["lang"]) && $field_info["lang"] && isset($infos[$name]))
	{
		$query_info_lang[] = "`$name`='".db()->string_escape($infos[$name])."'";
	}
	elseif ($field_info["type"] == "script")
	{
		$filename = $field_info["folder"]."/".str_replace("{name}", $this->name, $field_info["filename"]);
		if (isset($infos[$name]))
		{
			if (is_string($infos[$name]) && strlen($infos[$name])>0)
				fwrite(fopen($filename, "w"), htmlspecialchars_decode($infos[$name]));
			else
				unset($filename);
		}
		if (isset($infos["name"]) && $this->name != $infos["name"] && file_exists($filename))
		{
			$filename_new = $field_info["folder"]."/".str_replace("{name}", $infos["name"], $field_info["filename"]);
			rename($filename, $filename_new);
		}
	}
	elseif ($field_info["type"] == "object_list" && isset($infos[$name]))
	{
		$query_objects[] = $name;
	}
}

if (count($query_info))
	db()->query("UPDATE `_$type` SET ".implode(", ",$query_info)." WHERE `id`='$this->id'");
if (count($query_info_lang))
	db()->query("UPDATE `_".$type."_lang` SET ".implode(", ",$query_info_lang)." WHERE `id`='$this->id' AND `lang_id`='".SITE_LANG_ID."'");

foreach ($query_objects as $name)
{
	$field_info = $info_detail[$name];
	$object_type = $field_info["object_type"];
	db()->query("DELETE FROM `$field_info[db_table]` WHERE `$field_info[db_id]`='$this->id'");
	$query_object_list = array();
	if (is_array($infos[$name])) foreach($infos[$name] as $object_id) if ($object_type()->exists($object_id))
		$query_object_list[] = "('$object_id', '$this->id')";
	if (count($query_object_list)>0)
		db()->query("INSERT INTO `$field_info[db_table]` (`$field_info[db_field]`, `$field_info[db_id]`) VALUES ".implode(", ", $query_object_list));
}
	

$this->update_more($infos);

$this->query_info();
$type()->query_info();


}
protected function update_more($infos)
{

// To be extended if needed

}

function __tostring()
{

return "$this->name : $this->description";

}
public function id()
{

return $this->id;

}
public function name()
{

return $this->name;

}
public function label()
{

return $this->label;

}
public function info($name)
{

if (isset($this->{$name}))
	return $this->{$name};

}

public function update_form($action="")
{

if (!login()->perm(6))
	die("ONLY ADMIN CAN UPDATE $this->_type");

$_type = $this->_type;
?>
<form action="<?php echo $action; ?>" method="post" class="object_form">
<table cellspacing="0" cellpadding="1">
<tr>
	<td class="label"><label for="id">ID :</label></td>
	<td><input name="id" size="10" readonly value="<?php echo $this->id; ?>" /></td>
</tr>
<?php
foreach ($_type()->info_detail_list() as $name=>$info)
{
?>
<tr>
	<td class="label"><label for="<?php echo $name; ?>"><?php echo $info["label"]; ?> :</label></td>
<?php
	if ($info["type"] == "string") { ?>
	<td><input name="<?php echo $name; ?>" size="32" maxlength="<?php echo $info["size"]; ?>" value="<?php echo $this->{$name}; ?>" class="data_string" /></td>
<?php } elseif ($info["type"] == "text") { ?>
	<td><textarea name="<?php echo $name; ?>" class="data_text"><?php echo $this->{$name}; ?></textarea></td>
<?php } elseif ($info["type"] == "integer") { ?>
	<td><input name="<?php echo $name; ?>" size="10" maxlength="10" value="<?php echo $this->{$name}; ?>" class="data_integer" /></td>
<?php } elseif ($info["type"] == "boolean") { ?>
	<td><input name="<?php echo $name; ?>" type="radio" value="1"<?php if ($this->{$name}) echo " checked"; ?> /> OUI <input name="<?php echo $name; ?>" type="radio" value="0"<?php if (!$this->{$name}) echo " checked"; ?> /> NON</td>
<?php } elseif ($info["type"] == "select") { ?>
	<td><select name="<?php echo $name; ?>" class="data_select"><option value=""></option><?
	foreach($info["select_list"] as $i=>$j)
		if ($i == $this->{$name})
			echo "<option value=\"$i\" selected>$j</option>";
		else
			echo "<option value=\"$i\">$j</option>";
	?></select></td>
<?php } elseif ($info["type"] == "script") { ?>
	<td><textarea id="<?php echo $name; ?>" name="<?php echo $name; ?>" class="data_script"><?php
	$filename =  $info["folder"]."/".str_replace("{name}", $this->name, $info["filename"]);
	if (file_exists($filename) && ($filesize=filesize($filename)))
		echo $content = htmlspecialchars(fread(fopen($filename,"r"),$filesize));
	?></textarea></td>
<?php } elseif ($info["type"] == "object_list") { $object_type = $info["object_type"]; ?>
	<td><input name="<?php echo $name; ?>" type="hidden" /><select name="<?php echo $name; ?>[]" title="<?php echo $info["label"]; ?>" size="10" multiple class="data_fromlist"><?
	foreach($object_type()->list_detail_get() as $i=>$j)
		if (in_array($i, $this->{$name}))
			echo "<option value=\"$i\" selected>$j[label]</option>";
		else
			echo "<option value=\"$i\">$j[label]</option>";
	?></select></td>
<?php } elseif ($info["type"] == "object") { $object_type = $info["object_type"]; ?>
	<td><select name="<?php echo $name; ?>" class="data_select"><option value=""></option><?
	foreach($object_type()->list_detail_get() as $i=>$j)
		if ($i == $this->{$name})
			echo "<option value=\"$i\" selected>$j[label]</option>";
		else
			echo "<option value=\"$i\">$j[label]</option>";
	?></select></td>
<?php } else { ?>
	<td><textarea name="<?php echo $name; ?>" style="width:100%;"><?php echo $this->{$name}; ?></textarea></td>
<?php } ?>
</tr>
<?php
}
?>
<tr>
	<td>&nbsp;</td>
	<td><input type="submit" name="_update" value="Mettre à jour" /></td>
</tr>
</table>
</form>
<?php
}

}

/**
 * Page listing
 * 
 * @author mathieu
 */
class page_listing
{

// Number of records
protected $nb = 0;
// List of avalaible number of records per page
protected $page_nb_list = array( 10 );
// default number of records per page
protected $page_nb_default = 10;
// Url
protected $url = "";
// Url page_nb param
protected $url_page_nb_param = "page";
// Url page param
protected $url_page_param = "";
// Default page
protected $page_default = 0;

// Max number of pages
protected $page_max = 0;

// Current number of records per page
protected $page_nb = 0;
// Current displayed page
protected $page = 0;

function __construct($nb, $page_nb_list=array(10), $page_nb_default=10, $page_default=1, $url="")
{

if (!is_numeric($nb) || $nb < 0)
	$this->nb = 0;
else
	$this->nb = (int)$nb;

$this->page_nb_list_set($page_nb_list);
$this->page_nb_default_set($page_nb_default);
$this->page_nb_set($page_nb_default);
$this->page_default_set($page_default);
$this->page_set($page_default);
$this->url_set($url);

}

// Setter
function page_nb_list_set($page_nb_list)
{

if (!is_array($page_nb_list))
	$this->page_nb_list = array( 10 );
else
{
	$this->page_nb_list = array();
	foreach ($page_nb_list as $i=>$j)
		if (is_numeric($j) || $j >= 1)
			$this->page_nb_list[] = (int)$j;
	if (count($this->page_nb_list) == 0)
		$this->page_nb_list[] = 10;
}

}

// Setter
function page_nb_default_set($page_nb_default)
{

if (is_numeric($page_nb_default) && in_array($page_nb_default, $this->page_nb_list))
	$this->$page_nb_default = $page_nb_default;
else
	$this->$page_nb_default = $this->page_nb_list[0];

}

// Setter
function page_default_set($page_default)
{

if (!is_numeric($page_default) || $page_default<0 || $page_default>$this->page_max)
	$this->page_default = 0;
else
	$this->page_default = (int)$page_default;

}

// Setter
function url_set($url)
{

$this->url = (string)$url;

}

// Setter
function page_nb_set($page_nb)
{

if (is_numeric($page_nb) && in_array($page_nb, $this->page_nb_list))
	$this->page_nb = $page_nb;
else
	$this->page_nb = $this->$page_nb_default;

$this->page_max = ceil($this->nb/$this->page_nb);

}

// Page Setter
function page_set($page)
{

if (!is_numeric($page) || $page < 0)
	$this->page = $this->page_default;
elseif ($page > $this->page_max)
	$this->page = $this->page_max;
else
	$this->page = (int)$page;

}

function page_list($page=null)
{

if ($page !== null)
	$this->page_set($page);

// page_min + 4 < page < page_max - 4
if ($this->page > 5 && ($this->page + 4) < $this->page_max)
{
	$page_list = array(1,"");
	for ($i=$this->page-2; $i<=$this->page+2; $i++)
		$page_list[] = $i;
	$page_list[] = "";
	$page_list[] = $this->page_max;
}
// page_min + 4 < page_max - 4 <= page
elseif ($this->page > 5 && $this->page_max > 10)
{
	$page_list = array(1,"");
	for ($i=$this->page-2; $i<=$this->page_max; $i++)
		$page_list[] = $i;
}
// page <= page_min + 4 < page_max - 4
elseif (($this->page+4) < $this->page_max && $this->page_max > 10)
{
	$page_list = array();
	for ($i=1; $i<=($this->page+2); $i++)
		$page_list[] = $i;
	$page_list[] = "";
	$page_list[] = $this->page_max;
}
// page_min <= page <= page_max <= 10
else
{
	$page_list = array();
	for ($i=1; $i<=$this->page_max; $i++)
		$page_list[] = $i;
}

return $page_list;

}

function link_list($page=null)
{

$page_list = $this->page_list($page);

$link_list = array();

if (!is_numeric(strpos($this->url, "?")))
	$url = $this->url."?";
elseif (substr($this->url, -1, 1) != "&")
	$url = $this->url."&";

if (count($this->page_nb_list) > 1)
	$url .= "page_nb=$this->page_nb&";

foreach ($page_list as $i)
{
	if (!$i)
		$link_list[] = "...";
	elseif ($this->page == $i)
		if (in_array("", $page_list))
			$link_list[] = "<input class=\"autosize\" value=\"$i\" onfocus=\"this.select()\" onchange=\"document.location.href='".$url."page='+this.value\" onkeyup=\"if (this.value.length) this.style.width=(this.value.length*0.75+0.75)+'em'; else this.style.width='1.5em';\" style=\"width:".(strlen($i)*0.75+0.75)."em;\" />";
		else
		$link_list[] = "<span class=\"selected\">$i</span>";
	else
		$link_list[] = "<a href=\"".$url."page=$i\">$i</a>";
}

return $link_list;

}

function nb_start()
{

if ($this->page > 0)
	return ($this->page - 1) * $this->page_nb;
else
	return 0;

}

function nb_end()
{

if ($this->page > 0)
	return min($this->page*$this->page_nb, $this->nb) - 1;
else
	return -1;

}

// Getter
function __get($name)
{

if (in_array($name, array("page", "page_default", "page_nb", "page_max")))
	return $this->{$name};

}

}

/**
 * String manipulation
 *
 * @author mathieu
 */
class text
{

const ACCENT = "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿ";
const NOACCENT = "AAAAAAACEEEEIIIIDNOOOOOOUUUUYBsaaaaaaaceeeeiiiidnoooooouuuyyby";

function remove_accent($str)
{

$a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
$b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
return str_replace($a, $b, $str);

} 

/**
 * Returns a string without any special char, with "-" in place of " ", perfectly designed for url's ;-)
 */
static function rewrite_ref($string)
{

$reecriture=strtr(trim(utf8_decode($string)), utf8_decode(self::ACCENT), self::NOACCENT);
$url=preg_replace('/[^0-9a-zA-Z]/', ' ', $reecriture);
$url=preg_replace('/ +/', '-', trim($url));
return $url;

}

/**
 * Protects PHP scripts while including string.
 */
function php_protect($string)
{

return str_replace( array("<?", "?>"), array("&lt;?", "&gt;?"), $string);

}

/**
 * Convert a string so it is displayed "as-it", without HTML
 */
function html_protect($string)
{

return htmlspecialchars(htmlspecialchars_decode($string));

}

/**
 * Convert a string so it is displayed "as-it", with most entities as possible
 */
function html_convert($string)
{

return html_entity_encode(html_entity_decode($string));

}

// nl2br() : met des <br /> là où il y a des sauts de ligne !
// html_entity_decode()

/**
 * Retrieve only keywords from a sentense
 */
function keyword_extract($string)
{

$list = array
(
// * Préposition
	// Cause
	"à cause de",
	"à la suite de",
	"en raison de",
	"grâce à",
	"du fait de",
	// Conséquence ou but
	"au point de",
	"de peur de",
	"assez... pour",
	"assez",
	"pour",
	"afin de",
	"en vue de",
	// Addition
	"outre",
	"en plus de",
	"en sus de",
	// Concession ou opposition
	"malgré",
	"en dépit de",
	"loin de",
	"contre",
	"au contraire de",
	"au lieu de",
	// Hypothèse
	"en cas de",
// * Conjonctions de coordination et adverbes
	// Cause
	"car",
	"en effet",
	// Conséquence ou but
	"de là",
	"d’où",
	"donc",
	"aussi",
	"par conséquent",
	"en conséquence",
	"c’est pourquoi",
	"ainsi",
	"dès lors",
	// Addition
	"et",
	"en plus",
	"de plus",
	"en outre",
	"par ailleurs",
	"ensuite",
	"d’une part... d’autre part",
	"aussi",
	"également",
	// Concession ou opposition
	"mais",
	"or",
	"néanmoins",
	"cependant",
	"pourtant",
	"toutefois",
	//"au contraire",
	"inversement",
	"en revanche",
// * Conjonctions de subordination
	// Cause
	"parce que",
	"puisque",
	"comme",
	"étant donné que",
	// Conséquence ou but
	"pour que",
	"afin que",
	"si bien que",
	"de façon que",
	"de sorte que",
	"dès lors que",
	"tellement que",
	"tant que",
	"au point que",
	// Addition
	"outre que",
	"sans compter que",
	"et",
	// Concession ou opposition
	"bien que",
	"quoique",
	"même si",
	"alors que",
	"tandis que",
	"tout... que...",
	"quelque... que...",
	// Hypothèse
	"si",
	"au cas où",
	"pour le cas où",
	"selon que",
	"suivant que",
// * Verbes et locutions verbales
	// Cause
	//"venir de",
	//"découler de",
	//"résulter de",
	//"provenir",
	// Conséquence ou but
	//"causer",
	//"impliquer",
	//"entraîner",
	//"provoquer",
	//"susciter",
	//etc.
	// Addition
	//"s’ajouter",
	"s",
	//"marier",
	//etc.
	// Concession ou opposition
	//"s’opposer à",
	"à",
	//"contredire",
	//"avoir beau (+ verbe)",
	//"réfuter",
	//etc.
	// Hypothèse
	//"à supposer que",
	"que",
// * Divers
	// Articles
	"un",
	"une",
	"des",
	"le",
	"la",
	"les",
	"je",
	"tu",
	"il",
	"elle",
	"nous",
	"vous",
	"ils",
	"ce",
	"cette",
	"ces",
	"mon",
	"ton",
	"son",
	"leur",
	"leurs",
	// Autre
	"l",
	"n",
	"a",
	"t",
	"d",
	"de",
	"de la",
	"du",
	"au",
	"avec",
	"qui",
	"qu",
	"y",
	"dans",
	"entre",
	"ne"
);

$l = array();
foreach ($list as $t)
{
	$t = self::rewrite_ref($t);
	$t2 = explode("-", $t);
	foreach ($t2 as $t3) if (!in_array($t3, $l))
		$l[] = $t3;
}

print_r($l);

$s = explode("-", self::rewrite_ref($string));
foreach ($s as $nb=>$s2)
{
	if (in_array($s2, $l))
		unset($s[$nb]);
}
return implode("-",$s);

}


}

/**
 * Global class to send emails.
 * 
 * @author mathieu
 */
class mail
{

/**
 * Send emails, adding usefull header infos...
 * 
 * @param unknown_type $to
 * @param unknown_type $subject
 * @param unknown_type $message
 * @param unknown_type $headers
 */
static function common($to, $subject, $message, $headers="")
{

mail($to, $subject, $message, "X-Originating-IP: ".$_SERVER["REMOTE_ADDR"]."\r\nX-WebSite: ".SITE_DOMAIN."\r\nX-WebSite-AccountID: ".login()->id()."\r\n$headers");

}

/**
 * Send text/plain email
 * 
 * @param unknown_type $to
 * @param unknown_type $subject
 * @param unknown_type $message
 * @param unknown_type $headers
 */
static function text($to, $subject, $message, $headers="")
{

self::common($to, $subject, imap_8bit($message), "Content-Type: text/plain; charset=\"".SITE_CHARSET."\"\r\nContent-Transfer-Encoding: quoted-printable\r\n$headers");

}

/**
 * Send text/html email
 * 
 * @param unknown_type $to
 * @param unknown_type $subject
 * @param unknown_type $message_html
 * @param unknown_type $headers
 */
static function html($to, $subject, $message_html, $headers="")
{

$boundary = "-----=".md5(uniqid(rand()));

$message = "Ceci est un message au format MIME 1.0 multipart/alternative.\r\n";

$message .= "--$boundary\r\n";
$message .= "Content-Type: text/html; charset=\"".SITE_CHARSET."\"\r\n";
$message .= "Content-Transfer-Encoding: quoted-printable\r\n";
$message .= "\r\n";
$message .= wordwrap(imap_8bit($message_html))."\r\n";
$message .= "\r\n";

$message .= "--$boundary\r\n";
$message .= "Content-Type: text/plain; charset=\"".SITE_CHARSET."\"\r\n";
$message .= "Content-Transfer-Encoding: quoted-printable\r\n";
$message .= "\r\n";
// suppress header
$message_text = preg_replace("/\<head\>([^%]*)\<\/head\>/", "", $message_html);
// convert <br />
$message_text = str_replace("\r\n<br />", "\r\n", $message_text);
$message_text = str_replace("<br />\r\n", "\r\n", $message_text);
$message_text = str_replace("<br />", "\r\n", $message_text);
// other tags
$message_text = strip_tags($message_text);
// unify spaces
$message_text = preg_replace("/([[:space:]]+)/", " ", $message_text);
// encode
$message_text = wordwrap(imap_8bit($message_text));
$message .= $message_text."\r\n";
$message .= "\r\n";

$message .= "\r\n--$boundary--\r\n";

self::common($to, $subject, $message, "MIME-Version: 1.0\r\nContent-Type: multipart/alternative; charset=\"".SITE_CHARSET."\"; boundary=\"$boundary\"\r\n$headers");

}

}

/**
 * Image manipulation
 */
class img
{

static function resize($filename, $options=array())
{

if (strtolower(substr($filename, -3)) == "png")
{
	$read_fct = "imagecreatefrompng";
	$save_fct = "imagepng";
}
elseif (strtolower(substr($filename, -3)) == "jpg")
{
	$read_fct = "imagecreatefromjpeg";
	$save_fct = "imagejpeg";
}
elseif (strtolower(substr($filename, -3)) == "gif")
{
	$read_fct = "imagecreatefromgif";
	$save_fct = "imagegif";
}

$img_r = $read_fct($filename);
list($width, $height, $type, $attr) = getimagesize($filename);

// Maxwidth
if (isset($options["maxwidth"]) && is_numeric($maxwidth=$options["maxwidth"]) && ($width > $maxwidth))
{
	$maxheight = round($height*$maxwidth/$width);
	$dst_r = ImageCreateTrueColor($maxwidth, $maxheight);
	echo "<p>Image Retaillée : Largeur de ".$maxwidth."px et hauteur de ".$maxheight."px.</p>";
	imagecopyresampled($dst_r, $img_r, 0, 0, 0, 0, $maxwidth, $maxheight, $width, $height);
	$save_fct($dst_r, $filename);
}

// Width + Height
if (isset($options["width"]) && is_numeric($width2=$options["width"]) && isset($options["height"]) && is_numeric($height2=$options["height"]))
{
	$maxheight = round($height*$maxwidth/$width);
	$dst_r = ImageCreateTrueColor($maxwidth, $maxheight);
	echo "<p>Image Retaillée : Largeur de ".$maxwidth."px et hauteur de ".$maxheight."px.</p>";
	imagecopyresampled($dst_r, $img_r, 0, 0, 0, 0, $maxwidth, $maxheight, $width, $height);
	$save_fct($dst_r, $filename);
}

}

}

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [end]");

?>
