<?

/**
  * $Id: admin.inc.php 76 2009-10-15 09:24:20Z mathieu $
  * 
  * Copyright 2008 Mathieu Moulin - iProspective - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */

header("Content-type: text/html; charset=".SITE_CHARSET);
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">

<head>

<meta http-equiv="Content-Type" content="text/html; charset=<?=SITE_CHARSET?>" />
<meta http-equiv="content-Language" content="<?=SITE_LANG?>" />

<meta name="robots" content="noindex,nofollow" />

<title>ADMINISTRATION</title>

<link rel="stylesheet" type="text/css" href="/css/jquery.asmselect.css" media="print, projection, screen, tv" />

<script language="Javascript" type="text/javascript" src="/edit_area/edit_area_full.js"></script>
<script language="Javascript" type="text/javascript" src="/js/jquery.js"></script>
<script language="Javascript" type="text/javascript" src="/js/jquery.asmselect.js"></script>

<style type="text/css">
.menu li
{
	float: left;
	margin-right: 5px;
	list-style-type: none;
}
</style>

</head>

<body>
<?

function admin_load()
{

$menu = array
(
	"account" => "Comptes utilisateur",
	"globals" => "Parametres généraux",
	"lang" => "Langues",
	"module" => "Modules",
	"library" => "Librairies",
	"datamodel" => "Datamodel",
	//"databank" => "Databank",
	"template" => "Templates",
	"page" => "Pages",
	"menu" => "Menus",
	"widget" => "Widgets",
);

if (!isset($_GET["_page"]) || !($page=$_GET["_page"]))
{
	echo "<p style=\"float:right;\">Select a menu</p>";
	$page = "template";
}
elseif (!isset($menu[$page]))
{
	echo "<p style=\"float:right;\">inexistant menu</p>";
	$page = "template";
}

?>
<div class="menu" style="height: 50px;">
<ul><?
foreach ($menu as $_page => $_name)
{
	echo "<li><a href=\"/admin/$_page\">$_name</a></li>";
}
?></ul>
</div>
<hr />
<?php

echo "<h1>".$menu[$page]."</h1>\n";
echo "<hr />\n";

include PATH_ADMIN."/$page.inc.php";

}

admin_load();

?>
</body>

</html>