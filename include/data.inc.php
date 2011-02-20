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
  * location : /include : global include folder
  * 
  * Types de données & conteneurs
  * 
  * Agrégats de données de base pour les datamodels, les formulaires,
  * les méthodes de mise en page, la partie CMS, etc.
  * 
  * Types de données gérés au niveau du framework.
  * Vous pourrez en ajouter s'il en manque mais j'essayerais d'être exhaustif.
  * 
  * - Dans l'idée, chaque donnée est fortement typée.
  * - Ce sont les briques du "modèle" en MVC.
  * - Les controlleurs sont des méthodes associées à des instances de classe form pour l'utilisateur,
  *   permettant de définir différents formulaires suivant le contexte.
  * - Les vues sont des méthodes associées à des instances de classe data_display (quoi que j'évolue
  *   vers une classe fille de la classe template, ce qui serait plus judicieux et permerrait de tout sauvegarder
  *   en cache).
  * - On dispose aussi de contraintes (regexp, maxlength, compare, etc.) utilisables via des méthodes de vérification
  *   et de conversion au plus juste (dans certains cas) associées à des instances de classes de conversion
  *   Des méthodes de vérification et de conversion seront aussi définies dans la classe form,
  *   en javascript (et ajax si besoin), au niveau utilisateur
  * 
  * On peut aussi utiliser indépendamment les classes datamodel, agregat, display, form, conversion et conteneur.
  */

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [begin]");


/**
 * Data types access function
 * @param mime ref
 * @return mixed
 */
function data($ref=null)
{

if (!isset($GLOBALS["_data"]))
{
	if (DEBUG_GENTIME == true)
		gentime("retrieve data() [begin]");
	if (CACHE)
	{
		if (!($GLOBALS["_data"]=cache::retrieve("data")))
			$GLOBALS["_data"] = new _data_gestion();
	}
	// Session
	else
	{
		if (!isset($_SESSION["_data"]))
			$_SESSION["_data"] = new _data_gestion();
		$GLOBALS["_data"] = $_SESSION["_data"];
	}
	if (DEBUG_GENTIME == true)
		gentime("retrieve data() [end]");
}

if (is_numeric($ref))
	return $GLOBALS["_data"]->get($ref);
elseif (is_string($ref))
	return $GLOBALS["_data"]->get_name($ref);
else
	return $GLOBALS["_data"];

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
