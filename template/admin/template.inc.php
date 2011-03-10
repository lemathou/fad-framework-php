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
//var_dump($_POST);
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
if (isset($_POST["param"]))
{
	foreach ($_POST["param"] as $name=>$param)
	{
		if ($template->param_update($name, $param))
			echo "<p>Le paramètre $name a bien été mis à jour.</p>\n";
	}
}
?>

<form action="?id=<?=$id?>" method="post">
<table width="100%" cellspacing="0" border="0" cellpadding="0" class="tpl_params">
<tr class="label">
	<td colspan="2">&nbsp;</td>
	<td>Name</td>
	<td>Label</td>
	<td>Datatype</td>
	<td>Value (JSON)</td>
</tr>
<?

$ordermax = count($template->param_list_detail());

$order=0;
foreach ($template->param_list_detail() as $name=>$param)
{
	$order++;
?>
<tr>
	<td><a href="?id=<?php echo $id; ?>&param_delete=<?=$name?>" onclick="return(confirm('Êtes-vous sûr de vouloir effacer ?'))" style="color:red;">X</a></td>
	<td><select id="param[<?php echo $name; ?>][order]" onchange="this.name=this.id;this.form.submit();"><?php
	for ($i=1;$i<=$ordermax;$i++)
	{
		if ($order == $i)
			echo "<option value=\"$i\" selected>$i</option>\n";
		else
			echo "<option value=\"$i\">$i</option>\n";
	}
	?></select></td>
	<td><input id="param[<?php echo $name; ?>][name]" value="<?=$name?>" style="width:100%" /></td>
	<td><input id="param[<?php echo $name; ?>][label]" value="<?=$param["label"]?>" style="width:100%" /></td>
	<td><p><select id="param[<?=$name?>][datatype]"><option value="">-- Choisir --</option><?php
		foreach (data()->list_detail_get() as $info)
			if ($info["name"] == $param["datatype"])
				echo "<option value=\"$info[name]\" selected>$info[label]</option>";
			else
				echo "<option value=\"$info[name]\">$info[label]</option>";
	?></select></p>
	<p>Options</p>
	<div id="param_opt_list_<?=$name?>"><input type="hidden" id="param[<?=$name?>][opt]" /><?php
	if (isset($param["opt"])) foreach($param["opt"] as $i=>$j)
	{
		echo "<p>$i : <textarea id=\"param[$name][opt][$i]\">".json_encode($j)."</textarea> <input type=\"button\" value=\"-\" onclick=\"this.parentNode.parentNode.removeChild(this.parentNode)\" /></p>\n";
	}
	?></div>
	<p><input size="8" /> : <input size="16" /> <input type="button" value="+" onclick="template_param_opt_add('<?=$name?>', this.parentNode.childNodes[0].value, this.parentNode.childNodes[2].value)" /></p></td>
	<td><textarea id="param[<?php echo $name; ?>][value]" rows="4" style="width:100%"><?=json_encode($param["value"])?></textarea></td>
	<td><input type="submit" value="UPDATE" onclick="template_param_update('<?=$name?>');" /></td>
</tr>
<?php
}
?>
<tr>
	<td colspan="6"><h3>Ajouter un paramètre :</h3></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><select id="param_add[order]"><?php
	for ($i=1;$i<=$ordermax+1;$i++)
	{
		if ($i > $ordermax)
			echo "<option value=\"$i\" selected>$i</option>\n";
		else
			echo "<option value=\"$i\">$i</option>\n";
	}
	?></select></td>
	<td><input id="param_add[name]" style="width:100%" /></td>
	<td><input id="param_add[label]" style="width:100%" /></td>
	<td><select id="param_add[datatype]">
		<option value="">-- Sélectionner --</option>
	<?php
	foreach(data()->list_detail_get() as $datatype)
	{
		echo "<option value=\"$datatype[name]\">$datatype[label]</option>\n";
	}
	?>
	</select></td>
	<td><textarea id="param_add[value]" style="width:100%;" rows="4"></textarea></td>
	<td><input type="submit" value="ADD" onclick="template_param_add();" /></td>
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
	"container"=>"Principaux",
	"datamodel"=>"Datamodels",
	"inc"=>"Include",
	"page"=>"Pages",
);
if (!isset($_GET["filter"]) || !array_key_exists($_GET["filter"], $tpl_type_list))
	$_GET["filter"] = "";
?>
<p>Filtrer l'affchage : <select name="filter" onchange="this.form.submit()">
<?php
foreach($tpl_type_list as $i=>$j)
{
	if ($_GET["filter"]==$i)
		echo "<option value=\"$i\" selected>$j</option>";
	else
		echo "<option value=\"$i\">$j</option>";
}
?>
</select> <input type="submit" value="Filtrer" /></p>
</form>
<?php

if ($_GET["filter"])
	$_type()->table_list(array("type"=>$_GET["filter"]));
else
	$_type()->table_list();

}
?>
