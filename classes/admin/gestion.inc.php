<?php

/**
  * $Id: gestion.inc.php 27 2011-01-13 20:58:56Z lemathoufou $
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


class _gestion extends __gestion
{


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

public function insert_form($action="", $fields=array())
{

if (!login()->perm(6))
	die("ONLY ADMIN CAN ADD $this->type");

?>
<form action="<?php echo $action; ?>" method="post" class="object_form">
<table cellspacing="0" cellpadding="1">
<?php
foreach ($this->info_detail as $name=>$info)
{
	if (is_array($fields) && isset($fields[$name]))
		$info["default"] = $fields[$name];
	elseif (!isset($info["default"]))
		$info["default"] = null;
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
	<td><input name="<?php echo $name; ?>" type="radio" value="1"<?php if ($info["default"] == "1") echo " checked"; ?> /> <?php if (isset($info["value_list"])) echo $info["value_list"][1]; else echo "OUI"; ?> <input name="<?php echo $name; ?>" type="radio" value="0"<?php if ($info["default"] == "0") echo " checked"; ?> /> <?php if (isset($info["value_list"])) echo $info["value_list"][0]; else echo "NON"; ?></td>
<?php } elseif ($info["type"] == "select") { ?>
	<td><select name="<?php echo $name; ?>" class="data_select"><option value=""></option><?
	foreach($info["select_list"] as $i=>$j)
		if ($info["default"] == $i)
			echo "<option value=\"$i\" selected>$j</option>";
		else
			echo "<option value=\"$i\">$j</option>";
	?></select></td>
<?php } elseif ($info["type"] == "fromlist") { ?>
	<td><input name="<?php echo $name; ?>" type="hidden" /><select name="<?php echo $name; ?>[]" class="data_fromlist" multiple><?
	foreach($info["select_list"] as $i=>$j)
		echo "<option value=\"$i\">$j</option>";
	?></select></td>
<?php } elseif ($info["type"] == "object_list") { $object_type = $info["object_type"]; $object_type()->retrieve_objects(); ?>
	<td><input name="<?php echo $name; ?>" type="hidden" /><select name="<?php echo $name; ?>[]" title="<?php echo $info["label"]; ?>" size="10" multiple class="data_fromlist"><?
	foreach($object_type()->list_get() as $object_id=>$object)
		echo "<option value=\"$object_id\">".$object->label()."</option>";
	?></select></td>
<?php } elseif ($info["type"] == "object") { $object_type = $info["object_type"]; $object_type()->retrieve_objects(); ?>
	<td><select name="<?php echo $name; ?>" class="data_select"><option value=""></option><?
	foreach($object_type()->list_get() as $object_id=>$object)
		echo "<option value=\"$object_id\">".$object->label()."</option>";
	?></select></td>
<?php } elseif ($info["type"] == "script") { ?>
	<td><textarea id="<?php echo $name; ?>" name="<?php echo $name; ?>" class="data_script"><?php if (isset($info["default"])) echo $info["default"]; ?></textarea></td>
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

/**
 * Display a list
 * @param array params : filtering parameters
 * @param array field_list : fields to display (automatically adds id and name)
 * TODO : Create a query function with a sort parameter, that would be easier...
 */
public function table_list($params=array(), $field_list=true, $sort=null)
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
foreach ($this->list as $id=>$object)
{
	$aff = true;
	if (is_array($params) && count($params)) foreach($params as $i=>$j)
		if ($object->info($i) !== $j)
			$aff = false;
	if ($aff)
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
}
?>
</table>
<?

}

};

class _object_gestion extends __object_gestion
{

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
	if ($field_info["type"] == "fromlist" && isset($infos[$name]) && is_array($infos[$name]))
		$infos[$name] = implode(",", $infos[$name]);
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
			elseif (file_exists($filename))
				unlink($filename);
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
	//echo "DELETE FROM `$field_info[db_table]` WHERE `$field_info[db_id]`='$this->id'";
	db()->query("DELETE FROM `$field_info[db_table]` WHERE `$field_info[db_id]`='$this->id'");
	$query_object_list = array();
	//var_dump($infos[$name]);
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
	<td><input name="<?php echo $name; ?>" type="radio" value="1"<?php if ($this->{$name}) echo " checked"; ?> /> <?php if (isset($info["value_list"])) echo $info["value_list"][1]; else echo "OUI"; ?> <input name="<?php echo $name; ?>" type="radio" value="0"<?php if (!$this->{$name}) echo " checked"; ?> /> <?php if (isset($info["value_list"])) echo $info["value_list"][0]; else echo "NON"; ?></td>
<?php } elseif ($info["type"] == "select") { ?>
	<td><select name="<?php echo $name; ?>" class="data_select"><option value=""></option><?
	foreach($info["select_list"] as $i=>$j)
		if ($i == $this->{$name})
			echo "<option value=\"$i\" selected>$j</option>";
		else
			echo "<option value=\"$i\">$j</option>";
	?></select></td>
<?php } elseif ($info["type"] == "fromlist") { ?>
	<td><input name="<?php echo $name; ?>" type="hidden" /><select name="<?php echo $name; ?>[]" class="data_fromlist" multiple><?
	foreach($info["select_list"] as $i=>$j)
		if (in_array($i, $this->{$name}))
			echo "<option value=\"$i\" selected>$j</option>";
		else
			echo "<option value=\"$i\">$j</option>";
	?></select></td>
<?php } elseif ($info["type"] == "object_list") { $object_type = $info["object_type"]; $object_type()->retrieve_objects(); ?>
	<td><input name="<?php echo $name; ?>" type="hidden" /><select name="<?php echo $name; ?>[]" title="<?php echo $info["label"]; ?>" size="10" multiple class="data_fromlist"><?
	foreach($object_type()->list_get() as $object_id=>$object)
		if (in_array($object_id, $this->{$name}))
			echo "<option value=\"$object_id\" selected>".$object->label()."</option>";
		else
			echo "<option value=\"$object_id\">".$object->label()."</option>";
	?></select></td>
<?php } elseif ($info["type"] == "object") { $object_type = $info["object_type"]; $object_type()->retrieve_objects(); ?>
	<td><select name="<?php echo $name; ?>" class="data_select"><option value=""></option><?
	foreach($object_type()->list_get() as $object_id=>$object)
		if ($object_id == $this->{$name})
			echo "<option value=\"$object_id\" selected>".$object->label()."</option>";
		else
			echo "<option value=\"$object_id\">".$object->label()."</option>";
	?></select></td>
<?php } elseif ($info["type"] == "script") { ?>
	<td><textarea id="<?php echo $name; ?>" name="<?php echo $name; ?>" class="data_script"><?php
	$filename =  $info["folder"]."/".str_replace("{name}", $this->name, $info["filename"]);
	if (file_exists($filename) && ($filesize=filesize($filename)))
		echo $content = htmlspecialchars(fread(fopen($filename,"r"),$filesize));
	?></textarea></td>
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

};


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
