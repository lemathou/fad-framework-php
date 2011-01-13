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

$_type = "datamodel";
$_label = "Datamodel";

if (isset($_POST["_insert"]))
{
	$_type()->add($_POST);
}

if (isset($_POST["_update"]) && isset($_POST["id"]) && $_type()->exists($_POST["id"]))
{
	$_type($_POST["id"])->update($_POST);
}

if (isset($_POST["_delete"]) && $_type()->exists($_POST["_delete"]))
{
	$_type()->del($_POST["_delete"]);
}

?>
<form method="get" class="page_form">
<input type="submit" value="<?php echo $_label; ?>" />
<select name="id" onchange="this.form.submit()">
	<option value=""></option>
<?php
foreach ($_type()->list_detail_get() as $id=>$info)
{
	if (isset($_GET["id"]) && ($id==$_GET["id"]))
		echo "	<option value=\"$id\" selected>[$id] $info[label]</option>\n";
	else
		echo "	<option value=\"$id\">[$id] $info[label]</option>\n";
}
?>
</select>
<a href="?list">Liste</a>
<a href="?add">Ajouter</a>
</form>

<?php

$_type_list = $_type()->list_detail_get();

$library_list = library()->list_detail_get();

if (isset($_GET["id"]) && $_type()->exists($id=$_GET["id"]))
{

?>
<div class="admin_menu admin_submenu">
	<a href="javascript:;" name="update_form" onclick="admin_submenu(this.name)" <?php if (empty($_GET["field_edit"])) echo "class=\"selected\""; ?>>Formulaire</a>
	<a href="javascript:;" name="field_list" onclick="admin_submenu(this.name)" <?php if (!empty($_GET["field_edit"])) echo "class=\"selected\""; ?>>Champs de donnée</a>
</div>

<div id="update_form" class="subcontents"<?php if (!empty($_GET["field_edit"])) echo " style=\"display:none;\""; ?>>
<?php
$_type($id)->update_form();
?>
</div>

<div id="field_list" class="subcontents"<?php if (empty($_GET["field_edit"])) echo " style=\"display:none;\""; ?>>
<?php
$object = $_type($id);

list($db_sync) = db()->query("SELECT db_sync FROM _datamodel WHERE id='$id'")->fetch_row();

// Datamodel field add
if (isset($_POST["_field_add"]))
{
	$object->field_add($_POST);
}

// Datamodel field update
if (isset($_POST["_field_update"]))
{
	$object->field_update($_POST["name_orig"], $_POST);
}

// Datamodel field delete
if (isset($_GET["field_delete"]))
{
	$object->field_delete($_GET["field_delete"]);
}

// Datamodel db create
if (isset($_POST["_db_create"]))
{
	echo "<p>Création de la structure de base de données pour le datamodel \"<b>".$object->name()."</b>\"</p>\n";
	$object->db_create();
}

// Position maximale
$pos_max = count($object->fields());

$data_list_detail = data()->list_detail_get();

?>

<form method="post" action="?id=<?=$id?>" class="object_list_form">
<h3>Liste des champs :</h3>
<table border="0" cellspacing="0" cellpadding="1" width="100%">
	<tr class="label">
		<td>&nbsp;</td>
		<td>Pos</td>
		<td>Name</td>
		<td>Label</td>
		<td>Type</td>
		<td>Default value (JSON)</td>
		<td>Option</td>
		<td>Index</td>
		<td>langue</td>
	</tr>
<?php
$field_opt_list = array("" , "key" , "required" , "calculated");
$nb = 0;
foreach ($object->fields() as $name=>$field)
{
	$nb++;
	if (isset($_GET["field_edit"]) && $name==$_GET["field_edit"])
	{
	?>
	<tr>
		<td colspan="9"><hr /></td>
	</tr>
	<tr>
		<td><input name="_field_update" type="submit" onclick="return(confirm('Êtes-vous certain de vouloir mettre à jour ce champ ?'))" value="MàJ" /></td>
		<td><select name="pos">
		<?php
		for ($i=1;$i<=$pos_max;$i++)
			if ($i==$nb)
				echo "<option value=\"$i\" selected>$i</option>\n";
			else
				echo "<option value=\"$i\">$i</option>\n";
		?>
		</select><input type="hidden" name="pos_orig" value="<?=$nb?>" /></td>
		<td><input name="name" value="<?=$name?>" /><input type="hidden" name="name_orig" value="<?=$name?>" /></td>
		<td><input name="label" value="<?=$field->label?>" /></td>
		<td><select name="type"><?php
		foreach (data()->list_detail_get() as $j)
		{
			if (substr(get_class($field), 5)==$j["name"])
				echo "<option value=\"$j[name]\" selected>$j[label]</option>\n";
			else
				echo "<option value=\"$j[name]\">$j[label]</option>\n";
		}
		?></select></td>
		<td><textarea name="defaultvalue"><?php echo json_encode($field->value); ?></textarea></td>
		<td><select name="opt">
			<option value=""></option>
			<option value="key"<?php if (in_array($name, $object->fields_key())) echo " selected"; ?>>KEY</option>
			<option value="required"<?php if (in_array($name, $object->fields_required())) echo " selected"; ?>>REQUIRED</option>
			<option value="calculated">CALCULATED</option>
		</select></td>
		<td><select name="query">
			<option value="0"<?php if (!in_array($name, $object->fields_index())) echo " selected"; ?>>NON</option>
			<option value="1"<?php if (in_array($name, $object->fields_index())) echo " selected"; ?>>OUI</option>
		</select></td>
		<td><select name="lang">
			<option value="0"<?php if (!$field->db_opt("lang")) echo " selected"; ?>>NON</option>
			<option value="1"<?php if ($field->db_opt("lang")) echo " selected"; ?>>OUI</option>
		</select></td>
	</tr>
	<tr> <td colspan="9"><hr /></td> </tr>
	<tr>
		<td colspan="9"><?php
		// Récupération des valeurs des champs par classe défaut puis reparamétrés
		$opt = array
		(
			"structure"=>array(),
			"db"=>array(),
			"disp"=>array(),
		);
		?>
		<table cellspacing="0" cellpadding="0" width="100%">
		<tr>
			<td>structure_opt</td>
			<td>db_opt</td>
			<td>disp_opt</td>
		</tr>
		<tr>
		<?php foreach ($opt as $type=>$list) { ?>
			<td valign="top">
			<select id="<?=$type?>_opt_list"><option value="">-- Choisir --</option><?php
			$f = $type."_opt_list_get";
			foreach($field->$f() as $i=>$j)
			{
				$list[$i] = $j;
			}
			foreach(data::${"${type}_opt_list"} as $i)
			{
				if (!isset($list[$i]))
					echo "<option>$i</option>\n";
			}
			?></select><input type="button" value="ADD" onclick="datamodel_opt_add('<?=$type?>',document.getElementById('<?=$type?>_opt_list').value)" />
			<div id="opt_<?=$type?>">
			<?php
			foreach($list as $i=>$j) if (in_array($i, data::${"${type}_opt_list"}))
			{
				echo "<div id=\"opt_".$type."_$i\">\n";
				echo "<p style=\"margin-bottom: 0px;\">$i <a href=\"javascript:;\" onclick=\"datamodel_opt_del('$type','$i')\" style=\"color:red;\">X</a></p> <p style=\"margin: 0px;\"><textarea name=\"optlist[$type][$i]\">".json_encode($j)."</textarea></p>\n";
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
		<td colspan="9"><hr /></td>
	</tr>
	<?php
	}
	else
	{
	?>
	<tr<?php if ($nb%2==1) echo " class=\"alt\""; ?>>
		<td><a href="?id=<?=$id?>&field_delete=<?=$name?>" onclick="return(confirm('Êtes-vous certain de vouloir supprimer ce champ ?'))" class="delete_link">X</a></td>
		<td><?=$nb?></td>
		<td><a href="?id=<?=$id?>&field_edit=<?=$name?>"><?=$name?></a></td>
		<td><input readonly value="<?=$field->label?>" /></td>
		<td><?php echo data()->{substr(get_class($field), 5)}->label; ?></td>
		<td><?php echo json_encode($field->value); ?></td>
		<td><?php if (in_array($name, $object->fields_key())) echo "<span style=\"color:red;\">KEY</span>"; elseif (in_array($name, $object->fields_required())) echo "<span style=\"color:blue;\">REQUIRED</span>"; ?></td>
		<td><?php if (in_array($name, $object->fields_index())) echo "<span style=\"color:blue;\">INDEX</span>"; ?></td>
		<td><?php if ($field->db_opt("lang") == true) echo "<span style=\"color:blue;\">LANG</span>"; ?></td>
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
		<td><input name="name" value="" /><input type="hidden" name="id" value="<?=$id?>" /></td>
		<td><input name="label" value="" /></td>
		<td><select name="type"><?php
		foreach ($data_list_detail as $j)
		{
			echo "<option value=\"$j[name]\">$j[label]</option>\n";
		}
		?></select></td>
		<td><textarea name="defaultvalue"></textarea></td>
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
<form method="post" action="?id=<?=$id?>">
<p><input type="submit" name="_db_create" value="Créer les tables associées en base de donnée" /></p>
</form>
</div>

<?php
}

elseif (isset($_GET["add"]))
{

$_type()->insert_form();

}

else
{

$_type()->table_list();

}

?>
