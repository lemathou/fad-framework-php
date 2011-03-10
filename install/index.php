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

include "include/config.inc.php";
include "include/lang.inc.php";
include "include/functions.inc.php";
include "../classes/filesystem.inc.php";

// Update config params
foreach($_POST as $name=>$value) if (isset($config_list[$name]))
{
	$info = $config_list[$name];
	switch($info["type"])
	{
		case "select" :
			if (!$value || in_array($value, $info["select_list"]))
				$config_list[$name]["value"] = $value;
			break;
		case "boolean" :
			$config_list[$name]["value"] = $value ? "1" : "0";
			break;
		default :
			$config_list[$name]["value"] = $value;
			break;
	}
}

if (isset($_POST["_install"]))
	$install = true;
else
	$install = false;

$message = "";
// Paths
if (!$config_list["PATH_FRAMEWORK"]["value"] && !@is_dir($_POST["PATH_FRAMEWORK"]))
{
	$message = "<b>".$config_list["PATH_FRAMEWORK"]["label"]."</b> not correctly defined";
	$install = false;
}
else
{
	$config_list["PATH_FRAMEWORK"]["ok"] = true;
}
if (!$config_list["PATH_ROOT"]["value"] && !@is_dir($_POST["PATH_ROOT"]))
{
	$message = "<b>".$config_list["PATH_ROOT"]["label"]."</b> not correctly defined";
	$install = false;
}
else
{
	$config_list["PATH_ROOT"]["ok"] = true;
}
// Database
if ($config_list["DB_TYPE"]["value"])
	$config_list["DB_TYPE"]["ok"] = true;
if ($config_list["DB_HOST"]["value"] && $config_list["DB_USERNAME"]["value"] && $config_list["DB_PASSWORD"]["value"])
{
	if (!@mysql_connect($config_list["DB_HOST"]["value"], $config_list["DB_USERNAME"]["value"], $config_list["DB_PASSWORD"]["value"]))
	{
		$message = "Database connexion error : invalid password";
		$install = false;
	}
	elseif (!$config_list["DB_BASE"]["value"] || !@mysql_select_db($config_list["DB_BASE"]["value"]))
	{
		$message = "Database connexion error : no access to database '".$config_list["DB_BASE"]["value"];
		$install = false;
	}
	else
	{
		$config_list["DB_HOST"]["ok"] = true;
		$config_list["DB_USERNAME"]["ok"] = true;
		$config_list["DB_PASSWORD"]["ok"] = true;
		$config_list["DB_BASE"]["ok"] = true;
	}
}
else
{
	$message = "Database not correctly defined";
	$install = false;
}
// Email
if (!$config_list["SECURITY_EMAIL"]["value"])
{
	$message = "<b>".$config_list["SECURITY_EMAIL"]["label"]."</b> not correctly defined";
	$install = false;
}
else
{
	$config_list["SECURITY_EMAIL"]["ok"] = true;
}
// Domain
if (!$config_list["SITE_DOMAIN"]["value"])
{
	$message = "<b>".$config_list["SITE_DOMAIN"]["label"]."</b> not correctly defined";
	$install = false;
}
elseif (checkdnsrr($config_list["SITE_DOMAIN"]["value"], "A"))
{
	$config_list["SITE_DOMAIN"]["ok"] = true;
}
if (!$config_list["SITE_BASEPATH"]["value"])
{
	$message = "<b>".$config_list["SITE_BASEPATH"]["label"]."</b> not correctly defined";
	$install = false;
}
else
{
	$config_list["SITE_BASEPATH"]["ok"] = true;
}

$installed = false;
if ($install && install($config_list))
{
	$installed = true;
}
?>
<html>

<head>
<title>Installation</title>
<script type="text/javascript" src="/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="/js/common.js"></script>
<script type="text/javascript" src="/install/js/install.js"></script>
<style type="text/css">
table td.label
{
	border: 1px gray solid;
}
table td.label:hover
{
	background: #ffd;
}
table td p
{
	margin: 0;
}
table td.label label
{
	display: block;
	width: 100%;
}
</style>
</head>

<body>
<h1>Installation</h1>
<?php
if ($installed)
	echo "<p>Project sucessfully installed</p>";
if ($message)
	echo "<p>$message</p>\n";
?>
<form method="post">
<table cellspacing="2" cellpadding="2" border="0" width="100%">

<tr style="font-weight: bold;">
	<td>Options</td>
	<td width="300">&nbsp;</td>
	<td width="20">&nbsp;</td>
</tr>

<?php
foreach($config_list as $name=>$info)
{
	echo "<tr>\n";
	if (isset($info["info"]))
		echo "<td class=\"label\"><p><label for=\"$name\">$info[label]</label></p><p>($info[info])</p></td>\n";
	else
		echo "<td class=\"label\"><p><label for=\"$name\">$info[label]</label></p></td>\n";
	echo "<td>";
	if (!isset($info["value"]))
		$info["value"] = "";
	if ($info["type"] == "select")
	{
		echo "<select name=\"$name\" id=\"$name\"><option></option>";
		foreach($info["select_list"] as $value)
			if ($value && isset($info["select_control"]) && !$info["select_control"][$value])
				echo "<option value=\"$value\" disabled>$value (not installed)</option>";
			elseif ($value == $info["value"])
				echo "<option value=\"$value\" selected>$value</option>";
			else
				echo "<option value=\"$value\">$value</option>";
		echo "</select>";
	}
	elseif ($info["type"] == "text")
	{
		$onclick = (isset($info["onclick"])) ? " onclick=\"$info[onclick]\"" : "";
		$width = (isset($info["width"])) ? " style=\"width: $info[width];\"" : "";
		$readonly = (isset($info["onclick"])) ? " readonly" : "";
		echo "<input name=\"$name\" id=\"$name\" value=\"$info[value]\"$onclick$width$readonly />";
		if (isset($info["button"]))
			echo " ".$info["button"];
	}
	elseif ($info["type"] == "password")
	{
		echo "<input type=\"password\" id=\"$name\" name=\"$name\" value=\"$info[value]\" />";
		if (isset($info["button"]))
			echo " ".$info["button"];
	}
	elseif ($info["type"] == "boolean")
	{
		echo "<input type=\"radio\" id=\"${name}[0]\" name=\"$name\" value=\"0\"".($info["value"] == "0" ? " checked" : "")." /> <label for=\"${name}[0]\">NO</label> <input type=\"radio\" id=\"${name}[1]\" name=\"$name\" value=\"1\"".($info["value"] == "1" ? " checked" : "")." /> <label for=\"${name}[1]\">YES</label>";
	}
	echo "</td>";
		if (isset($info["ok"]) && $info["ok"])
		echo "<td style=\"color: blue;\">OK</td>\n";
	else
		echo "<td style=\"color: red;\">X</td>\n";
	echo "</tr>\n";
}
?>

<tr>
	<td>&nbsp;</td>
	<td><input type="submit" name="_verify" value="Verify config" /> <input type="submit" name="_install" value="INSTALL NOW" /></td>
</tr>

</table>
</form>
</body>

</html>
