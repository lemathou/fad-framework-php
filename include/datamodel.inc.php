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
  * Datamodels are designed to :
  * - Modelise tables in database
  * - Modelise a form
  * - Modelise a set of data
  * - Fill a template with a verified set of typed data
  * 
  * Il s'agit d'une liste de définitions de champs ainsi que de méthodes de travail
  * 
  * Il dispose aussi de méthodes de communication avec la base de donnée :
  * - Lecture
  * - Insertion
  * - Mise à jour
  * - Suppression
  * - Recherche simple/avancée
  * 
  */

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [begin]");


/**
 * Access function
 */
function datamodel($datamodel_id=null, $object_id=null)
{

if (!isset($GLOBALS["datamodel_gestion"]))
{
	if (DEBUG_GENTIME == true)
		gentime("retrieve datamodel() [begin]");
	if (CACHE)
	{
		if (!($GLOBALS["datamodel_gestion"]=cache::retrieve("datamodel_gestion")))
			$GLOBALS["datamodel_gestion"] = new datamodel_gestion();
	}
	else // Session
	{
		if (!isset($_SESSION["datamodel_gestion"]))
			$_SESSION["datamodel_gestion"] = new datamodel_gestion();
		$GLOBALS["datamodel_gestion"] = $_SESSION["datamodel_gestion"];
	}
	if (DEBUG_GENTIME == true)
		gentime("retrieve datamodel() [end]");
}

if ($datamodel_id === null)
{
	return $GLOBALS["datamodel_gestion"];
}

if ( !(is_numeric($datamodel_id) && ($datamodel=$GLOBALS["datamodel_gestion"]->get($datamodel_id))) && !(is_string($datamodel_id) && ($datamodel=$GLOBALS["datamodel_gestion"]->get_name($datamodel_id))))
{
	return null;
}

if ($object_id === null)
	return $datamodel;

if ($object=$datamodel->get($object_id))
	return $object;

return null;	

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
