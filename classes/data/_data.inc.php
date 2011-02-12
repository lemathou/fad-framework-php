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
 * Unique name as an identifier for example in forms
 *
 * @var string
 */
protected $name="";

protected $label="";

/**
 * Linked datamodel (optionnal)
 * 
 * @var unknown_type
 */
protected $datamodel_id=0;

/**
 * Données brutes dans le format dééfini et "contraint" le plus adapté
 * 
 * @var mixed
 */
protected $value=null;

protected $empty_value=null;

/**
 * Required means Not null
 * 
 * @var bool
 */
protected $required=false;

protected $opt = array();
protected static $opt_list = array
(
	// structure
	"size", "ereg", "numeric_signed", "numeric_precision", "value_list", "boolean", "date_format", "datetime_format", "object_type", "datamodel", "email_strict", "urltype",
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
 * @param mixed $value
 * @param array $options
 */
public function __construct($name, $value, $label, $options=array())
{

$this->name = $name;
$this->label = $label;
$this->value = $value;

if (is_array($options)) foreach ($options as $i=>$j)
{
	$this->opt_set($i, $j);
}

}

public function datamodel_set($datamodel_id)
{

$this->datamodel_id = $datamodel_id;

}
public function datamodel()
{

if ($this->datamodel_id && datamodel()->exists($this->datamodel_id))
	return datamodel($this->datamodel_id);
else
	return null;

}

/**
 * Options
 */
public function opt_set($name, $value)
{

//var_dump($value);

// TODO : verify the type of the value given in each case
if (in_array($name, self::$opt_list))
	$this->opt[$name] = $value;

}
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
 * @param mixed value
 * @param boolean force
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
 * @param mixed value
 * @return boolean
 */
public function verify(&$value, $convert=false, $options=array())
{

return true;

}
public function convert_before(&$value)
{
}
public function convert(&$value, $options=array())
{
}
public function convert_after(&$value)
{
}
/**
 * Returns if the value is empty (or not set)
 */
public function null()
{

return ($this->value === null);

}
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
 * @param unknown_type $value
 * @return unknown
 */
public function value_from_db($value)
{

$this->value = $value;

}
public function value_to_db()
{

return $this->value;

}
/**
 * Return the query string for the datamodel
 * 
 * @param $value
 * @return array
 */
public function db_query_param($value, $type="=")
{

if (!in_array($type, array("=", "<", ">", "<=", ">=", "<>", "LIKE", "NOT LIKE")))
	$type = "=";

if (!isset($this->opt["db_field"]) || !($fieldname=$this->opt["db_field"]))
	$fieldname = $this->name;

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
 *  
 */
public function db_field_create()
{

return array("type"=>"string");

}

/* FORMS */

/**
 * Convert the value from the appripriate format used in the form_field() view in an HTML form 
 *
 * @param mixed $value
 */
public function value_from_form($value)
{

$this->value_set($value, true);

}
/**
 * Convert the value to export it in an HTML form in the appropriate format
 * 
 * @param unknown_type $value
 * @return mixed
 */
public function value_to_form()
{

if ($this->nonempty())
	return $this->value;
else
	return "";

}
/**
 * Return the associated form field
 *
 * @param array $options
 * @return string
 */
public function form_field_disp($print=true, $options=array())
{

if ($print)
	echo "";
else
	return "";

}
/**
 * Return the associated form field for a selection
 *
 * @param array $options
 * @return string
 */
public function form_field_select_disp($print=true, $options=array())
{

$return = $this->form_field_disp(false, $options);
	
if ($print)
	echo $return;
else
	return $return;

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
