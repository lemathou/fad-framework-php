<?php

/**
  * $Id: folder.php 50 2011-03-05 18:30:47Z lemathoufou $
  * 
  * Copyright 2008-2011 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * http://sourceforge.net/projects/phpfadframework/
  * Licence : http://www.gnu.org/copyleft/gpl.html  GNU General Public License
  * 
  */

include "../classes/filesystem.inc.php";
header("Content-type: text/html; charset=UTF-8");

?>
<html>

<head>
<script type="text/javascript" src="/js/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="/install/js/install.js"></script>
<style type="text/css">
body, p, td
{
	font-size: 10pt;
}
</style>
</head>

<body>
<?php

$origin_e = explode("/", $_SERVER["SCRIPT_FILENAME"]);
array_pop($origin_e);
array_pop($origin_e);
$origin = implode("/", $origin_e);

// Context parameters
if (!isset($_GET["path"]) || !is_string($_GET["path"]) || !@is_dir($origin."/".$_GET["path"]))
	$path = ".";
else
	$path = $_GET["path"];

$hidden = (empty($_GET["hidden"])) ? "0" : "1";

// Actions
if (isset($_POST["folder_create"]) && is_string($name=$_POST["folder_create"]))
{
	filesystem::mkdir("$origin/$path/$name");
}
if (isset($_POST["file_delete"]) && is_string($name=$_POST["file_delete"]))
{
	filesystem::rmdir("$origin/$path/$name");
}
if (isset($_POST["file_rename"]) && is_string($name=$_POST["file_rename"]) && preg_match("/([[\.]*[a-z0-9_-]+[a-z0-9_\.-])/i", $name) && isset($_POST["file_rename_new"]) && is_string($newname=$_POST["file_rename_new"]) && preg_match("/([[\.]*[a-z0-9_-]+[a-z0-9_\.-])/i", $newname) && file_exists("$origin/$path/$name") && is_writable("$origin/$path") && is_writable("$origin/$path/$name"))
{
	filesystem::rename("$origin/$path/$name", "$origin/$path/$newname");
}

// Display
filesystem::folder_disp($path, array("origin"=>$origin, "hidden"=>$hidden));

?>
</body>
</html>
