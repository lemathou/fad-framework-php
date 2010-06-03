<?

/**
  * $Id: admin.php 76 2009-10-15 09:24:20Z mathieu $
  * 
  * Copyright 2008 Mathieu Moulin - iProspective - lemathou@free.fr
  * 
  * This file is part of FTNGroupWare.
  * 
  */

define("ADMIN_OK",true);
define("SITE_LANG",SITE_LANG_DEFAULT);
define("SITE_LANG_ID",SITE_LANG_DEFAULT_ID);

require_once "include/header.inc.php";

$menu = array
(
	"account" => "Comptes utilisateur",
	"globals" => "Parametres généraux",
	"template" => "Templates",
	"menu" => "Menus",
	"lang" => "Langues",
	"widget" => "Widgets",
	"databank" => "Databank",
	"datamodel" => "Datamodel",
	"library" => "Librairies",
	"module" => "Modules",
);

if (isset($_GET["_page"]) && isset($menu[$_GET["_page"]]))
{

$_page = $_GET["_page"];

}
else
{

$_page = "menu";

}

header("Content-type: text/html; charset=".SITE_CHARSET);

?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">

<head>

<meta http-equiv="Content-Type" content="text/html; charset=<?=SITE_CHARSET?>" />
<meta http-equiv="content-Language" content="<?=SITE_LANG?>" />

<meta name="robots" content="noindex,nofollow" />

<title>ADMINISTRATION</title>

<script language="Javascript" type="text/javascript" src="/edit_area/edit_area_full.js"></script>
<script language="Javascript" type="text/javascript" src="/js/jquery.js"></script>

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

<div class="menu" style="height: 50px;">
<ul>
<?
foreach ($menu as $page => $name)
{
	
print "<li><a href=\"/admin/$page\">$name</a></li>";

}
?>
</ul>
</div>

<hr/>

<?

$filename = "admin/$_page.inc.php";
print "<h1>".$menu[$_page]."</h1>\n";
print "<hr/>\n";

function admin_load($filename)
{

require_once($filename);

}

admin_load($filename);

?>

</body>

</html>
