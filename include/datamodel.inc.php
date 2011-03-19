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
 * @return _datamodel_manager
 * @return _datamodel
 * @return _dataobject
 */
function datamodel($datamodel_id=null, $query=null)
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
elseif (!(is_numeric($datamodel_id) && ($datamodel=$GLOBALS["_datamodel"]->get($datamodel_id))) && !(is_string($datamodel_id) && ($datamodel=$GLOBALS["_datamodel"]->get_name($datamodel_id))))
{
	return null;
}
elseif ($query === null)
{
	return $datamodel;
}
elseif (is_numeric($query) && ($object=$datamodel->get($query)))
{
	return $object;
}
elseif (is_string($query))
{
	// TODO : relevance definition test !
	if (count($objects=$datamodel->query(array(array("type"=>"fulltext", "value"=>$query)), true, array("relevance"=>"DESC"), 1)))
		return array_pop($objects);
	else
		return null;
}
elseif (is_array($query))
{
	if ($objects=$datamodel->query($query))
		return $objects;
	else
		return null;
}

return null;	

}
/**
 * Access function
 * @return _datamodel_ref_manager
 * @return _datamodel_ref
 */
function datamodel_ref($ref=null)
{

if (!isset($GLOBALS["_datamodel_ref"]))
{
	if (DEBUG_GENTIME == true)
		gentime("retrieve datamodel_ref() [begin]");
	if (CACHE)
	{
		if (!($GLOBALS["_datamodel_ref"]=cache::retrieve("datamodel_ref")))
			$GLOBALS["_datamodel_ref"] = new _datamodel_ref_manager();
	}
	else // Session
	{
		if (!isset($_SESSION["_datamodel_ref"]))
			$_SESSION["_datamodel_ref"] = new _datamodel_ref_manager();
		$GLOBALS["_datamodel_ref"] = $_SESSION["_datamodel_ref"];
	}
	if (DEBUG_GENTIME == true)
		gentime("retrieve datamodel_ref() [end]");
}

if (is_numeric($ref))
	return $GLOBALS["_datamodel_ref"]->get($ref);
elseif (is_string($ref))
	return $GLOBALS["_datamodel_ref"]->get_name($ref);
else
	return $GLOBALS["_datamodel_ref"];

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
