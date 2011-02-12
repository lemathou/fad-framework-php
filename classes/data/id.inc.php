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
 * ID
 * 
 * ID of a dataobject
 * 
 * Field name fixed to id
 * Label fixed to ID
 * Integer unsigned
 * 
 */
class data_id extends data_integer
{

protected $opt = array
(
	"integer_signed"=>false,
	"size"=>10,
	"auto_increment"=>true,
);

function __construct($name="id", $value=null, $label="ID")
{

data_integer::__construct($name, $value, $label);

}

public function db_field_create()
{

return array
(
	"type"=>"integer",
	"size"=>$this->opt["size"],
	"auto_increment"=>true
);

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
