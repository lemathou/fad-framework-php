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
 * Amount of money
 * 
 * Float unsigned
 * 
 */
class data_money extends data_measure
{

protected $opt = array
(
	"numeric_signed"=>true,
	"size"=>8,
	"numeric_precision"=>2,
	"numeric_type"=>"&euro;"
);

protected $empty_value = null;

function __construct($name, $value, $label="Amount", $options=array())
{

data_float::__construct($name, $value, $label, $options);

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
