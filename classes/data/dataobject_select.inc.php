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
 * Donnée de type dataobject avec choix du type de dataobject en param
 */
class data_dataobject_select extends data_object
{

protected $value_empty = array(0, 0);

protected $opt = array
(
	"datamodel" => array("datamodel_1","datamodel_2"), // datamodel list
	"db_databank_field" => "",
	"db_id_field" => ""
);

function __construct($name, $value, $label="Object (from a list)", $options=array())
{

data::__construct($name, $value, $label, $options);

}

/* Convert */
function value_to_db()
{

if ($this->nonempty())
	return $this->value;
else
	return array(null, null);

}
function verify(&$value)
{

//print_r($value);
if (!is_array($value) || !isset($value[0]) || !isset($value[1]) || !in_array(!$value[0],$this->opt["datamodel"]) || !datamodel($value[0],$value[1]))
{
	$value = array(0, 0);
}

}

/* View */
function __tostring()
{

if ($this->nonempty())
	return (string) $this->object();
else
	return "";

}

function object()
{

if ($this->nonempty() && ($datamodel=datamodel($this->value[0])) && ($object=$datamodel->get($this->value[1])))
	return $object;
else
	return null;

}

}

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
