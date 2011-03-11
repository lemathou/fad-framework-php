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
 * Data types global container class
 */
class _data_gestion extends _gestion
{

protected $type = "datatype";

protected $info_required = array("name", "label");

public function get($id)
{

if (array_key_exists($id, $this->list_detail))
{
	$datatype = "data_".$this->list_detail[$id]["name"];
	return new $datatype($this->list_detail[$id]["name"], null, $this->list_detail[$id]["label"]);
}
else
	return null;

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
