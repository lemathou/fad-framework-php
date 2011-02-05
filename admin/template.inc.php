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

$_type = "template";
$_label = "Template";

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
		echo "	<option value=\"$id\" selected>[$id] ".$object->info("type")." : ".$object->label()."</option>\n";
	else
		echo "	<option value=\"$id\">[$id] ".$object->info("type")." : ".$object->label()."</option>\n";
}
?></select>
<a href="?add">Ajouter</a>
<a href="?list">Retour à la liste</a>
</form>

<?php

// EDITION
if (isset($_GET["id"]) && $_type()->exists($id=$_GET["id"]))
{

$template = $_type($id);

?>
<div class="admin_menu admin_submenu">
	<a href="javascript:;" name="update_form" onclick="admin_submenu(this.name)"<?php if (empty($_GET["param_edit"])) echo " class=\"selected\""; ?>>Formulaire</a>
	<a href="javascript:;" name="param_list" onclick="admin_submenu(this.name)"<?php if (!empty($_GET["param_edit"])) echo " class=\"selected\""; ?>>Paramètres</a>
</div>
<div id="update_form" class="subcontents"<?php if (!empty($_GET["param_edit"])) echo " style=\"display:none;\""; ?>>
<?
$template->update_form();
?>
</div>

<div id="param_list" class="subcontents"<?php if (empty($_GET["param_edit"])) echo " style=\"display:none;\""; ?>>
<h2>Gestion des paramètres</h2>
<?php
// Ajout
if (isset($_POST["param_add"]) && is_array($_POST["param_add"]) && isset($_POST["param_add"]["name"]))
{
	if ($template->param_add($_POST["param_add"]["name"], $_POST["param_add"]))
		echo "<p>Le paramètre ".$_POST["param_add"]["name"]." a bien été ajouté.</p>\n";
}
// Suppression
if (isset($_GET["param_delete"]))
{
	if ($template->param_del($_GET["param_delete"]))
		echo "<p>Le paramètre ".$_GET["param_delete"]." a bien été supprimé.</p>\n";
}
// Mise à jour
if (isset($_GET["param_edit"]) && isset($_GET["option_del"]))
{
	//echo "DELETE FROM `_template_params_opt` WHERE template_id='$id' AND name='".$_GET["param_edit"]."' AND optname='".$_GET["option_del"]."'";
	db()->query("DELETE FROM `_template_params_opt` WHERE template_id='$id' AND name='".$_GET["param_edit"]."' AND optname='".$_GET["option_del"]."'");
}
if (isset($_POST["param_edit"]))
{
	foreach ($_POST["param_edit"] as $name=>$param)
	{
		db()->query("UPDATE `_template_params` SET name='$param[name]' , datatype='$param[datatype]' , defaultvalue='$param[defaultvalue]' WHERE template_id='$id' AND name='$name'");
		db()->query("UPDATE `_template_params_lang` SET description='".addslashes($param["description"])."' WHERE template_id='$id' AND name='$name' AND lang_id='".SITE_LANG_ID."'");
		if (isset($param["option_add"]["optname"]) && ($opt_add=$param["option_add"]))
		{
			db()->query("INSERT INTO `_template_params_opt` (template_id, name, optname, optvalue) VALUES ('$id', '$name', '$opt_add[optname]', '".json_encode($opt_add["optvalue"])."')");
		}
		echo "<p>Le paramètre $name a bien été mis à jour.</p>\n";
	}
}

// Edition
if (isset($_GET["param_edit"]) && ($param_edit=$_GET["param_edit"]) && ($query_str="SELECT t1.name, t1.datatype, t1.defaultvalue, t2.description FROM _template_params as t1 LEFT JOIN _template_params_lang as t2 ON t1.template_id=t2.template_id AND t1.name=t2.name AND t2.lang_id='".SITE_LANG_DEFAULT_ID."' WHERE t1.template_id = '$id' AND t1.name='$param_edit'") && ($query_params = db()->query($query_str)) && ($param = $query_params->fetch_assoc()))
{

$optlist = array();
$query = db()->query("SELECT optname , optvalue FROM _template_params_opt WHERE template_id='$id' AND name='$param_edit'");
if ($query->num_rows())
{
	while ($opt=$query->fetch_assoc())
	{
		$optlist[$opt["optname"]] = $opt["optvalue"];
	}
}

?>
<form action="?id=<?=$id?>&param_edit=<?=$param_edit?>" method="POST">
<table width="100%" cellspacing="1" border="1" cellpadding="1">
<tr>
	<td>Name :</td>
	<td><input name="param_edit[<?=$param["name"]?>][name]" value="<?=$param["name"]?>" /></td>
</tr>
<tr>
	<td>Description :</td>
	<td><textarea name="param_edit[<?=$param["name"]?>][description]" style="width:100%;"><?=$param["description"]?></textarea></td>
</tr>
<tr>
	<td>Datatype</td>
	<td><select name="param_edit[<?=$param["name"]?>][datatype]"><?php
	foreach (data()->list_detail_get() as $datatype)
	{
		if ($param["datatype"] == $datatype["name"])
			echo "<option value=\"$datatype[name]\" selected>$datatype[label]</option>\n";
		else
			echo "<option value=\"$datatype[name]\">$datatype[label]</option>\n";
	}
	?></select></td>
</tr>
<tr>
	<td>Valeur par défaut :<br />(JSON)</td>
	<td><?php
	if ($param["datatype"]=="dataobject" && isset($optlist["datamodel"]) && ($datamodel=datamodel($optlist["datamodel"])))
	{
		echo "<select name=\"param_edit[$param[name]][defaultvalue]\">";
			echo "<option value=\"0\">-- Choisir si besoin --</option>";
		foreach($datamodel->query() as $object)
		{
			if (isset($object->title))
				$aff = "ID#$object->id : $object->title";
			elseif (isset($object->name))
				$aff = "ID#$object->id : $object->name";
			elseif (isset($object->ref))
				$aff = "ID#$object->id : $object->ref";
			else
				$aff = "ID#$object->id";
			if ($param["defaultvalue"] == $object->id)
				echo "<option value=\"$object->id\" selected>$aff</option>";
			else
				echo "<option value=\"$object->id\">$aff</option>";
		}
		echo "</select>\n";
	}
	else
	{
	?>
	<textarea name="param_edit[<?=$param["name"]?>][defaultvalue]" style="width:100%;"><?=$param["defaultvalue"]?></textarea>
	<?php
	}
	?></td>
</tr>
<tr>
	<td>Options :</td>
	<td><?php
	$query = db()->query("SELECT optname , optvalue FROM _template_params_opt WHERE template_id='$id' AND name='$param_edit'");
	if ($query->num_rows())
	{
		while ($opt=$query->fetch_assoc())
		{
			echo "<p><a href=\"?id=$id&param_edit=$param_edit&option_del=$opt[optname]\" style=\"color:red;\">X</a>$opt[optname] : $opt[optvalue]<br /></p>";
		}
	}
	?></td>
<tr>
	<td>Ajouter une option :</td>
	<td><table cellspacing="0" cellpadding="0">
	<tr>
		<td>Name : </td>
		<td><input name="param_edit[<?=$param["name"]?>][option_add][optname]" /></td>
	</tr>
	<tr>
		<td>Value (JSON) : </td>
		<td><input name="param_edit[<?=$param["name"]?>][option_add][optvalue]" /></td>
	</tr>
	</table></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><input type="submit" value="Mettre à jour" /></td>
</tr>
</table>
</form>
<?php
}

?>

<form action="?id=<?=$id?>" method="post">
<table width="100%" cellspacing="0" border="0" cellpadding="0">
<tr class="label">
	<td colspan="2">&nbsp;</td>
	<td>Name</td>
	<td>Label</td>
	<td>Datatype</td>
	<td>Value (JSON)</td>
</tr>
<?

$ordermax = count($template->param_list_detail());

if (isset($_POST["order_change"]) && is_array($_POST["order_change"]))
{
	foreach ($_POST["order_change"] as $name=>$order)
	{
		if (list($oldorder) = db()->query("SELECT `order` FROM `_template_params` WHERE `template_id`='$id' AND `name`='".db()->string_escape($name)."'")->fetch_row())
		{
			db()->query("UPDATE `_template_params` SET `order`=`order`-1 WHERE `template_id`='$id' AND `order`>'$oldorder'");
			db()->query("UPDATE `_template_params` SET `order`='".db()->string_escape($order)."' WHERE `template_id`='$id' AND `name`='".db()->string_escape($name)."'");
			db()->query("UPDATE `_template_params` SET `order`=`order`+1 WHERE `template_id`='$id' AND `order`>='".db()->string_escape($order)."' AND  `name`!='".db()->string_escape($name)."'");
		}
	}
}

foreach ($template->param_list_detail() as $nb=>$param)
{
?>
<tr<?php if ($nb%2 == 0) echo " class=\"alt\""; ?>>
	<td><a href="?id=<?php echo $id; ?>&param_delete=<?=$param["name"]?>" onclick="return(confirm('Êtes-vous sûr de vouloir effacer ?'))" style="color:red;border:1px red dotted;">X</a></td>
	<td><select id="order_change[<?=$param["name"]?>]" onchange="this.name=this.id;this.form.submit();"><?php
	for ($i=0;$i<$ordermax;$i++)
	{
		if ($nb == $i)
			echo "<option value=\"$i\" selected>$i</option>\n";
		else
			echo "<option value=\"$i\">$i</option>\n";
	}
	?></select></td>
	<td><a href="?id=<?php echo $id; ?>&param_edit=<?=$param["name"]?>"><?=$param["name"]?></a></td>
	<td><?=$param["label"]?></td>
	<td><?=data()->get_name($param["datatype"])->label?></td>
	<td><?=json_encode($param["value"])?></td>
</tr>
<?php
}
?>
</table>
</form>

<h3>Ajouter un paramètre :</h3>
<form action="?id=<?=$id?>" method="post">
<table>
<tr>
	<td>Name :</td>
	<td><input name="param_add[name]" /></td>
</tr>
<tr>
	<td>Label :</td>
	<td><input name="param_add[label]" /></td>
</tr>
<tr>
	<td>Type de donnée :</td>
	<td><select name="param_add[datatype]">
		<option value="">-- Sélectionner --</option>
	<?php
	foreach(data()->list_detail_get() as $datatype)
	{
		echo "<option value=\"$datatype[name]\">$datatype[label]</option>\n";
	}
	?>
	</select></td>
</tr>
<tr>
	<td>Valeur par défaut :</td>
	<td><textarea name="param_add[value]" style="width:100%;" rows="10"></textarea></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><input type="submit" value="Ajouter" /></td>
</tr>
</table>
</form>
</div>

<?php

}

elseif (isset($_GET["add"]))
{

$_type()->insert_form();
	
}

// LISTE
else
{

?>
<h2>Liste et paramétrage des templates disponibles</h2>

<p>Un template est une maquette de page, généralement paramétrable.</p>
<p>Lorsque vous créez une page, vous devez lui associer un template et paramétrer ce template au besoin.</p>

<form method="get">
<?php
$tpl_type_list = array
(
	""=>"Tous les templates",
	"root"=>"Principaux",
	"datamodel"=>"Datamodels",
	"inc"=>"Include",
	"page"=>"Pages",
);
?>
<p>Filtrer l'affchage : <select name="filter" onchange="this.form.submit()">
<?php
foreach($tpl_type_list as $i=>$j)
{
	if (isset($_GET["filter"]) && $_GET["filter"]==$i)
		echo "<option value=\"$i\" selected>$j</option>";
	else
		echo "<option value=\"$i\">$j</option>";
}
?>
</select> <input type="submit" value="Filtrer" /></p>
</form>
<?php

$_type()->table_list();

}
?>
