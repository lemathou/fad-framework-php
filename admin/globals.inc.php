<?

/**
  * $Id$
  * 
  * � Copyright 2008 Mathieu Moulin - iProspective - lemathou@free.fr �
  * 
  * This file is part of FTNGroupWare.
  * 
  */

if (!defined("ADMIN_OK"))
	die("ACCES NON AUUTORISE");

// Insert
if (isset($_POST["insert"]) && is_array($_POST["insert"]))
{

$query_string = " INSERT INTO `_globals` ( `name` , `value` ) VALUES ( '".$_POST["insert"]["name"]."' , '".$_POST["insert"]["value"]."' ) ";
$query = db()->query($query_string);

if ($error = db()->error())
{
	print "<p>Une erreur est survenue : DEBUG : $error</p>\n";
}

}

// Update
if (isset($_POST["update"]) && is_array($_POST["update"]))
{
	
foreach($_POST["update"] as $name => $value)
{
	$query_string = " UPDATE `_globals` SET `value` = '$value' WHERE name = '$name' ";
	//print "<p>$query_string</p>\n";
	db()->query($query_string);
}

}

// Delete
if (isset($_GET["delete"]))
{

$name = $_GET["delete"];
$query_string = " DELETE FROM `_globals` WHERE name = '$name' ";
//print "<p>$query_string</p>\n";
db()->query($query_string);

}

?>

<style type="text/css">
table td
{
	vertical-align: top;
}
</style>

<form action="" method="POST">
<table>
<tr style="font-weight:bold;">
	<td width="200">Name</td>
	<td>Value</td>
</tr>
<tr>
	<td><input name="insert[name]" value="" /></td>
	<td><input name="insert[value]" value="" /></td>
	<td><input type="submit" value="Ajouter" /></td>
</tr>
</table>
</form>

<form action="" method="POST">
<table>
<tr style="font-weight:bold;">
	<td colspan="2" width="200">&nbsp;</td>
</tr>
<?
$query = db()->query(" SELECT name , value FROM _globals ORDER BY name ");
while ($global = $query->fetch_assoc())
{

print "<tr>\n";
print "<td><a href=\"?delete=$global[name]\" style=\"color:red;text-decoration:none;border:1px red dotted;\">X</a></td>\n";
print "<td>$global[name]</td>\n";
print "<td><input name=\"update[$global[name]]\" value=\"$global[value]\" /></td>\n";
print "</tr>\n";

}
?>
<tr>
	<td>&nbsp;</td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td>&nbsp;</td>
	<td><input type="submit" value="Mettre à jour" /></td>
</tr>
</table>
</form>