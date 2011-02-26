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
		echo "	<option value=\"$id\" selected>[$id] ".$object->label()."</option>\n";
	else
		echo "	<option value=\"$id\">[$id] ".$object->label()."</option>\n";
}
?>
</select>
<a href="?list">Liste</a>
<a href="?add">Ajouter</a>
</form>

<?php

if (isset($_GET["id"]) && $_type()->exists($id=$_GET["id"]))
{

$submenu = "update_form";

$object = $_type($id);

?>
<div class="admin_menu admin_submenu">
	<a href="javascript:;" name="update_form" onclick="admin_submenu(this.name)" <?php if ($submenu == "update_form") echo "class=\"selected\""; ?>>Formulaire</a>
	<a href="javascript:;" name="datamodel_perm" onclick="admin_submenu(this.name)" <?php if ($submenu == "datamodel_perm") echo "class=\"selected\""; ?>>Datamodels</a>
	<a href="javascript:;" name="dataobject_perm" onclick="admin_submenu(this.name)" <?php if ($submenu == "dataobject_perm") echo "class=\"selected\""; ?>>Dataobjects</a>
</div>

<div id="update_form" class="subcontents"<?php if ($submenu != "update_form") echo " style=\"display:none;\""; ?>>
<?php
$object->update_form();
?>
</div>

<div id="datamodel_perm" class="subcontents"<?php if ($submenu != "datamodel_perm") echo " style=\"display:none;\""; ?>>
<h1>Permissions spécifiques aux datamodels :</h1>
<table cellspacing="2" cellpadding="2" border="1">
<tr>
	<td>Datamodel</td>
<?php
foreach(_permission_gestion::perm_list() as $p)
	echo "<td>$p</td>\n";
?>
</tr>
<?php
datamodel()->retrieve_objects();
foreach (datamodel()->list_get() as $datamodel)
{
	$perm = $object->datamodel($datamodel->id());
	echo "<tr>\n";
	echo "<td>".$datamodel->label()."</td>\n";
	foreach(_permission_gestion::perm_list() as $p=>$q)
		if (strpos($perm, $p) !== false)
			echo "<td><b>OUI</b></td>\n";
		else
			echo "<td>NON</td>\n";
	echo "</tr>\n";
}
?>
</table>
</div>

<div id="dataobject_perm" class="subcontents"<?php if ($submenu != "dataobject_perm") echo " style=\"display:none;\""; ?>>
</div>
<?php

}

elseif (isset($_GET["add"]))
{

$_type()->insert_form();
	
}

else
{

$_type()->table_list();

}

?>
