<?php

/**
  * $Id$
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

<form method="get" class="page_form">
<input type="submit" value="Editer le Datamodel" />
<select name="id" onchange="this.form.submit()">
	<option value=""></option>
<?php
foreach (datamodel()->list_name_get() as $name=>$id)
{
	if (isset($_GET["id"]) && ($id==$_GET["id"]))
		echo "	<option value=\"$id\" selected>[$id] $name</option>\n";
	else
		echo "	<option value=\"$id\">[$id] $name</option>\n";
}
?>
</select>
<a href="?list">Liste</a>
<a href="?add">Ajouter</a>
</form>

<div style="padding-top: 30px">
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

// Datamodel field add
if (isset($_POST["_field_add"]))
{
	$datamodel->field_add($_POST);
}

// Datamodel field update
if (isset($_POST["_field_update"]))
{
	$datamodel->field_update($_POST["name_orig"], $_POST);
}

// Datamodel field delete
if (isset($_GET["field_delete"]))
{
	$datamodel->field_delete($_GET["field_delete"]);
}

// Datamodel db create
if (isset($_POST["_db_create"]))
{
	echo "<p>Création de la structure de base de données pour le datamodel \"<b>".$datamodel->name()."</b>\"</p>\n";
	$datamodel->db_create();
}

// Position maximale
list($pos_max)=db()->query("SELECT MAX(`pos`) FROM `_datamodel_fields` WHERE `datamodel_id`='".$datamodel->id()."'")->fetch_row();

if (empty($_GET["field_edit"])) { ?>
<form method="post"><table>
<tr>
	<td>ID :</td>
	<td><input name="id" value="<?=$datamodel->id()?>" size="6" readonly /></td>
</tr>
<tr>
	<td>Librairie :</td>
	<td><select name="library_id">
	<?php
	foreach (library()->list_detail_get() as $library)
	{
		if(is_a($datamodel->library(), "library") && $library["id"] == $datamodel->library()->id())
			echo "<option value=\"$library[id]\" selected>$library[name]</option>\n";
		else
			echo "<option value=\"$library[id]\">$library[name]</option>\n";
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
	<td><textarea name="description" style="width:100%;" rows="10"><?=$datamodel->info("description")?></textarea></td>
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
		<td>Index</td>
		<td>langue</td>
	</tr>
<?php
$field_opt_list = array("" , "key" , "required" , "calculated");

list($pos_max) = db()->query("SELECT MAX(t1.`pos`) FROM `_datamodel_fields` as t1 WHERE t1.`datamodel_id`='".$datamodel->id()."'")->fetch_row();
$query = db()->query("SELECT t1.`pos` , t1.`name` , t2.`label` , t1.`type` , t1.`defaultvalue` , t1.`opt` , t1.`query` , t1.`lang` , t1.`db_sync` FROM `_datamodel_fields` as t1 LEFT JOIN `_datamodel_fields_lang` as t2 ON t1.`datamodel_id`=t2.`datamodel_id` AND t1.`name`=t2.`fieldname` AND t2.`lang_id`=".SITE_LANG_ID." WHERE t1.`datamodel_id`='".$datamodel->id()."' ORDER BY t1.`pos`");
while ($field=$query->fetch_assoc())
{
	//print_r($field);
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
		foreach (data()->list_detail_get() as $j)
		{
			if ($field["type"]==$i)
				echo "<option value=\"$j[name]\" selected>$j[label]</option>\n";
			else
				echo "<option value=\"$j[name]\">$j[label]</option>\n";
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
		<td><select name="query">
			<option value="0"<?php if (!$field["query"]) echo " selected"; ?>>NON</option>
			<option value="1"<?php if ($field["query"]) echo " selected"; ?>>OUI</option>
		</select></td>
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
		<td><input readonly value="<?=$field["type"]?>" /></td>
		<td><?php if ($field["defaultvalue"] === null) { ?><i>NULL</i><?php } else { ?><textarea readonly rows="1"><?=$field["defaultvalue"]?></textarea><?php } ?></td>
		<td><input readonly value="<?=$field["opt"]?>" size="10" /></td>
		<td><input readonly value="<?php if ($field["query"]) echo "OUI"; else echo "NON"; ?>"<?php if ($field["query"]) echo " style=\"color:blue;\""; ?> size="3" /></td>
		<td><input readonly value="<?php if ($field["lang"]) echo "OUI"; else echo "NON"; ?>" size="3" /></td>
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
		foreach (data()->list_detail_get() as $j)
		{
			echo "<option value=\"$j[name]\">$j[label]</option>\n";
		}
		?></select></td>
		<td><textarea name="defaultvalue"></textarea><input type="checkbox" name="defaultvalue_null" /></td>
		<td><select name="opt"><?php
		foreach($field_opt_list as $opt)
		{
			echo "<option value=\"$opt\">$opt</option>\n";
		}
		?></select></td>
		<td><select name="query">
			<option value="0">NON</option>
			<option value="1">OUI</option>
		</select></td>
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
	foreach (library()->list_detail_get() as $id=>$library)
	{
		echo "<option value=\"$id\">$library[name]</option>";
	}
	?></select></td>
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
</div>

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
