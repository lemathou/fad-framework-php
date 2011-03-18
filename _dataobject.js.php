<?php

/**
  * $Id: dataobject.js.php 42 2011-02-20 10:57:47Z lemathoufou $
  * 
  * « Copyright 2008-2011 Mathieu Moulin - lemathou@free.fr »
  * 
  * This file is part of PHP FAD FRAMEWORK
  * http://sourceforge.net/projects/phpfadframework/
  * Licence : http://www.gnu.org/copyleft/gpl.html  GNU General Public License
  *
  */


if (!defined("PATH_INCLUDE"))
	die("Config file not loaded");

include PATH_INCLUDE."/header.inc.php";

if (isset($_GET["datamodel"]) && ($datamodel=datamodel($_GET["datamodel"])) && isset($_GET["id"]) && ($object=$datamodel->get($_GET["id"])))
	echo $object->js();

?>