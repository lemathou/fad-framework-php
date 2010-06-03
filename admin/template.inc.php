<?

/**
  * $Id: template.inc.php 58 2009-03-03 15:47:37Z mathieu $
  * 
  * Copyright 2008 Mathieu Moulin - iProspective - lemathou@free.fr
  * 
  * This file is part of FTNGroupWare.
  * 
  */

if (!defined("ADMIN_OK"))
{
	die("ACCES NON AUTORISE");
}

define("LANG_ID", 2);

/*
datamodel("template")->add( new data_id() , "key" );

datamodel("template")->add( new data_name("name", "", "Name", ""), "required" );
datamodel("template")->add( new data_name("title", "", "Title", "", array("lang"=>true)), "required" );
datamodel("template")->add( new data_text("description", "", array(), array("lang"=>true), array("label"=>"Description")), "required" );
datamodel("template")->add( new data_richtext("details", "", array(), array("lang"=>true), array("label"=>"Details")) );

datamodel("template")->add( new data_dataobject_list("library", null, "library", array("ref_table"=>"_template_library_ref")) );

datamodel("template")->db_opt_set("table", "_template");
*/

// Libraries
$library_list = array();
$query = db()->query(" SELECT id , name FROM _library ");
while ($library = $query->fetch_assoc())
{
	$library_list[$library["id"]] = $library["name"];
}

// Insert
if (isset($_POST["insert"]) && is_array($template=$_POST["insert"]) && isset($template["name"]))
{

$query_string = " INSERT INTO `_template` ( `name` ) VALUES ( '".$template["name"]."' ) ";
$query = db()->query($query_string);

$_GET["id"] = $id = $query->last_id();
$query_string = " INSERT INTO `_template_lang` ( `id`, `lang_id` , `title` , `description` , `details` ) VALUES ( '$id' , '".SITE_LANG_DEFAULT_ID."' , '".addslashes($template["title"])."' , '".addslashes($template["description"])."' , '".addslashes($template["details"])."' ) ";
$query = db()->query($query_string);
if (isset($template["library"]) && is_array($template["library"]) && (count($template["library"]) > 0))
{
	$query_perm_list = array();
	foreach($template["library"] as $library_id)
	{
		if (isset($library_list[$library_id]))
		{
			$query_library_list[] = "( $id , $library_id )";
		}
	}
	if (count($query_library_list)>0)
	{
		$query_string = " INSERT INTO `_template_library_ref` ( `template_id` , `library_id` ) VALUES ".implode(" , ",$query_library_list);
		db()->query($query_string);
	}
}

echo "<p>Le template a été ajouté avec succès, vous pouvez le modifier ci-dessous.</p>\n";


}

// Update
if (isset($_POST["update"]) && is_array($template=$_POST["update"]))
{

$query_string = "UPDATE `_template` SET `name` = '".addslashes($template["name"])."' WHERE id = '$template[id]' ";
db()->query($query_string);

$query_string = "UPDATE `_template_lang` SET `title` = '".addslashes($template["title"])."' , `description` = '".addslashes($template["description"])."' , `details` = '".addslashes($template["details"])."' WHERE id = '$template[id]' AND lang_id='".SITE_LANG_DEFAULT_ID."'";
db()->query($query_string);

db()->query("DELETE FROM `_template_library_ref` WHERE `template_id`='$template[id]'");
if (isset($template["library"]) && is_array($template["library"]) && (count($template["library"]) > 0))
{
	$query_library_list = array();
	foreach($template["library"] as $library_id)
		if (isset($library_list[$library_id]))
			$query_library_list[] = "( '$template[id]' , '$library_id' )";
	if (count($query_library_list)>0)
	{
		$query_string = " INSERT INTO `_template_library_ref` ( `template_id` , `library_id` ) VALUES ".implode(" , ",$query_library_list);
		db()->query($query_string);
	}
}

if (isset($template["filecontent"]))
{
	$filename = "template/$template[name].tpl.php";
	fwrite(fopen($filename,"w"), htmlspecialchars_decode($template["filecontent"]));
}


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
if (isset($_GET["id"]) && ($id=$_GET["id"]) && ($query=db()->query("SELECT t1.id , t1.name , t2.title , t2.description , t2.details FROM _template as t1 LEFT JOIN _template_lang as t2 ON t1.id=t2.id AND t2.lang_id='".SITE_LANG_DEFAULT_ID."' WHERE t1.id='$id'")) && $query->num_rows())
{

$template = $query->fetch_assoc();

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
		,word_wrap: true
		,language: "fr"
		,syntax: "php"	
	});
