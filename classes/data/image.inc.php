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
 * Image/Picture
 *
 */
class data_image extends data_file
{

static protected $format_list = array("jpg"=>"image/jpeg", "png"=>"image/png", "gif"=>"image/gif");

protected $opt = array("fileformat"=>"jpg", "imgquality"=>90);

/*
 * A TRAVAILLER... PAS EVIDENT
 * On doit pouvoir forcer un format en entr�e, convertir en un format donn� au besoin.
 */

function format_convert($format)
{

if (isset(self::$format_list[$format]))
	$this->format = $format;

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
