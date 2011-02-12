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
 * Video
 *
 */
class data_video extends data_file
{

static protected $format_list = array("wmv"=>"video/wmv", "avi"=>"video/avi", "ogg"=>"video/ogg", "flv"=>"video/flv", "mp4"=>"video/mpeg-4", "mov"=>"video/quicktime");

protected $opt = array("fileformat"=>"flv");

function format_convert($format)
{

if (isset(self::$format_list[$format]))
	$this->format = $format;

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