</script>

<form action="?id=<?php echo $id; ?>" method="POST">
<table width="100%" cellspacing="1" border="1" cellpadding="1">
<tr>
	<td class="label">ID</td>
	<td><input name="update[id]" value="<?php echo $template["id"]; ?>" readonly /></td>
</tr>
<tr>
	<td class="label">Name</td>
	<td><input name="update[name]" onclick="this.style.backgroundColor='#fff';" value="<?php echo $template["name"]; ?>" style="background-color:#eee;" size="32" /></td>
</tr>
<tr>
	<td class="label">Title</td>
	<td><input name="update[title]" onclick="this.style.backgroundColor='#fff';" value="<?php echo $template["title"]; ?>" style="background-color:#eee;" size="64" /></td>
</tr>
<tr>
	<td class="label">Description</td>
	<td><textarea name="update[description]" onclick="this.style.backgroundColor='#fff';" style="width:100%" rows="4" style="background-color:#eee;"><?php echo $template["description"]; ?></textarea></td>
</tr>
<tr>
	<td class="label">Details</td>
	<td><textarea name="update[details]" onclick="this.style.backgroundColor='#fff';" style="width:100%" rows="4" style="background-color:#eee;"><?php echo $template["details"]; ?></textarea></td>
</tr>
<tr>
	<td class="label">Libraries</td>
	<td><select name="update[library][]" size="4" multiple>
	<?
	$template["library"] = array();
	$query_library = db()->query(" SELECT library_id FROM _template_library_ref WHERE template_id = $template[id] ");
	while (list($library_id) = $query_library->fetch_row())
	{
		$template["library"][] = $library_id;
	}
	foreach($library_list as $i => $j)
	{
		if (in_array($i, $template["library"]))
			print "<option value=\"$i\" selected>$j</option>";
		else
			print "<option value=\"$i\">$j</option>";
	}
	?>
	</select></td>
