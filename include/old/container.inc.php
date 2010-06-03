<?

/**
  * $Id: container.inc.php 40 2008-10-01 07:37:20Z mathieu $
  * 
  * � Copyright 2008 Mathieu Moulin - iProspective - lemathou@free.fr �
  * 
  * This file is part of FTNGroupWare.
  * 
  * location : /include : global include folder
  * 
  * Conteneurs pour la mise en page
  * Cette classe d�finit des r�gles g�n�rales d'affichage des donn�es.
  * C'est ici que se fera la mise en cache �ventuelle de donn�es.
  * 
  * Module de gestion de mise en page d'�l�ments.
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
