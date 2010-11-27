<?php

/**
  * $Id: index.php 66 2009-03-03 15:51:14Z mathieu $
  * 
  * « Copyright 2008 Mathieu Moulin - lemathou@free.fr »
  * 
  * This file is part of PHP FAD FRAMEWORK
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
  * along with PHP FAD FRAMEWORK; if not, write to the Free Software
  * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
  *
  * Package : PHP FAD FRAMEWORK
  * Author : Mathieu Moulin <lemathou@free.fr>
  * Licence : http://www.gnu.org/copyleft/gpl.html  GNU General Public License
  *
  */

// Configuration
include "config/config.inc.php";

// Paramètres, Constantes, variables globales, constructeurs généraux, classes générales, fonctions diverses, etc.
include PATH_INCLUDE."/header.inc.php";

// Gestion de la langue
include PATH_INCLUDE."/lang.inc.php";

// Démarrage de la session
include PATH_INCLUDE."/session_start.inc.php";

// Mise en place des banques de donnée et des fonctions associées !!
databank();

// Controller (Warning !!)
//include "include/data_controller.inc.php";

if (REDIRECT)
{
	header("Location: http://".SITE_DOMAIN."/".SITE_LANG."/");
}

// Choix de la page
include PATH_INCLUDE."/page_choose.inc.php";

header("Content-type: text/html; charset=".SITE_CHARSET);

// Affichage du template
//echo PAGE_ID;
page_current()->tpl()->disp();
gentime("TEMPLATE_DISP");

if ($dr=login()->info_get("disconnect_reason"))
{
if ($dr==1)
	$dr="Ce compte ne figure pas dans nos bases.";
elseif ($dr==4)
	$dr="Mot de passe invalide.";
elseif ($dr==5)
	$dr="Compte temporairement désactivé, veuillez nous contacter pour plus d'information.";
else
	$dr="Erreur d'authentification.";
?>
<script type="text/javascript">
alert('<?=$dr?>');
</script>
<?
unset($dr);
}

// On incrémente le nombre de pages vues par le visiteur
login()->page_count++;

gentime("END");

if (login()->perm(6)) { ?>
<div style="width:980px;margin:5px;border:1px gray solid;padding:5px;background-color:white;">
<h1>DEBUG Gentime</h1>
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
</div>
<?php } ?>