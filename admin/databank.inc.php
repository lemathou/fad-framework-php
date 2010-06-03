<?

/**
  * $Id: databank.inc.php 58 2009-03-03 15:47:37Z mathieu $
  * 
  * Copyright 2008 Mathieu Moulin - iProspective - lemathou@free.fr
  * 
  * This file is part of FTNGroupWare.
  * 
  */

if (!defined("ADMIN_OK"))
	die("ACCES NON AUTORISE");

// Libraries
$library_list = array();
$query = db()->query("SELECT id , name FROM _library");
while ($library = $query->fetch_assoc())
{
	$library_list[$library["id"]] = $library["name"];
}

// Insert
if (isset($_POST["insert"]) && is_array($insert=$_POST["insert"]))
{

$query_string = "INSERT INTO `_databank` ( `library_id` , `name` , `description` ) VALUES ( '".db()->string_escape($insert["library_id"])."' , '".db()->string_escape($insert["name"])."' , '".db()->string_escape($insert["description"])."' ) ";
$query = db()->query($query_string);
$id = $query->last_id();
$query_string = "INSERT INTO `_databank_lang` ( `id` , `lang_id` , `name` ) VALUES ( '$id' , '".SITE_LANG_ID."' , '".db()->string_escape($insert["name_lang"])."' ) ";
$query = db()->query($query_string);

if ($error = db()->error())
{
	print "<p>Une erreur est survenue : DEBUG : $error</p>\n";
}

}
// Update
if (isset($_POST["update"]) && is_array($update=$_POST["update"]))
{

$query_string = "UPDATE `_databank` SET `library_id` = '".db()->string_escape($update["library_id"])."' , `name` = '".db()->string_escape($update["name"])."' , `description` = '".db()->string_escape($update["description"])."' WHERE id = '".db()->string_escape($update["id"])."'";
db()->query($query_string);
$query_string = "UPDATE `_databank_lang` SET `name` = '".db()->string_escape($update["name_lang"])."' WHERE id = '".db()->string_escape($update["id"])."' AND lang_id=".SITE_LANG_ID;
db()->query($query_string);

}

?>

<style type="text/css">
table td
{
	vertical-align: top;
}
</style>

<?php

if (isset($_GET["id"]) && ($id=$_GET["id"]))
{

?>

<p><a href="?list">Retour à la liste</a></p>

<form action="" method="POST">
<table>
<?
$query = db()->query(" SELECT t1.id , t1.library_id , t1.name , t1.description , t2.name as name_lang FROM _databank as t1 LEFT JOIN _databank_lang as t2 ON t1.id=t2.id AND t2.lang_id=".SITE_LANG_ID." WHERE t1.id='$id' ORDER BY t1.name ");
while ($row = $query->fetch_assoc())
{
?>
<tr>
	<td>ID :</td>
	<td><input name="update[id]" value="<?php echo $id; ?>" readonly /></td>
</tr>
<tr>
	<td>Name (et raccourcis pour la fonction) :</td>
	<td><input name="update[name]" value="<?php echo $row["name"]; ?>" /></td>
</tr>
<tr>
	<td>Nom complet :</td>
	<td><input name="update[name_lang]" value="<?php echo $row["name_lang"]; ?>" /></td>
</tr>
<tr>
	<td>Library :</td>
	<td><select name="update[library_id]" size="1">
	<?
	foreach($library_list as $i => $j)
		if ($row["library_id"] == $i)
			echo "<option value=\"$i\" selected>$j</option>";
		else
			echo "<option value=\"$i\">$j</option>";
	?>
	</select></td>
</tr>
<tr>
	<td>Description :</td>
	<td><textarea name="update[description]" cols="25" rows="4"><?php echo $row["description"]; ?></textarea></td>
</tr>
<?php
}
?>
</table>
<input type="submit" value="Mettre à jour" />
</form>

<?php

}

else
{

?>

<p>Ajouter une databank</p>
<form action="" method="POST">
<table>
<tr style="font-weight:bold;">
	<td>Name</td>
	<td>Library</td>
	<td>Nom complet (dans la langue)</td>
	<td>Description</td>
</tr>
<tr>
	<td><input name="insert[name]" value="" /></td>
	<td><input name="insert[name_lang]" value="" size="32" /></td>
	<td><select name="insert[library_id]" size="1">
	<?
	foreach($library_list as $i => $j)
		echo "<option value=\"$i\">$j</option>";
	?>
	</select></td>
	<td><textarea name="insert[description]" cols="32"></textarea></td>
	<td><input type="submit" value="Ajouter" /></td>
</tr>
</table>
</form>

<p>Liste des databank</p>
<table>
<tr style="font-weight:bold;">
	<td>&nbsp;</td>
	<td>ID</td>
	<td>Name</td>
	<td>Nom complet</td>
	<td>Library</td>
	<td>Description</td>
</tr>
<?
$query = db()->query(" SELECT t1.id , t1.library_id , t1.name , t1.description , t2.name as name_lang FROM _databank as t1 LEFT JOIN _databank_lang as t2 ON t1.id=t2.id AND t2.lang_id=".SITE_LANG_ID." ORDER BY t1.name ");
while ($row = $query->fetch_assoc())
{

print "<tr>\n";
print "<td><a href=\"?delete=$row[id]\" onclick=\"return(confirm('Êtes-vous vraiment certain de vouloir supprimer cette Databank ?'))\" style=\"color:red;border:1px red solid;\">X</a></td>\n";
print "<td><a href=\"?id=$row[id]\">$row[name]</a></td>\n";
print "<td>$row[name_lang]</td>\n";
print "<td>".$library_list[$row["library_id"]]."</td>\n";
print "<td>$row[description]</td>\n";
print "</tr>\n";

}
?>
</table>

<?php
}
?>
