<?

/**
  * $Id: datamodel.inc.php 58 2009-03-03 15:47:37Z mathieu $
  * 
  * Copyright 2008 Mathieu Moulin - iProspective - lemathou@free.fr
  * 
  * This file is part of FTNGroupWare.
  * 
  */

if (!defined("ADMIN_OK"))
	die("ACCES NON AUTORISE");

if (isset($_POST["_datamodel_insert"]))
{
	db()->query("INSERT INTO _datamodel ( `name` , `table` ) VALUES ( '".db()->string_escape($_POST["name"])."' , '".db()->string_escape($_POST["table"])."' )");
	$id = db()->last_id();
	db()->query("INSERT INTO _datamodel_lang ( `id` , `lang_id` , `label` , `description` ) VALUES ( '$id' , '".SITE_LANG_ID."' , '".db()->string_escape($_POST["label"])."' , '".db()->string_escape($_POST["description"])."' )");
}

if (isset($_POST["_datamodel_update"]))
{
	db()->query("UPDATE _datamodel SET `name`='".db()->string_escape($_POST["name"])."' , `table`='".db()->string_escape($_POST["table"])."' WHERE `id`='".$_POST["id"]."'");
	db()->query("UPDATE _datamodel_lang SET `label`='".db()->string_escape($_POST["label"])."' , `description`='".db()->string_escape($_POST["description"])."' WHERE `id`='".$_POST["id"]."' AND `lang_id`='".SITE_LANG_ID."'");
}

if (isset($_POST["_field_update"]))
{
	if (isset($_POST["defaultvalue_null"]))
		$defaultvalue = "NULL";
	else
		$defaultvalue = "'".db()->string_escape($_POST["defaultvalue"])."'";
	db()->query("UPDATE _datamodel_fields SET name='".db()->string_escape($_POST["name"])."' , type='".db()->string_escape($_POST["type"])."' , defaultvalue=$defaultvalue , opt='".db()->string_escape($_POST["opt"])."' , lang='".db()->string_escape($_POST["lang"])."' WHERE datamodel_id='".$_POST["id"]."' AND name='".$_POST["name_orig"]."'");
	db()->query("UPDATE _datamodel_fields_lang SET fieldname='".db()->string_escape($_POST["name"])."' , label='".db()->string_escape($_POST["label"])."' WHERE datamodel_id='".$_POST["id"]."' AND lang_id='".SITE_LANG_ID."' AND fieldname='".$_POST["name_orig"]."'");
	db()->query("UPDATE _datamodel_fields_opt SET fieldname='".db()->string_escape($_POST["name"])."' WHERE datamodel_id='".$_POST["id"]."' AND fieldname='".$_POST["name_orig"]."'");
	db()->query("DELETE FROM _datamodel_fields_opt WHERE fieldname='".db()->string_escape($_POST["name"])."' AND datamodel_id='".$_POST["id"]."'");
	if (isset($_POST["opt"]))
	{
		// Création objet par défaut, pour voir quelles options sont réellement à sauver (<> opt par défaut)
		$datatype = "data_$_POST[type]";
		$field = new $datatype("test", null);
		foreach($_POST["opt"] as $type=>$list)
		{
			$field_type = $field->{$type."_opt_list_get"}();
			foreach ($list as $i=>$j)
			{
				if (!isset($field_type[$i]) || $field_type[$i] != json_decode($j))
				{
					db()->query("INSERT INTO _datamodel_fields_opt ( datamodel_id ,  fieldname , opt_type , opt_name , opt_value ) VALUES ( '".$_POST["id"]."' , '".db()->string_escape($_POST["name"])."' , '$type' , '$i' , '".db()->string_escape($j)."' )");
				}
			}
		}
	}
	list($datamodel_name) = db()->query("SELECT name FROM _datamodel WHERE id='".$_POST["id"]."'")->fetch_row();
	$datamodel = datamodel($datamodel_name);
	db()->field_update($datamodel->db_opt("table"), $_POST["name_orig"], $_POST["name"], $datamodel->{$_POST["name"]}->db_field_create());
}

