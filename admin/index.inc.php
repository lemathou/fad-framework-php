<?php

/**
  * $Id: admin.inc.php 76 2009-10-15 09:24:20Z mathieu $
  * 
  * Copyright 2008 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */


// Page list
$admin_menu = array
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
	"data" => "Données",
);

// Default page
$admin_page = "template";

function admin_select()
{

global $admin_menu;
global $admin_page;

if (isset($_GET["_page"]) && isset($admin_menu[$_GET["_page"]]))
{
	$admin_page = $_GET["_page"];
}

}

function admin_disp()
{

global $admin_page;

include PATH_ADMIN."/$admin_page.inc.php";

}

admin_select();

header("Content-type: text/html; charset=".SITE_CHARSET);
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">

<head>

<meta http-equiv="Content-Type" content="text/html; charset=<?=SITE_CHARSET?>" />
<meta http-equiv="content-Language" content="<?=SITE_LANG?>" />

<meta name="robots" content="noindex,nofollow" />

<title><?php echo $admin_menu[$admin_page]; ?> - ADMINISTRATION</title>

<link rel="stylesheet" type="text/css" href="/css/jquery.asmselect.css" media="print, projection, screen, tv" />

<script language="Javascript" type="text/javascript" src="/edit_area/edit_area_full.js"></script>
<script language="Javascript" type="text/javascript" src="/js/jquery.js"></script>
<script language="Javascript" type="text/javascript" src="/js/jquery.asmselect.js"></script>

<style type="text/css">
body
{
	margin: 0px;
	border: 0px;
	padding: 0px;
}

.menu
{
	position: fixed;
	top: 0px;
	width: 100%;
	background-color: white;
	padding-bottom: 4px;
}
.menu ul
{
	height: 45px;
	margin: 0px;
	border-bottom: 1px blue solid;
}
.menu li
{
	float: left;
	margin: 0px;
	margin-right: 10px;
	list-style-type: none;
}
.menu li a
{
	text-decoration: none;
	color: blue;
	font-weight: bold;
}
.menu li a:hover, .menu li.selected a
{
	color: orange;
}

.page_content
{
	padding: 50px 5px 5px 5px;
}

.page_form
{
	position: fixed;
	top: 50px;
	left: 0px;
	width: 100%;
	height: 30px;
	border-bottom: 1px blue solid;
	padding: 0px 5px;
	background-color: white;
}

table td
{
	vertical-align: top;
}
</style>

</head>

<body>

<div class="menu">
<ul>
<?php
foreach ($admin_menu as $_page => $_name)
{
	if ($admin_page == $_page)
		echo "	<li class=\"selected\"><a href=\"/admin/$_page\">$_name</a></li>\n";
	else
		echo "	<li><a href=\"/admin/$_page\">$_name</a></li>\n";
}
?>
<li style="clear: both;"></li>
</ul>
</div>

<div class="page_content">
<?php admin_disp(); ?>
</div>

</body>

</html>