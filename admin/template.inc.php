<?

/**
  * $Id: template.inc.php 58 2009-03-03 15:47:37Z mathieu $
  * 
  * Copyright 2008 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */

if (!defined("ADMIN_OK"))
{
	die("ACCES NON AUTORISE");
}

define("LANG_ID", 2);

// Insert
if (isset($_POST["insert"]))
{

if ($id=$_GET["id"]=template()->add($_POST["insert"]))
{
	echo "<p>Le template a été ajouté avec succès (ID#$id), vous pouvez le modifier ci-dessous.</p>\n";
}
else
{
	echo "<p>Erreur à la création du template...</p>\n";
}

}

// Update
if (isset($_POST["update"]) && is_array($_POST["update"]) && isset($_POST["update"]["id"]) && template()->exists($_POST["update"]["id"]))
{

$template = template($_POST["update"]["id"]);
$template->update($_POST["update"]);

}

?>

<style type="text/css">
table td
{
	vertical-align: top;
}
</style>

<?php

// EDITION
if (isset($_GET["id"]) && template()->exists($id=$_GET["id"]))
{

$template = template($id);

?>

<p><a href="?list">Retour à la liste</a></p>

<h2>Edition d'un template</h2>

<script language="Javascript" type="text/javascript">
	// initialisation
	editAreaLoader.init({
		id: "update[filecontent]"	// id of the textarea to transform		
		,start_highlight: true	// if start with highlight
		,allow_resize: "both"
		,allow_toggle: true
		,word_wrap: false
		,language: "fr"
		,syntax: "php"	
	});
	editAreaLoader.init({
		id: "update[script]"	// id of the textarea to transform		
		,start_highlight: true	// if start with highlight
		,allow_resize: "both"
		,allow_toggle: true
		,word_wrap: false
		,language: "fr"
		,syntax: "php"	
	});
</script>

<form action="?id=<?=$id?>" method="POST">
<table width="100%" cellspacing="1" border="1" cellpadding="1">
<tr>
	<td class="label" width="200">ID</td>
	<td width="300"><input name="update[id]" value="<?=$id?>" readonly /></td>
	<td rowspan="11">
	<h3 style="margin-bottom: 0px;">TEMPLATE</h3>
	<textarea id="update[filecontent]" name="update[filecontent]" onclick="this.style.backgroundColor='#fff';" style="width: 100%;background-color:#eee;" rows="20"><?php
	$filename = "template/".$template->info("name").".tpl.php";
	if (file_exists($filename) && filesize($filename))
	{
		echo $content = htmlspecialchars(fread(fopen($filename,"r"),filesize($filename)));
	}
	else
	{
		$content="";
	}
	?></textarea>
	<h3 style="margin-bottom: 0px;">SCRIPT de contrôle / Modification / Mise à jour (optionnel)</h3>
	<textarea id="update[script]" name="update[script]" onclick="this.style.backgroundColor='#fff';" style="width: 100%;background-color:#eee;" rows="20"><?php
	$filename = "template/scripts/".$template->info("name").".inc.php";
	if (file_exists($filename) && filesize($filename))
	{
		echo $content = htmlspecialchars(fread(fopen($filename,"r"),filesize($filename)));
	}
	else
	{
		$content="";
	}
	?></textarea>
	</td>
</tr>
<tr>
	<td class="label">Type</td>
	<td><select name="update[type]"><?php
	$type_list = array
	(
		"container"=>"Conteneur (passage de variables)",
		"inc"=>"Inclusion fréquente",
		"page"=>"Page de contenu",
		"datamodel"=>"Datamodel",
	);
	foreach ($type_list as $i=>$j)
		if ($template->info("type") == $i)
			echo "<option value=\"$i\" selected>$j</option>\n";
		else
			echo "<option value=\"$i\">$j</option>\n";
	?></select></td>
</tr>
<tr>
	<td class="label">Name</td>
	<td><input name="update[name]" onclick="this.style.backgroundColor='#fff';" value="<?=$template->info("name")?>" style="background-color:#eee;width:100%;" /></td>
</tr>
<tr>
	<td class="label">Title</td>
	<td><input name="update[title]" onclick="this.style.backgroundColor='#fff';" value="<?=$template->info("title")?>" style="background-color:#eee;width:100%;" /></td>
</tr>
<tr>
	<td class="label">Description</td>
	<td><textarea name="update[description]" onclick="this.style.backgroundColor='#fff';" rows="4" style="background-color:#eee;width:100%;"><?=$template->info("description")?></textarea></td>
</tr>
<tr>
	<td class="label">Details</td>
	<td><textarea name="update[details]" onclick="this.style.backgroundColor='#fff';" rows="4" style="background-color:#eee;width:100%;"><?=$template->info("details")?></textarea></td>
</tr>
<tr>
	<td class="label">Durée Min du cache<br /><span style="color:#400">Attention avec ce paramètre !!</span></td>
	<td><input name="update[cache_mintime]" onclick="this.style.backgroundColor='#fff';" value="<?=$template->info("cache_mintime")?>" style="background-color:#eee;" size="3" maxlength="3" /></td>
</tr>
<tr>
	<td class="label">Durée Max du cache<br />(0 = pas de cache)</td>
	<td><input name="update[cache_maxtime]" onclick="this.style.backgroundColor='#fff';" value="<?=$template->info("cache_maxtime")?>" style="background-color:#eee;" size="3" maxlength="4" /></td>
</tr>
<tr>
	<td class="label">Dépendant du login</td>
	<td><input name="update[login_dependant]" value="0" type="radio"<?php if (!$template->info("login_dependant")) echo " checked"; ?> /> NON <input name="update[login_dependant]" value="1" type="radio"<?php if ($template->info("login_dependant")) echo " checked"; ?> /> OUI</td>
</tr>
<tr>
	<td class="label">Libraries</td>
	<td><select name="update[library][]" size="4" multiple>
	<?
	foreach(library()->list_get() as $i=>$library)
	{
		if (in_array($i, $template->info("library_list")))
			print "<option value=\"$i\" selected>$library->name</option>";
		else
			print "<option value=\"$i\">$library->name</option>";
	}
	?>
	</select></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><input type="submit" value="Mettre à jour" /></td>
</tr>
</table>
</form>

<h2>Gestion des paramètres</h2>
<?php
// Ajout
if (isset($_POST["param_add"]) && is_array($param_add=$_POST["param_add"]))
{
	list($param_add["order"]) = db()->query("SELECT COUNT(*) FROM `_template_params` WHERE `template_id`='$id'")->fetch_row();
	db()->query("INSERT INTO `_template_params` (`template_id`, `order`, `datatype`, `name`, `defaultvalue`) VALUES ('$id', '".$param_add["order"]."', '".$param_add["datatype"]."', '".$param_add["name"]."', '".addslashes($param_add["defaultvalue"])."' )");
	db()->query("INSERT INTO `_template_params_lang` (`template_id`, `lang_id`, `name`, `description`) VALUES ('$id', '".SITE_LANG_ID."', '".$param_add["name"]."', '".addslashes($param_add["description"])."' )");
	echo "<p>Le paramètre $param_add[name] a bien été ajouté.</p>\n";
}
// Suppression
if (isset($_GET["param_delete"]) && ($param_delete=$_GET["param_delete"]))
{
	db()->query("DELETE FROM `_template_params` WHERE template_id='$id' AND name='$param_delete'");
	db()->query("DELETE FROM `_template_params_lang` WHERE template_id='$id' AND name='$param_delete'");
	db()->query("DELETE FROM `_template_params_opt` WHERE template_id='$id' AND name='$param_delete'");
	echo "<p>Le paramètre $param_delete a bien été supprimé.</p>\n";
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
		if (isset($param["option_add"]["optname"]) && ($opt_add=$param["option_add"]) && isset($opt_add["opttype"]) && $opt_add["opttype"])
		{
			db()->query("INSERT INTO `_template_params_opt` (template_id, name, optname, opttype, optvalue) VALUES ('$id', '$name', '$opt_add[optname]', '$opt_add[opttype]', '".addslashes($opt_add["optvalue"])."')");
		}
		echo "<p>Le paramètre $name a bien été mis à jour.</p>\n";
	}
}
?>

