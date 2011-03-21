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
 * List of fields
 * 
 * Set of fields from a datamodel_ref bank
 */
class data_dataobject_list_ref extends data
{

protected $value_empty = array();

protected $opt = array
(
	"datamodel_ref" => null, // datamodel_ref id or name
	"datamodel_ref_id" => null, // datamodel_ref field identifying this object
);

protected $value_retrieve = false;
protected $value_use_ref = false;
protected $datamodel_ref = null;

function __construct($name, $value, $label="Field list", $options=array())
{

parent::__construct($name, $value, $label, $options);
//$this->value_retrieve();

}

public function __sleep()
{

// We don't save the value, which is handled by datamodel_ref().
return array("name", "label", "datamodel_id", "object_id", "empty_value", "opt");

}

public function __wakeup()
{

parent::__wakeup();
//$this->value_retrieve();

}

/**
 * 
 * Retrieve associated object list
 * @param boolean $force
 */
public function value_retrieve($force=false)
{

if (count($this->datamodel_ref()->fields_key()))
	$this->value_use_ref = true;

if ($this->object_id && (!$this->value_retrieve || $force))
{
	if ($this->value_use_ref)
	{
		$this->value = array();
		$list = $this->datamodel_ref()->query(array($this->opt["datamodel_ref_id"]=>$this->object_id), array());
		foreach($list as $o)
			$this->value[] = $o->id;
	}
	else
	{
		$this->value = $this->datamodel_ref()->query(array($this->opt["datamodel_ref_id"]=>$this->object_id));
	}
}

$this->value_retrieve = true;

}

function datamodel_ref($ref=null)
{

if ($this->datamodel_ref === null)
	$this->datamodel_ref = datamodel_ref($this->opt["datamodel_ref"]);

if ($ref === null)
	return $this->datamodel_ref;
else
	return $this->datamodel_ref->get($ref);

}

public function db_query_param($value, $type="=")
{

$type_list = array( "=", "LIKE", "<", ">", "<=", ">=", "NOT LIKE" );  
if (!in_array($type, $type_list))
	$type = "=";

return $this->datamodel_ref()->__get($this->opt["datamodel_ref_id"])->db_query_param($value, $type);

}

function __tostring()
{

return "";

}

/**
 * Returns specific objet fields in a list (usefull to retrieve easily associated objects)
 * @param string $name
 */
function field_list($name)
{

$this->value_retrieve();

if (!$this->datamodel_ref()->__isset($name))
	return array();

$return = array();
if ($this->value_use_ref)
{
	foreach($this->value as $ref)
		$return[] = $this->datamodel_ref($ref)->{$name};
}
else
{
	foreach($this->value as $object)
		$return[] = $object->{$name};
}
return $return;

}

/**
 * Returns objets in a list
 */
function object_list()
{

$this->value_retrieve();

if ($this->value_use_ref)
{
	$return = array();
	foreach ($this->value as $ref)
		$return[] = $this->datamodel_ref($ref);
	return $return;
}
else
{
	return $this->value;
}

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>