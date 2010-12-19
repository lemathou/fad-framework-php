<?php

/**
  * $Id: permission.inc.php 21 2010-12-17 15:48:39Z lemathoufou $
  * 
  * Copyright 2008 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */

if (!defined("ADMIN_OK"))
	die("ACCES NON AUTORISE");

$_type = "permission";
$_label = "Permission";

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

$_type_list = $_type()->list_detail_get();

?>
<form method="get" class="page_form">
<input type="submit" value="<?php echo $_label; ?>" />
<select name="id" onchange="this.form.submit()">
	<option value=""></option>
<?php
foreach ($_type()->list_detail_get() as $id=>$info)
{
	if (isset($_GET["id"]) && ($id==$_GET["id"]))
		echo "	<option value=\"$id\" selected>[$id] $info[name]</option>\n";
	else
		echo "	<option value=\"$id\">[$id] $info[name]</option>\n";
}
?></select>
<a href="?add">Ajouter</a>
<a href="?list">Retour à la liste</a>
</form>

<div style="padding-top: 30px">
<?php

if (isset($_GET["id"]) && $_type()->exists($id=$_GET["id"]))
{

$object = $_type($id);

?>
<form action="?id=<?=$id?>" method="post">
<table width="100%">
<tr>
	<td class="label"><label for="id">ID :</label></td>
	<td><input name="id" value="<?=$id?>" readonly /></td>
</tr>
<tr>
	<td class="label"><label for="name">Name :</label></td>
	<td><input name="name" value="<?=$object->info("name")?>" maxlength="64" size="64" /></td>
</tr>
<tr>
	<td class="label"><label for="label">Nom complet :</label></td>
	<td><input name="label" value="<?=$object->info("label")?>" maxlength="128" size="64" /></td>
</tr>
<tr>
	<td class="label"><label for="description">Description :</label></td>
	<td><textarea name="description" style="width:100%;" rows="10"><?=$object->info("description")?></textarea></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><input type="submit" name="_update" value="Mettre à jour" /></td>
</tr>
</table>
</form>
<?php

}

elseif (isset($_GET["add"]))
{

$object = array
(
	"name"=>"",
	"label"=>"",
	"description"=>""
);
?>
<form action="?list" method="post">
<table width="100%">
<tr>
	<td class="label"><label for="name">Name :</label></td>
	<td><input name="name" value="<?=$object["name"]?>" maxlength="64" size="64" /></td>
</tr>
<tr>
	<td class="label"><label for="label">Nom complet :</label></td>
	<td><input name="label" value="<?=$object["label"]?>" maxlength="128" size="64" /></td>
</tr>
<tr>
	<td class="label"><label for="description">Description :</label></td>
	<td><textarea name="description" style="width:100%;" rows="10"><?=$object["description"]?></textarea></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><input type="submit" name="_insert" value="Ajouter" /></td>
</tr>
</table>
</form>
<?php

}

else
{

$_type()->table_list(array(), array("label", "description"));

}

?>
</div>