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

// Config params
$config_list = array
(
	"PATH_FRAMEWORK"=>array("label"=>"Framework root folder", "type"=>"text", "value"=>PATH_FRAMEWORK, "onclick"=>"folder_lookup('PATH_FRAMEWORK')", "width"=>"100%"),
	"PATH_ROOT"=>array("label"=>"Project install folder", "info"=>"Default to Framework folder", "type"=>"text", "value"=>PATH_FRAMEWORK, "onclick"=>"folder_lookup('PATH_ROOT')", "width"=>"100%"),
	"SECURITY_EMAIL"=>array("label"=>"Security admin email", "type"=>"text", "value"=>"", "width"=>"100%"),
	"SITE_COPYRIGHT"=>array("label"=>"Copyright", "type"=>"text", "value"=>"", "width"=>"100%", "ok"=>true),
	"SITE_ORGANISATION"=>array("label"=>"Organisation", "type"=>"text", "value"=>"My company", "width"=>"100%", "ok"=>true),
	"SITE_DOMAIN"=>array("label"=>"Domain name", "type"=>"text", "value"=>$_SERVER["SERVER_NAME"], "width"=>"100%"),
	"SITE_BASEPATH"=>array("label"=>"Domain location", "type"=>"text", "value"=>"/", "width"=>"100%"),
	"SITE_SSL_ENABLE"=>array("label"=>"SSL enabled", "type"=>"boolean", "value"=>"0", "ok"=>true),
	"SITE_SSL_REDIRECT"=>array("label"=>"SSL force redirect", "type"=>"boolean", "value"=>"0", "ok"=>true),
	"DB_TYPE"=>array("label"=>"Database engine", "type"=>"select", "value"=>"MySQL", "select_list"=>array("mysql", "mysqli", "postgresql"), "select_control"=>&$php["db"]),
	//"DB_PERSISTANT"=>array("label"=>"Database engine", "type"=>"select", "select_list"=>array("MySQL", "MySQLi", "postgreSQL"), "select_control"=>&$php),
	"DB_HOST"=>array("label"=>"Database hostname", "type"=>"text", "value"=>"", "width"=>"100%"),
	"DB_USERNAME"=>array("label"=>"Database username", "type"=>"text", "value"=>"", "width"=>"100%"),
	"DB_PASSWORD"=>array("label"=>"Database password", "type"=>"password", "value"=>"", "width"=>"100%"),
	"DB_BASE"=>array("label"=>"Database name", "type"=>"text", "value"=>"", "width"=>"100%"),
	"SITE_MULTILANG"=>array("label"=>"Multi lang", "type"=>"boolean", "value"=>"0", "ok"=>true),
	"CACHE_TYPE"=>array("label"=>"Object cache type", "info"=>"highly recommended if available", "type"=>"select", "value"=>"", "select_list"=>array("apc", "memcached"), "select_control"=>&$php["cache"], "ok"=>true),
	"TEMPLATE_CACHE_TYPE"=>array("label"=>"Template cache", "info"=>"highly recommended", "type"=>"select", "value"=>"", "select_list"=>array("file", "apc", "memcached"), "select_control"=>&$php["cache"], "value"=>"file", "ok"=>true),
);
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