</tr>
<tr>
	<td>Contenu</td>
	<td><textarea id="update[filecontent]" name="update[filecontent]" onclick="this.style.backgroundColor='#fff';" style="width: 100%;background-color:#eee;" rows="40"><?php
	$filename = "template/$template[name].tpl.php";
	if (file_exists($filename) && filesize($filename))
	{
		echo $content = htmlspecialchars(fread(fopen($filename,"r"),filesize($filename)));
	}
	else
	{
		$content="";
	}
	?></textarea></td>
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
if (isset($_POST["param_add"]) && ($param_add=$_POST["param_add"]))
{
	db()->query("INSERT INTO `_template_params` ( template_id , datatype , name , defaultvalue ) VALUES ( '$id' , '".$param_add["datatype"]."' , '".$param_add["name"]."' , '".addslashes($param_add["defaultvalue"])."' )");
	db()->query("INSERT INTO `_template_params_lang` ( template_id , lang_id , name , description ) VALUES ( '$id' , '".SITE_LANG_ID."' , '".$param_add["name"]."' , '".addslashes($param_add["description"])."' )");
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
if (isset($_POST["param_edit"]))
{
	foreach ($_POST["param_edit"] as $name=>$param)
	{
		db()->query("UPDATE `_template_params` SET name='$param[name]' , datatype='$param[datatype]' , defaultvalue='$param[defaultvalue]' WHERE template_id='$id' AND name='$name'");
		db()->query("UPDATE `_template_params_lang` SET description='".addslashes($param["description"])."' WHERE template_id='$id' AND name='$name' AND lang_id='".SITE_LANG_ID."'");
		//db()->query("UPDATE `_template_params_opt` WHERE template_id='$id' AND name='$param_delete'");
		if (isset($param["option_add"]["optname"]) && $opt_add=$param["option_add"])
		{
			db()->query("INSERT INTO `_template_params_opt` ( template_id , name , optname , opttype , optvalue ) VALUES ( '$id' , '$name' , '$opt_add[optname]' , '$opt_add[opttype]' , '".addslashes($opt_add["optvalue"])."' )");
		}	
		echo "<p>Le paramètre $name a bien été mis à jour.</p>\n";
	}
}
?>

<?php
// Edition
if (isset($_GET["param_edit"]) && ($param_edit=$_GET["param_edit"]) && ($query_params = db()->query(" SELECT t1.name , t1.datatype , t1.defaultvalue , t2.description FROM _template_params as t1 LEFT JOIN _template_params_lang as t2 ON t1.template_id=t2.template_id AND t1.name=t2.name AND t2.lang_id='".SITE_LANG_DEFAULT_ID."' WHERE t1.template_id = '$template[id]' AND t1.name='$param_edit' ")) && ($param = $query_params->fetch_assoc()))
{
?>
<form action="?id=<?php echo $id; ?>" method="POST">
<p><a href="?id=<?php echo $id; ?>">Ajouter un paramètre</a></p>
<table style="border:1px black solid;">
<tr>
	<td>Name :</td>
	<td><input name="param_edit[<?php echo $param["name"]; ?>][name]" value="<?php echo $param["name"]; ?>" /></td>
</tr>
<tr>
	<td>Description :</td>
	<td><textarea name="param_edit[<?php echo $param["name"]; ?>][description]" style="width:100%;"><?php echo $param["description"]; ?></textarea></td>
</tr>
<tr>
	<td>Datatype</td>
	<td><select name="param_edit[<?php echo $param["name"]; ?>][datatype]">
	<?php
	$query = db()->query("SELECT t1.name , t2.title FROM _datatype as t1 LEFT JOIN _datatype_lang as t2 ON t1.id=t2.datatype_id ORDER BY t2.title");
	while(list($name, $title)=$query->fetch_row())
	{
		if ($param["datatype"] == $name)
			echo "<option value=\"$name\" selected>$title</option>\n";
		else
			echo "<option value=\"$name\">$title</option>\n";
	}
	?>
	</select></td>
</tr>
<tr>
	<td>Valeur par défaut :</td>
	<td><textarea name="param_edit[<?php echo $param["name"]; ?>][defaultvalue]" style="width:100%;"><?php echo $param["defaultvalue"]; ?></textarea></td>
</tr>
<tr>
	<td>Options :</td>
	<td><?php
	$query = db()->query("SELECT opttype , optname , optvalue FROM _template_params_opt WHERE template_id='$id' AND name='$param_edit'");
	if ($query->num_rows())
	{
		while ($opt=$query->fetch_assoc())
		{
			echo "$opt[opttype] / $opt[optname] : $opt[optvalue]<br />";
		}
	}
	?>
	<p>Ajouter :
	<br />Type :<select name="param_edit[<?php echo $param["name"]; ?>][option_add][opttype]"><option value="structure">structure</option><option value="db">db</option><option value="disp">disp</option><option value="form">form</option></select>
	<br />Name :<input name="param_edit[<?php echo $param["name"]; ?>][option_add][optname]" />
	<br />Value :<input name="param_edit[<?php echo $param["name"]; ?>][option_add][optvalue]" />
	</p>
	</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><input type="submit" value="Mettre à jour" /></td>
</tr>
</table>
</form>
<?php
}
else
{
?>
<p>Ajouter un paramètre :</p>
<form action="?id=<?php echo $id; ?>" method="post">
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
	$query = db()->query("SELECT t1.name , t2.title FROM _datatype as t1 LEFT JOIN _datatype_lang as t2 ON t1.id=t2.datatype_id ORDER BY t2.title");
	while(list($name, $title)=$query->fetch_row())
	{
		echo "<option value=\"$name\">$title</option>\n";
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
?>

<table>
<tr>
	<td>&nbsp;</td>
	<td>Name</td>
	<td>description</td>
	<td>Datatype</td>
	<td>Defaultvalue</td>
</tr>
<?

$template["params"] = array();
$query_params = db()->query(" SELECT t1.name , t2.description , t1.datatype , t1.defaultvalue , t2.description FROM _template_params as t1 LEFT JOIN _template_params_lang as t2 ON t1.template_id=t2.template_id AND t1.name=t2.name AND t2.lang_id='".SITE_LANG_DEFAULT_ID."' WHERE t1.template_id = '$template[id]' ");
while ($param = $query_params->fetch_assoc())
{
?>
<tr>
	<td><a href="?id=<?php echo $id; ?>&param_delete=<?php echo $param["name"]; ?>" onclick="return(confirm('Êtes-vous sûr de vouloir effacer ?'))" style="color:red;border:1px red dotted;">X</a></td>
	<td><a href="?id=<?php echo $id; ?>&param_edit=<?php echo $param["name"]; ?>"><?php echo $param["name"]; ?></a></td>
	<td><?php echo $param["description"]; ?></td>
	<td><?php echo $param["datatype"]; ?></td>
	<td><input type="text" value="<?php echo $param["defaultvalue"]; ?>" readonly /></td>
</tr>
<?php
}

?>
</table>

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
	"library" => array(),
);

?>

<p><a href="?list">Retour à la liste</a></p>

<h2>Ajout d'un template</h2>

<p>La gestion des paramètres se fera à la page suivante</p>

<form action="" method="POST">
<table>
<tr>
	<td class="label">Name</td>
	<td><input name="insert[name]" value="<?php echo $template["name"]; ?>" size="32" /></td>
</tr>
<tr>
	<td class="label">Title</td>
	<td><input name="insert[title]" value="<?php echo $template["title"]; ?>" size="64" /></td>
</tr>
<tr>
	<td class="label">Description</td>
	<td><textarea name="insert[description]" cols="64" rows="4"><?php echo $template["description"]; ?></textarea></td>
</tr>
<tr>
	<td class="label">Details</td>
	<td><textarea name="insert[details]" cols="64" rows="8"><?php echo $template["details"]; ?></textarea></td>
</tr>
<tr>
	<td class="label">Libraries</td>
	<td><select name="insert[library][]" size="4" multiple>
	<?
	foreach($library_list as $i => $j)
	{
		if (in_array($i, $template["library"]))
			print "<option value=\"$i\" selected>$j</option>";
		else
			print "<option value=\"$i\">$j</option>";
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

<table cellspacing="1" border="1" cellpadding="1">
<tr style="font-weight:bold;">
	<td>ID</td>
	<td>Name</td>
	<td>Title</td>
	<td>Description</td>
</tr>
<?
$query = db()->query(" SELECT t1.`id` , t1.`name` , t2.`title` , t2.`description` , t2.`details` FROM `_template` as t1 LEFT JOIN `_template_lang` as t2 ON t1.id=t2.id AND t2.lang_id=".SITE_LANG_DEFAULT_ID." ORDER BY t1.id ");
while ($template = $query->fetch_assoc())
{

print "<tr>\n";
print "<td><a href=\"?id=$template[id]\">$template[id]</a></td>\n";
print "<td><a href=\"?id=$template[id]\">$template[name]</a></td>\n";
print "<td>$template[title]</td>\n";
print "<td>$template[description]</td>\n";
print "</tr>\n";

}

?>
</table>

<?php
}
?>