if (isset($_POST["_field_add"]))
{
	if (isset($_POST["defaultvalue_null"]))
		$defaultvalue = "NULL";
	else
		$defaultvalue = "'".db()->string_escape($_POST["defaultvalue"])."'";
	db()->query("INSERT INTO _datamodel_fields ( datamodel_id , name , type , defaultvalue , opt , lang ) VALUES ( '".$_POST["id"]."' , '".db()->string_escape($_POST["name"])."' , '".db()->string_escape($_POST["type"])."' , $defaultvalue , '".db()->string_escape($_POST["opt"])."' , '".db()->string_escape($_POST["lang"])."' )");
	db()->query("INSERT INTO _datamodel_fields_lang ( datamodel_id , lang_id , fieldname , label ) VALUES ( '".$_POST["id"]."' , '".SITE_LANG_ID."' , '".db()->string_escape($_POST["name"])."' , '".db()->string_escape($_POST["label"])."' )");
}

if (isset($_GET["datamodel"]) && is_a($datamodel=datamodel($_GET["datamodel"]), "datamodel") && isset($_GET["field_delete"]))
{
	db()->query("DELETE FROM _datamodel_fields WHERE name='".db()->string_escape($_GET["field_delete"])."' AND datamodel_id='".$datamodel->id()."'");
	db()->query("DELETE FROM _datamodel_fields_lang WHERE fieldname='".db()->string_escape($_GET["field_delete"])."' AND datamodel_id='".$datamodel->id()."'");
	db()->query("DELETE FROM _datamodel_fields_opt WHERE fieldname='".db()->string_escape($_GET["field_delete"])."' AND datamodel_id='".$datamodel->id()."'");
}

if (isset($_GET["datamodel"]) && is_a($datamodel=datamodel($_GET["datamodel"]), "datamodel") && isset($_GET["db_create"]))
{
	echo "<p>Création de la structure de base de données pour le datamodel \"<b>".$datamodel->name()."</b>\"</p>\n";
	$datamodel->db_create();
	db()->query("UPDATE _datamodel SET db_sync='1' WHERE datamodel_id='".$datamodel->id()."'");
	db()->query("UPDATE _datamodel_fields SET db_sync='1' WHERE datamodel_id='".$datamodel->id()."'");
}

?>

<form method="post">
Nom : <input name="name" /> Label : <input name="label" /> Table : <input name="table" /> Description : <input name="description" /> <input name="_datamodel_insert" "type="submit" value="Ajouter" />
</form>

<form method="get">
<select name="datamodel"><?php
foreach (datamodel()->list_get() as $name=>$datamodel)
{
	if (isset($_GET["datamodel"]) && $name == $_GET["datamodel"])
		echo "<option value=\"$name\" selected>$name</option>";
	else
		echo "<option value=\"$name\">$name</option>";
}
?></select>
<input type="submit" value="Editer" />
</form>

<hr />

<?php

