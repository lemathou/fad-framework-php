<?php

/**
  * $Id: account.inc.php 76 2009-10-15 09:24:20Z mathieu $
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

?>

<form method="get" class="page_form">
<input type="submit" value="Donnée" />
<select name="datamodel_id" onchange="this.form.object_id.selectedIndex=0;this.form.submit()">
	<option value=""></option>
<?php
foreach (datamodel()->list_name_get() as $name=>$id)
{
	if (isset($_GET["datamodel_id"]) && ($id==$_GET["datamodel_id"]))
		echo "	<option value=\"$id\" selected>[$id] $name</option>\n";
	else
		echo "	<option value=\"$id\">[$id] $name</option>\n";
}
?>
</select>
<select name="object_id" onchange="this.form.submit()">
	<option value=""></option>
<?php
if (isset($_GET["datamodel_id"]) && $_GET["datamodel_id"]) foreach (databank($_GET["datamodel_id"])->query() as $object)
{
	if (isset($_GET["object_id"]) && ("$object->id"==$_GET["object_id"]))
		echo "	<option value=\"$object->id\" selected>[$object->id] $object</option>\n";
	else
		echo "	<option value=\"$object->id\">[$object->id] $object</option>\n";
}
?>
</select>
<?php if (isset($_GET["datamodel_id"]) && isset($_GET["datamodel_id"])) { ?>
<a href="?datamodel_id=<?php echo $_GET["datamodel_id"]; ?>&list">Liste</a>
<a href="?datamodel_id=<?php echo $_GET["datamodel_id"]; ?>&add">Ajouter</a>
<?php } ?>
</form>

<div style="padding-top: 30px">
<?php

if (isset($_GET["datamodel_id"]) && $_GET["datamodel_id"] && isset($_GET["add"]))
{

datamodel($_GET["datamodel_id"])->insert_form()->disp();

}

elseif (isset($_GET["datamodel_id"]) && isset($_GET["object_id"]) && $_GET["object_id"])
{

$object = databank($_GET["datamodel_id"])->get($_GET["object_id"]);

$object->form()->disp();

}

?>
</div>