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
 * "Abstract" base datatype
 * 
 * This is the main datatype, from which we will create the others :
 * 
 * L'intérêt de spécifier chaque donnée entrée est aussi de s'abstraire du moteur de stockage (MySQL pour l'instant).
 * Chaque donnée dispose :
 * - de spécifications de format à respecter avec méthodes de contrôle et lorsque cela est possible correction
 * - de champs de formulaire spécifiques (adaptés) :
 * 		- pour l'insertion,
 * 		- pour l'édition,
 * 		- pour le listing des éventuelles différentes valeurs
 * - de méthodes de traitement
 * 		- importation/exportation pour le moteur de stockage
 * 		- importation/exportation pour les formulaires
 * - de méthodes d'affichage
 * 
 */
class data
{

/**
 * Unique name as an identifier for a set of fields (for example in forms)
 * @var string
 */
protected $name = "";

/**
 * @var string
 */
protected $label = "";

/**
 * Linked datamodel (optionnal)
 * TODO : Can be pagemodel, template, datamodel_ref, etc. => What the fuck !
 * TODO : A SIMPLIFIER !!!!!
 * @var integer
 */
protected $datamodel_id;
/**
 * Parent object
 * TODO : Can be pagemodel, template, datamodel_ref, etc. => What the fucking hell !
 * @var integer
 */
protected $object_id;
/**
 * @var _datamodel|dataobject|_datamodel_ref|dataobject_ref|_pagemodel|_template
 */
protected $object;

/**
 * Données brutes dans le format dééfini et "contraint" le plus adapté
 * @var mixed
 */
protected $value = null;

/**
 * @var mixed
 */
protected $empty_value = null;

/**
 * Required means Not empty value
 * @var boolean
 */
protected $required = false;

protected $opt = array();
protected static $opt_list = array
(
	// structure
	"size", "ereg", "numeric_signed", "numeric_precision", "numeric_type", "value_list", "boolean", "date_format", "datetime_format", "object_type", "email_strict", "urltype",
	// dataobject specific
	"datamodel", "datamodel_ref", "datamodel_ref_id",
	// db
	"db_table", "db_field", "db_ref_table", "db_ref_field", "db_ref_id", "db_order_field", "db_databank_field", "db_type", "select_params",
	// disp
	"ref_field_disp", "preg_replace", "template_datamodel",
	// Other
	"lang", "auto_increment",
);

/**
 * Constructor
 *
 * @param string $name
 * @param string $label
 * @param mixed $value
 * @param array $opt
 */
public function __construct($name, $value, $label, $opt=array())
{

$this->name = $name;
$this->label = $label;
$this->value = $value;

if (is_array($opt)) foreach ($opt as $i=>$j)
{
	$this->opt_set($i, $j);
}

}

public function __sleep()
{

return array("name", "label", "datamodel_id", "object_id", "value", "empty_value", "opt");

}
public function __wakeup()
{

if ($this->datamodel_id && $this->object_id && ($datamodel=datamodel()->get($this->datamodel_id)) && ($object=$datamodel->get($this->object_id)))
	$this->object = $object;

}

function __clone()
{

$this->object_id = null;
$this->object = null;

}

public function datamodel_set($datamodel_id)
{

$this->datamodel_id = $datamodel_id;

}
public function object_set($object)
{

//$this->datamodel_id = $object->datamodel()->id();
$this->object_id = $object->id;
$this->object = $object;

}
/**
 * @return _datamodel
 */
public function datamodel()
{

if ($this->datamodel_id && datamodel()->exists($this->datamodel_id))
	return datamodel($this->datamodel_id);
else
	return null;

}
/**
 * @return dataobject
 */
public function dataobject()
{

return $this->object;

}

/**
 * Options
 * @param string $name
 * @param mixed $value
 */
public function opt_set($name, $value)
{

//var_dump($value);

// TODO : verify the type of the value given in each case
if (in_array($name, self::$opt_list))
	$this->opt[$name] = $value;

}
/**
 * Returns opt value
 * @param string $name
 * @return mixed
 */
public function opt($name)
{

if (isset($this->opt[$name]))
	return $this->opt[$name];
else
	return null;
/*
TODO : default values ..?
elseif (in_array($name, self::$opt_list))
	return self::$opt_list[$name];
*/

}
/**
 * Returns opt value
 * @param string $name
 * @return mixed
 */
public function opt_get($name)
{

return $this->opt($name);

}


/* SET */

/**
 * Only the value can be updated, the other properties are too complex
 *
 * @param unknown_type $name
 * @param unknown_type $value
 * @return unknown
 */
public function __set($name, $value)
{

if ($name == "value")
	$this->value = $value;

}
/**
 * Update the value and force conversion if needed
 * 
 * @param mixed $value
 * @param boolean $force
 * @return boolean
 */
public function value_set($value, $convert=false, $options=array())
{

$this->value = &$value;

// Verify and update
if ($value !== null)
{
	$this->convert_before($value);
	$this->verify($value, $convert, $options);
	$this->convert_after($value);
}

}

/* VERIFY / CONVERT */

/**
 * Verify the structure of the value
 * 
 * @param mixed $value
 * @return boolean
 */
public function verify(&$value, $convert=false, $options=array())
{

return true;

}
/**
 * OVERLOAD
 * @param mixed $value
 */
public function convert_before(&$value)
{
}
/**
 * OVERLOAD
 * @param mixed $value
 */
public function convert(&$value, $options=array())
{

$value =  $this->empty_value;

}
/**
 * OVERLOAD
 * @param mixed $value
 */
public function convert_after(&$value)
{
}
/**
 * Returns if the value is empty (or not set)
 * @return boolean
 */
public function null()
{

return ($this->value === null);

}
/**
 * Returns if the value is not empty, refering on the empty_value parameter
 * @return boolean
 */
public function nonempty()
{

return ($this->value !== null && $this->value !== $this->empty_value);

}

/* GET */

/**
 * Returns a reference to the value (use with caution)
 * 
 * @return mixed
 */
public function &value_ref()
{

return $this->value;

}
/**
 * Read access to data
 * @param string
 * @return mixed
 */
public function __get($name)
{

if (in_array($name, array("name", "label", "value", "datamodel_id", "opt")))
	return $this->{$name};
elseif ($name == "opt_list")
	return self::${$name};
elseif ($name == "type")
	return substr(get_called_class(), 5);
elseif ($name == "datamodel")
	return datamodel($this->datamodel_id);
else
	return null;

}
/**
 * Returns value
 *
 * @return string
 */
public function __tostring()
{

if ($this->nonempty())
	return (string)$this->value;
else
	return "";

}

/* DB */

/**
 * Convert the value in database format
 * 
 * @param mixed $value
 */
public function value_from_db($value)
{

$this->value = &$value;
if ($value !== null)
{
	$this->convert_from_db($value);
	$this->verify($value, true);
	$this->convert_after($value);
}

}
/**
 * @return mixed
 */
public function value_to_db()
{

return $this->value;

}
/**
 * OVERLOAD
 * @param mixed $value
 */
public function convert_from_db(&$value)
{
}
/**
 * Return the query string for the datamodel
 * 
 * @param mixed $value
 * @param string $type
 * @return string
 */
public function db_query_param($value, $type="=")
{

if (!in_array($type=strtoupper($type), array("=", "<", ">", "<=", ">=", "<>", "LIKE", "NOT LIKE")))
	$type = "=";

$fieldname = $this->db_fieldname();

if (is_array($value))
{
	if (count($value))
	{
		$q = array();
		foreach($value as $i)
			$q[] = "'".db()->string_escape($i)."'";
		return "`".$fieldname."` IN (".implode(" , ",$q).")";
	}
	else
		return "`".$fieldname."` IN ( null )";
}
else
	return "`".$fieldname."` $type '".db()->string_escape($value)."'";

}
/**
 * Defines the table field that would be created by a db::table_create invoqued from datamodel.
 * @return string
 */
public function db_fieldname()
{

if (isset($this->opt["db_field"]) && ($fieldname=$this->opt["db_field"]))
	return $fieldname;
else
	return $this->name;

}
public function db_field_create()
{

return array("name"=>$this->db_fieldname(), "type"=>"string");

}

/* FORMS */

/**
 * Convert the value from the appripriate format used in the form_field() view in an HTML form 
 *
 * @param mixed $value
 */
public function value_from_form($value)
{

$this->value = &$value;
if ($value !== null)
{
	$this->convert_from_form($value);
	$this->verify($value, true);
	$this->convert_after($value);
}

}
/**
 * OVERLOAD
 * @param mixed $value
 */
public function convert_from_form(&$value)
{

}
/**
 * Convert the value to export it in an HTML form in the appropriate format
 * 
 * @return string
 */
public function value_to_form()
{

return $this->__tostring();

}
/**
 * Return the associated form field
 *
 * @param array $options
 * @return string
 */
public function form_field_disp($options=array())
{

return "";

}
/**
 * Return the associated form field for a selection
 *
 * @param array $options
 * @return string
 */
public function form_field_select_disp($options=array())
{

return $this->form_field_disp($options);

}

/**
 * Returns the field details for Javascript control functions
 * @return string
 */
public function js()
{

return "{\"type\": \"$this->type\", \"label\": ".json_encode($this->label).", \"value\": ".json_encode($this->value).", \"opt\": ".json_encode($this->opt)."}";

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
