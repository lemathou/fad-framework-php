<?php

/**
  * $Id$
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
 * Number field to count objects
 * 
 * Float unsigned
 * 
 */
class data_measure extends data_float
{

protected $opt = array
(
	"numeric_signed"=>false,
	"size"=>10,
	"numeric_precision"=>4,
	"numeric_type"=>"Km/h"
);

function __construct($name, $value, $label="Measure", $options=array())
{

data_float::__construct($name, $value, $label, $options);

}

function __tostring()
{

if ($this->nonempty()) // TODO : fnction pour afficher proprement une mesure
	return $this->value." ".$this->opt["numeric_type"];
else
	return "";

}

function type_change($type)
{

// TODO !!
$this->opt["numeric_type"] = $type;

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
