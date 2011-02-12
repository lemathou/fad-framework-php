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
 * Name, label, etc.
 *
 * Maxlength fixed to 64
 *
 */
class data_name extends data_string
{

protected $opt = array
(
	"size"=>64
);

function __construct($name, $value, $label="Name", $options=array())
{

data_string::__construct($name, $value, $label, $options);

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
