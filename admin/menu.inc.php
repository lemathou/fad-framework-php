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

$_type = "menu";
$_label = "Menu";

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
?>
</select>
<a href="?add">Ajouter</a>
<a href="?list">Retour à la liste</a>
</form>

<?php

if (isset($_GET["id"]) && $_type()->exists($id=$_GET["id"]))
{

$_type($id)->update_form();

$menu = menu($id);

if (isset($_POST["page_add"]) && $_POST["page_add"])
{
	$menu->add($_POST["page_add"]);
}

if (isset($_POST["pos_move"]))
{
	foreach ($_POST["pos_move"] as $pos_from=>$pos_to)
	{
		$menu->pos_change($pos_from, $pos_to);
	}
}

if (isset($_POST["pos_del"]) && $_POST["pos_del"])
{
	$menu->del($_POST["pos_del"]);
}
?>

<form method="post" id="menu_form">
<input id="pos_del" name="pos_del" type="hidden" value="0" />
<p><select name="page_add"><option value="0">Sélectionnez une page à ajouter</option><?php
foreach (page()->list_get() as $page)
{
	echo "<option value=\"".$page->id()."\">ID#".$page->id()." : ".$page->name()."</option>\n";
}
?></select> <input type="submit" value="Ajouter la page en fin de menu" /></p>
<table cellspacing="0" cellpadding="2" border="1">
<tr class="titre">
	<td>&nbsp;</td>
	<td>Pos.</td>
	<td>ID</td>
	<td>Nom</td>
	<td>Aperçu</td>
</tr>
<?php
$list = $menu->list_get();
$nbmax = count($list);
foreach($list as $i=>$page_id)
{
	echo "<tr>\n";
	echo "<td><a href=\"javascript:;\" onclick=\"document.getElementById('pos_del').value=$i;document.getElementById('menu_form').submit();\" style=\"color:red;text-decoration:none;\">X</a></td>\n";
	echo "<td><select id=\"pos_move[$i]\" onchange=\"this.name=this.id;this.form.submit();\">\n";
	for ($j=0;$j<$nbmax;$j++)
		if ($j == $i)
			echo "<option value=\"$j\" selected>$j</option>";
		else
			echo "<option value=\"$j\">$j</option>";
	echo "</select></td>\n";
	echo "<td>".page($page_id)->id()."</td>\n";
	echo "<td>".page($page_id)->name()."</td>\n";
	echo "<td>".page($page_id)->link()."</td>\n";
	echo "</tr>\n";
}
?>
</table>
</form>
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