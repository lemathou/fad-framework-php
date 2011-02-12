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
 * Donnée de type dataobject avec choix du type de dataobject en param
 */
class data_dataobject_select extends data_object
{

protected $value_empty = array();

protected $opt = array
(
	"datamodel_list" => array(), // liste des databank concern�es
	"db_databank_field" => "",
	"db_id_field" => ""
);

function __construct($name, $value, $label="Object (from a list)", $options=array())
{

data::__construct($name, $value, $label, $options);

}

function nonempty()
{

if ($this->value[0])
	return true;
else
	return false;

}

function __tostring()
{

if ($this->nonempty())
	return (string) $this->object();
else
	return "";

}

function object()
{

if ($this->nonempty())
	return datamodel($this->value[0])->get($this->value[1]);
else
	return null;

}

// We retrieve the list (datamodel_id, object_id)
function value_from_db($value)
{

if (!is_string($value) || count($list=explode(",",$value)) != 2)
{
	trigger_error("Data field '$this->name' : Bad value type");
	$this->value = array(0, 0);
}
elseif (!in_array(($databank=$list[0]),$this->opt["datamodel_list"]))
{
	trigger_error("Data field '$this->name' : Undefined databank '$databank' in value");
	$this->value = array(0, 0);
}
elseif(!($object = datamodel($databank,$list[1])))
{
	trigger_error("Data field '$this->name' : Undefined object in value");
	$this->value = array(0, 0);
}
else
{
	$this->value = $object;
}

}

function value_to_db()
{

if ($this->nonempty())
{
	return $this->value;
}
else
{
	return array(null, null);
}

}

function value_from_form($value)
{

//print_r($value);
if (is_array($value) && isset($value[0]) && isset($value[1]) && in_array(($databank=$value[0]),$this->opt["datamodel_list"]) && ($object = datamodel($databank,$value[1])))
{
	$this->value = $value;
}
else
{
	$this->value = array(0, 0);
}

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
