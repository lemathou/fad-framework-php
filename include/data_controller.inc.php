<?

/**
  * $Id: data_controller.inc.php 40 2008-10-01 07:37:20Z mathieu $
  * 
  * Copyright 2008 Mathieu Moulin - iProspective - lemathou@free.fr
  * 
  * This file is part of FTNGroupWare.
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

function data_update($data)
{

$update = $data["_update"];
unset($data["_update"]);

if (!is_array($update))
{
	die("Update must be an array");
}
elseif (!isset($update["datamodel"]) || !is_a($databank=databank($update["datamodel"]), "data_bank"))
{
	die("datamodel required ($update[datamodel] given)");
}
elseif (!isset($update["id"]) || !is_a($object = $databank->get($update["id"]), "data_bank_agregat"))
{
	die("dataobject id required ($update[id] given)");
}
elseif (!is_array($data))
{
	die("data required");
}
else
{
	$object->update_from_form($data);
	if (isset($update["save"]) && $update["save"] == true)
		$object->db_update();
}

}

if (isset($_POST["_update"]))
{
	data_update($_POST);
}

if (DEBUG_GENTIME ==  true)
        gentime(__FILE__." [end]");

?>