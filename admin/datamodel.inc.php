<?php

/**
  * $Id$
  * 
  * Copyright 2008-2011 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * http://sourceforge.net/projects/phpfadframework/
  * Licence : http://www.gnu.org/copyleft/gpl.html  GNU General Public License
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

$_type()->retrieve_objects();

?>
<form method="get" class="page_form">
<input type="submit" value="<?php echo $_label; ?>" />
<select name="id" onchange="this.form.submit()">
	<option value=""></option>
<?php
foreach ($_type()->list_get() as $id=>$object)
{
	if (isset($_GET["id"]) && ($id==$_GET["id"]))
		echo "	<option value=\"$id\" selected>[$id] ".$object->label()."</option>\n";
	else
		echo "	<option value=\"$id\">[$id] ".$object->label()."</option>\n";
}
?>
</select>
<a href="?list">Liste</a>
<a href="?add">Ajouter</a>
</form>

<?php

$_type_list = $_type()->list_detail_get();

$library_list = library()->list_detail_get();

if (isset($_GET["id"]) && ($object=$_type($id=$_GET["id"])))
{

$submenu = "update_form";

// Datamodel field add
if (isset($_POST["_field_add"]))
{
	$object->field_add($_POST);
	$submenu = "field_list";
}

// Datamodel field update
if (isset($_POST["_field_update"]))
{
	//var_dump($_POST);
	$object->field_update($_POST["name_orig"], $_POST);
	$submenu = "field_list";
}

// Datamodel db create
if (isset($_GET["field_edit"]))
{
	$submenu = "field_list";
}

// Datamodel field delete
if (isset($_GET["field_delete"]))
{
	$object->field_delete($_GET["field_delete"]);
	$submenu = "field_list";
}

// Datamodel db create
if (isset($_POST["_db_create"]))
{
	echo "<p>Création de la structure de base de données pour le datamodel \"<b>".$object->name()."</b>\"</p>\n";
	$object->db_create();
}

?>
<div class="admin_menu admin_submenu">
	<a href="javascript:;" name="update_form" onclick="admin_submenu(this.name)" <?php if ($submenu == "update_form") echo "class=\"selected\""; ?>>Formulaire</a>
	<a href="javascript:;" name="field_list" onclick="admin_submenu(this.name)" <?php if ($submenu == "field_list") echo "class=\"selected\""; ?>>Champs de donnée</a>
	<a href="javascript:;" name="references" onclick="admin_submenu(this.name)" <?php if ($submenu == "references") echo "class=\"selected\""; ?>>Références</a>
	<a href="javascript:;" name="actions" onclick="admin_submenu(this.name)" <?php if ($submenu == "actions") echo "class=\"selected\""; ?>>Actions</a>
</div>

<div id="update_form" class="subcontents"<?php if ($submenu != "update_form") echo " style=\"display:none;\""; ?>>
<?php
$_type($id)->update_form();
?>
</div>

<div id="references" class="subcontents"<?php if ($submenu != "references") echo " style=\"display:none;\""; ?>>
<h3>Références dans d'autres datamodels</h3>
<table>
<tr class="label">
	<td colspan="2">Other Datamodel</td>
	<td colspan="2">This datamodel</td>
</tr>
<tr class="label">
	<td></td>
	<td>Field</td>
	<td></td>
	<td>Field</td>
</tr>
<?php
// TODO : opération inverse pour avoir la correspondance
$query_string = "SELECT t1.`datamodel_id` as id, t1.`fieldname` as name, t2.`fieldname` as ref_name FROM `_datamodel_fields_opt` as t1 LEFT JOIN `_datamodel_fields_opt` as t2 ON t2.`datamodel_id`='$id' AND t2.`opt_name`='datamodel' AND t2.`opt_value`=t1.`datamodel_id` WHERE t1.`datamodel_id` != '$id' AND t1.`opt_name`='datamodel' AND t1.`opt_value` IN ('$id', '\"".$object->name()."\"')";
$query = db($query_string);
//echo "<p>$query_string</p>\n";
while($ref=$query->fetch_assoc())
{
	echo "<tr>";
	echo "<td><a href=\"datamodel?id=".$ref["id"]."\">".datamodel($ref["id"])->name()."</a></td>";
	echo "<td>".$ref["name"]."</td>";
	if ($ref["ref_name"])
	{
		echo "<td>&lt;--&gt;</td>";
		echo "<td>".$ref["ref_name"]."</td>";
	}
	echo "</tr>\n";
}
?>
</table>
<h3>Références dans les paramètres de templates</h3>
<?php
$query_string = "SELECT t1.`template_id` as id, t1.`name` as name FROM `_template_params_opt` as t1 WHERE t1.`optname`='datamodel' AND t1.`optvalue` IN ('$id', '\"".$object->name()."\"')";
$query = db($query_string);
//echo "<p>$query_string</p>\n";
while($ref=$query->fetch_assoc())
{
	echo "<p><a href=\"datamodel?id=".$ref["id"]."\">".template($ref["id"])->name()."</a> : champ ".$ref["name"]."</p>\n";
}
?>
</div>

<div id="field_list" class="subcontents"<?php if ($submenu != "field_list") echo " style=\"display:none;\""; ?>>
<?php

list($db_sync) = db()->query("SELECT db_sync FROM _datamodel WHERE id='$id'")->fetch_row();
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
		<td>Update</td>
		<td>Index</td>
		<td>langue</td>
		<td>Inactif</td>
	</tr>
<?php
$field_update_list = array("" , "readonly" , "calculated");
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
		<td><select name="update">
			<option value=""></option>
			<option value="readonly">READONLY</option>
			<option value="calculated"<?php if (in_array($name, $object->fields_calculated())) echo " selected"; ?>>CALCULATED</option>
		</select></td>
		<td><select name="query">
			<option value="0"<?php if (!in_array($name, $object->fields_index())) echo " selected"; ?>>NON</option>
			<option value="1"<?php if (in_array($name, $object->fields_index())) echo " selected"; ?>>OUI</option>
		</select></td>
		<td><select name="lang">
			<option value="0"<?php if (!isset($field->opt["lang"]) || !$field->opt["lang"]) echo " selected"; ?>>NON</option>
			<option value="1"<?php if (isset($field->opt["lang"]) && $field->opt["lang"]) echo " selected"; ?>>OUI</option>
		</select></td>
		<td><select name="actif">
			<option value="0">Inactif</option>
			<option value="1" selected>Actif</option>
		</select></td>
	</tr>
	<tr> <td colspan="9"><hr /></td> </tr>
	<tr>
		<td colspan="9"><?php
		// Récupération des valeurs des champs par classe défaut puis reparamétrés
		?>
		<div>
		<select id="_opt_list"><option value="">-- Choisir --</option><?php
		$list = array();
		foreach($field->opt_list as $i)
		{
			if (!isset($field->opt[$i]))
				echo "<option>$i</option>\n";
		}
		?></select><input type="button" value="ADD" onclick="datamodel_opt_add(document.getElementById('_opt_list').value)" /></div>
		<div id="_opt">
		<?php
		foreach($field->opt as $i=>$j)
		{
			echo "<div id=\"opt_$i\">\n";
			echo "<p style=\"margin-bottom: 0px;\">$i <a href=\"javascript:;\" onclick=\"datamodel_opt_del('$i')\" style=\"color:red;\">X</a></p> <p style=\"margin: 0px;\"><textarea style=\"width: 100%;\" name=\"optlist[$i]\">".json_encode($j)."</textarea></p>\n";
			echo "</div>\n";
		}
		?>
		</div>
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
		<td><?php if (in_array($name, $object->fields_calculated())) echo "<span style=\"color:blue;\">CALCULATED</span>"; ?></td>
		<td><?php if (in_array($name, $object->fields_index())) echo "<span style=\"color:blue;\">INDEX</span>"; ?></td>
		<td><?php if ($field->opt_get("lang")) echo "<span style=\"color:blue;\">LANG</span>"; ?></td>
		<td>ACTIF</td>
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
		<td><select name="update"><?php
		foreach($field_update_list as $opt)
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
		<td><select name="actif">
			<option value="0">NON</option>
			<option value="1" selected>OUI</option>
		</select></td>
	</tr>
<?php } ?>
</table></form>

<hr />
<form method="post" action="?id=<?=$id?>">
<p><input type="submit" name="_db_create" value="Créer les tables associées en base de donnée" /></p>
</form>
</div>

<div id="actions" class="subcontents"<?php if ($submenu != "actions") echo " style=\"display:none;\""; ?>>
<h3>Actions définies sur les objets</h3>
<?php
?>
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
