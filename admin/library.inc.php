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

$library_list = library()->list_detail_get();

// Insert
if (isset($_POST["insert"]))
{

library()->add($_POST["insert"]);

}

// Update
if (isset($_POST["update"]) && is_array($update=$_POST["update"]) && isset($update["id"]) && library()->exists($update["id"]))
{

library($update["id"])->update($update);

}
?>
<form method="get" class="page_form">
<input type="submit" value="Editer la librairie" />
<select name="id" onchange="this.form.submit()">
	<option value=""></option>
<?php
foreach ($library_list as $id=>$library)
{
	if (isset($_GET["id"]) && ($id==$_GET["id"]))
		echo "	<option value=\"$id\" selected>[$id] $library[name]</option>\n";
	else
		echo "	<option value=\"$id\">[$id] $library[name]</option>\n";
}
?></select>
<a href="?add">Ajouter</a>
<a href="?list">Retour à la liste</a>
</form>

<div style="padding-top: 30px">
<?php

if (isset($_GET["id"]) && library()->exists($id=$_GET["id"]))
{

$update = $library_list[$id];
$update["library_list"]=array();
$query_library = db()->query(" SELECT parent_id FROM _library_ref WHERE id = '$id' ");
while (list($library_id) = $query_library->fetch_row())
	$update["library_list"][] = $library_id;
	
?>
<form action="?id=<?=$id?>" method="POST">
<table width="100%">
<tr style="font-weight:bold;">
	<td width="200">ID :</td>
	<td><input name="update[id]" value="<?=$id?>" readonly /></td>
	<td rowspan="10" width="60%"><textarea id="filecontent" name="update[filecontent]" style="width:100%" rows="40"><?php 
	$filename = "library/$update[name].inc.php";
	if (file_exists($filename))
	{
		echo $content = htmlspecialchars(fread(fopen($filename,"r"),filesize($filename)));
	}
	?></textarea></td>
</tr>
<tr>
	<td>Name :</td>
	<td><input name="update[name]" value="<?=$update["name"]?>" maxlength="64" style="width:100%;" /></td>
</tr>
<tr>
	<td>Nom complet :</td>
	<td><input name="update[label]" value="<?=$update["label"]?>" maxlength="128" style="width:100%;" /></td>
</tr>
<tr>
	<td>Description :</td>
	<td><textarea name="update[description]" style="width:100%;" rows="10"><?=$update["description"]?></textarea></td>
</tr>
<tr>
	<td>Dependances :</td>
	<td><select name="update[library_list][]" size="10" multiple style="width:100%;">
	<?
	foreach($library_list as $i => $j)
		if (in_array($i, $update["library_list"]))
			echo "<option value=\"$i\" selected>$j[label]</option>";
		elseif ($id != $i)
			echo "<option value=\"$i\">$j[label]</option>";
	?>
	</select></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><input type="submit" value="Mettre à jour" /></td>
</tr>
</table>
</form>

<script language="Javascript" type="text/javascript">
$(document).ready(function(){
	// initialisation
	editAreaLoader.init({
		id: "filecontent"	// id of the textarea to transform		
		,start_highlight: true	// if start with highlight
		,allow_resize: "both"
		,allow_toggle: true
		,word_wrap: true
		,language: "fr"
		,syntax: "php"	
	});
});
</script>
<?php

}

elseif (isset($_GET["add"]))
{

?>
<form action="?add" method="POST">
<table width="100%">
<tr style="font-weight:bold;">
	<td class="label">Name (unique) :</td>
	<td><input name="insert[name]" value="" maxlength="64" style="width:100%;" /></td>
	<td rowspan="10" width="60%"><textarea id="insert[filecontent]" name="insert[filecontent]" style="width:100%;" rows="40"></textarea></td>
</tr>
<tr>
	<td class="label">Nom complet :</td>
	<td><input name="insert[label]" value="" maxlength="128" style="width:100%;" /></td>
</tr>
<tr>
	<td class="label">Description :</td>
	<td><textarea name="insert[description]" style="width:100%;height:100%;" rows="10"></textarea></td>
</tr>
<tr>
	<td class="label">Dependances :</td>
	<td><select name="insert[library_list][]" size="10" multiple style="width:100%;">
	<?
	foreach($library_list as $i => $j)
		echo "<option value=\"$i\">$j[label]</option>";
	?>
	</select></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><input type="submit" value="Ajouter" /></td>
</tr>
</table>
</form>

<script language="Javascript" type="text/javascript">
$(document).ready(function(){
	// initialisation
	editAreaLoader.init({
		id: "insert[filecontent]"	// id of the textarea to transform		
		,start_highlight: true	// if start with highlight
		,allow_resize: "both"
		,allow_toggle: true
		,word_wrap: true
		,language: "fr"
		,syntax: "php"	
	});
});
</script>
<?

}

else
{

?>
<h3>Liste des librairies :</h3>

<p>Une librairie contient l'ensemble des méthode agissant sur une famille de dataobjects.</p>
<p>Les principales méthodes seront __tostring() s'agissant de l'affichage par défaut (en général le nom de l'objet).</p>
<p>On y trouvera parfois des méthodes de calcul faites sur plusieurs champs, des extractions de listes mises en forme, des variables statiques utiles pour l'ensemble des objets, etc.</p>

<table width="100%" cellpadding="2" cellspacing="2" border="1">
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
	<td><?php echo $library["label"]; ?></td>
	<td><?php echo $library["description"]; ?></td>
	<td><?php
	$library_show = array();
	foreach($library_list as $i => $j)
		if (in_array($i, $library_library))
			$library_show[] = $j["label"];
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
</div>