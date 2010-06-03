<?

/**
  * $Id: container.inc.php 40 2008-10-01 07:37:20Z mathieu $
  * 
  * « Copyright 2008 Mathieu Moulin - iProspective - lemathou@free.fr »
  * 
  * This file is part of FTNGroupWare.
  * 
  * location : /include : global include folder
  * 
  * Conteneurs pour la mise en page
  * Cette classe définit des règles générales d'affichage des données.
  * C'est ici que se fera la mise en cache éventuelle de données.
  * 
  * Module de gestion de mise en page d'éléments.
  * - sous-conteneurs,
  * - textes,
  * - tableaux,
  * - listes,
  * - objets divers,
  * - videos,
  * - etc.
  * 
  */

class container
{

protected $list = array();

public function add($name, $value, $options=array())
{

$this->list[$name] = array ( "value"=>$value , "options"=>$options );

}

}

?>
