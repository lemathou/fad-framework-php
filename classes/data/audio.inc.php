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
 * Audio
 *
 */
class data_audio extends data_file
{

static protected $format_list = array("mp3"=>"audio/mpeg-1", "wav"=>"audio/wave", "wma"=>"audio/wma", "ogg"=>"audio/ogg", "ogg"=>"image_gif");

protected $opt = array("fileformat"=>"mp3");

function format_convert($format)
{

if (isset(self::$format_list[$format]))
	$this->format = $format;

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
