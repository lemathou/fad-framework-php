<?

/**
  * $Id: library.inc.php 58 2009-03-03 15:47:37Z mathieu $
  * 
  * Copyright 2008 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */

if (!defined("ADMIN_OK"))
	die("ACCES NON AUTORISE");

$library_list = library()->list_detail();

// Insert
if (isset($_POST["insert"]) && is_array($_POST["insert"]))
{

$query_string = " INSERT INTO `_library` ( `name` , `description` ) VALUES ( '".$_POST["insert"]["name"]."' , '".$_POST["insert"]["description"]."' ) ";
$query = db()->query($query_string);

if (($id = $_GET["id"] = $query->last_id()))
{
	$query_string = " INSERT INTO `_library_lang` ( `id` , `lang_id` , `name` ) VALUES ( '$id' , '".SITE_LANG_ID."' , '".addslashes($_POST["insert"]["name_lang"])."' ) ";
	$query = db()->query($query_string);
	if (isset($_POST["insert"]["library_list"]) && is_array($_POST["insert"]["library_list"]) && (count($_POST["insert"]["library_list"]) > 0))
	{
		$query_perm_list = array();
		foreach($_POST["insert"]["library_list"] as $library_id)
			$query_library_list[] = "( '$library_id' , '$id' )";
		if (count($query_library_list)>0)
		{
			$query_string = " INSERT INTO `_library_ref` ( `parent_id` , `id` ) VALUES ".implode(" , ",$query_library_list);
			db()->query($query_string);
		}
	}
}
elseif ($error = db()->error())
{
	print "<p>Une erreur est survenue : DEBUG : $error</p>\n";
}

}

// Update
if (isset($_POST["update"]) && is_array($update=$_POST["update"]) && isset($update["id"]) && library()->exists($update["id"]))
{

library($update["id"])->update($update);

}
?>

<style type="text/css">
table td
{
	vertical-align: top;
}
</style>

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

<?php

if (isset($_GET["id"]) && isset($library_list[$id=$_GET["id"]]))
{

$update = $library_list[$id];
$update["library_list"]=array();
$query_library = db()->query(" SELECT parent_id FROM _library_ref WHERE id = '$id' ");
while (list($library_id) = $query_library->fetch_row())
	$update["library_list"][] = $library_id;
	
?>
<p><a href="?list">Retour à la liste</a></p>
<form action="?id=<?=$id?>" method="POST">
<table width="100%">
<tr style="font-weight:bold;">
	<td width="200">ID :</td>
	<td><input name="update[id]" value="<?=$id?>" readonly /></td>
	<td rowspan="10" width="60%"><textarea id="update[filecontent]" name="update[filecontent]" style="width:100%" rows="40"><?php 
	$filename = "library/$update[name].inc.php";
	if (file_exists($filename))
	{
		echo $content = htmlspecialchars(fread(fopen($filename,"r"),filesize($filename)));
	}
	?></textarea></td>
</tr>
<tr>
	<td>Name :</td>
	<td><input name="update[name]" value="<?=$update["name"]?>" /></td>
</tr>
<tr>
	<td>Nom complet :</td>
	<td><input name="update[title]" value="<?=$update["title"]?>" style="width:100%" /></td>
</tr>
<tr>
	<td>Description :</td>
	<td><textarea name="update[description]" style="width:100%" rows="4"><?=$update["description"]?></textarea></td>
</tr>
<tr>
	<td>Dependances :</td>
	<td><select name="update[library_list][]" size="4" multiple>
	<?
	foreach($library_list as $i => $j)
		if (in_array($i, $update["library_list"]))
			echo "<option value=\"$i\" selected>$j[title]</option>";
		elseif ($id != $i)
			echo "<option value=\"$i\">$j[title]</option>";
	?>
	</select></td>
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

<p>Ajouter une librairie</p>
<form action="" method="POST">
<table>
<tr style="font-weight:bold;">
	<td>Name</td>
	<td>Nom complet (langue)</td>
	<td>Description</td>
	<td>Dependances</td>
</tr>
<tr>
	<td><input name="insert[name]" value="" /></td>
	<td><input name="insert[name_lang]" value="" /></td>
	<td><textarea name="insert[description]" cols="25" rows="4"></textarea></td>
	<td><select name="insert[library_list][]" size="4" multiple>
	<?
	foreach($library_list as $i => $j)
		print "<option value=\"$i\">$j</option>";
	?>
	</select></td>
	<td><input type="submit" value="Ajouter" /></td>
</tr>
</table>
</form>

<p>Liste des librairies :</p>
<table>
<tr style="font-weight:bold;">
	<td>&nbsp;</td>
	<td>ID</td>
	<td>Name</td>
	<td>Nom complet</td>
	<td>Description</td>
	<td>Dependances</td>
</tr>
<?
foreach ($library_list as $library)
{

$library_library = array();
$query_library = db()->query(" SELECT parent_id FROM _library_ref WHERE id = '$library[id]' ");
while (list($id) = $query_library->fetch_row())
	$library_library[] = $id;
?>
<tr>
	<td><a href="" onclick="return(confirm('Êtes vous bien certain de vouloir supprimer cette librairie ?'))" style="color:red; border:1px red solid;">X</a></td>
	<td><a href="?id=<?php echo $library["id"]; ?>"><?php echo $library["id"]; ?></a></td>
	<td><a href="?id=<?php echo $library["id"]; ?>"><?php echo $library["name"]; ?></a></td>
	<td><?php echo $library["title"]; ?></td>
	<td><?php echo $library["description"]; ?></td>
	<td><?php
	$library_show = array();
	foreach($library_list as $i => $j)
		if (in_array($i, $library_library))
			$library_show[] = $j["title"];
	if (count($library_show))
		echo implode(" , ", $library_show);
	?></td>
</tr>
<?php
}
?>
</table>

<?php

}

?>
