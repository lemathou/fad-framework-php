<?php

/**
  * $Id: index.php 66 2009-03-03 15:51:14Z mathieu $
  * 
  * « Copyright 2008 Mathieu Moulin - iProspective - lemathou@free.fr »
  * 
  * This file is part of FTNGroupWare.
  * 
  * FTNGroupWare is free software; you can redistribute it and/or modify
  * it under the terms of the GNU General Public License as published by
  * the Free Software Foundation; either version 2 of the License, or
  * (at your option) any later version.
  *
  * FTNGroupWare is distributed in the hope that it will be useful,
  * but WITHOUT ANY WARRANTY; without even the implied warranty of
  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  * GNU General Public License for more details.
  *
  * You should have received a copy of the GNU General Public License
  * along with FTNGroupWare; if not, write to the Free Software
  * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
  *
  * Package : FTNGroupWare
  * Author : Mathieu Moulin, iProspective <lemathou@free.fr>
  * Licence : http://www.gnu.org/copyleft/gpl.html  GNU General Public License
  *
  */

// Configuration
include "config/config.inc.php";

// Paramètres, Constantes, variables globales, constructeurs généraux, classes générales, fonctions diverses, etc.
include "include/header.inc.php";

// Gestion de la langue
include "include/lang.inc.php";

// Démarrage de la session
include "include/session_start.inc.php";

if (!isset($_SESSION["site_lang"]))
	$_SESSION["site_lang"] = SITE_LANG;

// Mise en place des banques de donnée et des fonctions associées !!
databank();

// Controller
include "include/data_controller.inc.php";

// Choix de la page
include "include/page_choose.inc.php";

header("Content-type: text/html; charset=".SITE_CHARSET);

// Affichage du template
page_current()->tpl()->disp();
gentime("TEMPLATE_DISP");

// On incrémente le nombre de pages vues par le visiteur
login()->page_count++;

gentime("END");

?>

<?php if (DEBUG_GENTIME) { ?>
<hr />
<h1>Tests cache</h1>
<?php
ob_start();
echo "<p>Une phrase de la mort qui tue</p>";
?>
<p>Une seconde phrase</p>
<?php
$tampon = ob_get_contents();
//file_put_contents('cache/index.html', $tampon)
ob_end_clean(); // toujours fermer et vider le tampon
echo $tampon;
?>

<hr />
<h1>Stats</h1>
<h3>PHP</h3>
<?
echo gentime()->total();
?>
<h3>MySQL</h3>
<?
echo "<br />queries ".db()->queries;
echo "<br />queries_total ".db()->queries_total;
echo "<br />fetch_results ".db()->fetch_results;
echo "<br />fetch_results_total ".db()->fetch_results_total;
echo "<br />time ".db()->time;
echo "<br />time_total ".db()->time_total;
foreach(db()->query_list as $query)
	echo "<br />$query\n";
?>
<?php } ?>
