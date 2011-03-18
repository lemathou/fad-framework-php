<?php

/**
  * $Id: view.php 32 2011-01-24 07:13:42Z lemathoufou $
  * 
  * Copyright 2008-2011 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * http://sourceforge.net/projects/phpfadframework/
  * Licence : http://www.gnu.org/copyleft/gpl.html  GNU General Public License
  * 
  */

if (!defined("PATH_INCLUDE"))
	die("Config file not loaded");

include PATH_INCLUDE."/header.inc.php";

function action()
{

foreach($_GET as $i=>$j)
	$_POST[$i] = $j;

if (!isset($_POST["datamodel"]) || !($datamodel=datamodel($_POST["datamodel"])))
	die();

$object = $datamodel->create();

header("Content-type: text/html; charset=".SITE_CHARSET);

if (isset($_POST["template"]) && ($template=template($_POST["template"])))
	$object->disp($_POST["template"]);
else
	echo $object->insert_form()->content_disp();

}

action();

?>