if (isset($_GET["datamodel"]) && is_a($datamodel=datamodel($_GET["datamodel"]), "datamodel"))
{

?>

<h2>Edition du datamodel : <?=$datamodel->name()?></h2>

<?php if (empty($_GET["field_edit"])) { ?>
<form method="post"><table>
<tr>
	<td>ID :</td>
	<td><input name="id" value="<?=$datamodel->id()?>" size="6" readonly /></td>
</tr>
<tr>
	<td>Name :</td>
	<td><input name="name" value="<?=$datamodel->name()?>" /></td>
</tr>
<tr>
	<td>Label :</td>
	<td><input name="label" value="<?=$datamodel->label()?>" size="64" /></td>
</tr>
<tr>
	<td>Description :</td>
	<td><textarea name="description" style="width:100%;" rows="10"><?php echo array_pop(db()->query("SELECT description FROM _datamodel_lang WHERE id = ".$datamodel->id()." AND lang_id=".SITE_LANG_ID)->fetch_row()) ?></textarea></td>
</tr>
<tr>
	<td>Table (en BDD) :</td>
	<td><input name="table" value="<?=$datamodel->db_opt("table")?>" /></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><input type="submit" name="_datamodel_update" value="Mettre à jour" onclick="return(confirm('Êtes-vous certain de vouloir mettre à jour ce datamodel ?'))" /></td>
</tr>
</table></form>
<?php } ?>

<form method="post" action="?datamodel=<?=$datamodel->name()?>">
<h3>Liste des champs :</h3>
<table border="0" cellspacing="2" cellpadding="2">
	<tr style="font-weight: bold;">
		<td>&nbsp;</td>
		<td>Name</td>
		<td>Label</td>
		<td>Type</td>
		<td>Defaultvalue / null</td>
		<td>Option</td>
		<td>Alt langue</td>
		<td>DB Sync</td>
	</tr>
<?php
$field_opt_list = array("" , "key" , "required" , "calculated");
$query_type = db()->query("SELECT t1.name , t2.title FROM _datatype as t1 LEFT JOIN _datatype_lang as t2 ON t1.id=t2.datatype_id AND t2.lang_id=".SITE_LANG_ID." ORDER BY t2.title");
while (list($i,$j) = $query_type->fetch_row())
{
	$field_type_list[$i] = $j;
}

$query = db()->query("SELECT t1.`name` , t2.`label` , t1.`type` , t1.`defaultvalue` , t1.`opt` , t1.`lang` , t1.`db_sync` FROM `_datamodel_fields` as t1 LEFT JOIN `_datamodel_fields_lang` as t2 ON t1.`datamodel_id`=t2.`datamodel_id` AND t1.`name`=t2.`fieldname` AND t2.`lang_id`=".SITE_LANG_ID." WHERE t1.`datamodel_id`='".$datamodel->id()."'");
while ($field=$query->fetch_assoc())
{
	if (isset($_GET["field_edit"]) && $field["name"]==$_GET["field_edit"])
	{
	?>
	<tr>
		<td colspan="8"><hr /></td>
	</tr>
	<tr>
		<td><input name="_field_update" type="submit" onclick="return(confirm('Êtes-vous certain de vouloir mettre à jour ce champ ?'))" value="MàJ" /></td>
		<td><input name="name" value="<?=$field["name"]?>" /><input type="hidden" name="name_orig" value="<?=$field["name"]?>" /><input type="hidden" name="id" value="<?=$datamodel->id()?>" /></td>
		<td><input name="label" value="<?=$field["label"]?>" /></td>
		<td><select name="type"><?php
		foreach ($field_type_list as $i=>$j)
		{
			if ($field["type"]==$i)
				echo "<option value=\"$i\" selected>$j</option>\n";
			else
				echo "<option value=\"$i\">$j</option>\n";
		}
		?></select></td>
		<td><textarea name="defaultvalue"><?php echo $field["defaultvalue"]; ?></textarea><input type="checkbox" name="defaultvalue_null" <?php if ($field["defaultvalue"]===null) echo "checked"; ?> /></td>
		<td><select name="opt"><?php
		foreach($field_opt_list as $opt)
		{
			if($opt == $field["opt"])
				echo "<option value=\"$opt\" selected>$opt</option>\n";
			else
				echo "<option value=\"$opt\">$opt</option>\n";
		}
		?></select></td>
		<td><select name="lang">
			<option value="0"<?php if (!$field["lang"]) echo " selected"; ?>>NON</option>
			<option value="1"<?php if ($field["lang"]) echo " selected"; ?>>OUI</option>
		</select></td>
		<td><input name="_field_sync" type="submit" onclick="return(confirm('Êtes-vous certain de vouloir mettre à jour ce champ ?'))" value="Sync" /></td>
	</tr>
	<tr> <td colspan="8"><hr /></td> </tr>
	<tr>
		<td colspan="8"><?php
		// Récupération des valeurs des champs par classe défaut puis reparamétrés
		$opt = array
		(
			"structure"=>array(),
			"db"=>array(),
			"disp"=>array(),
			"form"=>array(),
		);
		?>
		<table cellspacing="0" cellpadding="0" width="100%">
		<tr>
			<td>structure_opt</td>
			<td>db_opt</td>
			<td>disp_opt</td>
			<td>form_opt</td>
		</tr>
		<tr>
		<?php foreach ($opt as $type=>$list) { ?>
			<td style="padding:0px 5px;" valign="top">
			<select id="<?=$type?>_opt_list"><option value="">-- Choisir --</option><?php
			$f = $type."_opt_list_get";
			foreach($datamodel->{$field["name"]}->$f() as $i=>$j)
				$list[$i] = $j;
			foreach(data::${"${type}_opt_list"} as $i)
			{
				if (!isset($list[$i]))
					echo "<option>$i</option>\n";
			}
			?></select><input type="button" value="ADD" />
			<table>
			<?php
			foreach($list as $i=>$j)
			{
				echo "<tr>\n";
				echo "	<td>$i :</td> <td><textarea name=\"opt[$type][$i]\">".json_encode($j)."</textarea></td>\n";
				echo "</tr>\n";
			}
			?>
			</table>
			</td>
		<?php } ?>
		</tr></table>
		</td>
	</tr>
	<tr>
		<td colspan="8"><hr /></td>
	</tr>
	<?php
	}
	else
	{
	?>
	<tr>
		<td><a href="?datamodel=<?=$datamodel->name()?>&field_delete=<?=$field["name"]?>" onclick="return(confirm('Êtes-vous certain de vouloir supprimer ce champ ?'))" style="color:red;border:1px red solid;">X</a></td>
		<td><a href="?datamodel=<?=$datamodel->name()?>&field_edit=<?=$field["name"]?>"><?=$field["name"]?></a></td>
		<td><input readonly value="<?=$field["label"]?>" /></td>
		<td><input readonly value="<?=$field_type_list[$field["type"]]?>" /></td>
		<td><input readonly value="<?=$field["defaultvalue"]?>" /></td>
		<td><input readonly value="<?=$field["opt"]?>" size="10" /></td>
		<td><input readonly value="<?php if ($field["lang"]) echo "OUI"; else echo "NON"; ?>" size="3" /></td>
		<td><input readonly value="<?php if ($field["db_sync"]) echo "OUI"; else echo "NON"; ?>" size="3" /></td>
	</tr>
	<?php
	}
}
if (!isset($_GET["field_edit"])) {
?>
	<tr>
		<td><input name="_field_add" type="submit" onclick="return(confirm('Êtes-vous certain de vouloir ajouter ce champ ?'))" value="Add" /></td>
		<td><input name="name" value="" /><input type="hidden" name="id" value="<?=$datamodel->id()?>" /></td>
		<td><input name="label" value="" /></td>
		<td><select name="type"><?php
		foreach ($field_type_list as $i=>$j)
		{
			echo "<option value=\"$i\">$j</option>\n";
		}
		?></select></td>
		<td><textarea name="defaultvalue"></textarea><input type="checkbox" name="defaultvalue_null" /></td>
		<td><select name="opt"><?php
		foreach($field_opt_list as $opt)
		{
			echo "<option value=\"$opt\">$opt</option>\n";
		}
		?></select></td>
		<td><select name="lang">
			<option value="0">NON</option>
			<option value="1">OUI</option>
		</select></td>
	</tr>
<?php } ?>
</table></form>

<a href="?datamodel=<?=$datamodel->name()?>&db_create">Créer les tables associées en base de donnée</a>

<?php
}

?>