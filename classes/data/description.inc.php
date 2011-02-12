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
 * Description (text) field
 *
 * Maxlength fixed to 256
 *
 */
class data_description extends data_text
{

protected $opt = array
(
	"size"=>256
);
	
function __construct($name, $value, $label="Description", $options=array())
{

data_text::__construct($name, $value, $label, $options);

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
