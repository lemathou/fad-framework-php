<?

/**
  * $Id: datamodel.inc.php 58 2009-03-03 15:47:37Z mathieu $
  * 
  * Copyright 2008 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */

if (!defined("ADMIN_OK"))
	die("ACCES NON AUTORISE");

if (isset($_POST["_datamodel_insert"]))
{
	datamodel()->add($_POST);
}

if (isset($_POST["_datamodel_delete"]))
{
	datamodel()->del($_POST["_datamodel_delete"]);
}

?>

<form method="get">
<select name="id" onchange="this.form.submit()"><?php
foreach (datamodel()->list_name_get() as $name=>$id)
{
	if (isset($_GET["id"]) && ($id==$_GET["id"]))
		echo "<option value=\"$id\" selected>[$id] $name</option>";
	else
		echo "<option value=\"$id\">[$id] $name</option>";
}
?></select>
<input type="submit" value="Editer" />
</form>

<?php

if (isset($_GET["id"]) && datamodel()->exists($id=$_GET["id"]))
{

$datamodel = datamodel($id);

list($db_sync) = db()->query("SELECT db_sync FROM _datamodel WHERE id='$id'")->fetch_row();

// Datamodel update
if (isset($_POST["_datamodel_update"]))
{
	$datamodel->update($_POST);
}

// Datamodel field update
if (isset($_POST["_field_update"]))
{
	if (isset($_POST["defaultvalue_null"]))
		$defaultvalue = "NULL";
	elseif (isset($_POST["defaultvalue"]))
		$defaultvalue = "'".db()->string_escape($_POST["defaultvalue"])."'";
	else
		$defaultvalue = "''";
	if (!isset($_POST["defaultvalue"]))
		$_POST["defaultvalue"] = "";
	$query_str = "UPDATE _datamodel_fields SET name='".db()->string_escape($_POST["name"])."', type='".db()->string_escape($_POST["type"])."', defaultvalue=$defaultvalue, opt='".db()->string_escape($_POST["opt"])."', lang='".db()->string_escape($_POST["lang"])."' WHERE datamodel_id='".$_GET["id"]."' AND name='".$_POST["name_orig"]."'";
	//echo "<br />$query_str";
	db()->query($query_str);
	$query_str = "UPDATE _datamodel_fields_lang SET fieldname='".db()->string_escape($_POST["name"])."', label='".db()->string_escape($_POST["label"])."' WHERE datamodel_id='".$_GET["id"]."' AND lang_id='".SITE_LANG_ID."' AND fieldname='".$_POST["name_orig"]."'";
	//echo "<br />$query_str";
	db()->query($query_str);
	$query_str = "DELETE FROM _datamodel_fields_opt WHERE fieldname='".db()->string_escape($_POST["name"])."' AND datamodel_id='".$_GET["id"]."'";
	//echo "<br />$query_str";
	db()->query($query_str);
	// Création objet par défaut, pour voir quelles options sont réellement à sauver (<> opt par défaut)
	$fieldtype = "data_$_POST[type]";
	$datafield = new $fieldtype("test", null, null);
	if (isset($_POST["optlist"]))
	{
		foreach($_POST["optlist"] as $type=>$list)
		{
			$field_type = $datafield->{$type."_opt_list_get"}();
			foreach ($list as $i=>$j)
			{
				if (!isset($field_type[$i]) || $field_type[$i] !== json_decode($j))
				{
					$datafield->{$type."_opt_set"}($i, json_decode($j));
					$finalvalue = json_encode($datafield->{$type."_opt"}[$i]);
					$query_str = "INSERT INTO _datamodel_fields_opt ( datamodel_id ,  fieldname , opt_type , opt_name , opt_value ) VALUES ( '".$_GET["id"]."' , '".db()->string_escape($_POST["name"])."' , '$type' , '$i' , '".db()->string_escape($j)."' )";
					db()->query($query_str);
				}
			}
		}
	}
	// Gestion du repositionnement
	if ($_POST["pos"] < $_POST["pos_orig"])
	{
		db()->query("UPDATE _datamodel_fields SET pos=pos+1 WHERE datamodel_id='".$_GET["id"]."' AND pos >= ".($_POST["pos"])." AND pos < ".($_POST["pos_orig"]));
		db()->query("UPDATE _datamodel_fields SET pos=".($_POST["pos"])." WHERE datamodel_id='".$_GET["id"]."' AND name='".($_POST["name"])."'");
	}
	elseif ($_POST["pos_orig"] < $_POST["pos"])
	{
		db()->query("UPDATE _datamodel_fields SET pos=pos-1 WHERE datamodel_id='".$_GET["id"]."' AND pos > ".($_POST["pos_orig"])." AND pos <= ".($_POST["pos"]));
		db()->query("UPDATE _datamodel_fields SET pos=".($_POST["pos"])." WHERE datamodel_id='".$_GET["id"]."' AND name='".($_POST["name"])."'");
	}
	// Mise à jour du champ dans la table associée
	if ($db_sync)
	{
		$datamodel->query_info();
		if (isset($_POST["lang"]) && $_POST["lang"])
			db()->field_update($datamodel->db_opt("table")."_lang", $_POST["name_orig"], $_POST["name"], $datafield->db_field_create());
		else
			db()->field_update($datamodel->db_opt("table"), $_POST["name_orig"], $_POST["name"], $datafield->db_field_create());
	}
}
// Datamodel field add
if (isset($_POST["_field_add"]))
{
	if (isset($_POST["defaultvalue_null"]))
		$defaultvalue = "NULL";
	else
		$defaultvalue = "'".db()->string_escape($_POST["defaultvalue"])."'";
	list($pos_max)=db()->query("SELECT MAX(pos)+1 FROM _datamodel_fields WHERE datamodel_id='".$datamodel->id()."'")->fetch_row();
	if (!$pos_max)
		$pos_max = 1;
	db()->query("INSERT INTO _datamodel_fields ( datamodel_id , pos, name , type , defaultvalue , opt , lang ) VALUES ( '".$_POST["id"]."' ,'".($pos_max)."' ,  '".db()->string_escape($_POST["name"])."' , '".db()->string_escape($_POST["type"])."' , $defaultvalue , '".db()->string_escape($_POST["opt"])."' , '".db()->string_escape($_POST["lang"])."' )");
	db()->query("INSERT INTO _datamodel_fields_lang ( datamodel_id , lang_id , fieldname , label ) VALUES ( '".$_POST["id"]."' , '".SITE_LANG_ID."' , '".db()->string_escape($_POST["name"])."' , '".db()->string_escape($_POST["label"])."' )");
	// Gestion du repositionnement
	if ($_POST["pos"] < $pos_max)
	{
		db()->query("UPDATE _datamodel_fields SET pos=pos+1 WHERE datamodel_id='".$_GET["id"]."' AND pos >= ".($_POST["pos"]));
		db()->query("UPDATE _datamodel_fields SET pos=".($_POST["pos"])." WHERE datamodel_id='".$_GET["id"]."' AND name='".($_POST["name"])."'");
	}
	// Insertion du champ dans la tables associée
	if ($db_sync)
	{
		$datamodel->query_info();
		if (isset($_POST["lang"]) && $_POST["lang"])
			db()->field_create($datamodel->db_opt("table")."_lang", $_POST["name"], $datamodel->{$_POST["name"]}->db_field_create());
		else
			db()->field_create($datamodel->db_opt("table"), $_POST["name"], $datamodel->{$_POST["name"]}->db_field_create());
	}
}
// Datamodel field move UP
if (isset($_GET["field_move_up"]))
{
	$query = db()->query("SELECT t1.`name`, t1.`type`, t1.`opt`, t1.`lang` FROM `_datamodel_fields` as t1 WHERE t1.`datamodel_id`='".$_GET["id"]."' AND t1.`pos`='".($_GET["field_move_up"])."'");
	if ($field=$query->fetch_assoc())
	{
		db()->query("UPDATE _datamodel_fields SET pos='0' WHERE datamodel_id='".$_GET["id"]."' AND pos='".($_GET["field_move_up"]-1)."'");
		db()->query("UPDATE _datamodel_fields SET pos=pos-1 WHERE datamodel_id='".$_GET["id"]."' AND pos='".$_GET["field_move_up"]."'");
		db()->query("UPDATE _datamodel_fields SET pos='".($_GET["field_move_up"])."' WHERE datamodel_id='".$_GET["id"]."' AND pos='0'");
		// Gérer mieux les positionnements dans les tables surtout avec les langues !
		if ($db_sync)
		{
			$datamodel->query_info();
			if ($field["lang"])
			{
				$position = "";
				db()->field_update($datamodel->db_opt("table")."_lang", $field["name"], $datamodel->{$field["name"]}->db_field_create(), $position);
			}
			else
			{
				if (($_GET["field_move_up"]-1) == 1)
				{
					$position = "FIRST";
				}
				else
				{
					list($position_after)=db()->query("SELECT `name` FROM `_datamodel_fields` WHERE `datamodel_id`='".$_GET["id"]."' AND `pos`='".($_GET["field_move_up"]-2)."'")->fetch_row();
					$position = "AFTER `$position_after`";
				}
				db()->field_update($datamodel->db_opt("table"), $field["name"], $field["name"], $datamodel->{$field["name"]}->db_field_create(), $position);
			}
		}
	}
}

if (isset($_GET["field_delete"]))
{
	db()->query("DELETE FROM _datamodel_fields WHERE name='".db()->string_escape($_GET["field_delete"])."' AND datamodel_id='".$datamodel->id()."'");
	db()->query("DELETE FROM _datamodel_fields_lang WHERE fieldname='".db()->string_escape($_GET["field_delete"])."' AND datamodel_id='".$datamodel->id()."'");
	db()->query("DELETE FROM _datamodel_fields_opt WHERE fieldname='".db()->string_escape($_GET["field_delete"])."' AND datamodel_id='".$datamodel->id()."'");
	if ($db_sync)
	{
		$datamodel->query_info();
		db()->field_delete($datamodel->db_opt("table"), $_GET["field_delete"]);
	}
}

if (isset($_POST["_db_create"]))
{
	echo "<p>Création de la structure de base de données pour le datamodel \"<b>".$datamodel->name()."</b>\"</p>\n";
	$datamodel->query_info();
	$datamodel->db_create();
	db()->query("UPDATE _datamodel SET db_sync='1' WHERE id='".$datamodel->id()."'");
	db()->query("UPDATE _datamodel_fields SET db_sync='1' WHERE datamodel_id='".$datamodel->id()."'");
}

// Position maximale
list($pos_max)=db()->query("SELECT pos FROM _datamodel_fields WHERE datamodel_id='".$datamodel->id()."' ORDER BY pos DESC LIMIT 1")->fetch_row();

?>

<hr />

<p><a href="?list">Retour à la liste</a></p>

<h2>Edition du datamodel : <?=$datamodel->name()?></h2>

<?php if (empty($_GET["field_edit"])) { ?>
<form method="post"><table>
<tr>
	<td>ID :</td>
	<td><input name="id" value="<?=$datamodel->id()?>" size="6" readonly /></td>
</tr>
<tr>
	<td>Librairie :</td>
	<td><select name="library_id">
	<?php
	foreach (library()->list_get() as $library)
	{
		if(is_a($datamodel->library(), "library") && $library->id == $datamodel->library()->id)
			echo "<option value=\"$library->id\" selected>$library->name</option>\n";
		else
			echo "<option value=\"$library->id\">$library->name</option>\n";
	}
	?>
	</select></td>
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

<form method="post" action="?id=<?=$datamodel->id()?>">
<h3>Liste des champs :</h3>
<table border="0" cellspacing="2" cellpadding="2">
	<tr style="font-weight: bold;">
		<td>&nbsp;</td>
		<td>Pos</td>
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

list($pos_max) = db()->query("SELECT MAX(t1.`pos`) FROM `_datamodel_fields` as t1 WHERE t1.`datamodel_id`='".$datamodel->id()."'")->fetch_row();
$query = db()->query("SELECT t1.`pos` , t1.`name` , t2.`label` , t1.`type` , t1.`defaultvalue` , t1.`opt` , t1.`lang` , t1.`db_sync` FROM `_datamodel_fields` as t1 LEFT JOIN `_datamodel_fields_lang` as t2 ON t1.`datamodel_id`=t2.`datamodel_id` AND t1.`name`=t2.`fieldname` AND t2.`lang_id`=".SITE_LANG_ID." WHERE t1.`datamodel_id`='".$datamodel->id()."' ORDER BY t1.`pos`");
while ($field=$query->fetch_assoc())
{
	if (isset($_GET["field_edit"]) && $field["name"]==$_GET["field_edit"])
	{
		$datatype = "data_$field[type]";
		$datafield = new $datatype($field["name"], $field["defaultvalue"], $field["label"]);
		$query_opt = db()->query("SELECT opt_type, opt_name, opt_value FROM _datamodel_fields_opt WHERE datamodel_id='".$datamodel->id()."' AND fieldname='$field[name]'");
		while ($opt=$query_opt->fetch_assoc())
		{
			$method="$opt[opt_type]_opt_set";
			$datafield->$method($opt["opt_name"], json_decode($opt["opt_value"], true));
			//echo "<br />".json_decode($opt["opt_value"], true);
		}
	?>
	<tr>
		<td colspan="9"><hr /></td>
	</tr>
	<tr>
		<td><input name="_field_update" type="submit" onclick="return(confirm('Êtes-vous certain de vouloir mettre à jour ce champ ?'))" value="MàJ" /></td>
		<td><select name="pos">
		<?php
		for ($i=1;$i<=$pos_max;$i++)
			if ($i==$field["pos"])
				echo "<option value=\"$i\" selected>$i</option>\n";
			else
				echo "<option value=\"$i\">$i</option>\n";
		?>
		</select><input type="hidden" name="pos_orig" value="<?=$field["pos"]?>" /></td>
		<td><input name="name" value="<?=$field["name"]?>" /><input type="hidden" name="name_orig" value="<?=$field["name"]?>" /></td>
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
			<td valign="top">
			<select id="<?=$type?>_opt_list"><option value="">-- Choisir --</option><?php
			$f = $type."_opt_list_get";
			foreach($datafield->$f() as $i=>$j)
			{
				$list[$i] = $j;
			}
			foreach(data::${"${type}_opt_list"} as $i)
			{
				if (!isset($list[$i]))
					echo "<option>$i</option>\n";
			}
			?></select><input type="button" value="ADD" onclick="opt_add('<?=$type?>',document.getElementById('<?=$type?>_opt_list').value)" />
			<div id="opt_<?=$type?>">
			<?php
			foreach($list as $i=>$j) if (in_array($i, data::${"${type}_opt_list"}))
			{
				echo "<div id=\"opt_".$type."_$i\">\n";
				echo "<p style=\"margin-bottom: 0px;\">$i <a href=\"javascript:;\" onclick=\"opt_del('$type','$i')\" style=\"color:red;\">X</a></p> <p style=\"margin: 0px;\"><textarea name=\"optlist[$type][$i]\">".json_encode($j)."</textarea></p>\n";
				echo "</div>\n";
			}
			?>
			</div>
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
		<td><a href="?id=<?=$datamodel->id()?>&field_delete=<?=$field["name"]?>" onclick="return(confirm('Êtes-vous certain de vouloir supprimer ce champ ?'))" style="color:red;border:1px red solid;">X</a><?php if (false && $field["pos"]>1) { ?> <a href="?id=<?=$datamodel->id()?>&field_move_up=<?=$field["pos"]?>">U</a><?} ?></td>
		<td><?=$field["pos"]?></td>
		<td><a href="?id=<?=$datamodel->id()?>&field_edit=<?=$field["name"]?>"><?=$field["name"]?></a></td>
		<td><input readonly value="<?=$field["label"]?>" /></td>
		<td><input readonly value="<?=$field_type_list[$field["type"]]?>" /></td>
		<td><?php if ($field["defaultvalue"] === null) { ?><i>NULL</i><?php } else { ?><input readonly value="<?=$field["defaultvalue"]?>" /><?php } ?></td>
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
		<td><select name="pos"><?
		for  ($pos=1;$pos<=$pos_max;$pos++)
		{
			echo "<option value=\"$pos\">$pos</option>\n";
		}
		echo "<option value=\"".($pos_max+1)."\" selected>".($pos_max+1)."</option>\n";
		?></select></td>
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

<hr />
<form method="post" action="?id=<?=$datamodel->id()?>">
<p><input type="submit" name="_db_create" value="Créer les tables associées en base de donnée" /></p>
</form>

<?php
}
else
{
?>

<hr />

<form method="post" action="?add">
<p>Ajouter un datamodel</p>
<table>
<tr>
	<td>Name :</td>
	<td><input name="name" /></td>
</tr>
<tr>
	<td>Label :</td>
	<td><input name="label" /></td>
</tr>
<tr>
	<td>Librairie :</td>
	<td><select name="library_id"><?php
	foreach (library()->list_get() as $library)
	{
		echo "<option value=\"$library->id\">$library->name</option>\n";
	}
	?>
	</select></td>
</tr>
<tr>
	<td>Table :</td>
	<td><input name="table" /></td>
</tr>
<tr>
	<td>Description :</td>
	<td><textarea name="description"></textarea></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><input name="_datamodel_insert" "type="submit" value="Ajouter" /></td>
</tr>
</table>
</form>
<?php
}
?>

<script type="text/javascript">
function opt_add(type, name)
{
	if (type && name && !document.getElementById('opt_'+type+'_'+name))
	{
		$("#opt_"+type).append('<div id="opt_'+type+'_'+name+'"><p style="margin-bottom: 0px;">'+name+' <a href="javascript:;" onclick="opt_del(\''+type+'\',\''+name+'\')" style="color:red;">X</a></p> <p style="margin-top: 0px;"><textarea name="optlist['+type+']['+name+']"></textarea></p>');
	}
}
function opt_del(type, name)
{
	$("#opt_"+type+'_'+name).remove();
}
</script>
