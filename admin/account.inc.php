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

// Permissions
$perm_list = array();
$query = db()->query(" SELECT id , name FROM _perm ");
while ($perm = $query->fetch_assoc())
{
	$perm_list[$perm["id"]] = $perm["name"];
}

// Lang
$lang_list = array();
$query = db()->query(" SELECT id , name FROM _lang ");
while ($lang = $query->fetch_assoc())
{
	$lang_list[$lang["id"]] = $lang["name"];
}

// Insert
if (isset($_POST["insert"]) && is_array($_POST["insert"]))
{

$query_string = " INSERT INTO `_account` ( `create_datetime` , `update_datetime` , `actif` , `email` , `password` , `password_crypt` , `lang_id` , `sid` ) VALUES ( NOW() , NOW() , '".$_POST["insert"]["actif"]."' , '".$_POST["insert"]["email"]."' , '".$_POST["insert"]["password"]."' , '".md5($_POST["insert"]["password"])."' , '".$_POST["insert"]["lang_id"]."' , '".$_POST["insert"]["sid"]."' ) ";
$query = db()->query($query_string);

if (($id = $query->last_id()))
{
	if (isset($_POST["insert"]["perm_list"]) && is_array($_POST["insert"]["perm_list"]) && (count($_POST["insert"]["perm_list"]) > 0))
	{
		$query_perm_list = array();
		foreach($_POST["insert"]["perm_list"] as $perm_id)
			if (isset($perm_list[$perm_id]))
				$query_perm_list[] = "( $id , $perm_id )";
		if (count($query_perm_list)>0)
		{
			$query_string = " INSERT INTO `_account_perm_ref` ( `account_id` , `perm_id` ) VALUES ".implode(" , ",$query_perm_list);
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
if (isset($_POST["update"]) && is_array($_POST["update"]))
{
	
foreach($_POST["update"] as $id => $field)
{
	$query_string = " UPDATE `_account` SET `update_datetime` = NOW() , `actif` = '$field[actif]' , `username` = '$field[username]' , `password` = '$field[password]' , `password_crypt`  = '".md5($field["password"])."' , `lang_id`= '$field[lang_id]' , `email` = '$field[email]' , `sid` = '$field[sid]' WHERE id = $id ";
	//print "<p>$query_string</p>\n";
	db()->query($query_string);
	db()->query(" DELETE FROM `_account_perm_ref` WHERE `account_id` = $id ");
	if (isset($field["perm_list"]) && is_array($field["perm_list"]) && (count($field["perm_list"]) > 0))
	{
		$query_perm_list = array();
		foreach($field["perm_list"] as $perm_id)
			if (isset($perm_list[$perm_id]))
				$query_perm_list[] = "( $id , $perm_id )";
		if (count($query_perm_list)>0)
		{
			$query_string = " INSERT INTO `_account_perm_ref` ( `account_id` , `perm_id` ) VALUES ".implode(" , ",$query_perm_list);
			db()->query($query_string);
		}
	}
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
if (isset($_GET["add"]))
{
?>
<p><a href="?list">Retourner à la liste</a></p>
<form action="?view" method="POST">
<table>
<tr style="font-weight:bold;">
	<td>Actif</td>
	<td>Email</td>
	<td>Password</td>
	<td>Langage</td>
	<td>SID</td>
	<td>Permissions globales</td>
</tr>
<tr>
	<td><input name="insert[actif]" value="" size="1" /></td>
	<td><input name="insert[email]" value="" /></td>
	<td><input name="insert[password]" value="" size=\"16\" /></td>
	<td><select name="insert[lang_id]" size="1">
		<option value="0">-- Choisissez --</option>
	<?
	foreach($lang_list as $i => $j)
		print "<option value=\"$i\">$j</option>";
	?>
	</select></td>
	<td><input name="insert[sid]" value="" /></td>
	<td><select name="insert[perm_list][]" size="4" multiple>
	<?
	foreach($perm_list as $i => $j)
		print "<option value=\"$i\">$j</option>";
	?>
	</select></td>
	<td><input type="submit" value="Ajouter" /></td>
</tr>
</table>
</form>
<?php
}
elseif (isset($_GET["edit"]))
{

$account = new account($_GET["edit"]);

?>

<p><a href="?list">Retourner à la liste</a></p>

<form action="?view" method="POST">
<table>
<tr>
	<td>ID</td>
	<td><?php echo $account->id; ?></td>
</tr>
<tr>
	<td>Date création</td>
	<td><?php echo $account->create_datetime; ?></td>
</tr>
<tr>
	<td>Date mise à jour</td>
	<td><?php echo $account->update_datetime; ?></td>
</tr>
<tr>
	<td>Actif</td>
	<td><?php echo $account->actif; ?></td>
</tr>
<tr>
	<td>Email</td>
	<td><?php echo $account->email; ?></td>
</tr>
<tr>
	<td>Password</td>
	<td><?php echo $account->password; ?></td>
</tr>
<tr>
	<td>Password encrypté</td>
	<td><?php echo $account->password_crypt; ?></td>
</tr>
<tr>
	<td>Langage</td>
	<td><select name=""><?php
	foreach($lang_list as $i => $j)
		if ($i == "$account->lang_id")
			print "<option value=\"$i\" selected=\"selected\">$j</option>";
		else
			print "<option value=\"$i\">$j</option>";
	?></select></td>
</tr>
<tr>
	<td>SID</td>
	<td><?php echo $account->sid; ?></td>
</tr>
<tr>
	<td>Permissions globales</td>
	<td><select name="" multiple><?php
	foreach($perm_list as $i => $j)
	{
		if (in_array($i, $account->perm_list))
			print "<option value=\"$i\" selected=\"selected\">$j</option>";
		else
			print "<option value=\"$i\">$j</option>";
	}
	?></select></td>
</tr>
</table>
<input type="submit" value="Mettre à jour" />
</form>

<?php
}
else
{
?>

<form action="" method="POST">
<table>
<tr style="font-weight:bold;">
	<td>ID</td>
	<td>Email</td>
	<td>Date création</td>
	<td>Date mise à jour</td>
	<td>Actif</td>
	<td>Langage</td>
	<td>Permissions globales</td>
</tr>
<?
$query = db()->query(" SELECT id , `create_datetime` , `update_datetime` , `actif` , `password` , `lang_id` , `email` , `sid` FROM _account ORDER BY email ");
while ($account = $query->fetch_assoc())
{

$account_perm = array();
$query_perm = db()->query(" SELECT perm_id FROM _account_perm_ref WHERE account_id = $account[id] ");
while (list($id) = $query_perm->fetch_row())
	$account_perm[] = $id;

print "<tr>\n";
print "<td><a href=\"?edit=$account[id]\">$account[id]</a></td>\n";
print "<td>$account[email]</td>\n";
print "<td><input value=\"$account[create_datetime]\" readonly size=\"19\" /></td>\n";
print "<td><input value=\"$account[update_datetime]\" readonly size=\"19\" /></td>\n";
if ($account["actif"])
	print "<td style=\"color:green;\">OUI</td>\n";
else
	print "<td style=\"color:red;\">NON</td>\n";
print "<td><select name=\"update[$account[id]][lang_id]\" size=\"1\">";
foreach($lang_list as $i => $j)
	if ($i == $account["lang_id"])
		print "<option value=\"$i\" selected=\"selected\">$j</option>";
	else
		print "<option value=\"$i\">$j</option>";
print "</select></td>\n";
print "<td><select name=\"update[$account[id]][perm_list][]\" multiple=\"true\" size=\"4\">";
foreach($perm_list as $i => $j)
	if (in_array($i, $account_perm))
		print "<option value=\"$i\" selected=\"selected\">$j</option>";
	else
		print "<option value=\"$i\">$j</option>";
print "</select></td>\n";
print "</tr>\n";

}
?>
</table>
<input type="submit" value="Mettre à jour" />
</form>

<?php
}
?>
