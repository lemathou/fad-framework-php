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

if (isset($_POST["install_folder"]) && $_POST["install_folder"] && @is_dir($_POST["install_folder"]))
{
	filesystem::copydir(PATH_FRAMEWORK."/install/project", $_POST["install_folder"]);
}

// Config params
$config_list = array
(
	"PATH_ROOT"=>array("label"=>"Project install folder", "info"=>"Default to Framework folder", "type"=>"text", "button"=>"<input type=\"button\" value=\"Lookup\" onclick=\"folder_lookup('PATH_ROOT')\" />"),
	"SECURITY_EMAIL"=>array("label"=>"Security admin email", "type"=>"text"),
	"SITE_COPYRIGHT"=>array("label"=>"Copyright", "type"=>"text"),
	"SITE_ORGANISATION"=>array("label"=>"Organisation", "type"=>"text", "value"=>"My company"),
	"SITE_DOMAIN"=>array("label"=>"Domain name", "type"=>"text", "value"=>$_SERVER["SERVER_NAME"]),
	"SITE_BASEPATH"=>array("label"=>"Domain location", "type"=>"text", "value"=>"/"),
	"SITE_SSL_ENABLE"=>array("label"=>"SSL enabled", "type"=>"boolean"),
	"SITE_SSL_REDIRECT"=>array("label"=>"SSL force redirect", "type"=>"boolean"),
	"DB_TYPE"=>array("label"=>"Database engine", "type"=>"select", "select_list"=>array("MySQL", "MySQLi", "postgreSQL"), "select_control"=>&$php),
	//"DB_PERSISTANT"=>array("label"=>"Database engine", "type"=>"select", "select_list"=>array("MySQL", "MySQLi", "postgreSQL"), "select_control"=>&$php),
	"DB_HOST"=>array("label"=>"Database name", "type"=>"text", "value"=>""),
	"DB_USERNAME"=>array("label"=>"Database username", "type"=>"text", "value"=>""),
	"DB_PASSWORD"=>array("label"=>"Database password", "type"=>"text", "value"=>""),
	"DB_BASE"=>array("label"=>"Database name", "type"=>"text", "value"=>""),
	"SITE_MULTILANG"=>array("label"=>"Multi lang", "type"=>"boolean", "value"=>"0"),
	"CACHE_TYPE"=>array("label"=>"Object cache type", "info"=>"highly recommended if available", "type"=>"select", "select_list"=>array("", "APC", "MEMCACHED"), "select_control"=>&$php),
	"TEMPLATE_CACHE"=>array("label"=>"Template cache", "info"=>"highly recommended", "type"=>"select", "select_list"=>array("", "file", "APC", "MEMCACHED"), "value"=>"file"),
);
// Update config params
foreach($_POST as $name=>$value) if (isset($config_list[$name]))
{
	$info = $config_list[$name];
	switch($info["type"])
	{
		case "select" :
			if (in_array($value, $info["select_list"]))
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

?>
<html>

<head>
<title>Installation</title>
<script type="text/javascript" src="/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="/js/common.js"></script>
<script type="text/javascript" src="/install/js/install.js"></script>
</head>

<body>
<h1>Installation</h1>
<form method="post">
<table cellspacing="2" cellpadding="2" border="1" width="100%">

<tr style="font-weight: bold;">
	<td>Options</td>
	<td width="300">&nbsp;</td>
</tr>

<?php
foreach($config_list as $name=>$info)
{
	echo "<tr>\n";
	if (isset($info["info"]))
		echo "<td>$info[label]<br />($info[info])</td>\n";
	else
		echo "<td>$info[label]</td>\n";
	echo "<td>";
	if (!isset($info["value"]))
		$info["value"] = "";
	if ($info["type"] == "select")
	{
		echo "<select name=\"$name\">";
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
		echo "<input name=\"$name\" value=\"$info[value]\">";
		if (isset($info["button"]))
			echo " ".$info["button"];
	}
	elseif ($info["type"] == "boolean")
	{
		echo "<input type=\"radio\" name=\"$name\" value=\"0\"".($info["value"] == "0" ? " checked" : "")."> NO <input type=\"radio\" name=\"$name\" value=\"1\"".($info["value"] == "1" ? " checked" : "")."> YES";
	}
	echo "</td>";
	echo "</tr>\n";
}
?>

<tr>
	<td>&nbsp;</td>
	<td><input type="submit" value="INSTALL NOW" /></td>
</tr>

</table>
</form>
</body>

</html>