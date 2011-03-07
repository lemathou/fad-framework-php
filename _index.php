<?php

/**
  * $Id: index.php 42 2011-02-20 10:57:47Z lemathoufou $
  * 
  * « Copyright 2008-2011 Mathieu Moulin - lemathou@free.fr »
  * 
  * This file is part of PHP FAD FRAMEWORK
  * http://sourceforge.net/projects/phpfadframework/
  * Licence : http://www.gnu.org/copyleft/gpl.html  GNU General Public License
  * 
  * PHP FAD FRAMEWORK is free software; you can redistribute it and/or modify
  * it under the terms of the GNU General Public License as published by
  * the Free Software Foundation; either version 2 of the License, or
  * (at your option) any later version.
  *
  * PHP FAD FRAMEWORK is distributed in the hope that it will be useful,
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

include PATH_INCLUDE."/header.inc.php";

// Controller (Warning !!)
//include "include/data_controller.inc.php";

if (REDIRECT_LANG)
{
	header("Location: http://".SITE_DOMAIN."/".SITE_LANG."/");
	die("Redirection en cours...");
}

// Choix de la page à partir de l'url
page()->set();
// Actions sur la page
page_current()->action();
// Affichage du template
page_current()->template_disp();

// Gestion message login
login()->message_show();

// On incrémente le nombre de pages vues par le visiteur
login()->page_count++;

if (DEBUG_GENTIME == true)
	gentime("END");

?>

<?php if (DEBUG_GENTIME == true && login()->perm(2)) { // SuperAdmin ?>

<div style="width:980px;margin:5px;padding:5px;background-color:white;">
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
