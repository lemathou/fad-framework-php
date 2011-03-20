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
	<a href="javascript:;" name="page_perm" onclick="admin_submenu(this.name)" <?php if ($submenu == "page_perm") echo "class=\"selected\""; ?>>Pages</a>
</div>

<div id="update_form" class="subcontents"<?php if ($submenu != "update_form") echo " style=\"display:none;\""; ?>>
<?php
$object->update_form();
?>
</div>

<div id="datamodel_perm" class="subcontents"<?php if ($submenu != "datamodel_perm") echo " style=\"display:none;\""; ?>>
<h1>Permissions spécifiques aux datamodels :</h1>
<p>En blue : permission OUI globale, en rouge : permission OUI cumulée</p>
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
		if (in_array($p, $datamodel->info("perm")))
			if (strpos($perm, $p) !== false)
				echo "<td><b style=\"color: red;\">OUI</b></td>\n";
			else
				echo "<td><b style=\"color: blue;\">OUI</b></td>\n";
		elseif (strpos($perm, $p) !== false)
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

<div id="page_perm" class="subcontents"<?php if ($submenu != "page_perm") echo " style=\"display:none;\""; ?>>
<h1>Permissions spécifiques aux pages :</h1>
<p>En blue : permission OUI globale, en rouge : permission OUI cumulée</p>
<table cellspacing="2" cellpadding="2" border="1">
<tr>
	<td>Page</td>
	<td>Accès</td>
</tr>
<?php
page()->retrieve_objects();
foreach (page()->list_get() as $page)
{
	$perm = $object->page($page->id());
	echo "<tr>\n";
	echo "<td>".$page->label()."</td>\n";
	if ($page->info("perm"))
		if (is_array($perm))
			echo "<td><b style=\"color: red;\">OUI</b></td>\n";
		else
			echo "<td><b style=\"color: blue;\">OUI</b></td>\n";
	elseif (is_array($perm))
		echo "<td><b>OUI</b></td>\n";
	else
		echo "<td>NON</td>\n";
	echo "</tr>\n";
}
?>
</table>
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
