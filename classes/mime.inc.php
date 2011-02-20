<?php

/**
  * $Id: mime.inc.php 30 2011-01-18 23:29:06Z lemathoufou $
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


class _mime_gestion extends _gestion
{

protected $type = "mime";

protected $info_list = array("name", "type");

protected $info_detail = array
(
	"name"=>array("label"=>"Nom", "type"=>"string", "size"=>64, "lang"=>false),
	"type"=>array("label"=>"Type", "type"=>"select", "lang"=>false, "default"=>"page", "select_list"=> array('application'=>"application",'text'=>"text",'audio'=>"audio",'image'=>"image", "video"=>"video"))
);

protected $info_required = array("name", "type");

protected $retrieve_details = false;

}

/**
 * Defines the display of the page, based on database infos and a template file
 * 
 */
class _mime extends _object_gestion
{


}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
