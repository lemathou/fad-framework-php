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

if (!isset($GLOBALS["_datamodel"]))
{
	if (DEBUG_GENTIME == true)
		gentime("retrieve datamodel() [begin]");
	if (CACHE)
	{
		if (!($GLOBALS["_datamodel"]=cache::retrieve("datamodel")))
			$GLOBALS["_datamodel"] = new _datamodel_manager();
	}
	else // Session
	{
		if (!isset($_SESSION["_datamodel"]))
			$_SESSION["_datamodel"] = new _datamodel_manager();
		$GLOBALS["_datamodel"] = $_SESSION["_datamodel"];
	}
	if (DEBUG_GENTIME == true)
		gentime("retrieve datamodel() [end]");
}

if ($datamodel_id === null)
{
	return $GLOBALS["_datamodel"];
}

if ( !(is_numeric($datamodel_id) && ($datamodel=$GLOBALS["_datamodel"]->get($datamodel_id))) && !(is_string($datamodel_id) && ($datamodel=$GLOBALS["_datamodel"]->get_name($datamodel_id))))
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
