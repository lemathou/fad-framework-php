<?

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

// Insert
if (isset($_POST["_insert"]))
{

library()->add($_POST);

}

// Update
if (isset($_POST["_update"]) && isset($_POST["id"]) && library()->exists($_POST["id"]))
{

library($_POST["id"])->update($_POST);

}

// Delete
if (isset($_POST["_delete"]) && library()->exists($_POST[_delete]))
{

library()->delete($_POST["_delete"]);

}

$library_list = library()->list_detail_get();

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

$library = $library_list[$id];

?>
<form action="?id=<?=$id?>" method="post">
<table width="100%">
<tr>
	<td class="label">ID :</td>
	<td><input name="id" value="<?=$id?>" readonly /></td>
	<td rowspan="6" width="60%"><textarea id="filecontent" name="filecontent" style="width:100%;"><?php 
	$filename = "library/$library[name].inc.php";
	if (file_exists($filename))
	{
		echo $content = htmlspecialchars(fread(fopen($filename,"r"),filesize($filename)));
	}
	?></textarea></td>
</tr>
<tr>
	<td class="label">Name :</td>
	<td><input name="name" value="<?=$library["name"]?>" maxlength="64" size="32" /></td>
</tr>
<tr>
	<td class="label">Nom complet :</td>
	<td><input name="label" value="<?=$library["label"]?>" maxlength="128" size="32" /></td>
</tr>
<tr>
	<td class="label">Description :</td>
	<td><textarea name="description" class="data_text" style="width:100%;"><?=$library["description"]?></textarea></td>
</tr>
<tr>
	<td class="label">Dependances :</td>
	<td><select name="library_list[]" size="10" multiple>
	<?
	foreach($library_list as $i=>$j)
		if (in_array($i, $library["dep_list"]))
			echo "<option value=\"$i\" selected>$j[label]</option>";
		elseif ($id != $i)
			echo "<option value=\"$i\">$j[label]</option>";
	?>
	</select></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><input type="submit" name="_update" value="Mettre à jour" /></td>
</tr>
</table>
</form>

<script language="Javascript" type="text/javascript">
$(document).ready(function(){
	// initialisation
	editAreaLoader.init({
		"id": "filecontent"	// id of the textarea to transform		
		,"start_highlight": true	// if start with highlight
		,"allow_resize": "both"
		,"min_height": "600"
		,"allow_toggle": true
		,"word_wrap": true
		,"language": "fr"
		,"syntax": "php"	
	});
});
</script>
<?php

}

elseif (isset($_GET["add"]))
{

$info = array
(
	"name"=>"",
	"label"=>"",
	"description"=>"",
	"dep_list"=>array(),
	"filecontent"=>""
);

?>
<form action="?add" method="POST">
<table width="100%">
<tr>
	<td class="label">Name (unique) :</td>
	<td><input name="name" value="<?php echo $info["name"]; ?>" maxlength="64" size="32" /></td>
	<td rowspan="10" width="60%"><textarea id="filecontent" name="filecontent" style="width:100%;"><?php echo $info["filecontent"]; ?></textarea></td>
</tr>
<tr>
	<td class="label">Nom complet :</td>
	<td><input name="label" value="<?php echo $info["label"]; ?>" maxlength="128" size="32" /></td>
</tr>
<tr>
	<td class="label">Description :</td>
	<td><textarea name="description" style="width:100%;" rows="10"><?php echo $info["description"]; ?></textarea></td>
</tr>
<tr>
	<td class="label">Dependances :</td>
	<td><select name="dep_list[]" size="10" multiple>
	<?
	foreach($library_list as $i=>$j)
		echo "<option value=\"$i\">$j[label]</option>";
	?>
	</select></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><input type="submit" name="_insert" value="Ajouter" /></td>
</tr>
</table>
</form>

<script language="Javascript" type="text/javascript">
$(document).ready(function(){
	// initialisation
	editAreaLoader.init({
		"id": "filecontent"	// id of the textarea to transform		
		,"start_highlight": true	// if start with highlight
		,"allow_resize": "both"
		,"min_height": "600"
		,"allow_toggle": true
		,"word_wrap": true
		,"language": "fr"
		,"syntax": "php"	
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

<?php

library()->table_list(array(), array("label", "description", "dep_list"));

}

?>
</div>