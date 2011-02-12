<?php

/**
  * $Id: data.inc.php 32 2011-01-24 07:13:42Z lemathoufou $
  * 
  * Copyright 2008-2011 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * http://sourceforge.net/projects/phpfadframework/
  * Licence : http://www.gnu.org/copyleft/gpl.html  GNU General Public License
  * 
  */

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [begin]");


/**
 * Donnée de type fichier
 * 
 * Dans ce cas, form_field() doit permettre de visualiser & modifier le fichier.
 * Pas évident... on doit pouvoir le modifier parmis une liste existante,
 * en charger un autre, et la visualisation va dépendre de pas mal de choses !
 * Il faut donc lui donner les paramètres nécessaires à ce bon fonctionnement...
 * 
 * Quitte à créer un autre datatype pour d'autres besoins,
 * le data_file doit passer par un gestionnaire de fichier indépendant,
 * et la donnée sera effectivement stoquée sur le disque.
 * 
 * Cette classe sera surchargée de nombreuses fois.
 * Elle fournit les méthodes de base de téléchargement, lien, etc.
 * 
 */
class data_file extends data
{

protected $opt = array("fileformat"=>"");

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