<?php
// Edition
if (isset($_GET["param_edit"]) && ($param_edit=$_GET["param_edit"]) && ($query_str="SELECT t1.name, t1.datatype, t1.defaultvalue, t2.description FROM _template_params as t1 LEFT JOIN _template_params_lang as t2 ON t1.template_id=t2.template_id AND t1.name=t2.name AND t2.lang_id='".SITE_LANG_DEFAULT_ID."' WHERE t1.template_id = '".$template->id()."' AND t1.name='$param_edit'") && ($query_params = db()->query($query_str)) && ($param = $query_params->fetch_assoc()))
{

$optlist = array();
$query = db()->query("SELECT opttype , optname , optvalue FROM _template_params_opt WHERE template_id='$id' AND name='$param_edit'");
if ($query->num_rows())
{
	while ($opt=$query->fetch_assoc())
	{
		$optlist[$opt["opttype"]][$opt["optname"]] = $opt["optvalue"];
	}
}

?>
<form action="?id=<?=$id?>&param_edit=<?=$param_edit?>" method="POST">
<p><a href="?id=<?=$id?>">Retour / annulation</a></p>
<table style="border:1px black solid;">
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
	$query = db()->query("SELECT `_datatype`.`name`, `_datatype_lang`.`title` FROM `_datatype` LEFT JOIN `_datatype_lang` ON `_datatype`.`id`=`_datatype_lang`.`datatype_id` ORDER BY `_datatype_lang`.`title`");
	while(list($name, $title)=$query->fetch_row())
	{
		if ($param["datatype"] == $name)
			echo "<option value=\"$name\" selected>$title</option>\n";
		else
			echo "<option value=\"$name\">$title</option>\n";
	}
	?></select></td>
</tr>
<tr>
	<td>Valeur par défaut :<br />(JSON)</td>
	<td><?php
	if ($param["datatype"]=="dataobject" && isset($optlist["structure"]["databank"]) && is_a($databank=databank($optlist["structure"]["databank"]),"data_bank"))
	{
		echo "<select name=\"param_edit[$param[name]][defaultvalue]\">";
			echo "<option value=\"0\">-- Choisir si besoin --</option>";
		foreach($databank->query() as $object)
		{
			if (isset($object->title))
				$aff = "ID#$object->id : $object->title";
			elseif (isset($object->name))
				$aff = "ID#$object->id : $object->name";
			elseif (isset($object->ref))
				$aff = "ID#$object->id : $object->ref";
			else
				$aff = "ID#$object->id";
			if ($param["defaultvalue"] == $object->id->value)
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
	$query = db()->query("SELECT opttype , optname , optvalue FROM _template_params_opt WHERE template_id='$id' AND name='$param_edit'");
	if ($query->num_rows())
	{
		while ($opt=$query->fetch_assoc())
		{
			echo "<p><a href=\"?id=$id&param_edit=$param_edit&option_del=$opt[optname]\" style=\"color:red;\">X</a>$opt[opttype] / $opt[optname] : $opt[optvalue]<br /></p>";
		}
	}
	?></td>
<tr>
	<td>Ajouter une option :</td>
	<td><table cellspacing="0" cellpadding="0">
	<tr>
		<td>Type : </td>
		<td><select name="param_edit[<?=$param["name"]?>][option_add][opttype]">
			<option value="">-- Choisir --</option>
			<option value="structure">structure</option>
			<option value="db">db</option>
			<option value="disp">disp</option>
			<option value="form">form</option>
		</select></td>
	</tr>
	<tr>
		<td>Name : </td>
		<td><input name="param_edit[<?=$param["name"]?>][option_add][optname]" /></td>
	</tr>
	<tr>
		<td>Value : </td>
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
echo mysql_error();
?>

<form action="?id=<?=$id?>" method="post">
<table>
<tr>
	<td colspan="2">&nbsp;</td>
	<td>Name</td>
	<td>description</td>
	<td>Datatype</td>
	<td>Defaultvalue (JSON)</td>
</tr>
<?

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

list($ordermax) = db()->query("SELECT COUNT(*) FROM `_template_params` WHERE `template_id`='$id'")->fetch_row();

$query_params = db()->query("SELECT t1.name , t1.order , t2.description , t1.datatype , t1.defaultvalue , t2.description FROM _template_params as t1 LEFT JOIN _template_params_lang as t2 ON t1.template_id=t2.template_id AND t1.name=t2.name AND t2.lang_id='".SITE_LANG_DEFAULT_ID."' WHERE t1.template_id = '$id' ORDER BY t1.order");
while ($param = $query_params->fetch_assoc())
{
?>
<tr>
	<td><a href="?id=<?php echo $id; ?>&param_delete=<?=$param["name"]?>" onclick="return(confirm('Êtes-vous sûr de vouloir effacer ?'))" style="color:red;border:1px red dotted;">X</a></td>
	<td><select id="order_change[<?=$param["name"]?>]" onchange="this.name=this.id;this.form.submit();"><?php
	for ($i=0;$i<$ordermax;$i++)
	{
		if ($param["order"] == $i)
			echo "<option value=\"$i\" selected>$i</option>\n";
		else
			echo "<option value=\"$i\">$i</option>\n";
	}
	?></select></td>
	<td><a href="?id=<?php echo $id; ?>&param_edit=<?=$param["name"]?>"><?=$param["name"]?></a></td>
	<td><?=$param["description"]?></td>
	<td><?=data()->title($param["datatype"])?></td>
	<td><?=$param["defaultvalue"]?></td>
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
	<td>Description :</td>
	<td><textarea name="param_add[description]" style="width:100%;" rows="4"></textarea></td>
</tr>
<tr>
	<td>Type de donnée :</td>
	<td><select name="param_add[datatype]">
		<option value="">-- Sélectionner --</option>
	<?php
	foreach(data()->list_get() as $datatype)
	{
		echo "<option value=\"$datatype[name]\">$datatype[title]</option>\n";
	}
	?>
	</select></td>
</tr>
<tr>
	<td>Valeur par défaut :</td>
	<td><textarea name="param_add[defaultvalue]" style="width:100%;" rows="10"></textarea></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><input type="submit" value="Ajouter" /></td>
</tr>
</table>
</form>

<?php

}

// INSERTION
elseif (isset($_GET["add"]))
{

$template = array
(
	"name" => "",
	"title" => "",
	"description" => "",
	"details" => "",
	"cache_mintime" => TEMPLATE_CACHE_MIN_TIME,
	"cache_maxtime" => TEMPLATE_CACHE_MAX_TIME,
	"library" => array(),
);

if (isset($_POST["insert"]))
{
	foreach ($_POST["insert"] as $name=>$value)
		if (isset($template[$name]))
			$template[$name] = $value;
}

?>

<p><a href="?list">Retour à la liste</a></p>

<h2>Ajout d'un template</h2>

<p>La gestion des paramètres se fera à la page suivante</p>

<form action="" method="POST">
<table>
<tr>
	<td class="label">Name</td>
	<td><input name="insert[name]" value="<?=$template["name"]?>" size="32" /></td>
</tr>
<tr>
	<td class="label">Title</td>
	<td><input name="insert[title]" value="<?=$template["title"]?>" size="64" /></td>
</tr>
<tr>
	<td class="label">Description</td>
	<td><textarea name="insert[description]" cols="64" rows="4"><?=$template["description"]?></textarea></td>
</tr>
<tr>
	<td class="label">Details</td>
	<td><textarea name="insert[details]" cols="64" rows="8"><?=$template["details"]?></textarea></td>
</tr>
<tr>
	<td class="label">Durée mini du cache<br />Attention toutefois</td>
	<td><input name="insert[cache_mintime]" value="<?=$template["cache_mintime"]?>" size="3" maxlength="3" /></td>
</tr>
<tr>
	<td class="label">Durée max du cache<br />(0 = pas de mise en cache)</td>
	<td><input name="insert[cache_maxtime]" value="<?=$template["cache_maxtime"]?>" size="3" maxlength="4" /></td>
</tr>
<tr>
	<td class="label">Libraries</td>
	<td><select name="insert[library][]" size="4" multiple>
	<?
	foreach(library()->list_get() as $i => $j)
	{
		if (in_array($i, $template["library"]))
			print "<option value=\"$i\" selected>$j->title</option>";
		else
			print "<option value=\"$i\">$j->title</option>";
	}
	?>
	</select></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><input type="submit" value="Ajouter" /></td>
</tr>
</table>
</form>

<?php

}

// LISTE
else
{

?>

<h2>Liste et paramétrage des templates disponibles</h2>

<p>Un template est une maquette de page, généralement paramétrable.</p>
<p>Lorsque vous créez une page, vous devez lui associer un template et paramétrer ce template au besoin.</p>

<p><a href="?add">Ajouter un template</a></p>

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

<table cellspacing="1" border="1" cellpadding="1">
<tr style="font-weight:bold;">
	<td>ID</td>
	<td>Name</td>
	<td>Title</td>
	<td>Description</td>
</tr>
<?

if (!isset($_GET["filter"]) || !$_GET["filter"] || !isset($tpl_type_list[$_GET["filter"]]))
{
	$query_where = "";
}
elseif ($_GET["filter"] == "root")
{
	$query_where = "WHERE t1.name NOT LIKE '%/%'";
}
else
{
	$query_where = "WHERE t1.name LIKE '$_GET[filter]/%'";
}
$query = db()->query(" SELECT t1.`id` , t1.`name` , t2.`title` , t2.`description` , t2.`details` FROM `_template` as t1 LEFT JOIN `_template_lang` as t2 ON t1.id=t2.id AND t2.lang_id=".SITE_LANG_DEFAULT_ID." $query_where ORDER BY t1.name ");
while ($template = $query->fetch_assoc())
{
	echo "<tr>\n";
	echo "<td><a href=\"?id=$template[id]\">$template[id]</a></td>\n";
	echo "<td><a href=\"?id=$template[id]\">$template[name]</a></td>\n";
	echo "<td>$template[title]</td>\n";
	echo "<td>$template[description]</td>\n";
	echo "</tr>\n";
}

?>
</table>

<?php
}
?>
