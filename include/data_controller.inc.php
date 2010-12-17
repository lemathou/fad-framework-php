<?

/**
  * $Id$
  * 
  * Copyright 2008 Mathieu Moulin - iProspective - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  * location : /include : global include folder
  * 
  * Controlleurs pour les databank
  * Permet de modifier des données à la volée en envoyant juste les infos requises.
  * Et le pire, c'est que ça fonctionne !
  * 
  */

if (DEBUG_GENTIME ==  true)
        gentime(__FILE__." [begin]");

/**
 * Global update controller
 * @param unknown_type $update
 */
function data_update($databank, $dataobject_id, $fields)
{

if (!databank()->exists($databank))
{
	die("Wrong databank name given : $databank");
}
elseif (!databank($databank)->exists($dataobject_id))
{
	die("Dataobject $databank ID#$dataobject_id does not exists");
}
elseif (!is_array($fields))
{
	die("Update fields must be an array");
}
else
{
	$object = databank($databank)->get($dataobject_id);
	$object->update_from_form($fields);
	if (false)
	{
		$object->db_update();
	}
}

}

/**
 * Global insert controller
 * @param $insert
 */
function data_insert($databank, $fields)
{

die("VERBOTTEN !");

}

/**
 * GLobal delete controller
 * @param unknown_type $delete
 */
function data_delete($databank, $dataobject_id)
{

die("VERBOTTEN !");

}

// ACTION

if (isset($_POST["_delete"]))
{
	data_delete($_POST["_delete"]);
}

if (isset($_POST["_insert"]))
{
	data_insert($_POST["_insert"]);
}

if (isset($_POST["_update"]) && is_array($_POST["_update"]) && isset($_POST["_update"]["databank"]) && isset($_POST["_update"]["dataobject"]) && isset($_POST["_update"]["fields"]))
{
	data_update($_POST["_update"]["databank"], $_POST["_update"]["dataobject"], $_POST["_update"]["fields"]);
}

if (DEBUG_GENTIME ==  true)
        gentime(__FILE__." [end]");

?>