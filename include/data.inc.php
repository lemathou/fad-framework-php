<?php

/**
  * $Id$
  * 
  * Copyright 2008-2011 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  * location : /include : global include folder
  * 
  * Types de données & conteneurs
  * 
  * Agrégats de données de base pour les datamodels, les formulaires,
  * les méthodes de mise en page, la partie CMS, etc.
  * 
  * Types de données gérés au niveau du framework.
  * Vous pourrez en ajouter s'il en manque mais j'essayerais d'être exhaustif.
  * 
  * - Dans l'idée, chaque donnée est fortement typée.
  * - Ce sont les briques du "modèle" en MVC.
  * - Les controlleurs sont des méthodes associées à des instances de classe form pour l'utilisateur,
  *   permettant de définir différents formulaires suivant le contexte.
  * - Les vues sont des méthodes associées à des instances de classe data_display (quoi que j'évolue
  *   vers une classe fille de la classe template, ce qui serait plus judicieux et permerrait de tout sauvegarder
  *   en cache).
  * - On dispose aussi de contraintes (regexp, maxlength, compare, etc.) utilisables via des méthodes de vérification
  *   et de conversion au plus juste (dans certains cas) associées à des instances de classes de conversion
  *   Des méthodes de vérification et de conversion seront aussi définies dans la classe form,
  *   en javascript (et ajax si besoin), au niveau utilisateur
  * 
  * On peut aussi utiliser indépendamment les classes datamodel, agregat, display, form, conversion et conteneur.
  */

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [begin]");


/**
 * Data types global container class
 */
class data_gestion extends gestion
{

protected $type = "datatype";

public function get($id)
{

if ($this->exists($id))
{
	$datatype = "data_".$this->list_detail[$id]["name"];
	return new $datatype($this->list_detail[$id]["name"], null, $this->list_detail[$id]["label"]);
}
else
	return null;

}

}

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
	"size", "ereg", "numeric_signed", "numeric_precision", "value_list", "boolean", "date_format", "datetime_format" , "object_type" , "datamodel", "email_strict", "urltype",
	// db
	"db_table", "db_field" , "db_type" , "db_ref_table" , "db_ref_field" , "db_ref_id", "db_order_field" , "databank_field" , "select_params" , "lang" , "auto_increment",
	// disp
	"ref_field_disp", "preg_replace", "template_datamodel",
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

// TODO : verify the type of the value given in each case

if (isset(self::$opt_list[$name]))
	$this->opt[$name] = $value;

}
public function opt_get($name)
{

if (isset($this->opt[$name]))
	return $this->opt[$name];
elseif (isset(self::$opt_list[$name]))
	return self::$opt_list[$name];

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
public function convert(&$value, $options=array())
{
}
public function convert_after(&$value)
{
}
public function convert_before(&$value)
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

if ($this->value !== null && $this->value !== $this->empty_value)
	return true;
else
	return false;

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

if (is_string($name) && in_array($name, array("name", "label", "value", "opt_list", "datamodel_id", "opt")) && isset($this->{$name}))
	return $this->{$name};
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

if ($this->value === null)
	return "";
else
	return (string)$this->value;

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

if ($this->value === null)
	return null;
else
	return "$this->value";

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

return array
(
	"type"=>"string"
);

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

if ($this->value === null)
	return "";
else
	return $this->value;

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


/** 
  * Base data types
  * 
  * - String (with maxlength)
  * - Numbers : Integer, Float, etc.
  * - Date/Time
  * - Lists (width single or multiple selection)
  * - Boolean
  */


/**
 * String
 * 
 * With a maxlength
 * Associated to an input/text form, and a varchar db field
 *
 */
class data_string extends data
{

protected $empty_value = "";

protected $opt = array
(
	"size" => 256
);

public function db_field_create()
{

return array("type"=>"string", "size"=>$this->opt["size"]);

}

/* Verify */
public function verify(&$value, $convert=false, $options=array())
{

$return = true;

if (!is_string($value))
{
	if ($convert)
	{
		$value = "";
		$return = false;
	}
	else
		return false;
}

if (isset($this->opt["size"]) && ($maxlength=$this->opt["size"]) && strlen($value) > $maxlength)
{
	if ($convert)
	{
		$value = substr($value, 0, $maxlength);
		$return = false;
	}
	else
		return false;
}

if (isset($this->opt["ereg"]) && ($ereg=$this->opt["ereg"]) && !preg_match($ereg, $value))
{
	if ($convert)
	{
		$value = null;
		$return = false;
	}
	else
		return false;
}

return $return;

}
public function convert(&$value)
{

if (!is_string($value))
	$value = (string)$value;
if (isset($this->opt["size"]) && ($maxlength=$this->opt["size"]) && strlen($value) > $maxlength)
	$value = substr($value, 0, $maxlength);
if (isset($this->opt["ereg"]) && ($ereg=$this->opt["ereg"]) && !preg_match($ereg, $value))
	$value = null;

}

/* View */
public function __tostring()
{

if ($this->value === null)
	return "";
elseif (isset($this->opt["preg_replace"]) && is_array($opt=$this->opt["preg_replace"]) && isset($opt["pattern"]) && isset($opt["replace"]) && preg_match($opt["pattern"], $this->value))
{
	return preg_replace($opt["pattern"], $opt["replace"], (string)$this->value);
}
else
	return (string)$this->value;

}
public function form_field_disp($print=true, $options=array())
{

$attrib_size = ( isset($this->opt["size"]) && $this->opt["size"] > 0 && $this->opt["size"] < 32 )
	? " size=\"".$this->opt["size"]."\""
	: "";
$attrib_maxlength = ( isset($this->opt["size"]) && $this->opt["size"] > 0 )
	? " maxlength=\"".$this->opt["size"]."\""
	: "";

if ($this->type == "password")
	$type = "password";
else
	$type = "text";

$return = "<input type=\"$type\" name=\"$this->name\" value=\"$this->value\"$attrib_size$attrib_maxlength class=\"".get_called_class()."\" />";

if ($print)
	print $return;
else
	return $return;

}

}

/**
 * Password
 * 
 * Can handle different encryption types
 * Associated to a input/password form
 *
 */
class data_password extends data_string
{

protected $opt = array
(
	"size" => 64,
	"enctype" => "md5"
);

/* TODO : penser à la conversion en md5=>voir comment modifier par la suite
 * le mieux est peut-être de stocker directement à l'insertion en md5
 * pour pouvoir plus aisément comparer... a voir !!
 */

}

/**
 * Integer (numeric)
 *
 */
class data_integer extends data_string
{

protected $empty_value = 0;
	
protected $opt = array
(
	"size"=>11,
	"numeric_signed" => true,
);

public function db_field_create()
{

$return = array
(
	"type" => "integer",
	"size" => $this->opt["size"],
	
);
if ($this->opt["numeric_signed"])
{
	$return["signed"] = true;
}

return $return;

}

/* Convert */
public function verify(&$value, $convert=false, $options=array())
{

$return = true;

if (!is_numeric($value) || (int)$value != $value)
	if ($convert)
	{
		$value = (int)$value;
		$return = false;
	}
	else
		return false;

if (isset($this->opt["numeric_signed"]) && $this->opt["numeric_signed"] == false && $value < 0)
	if ($convert)
	{
		$value = -$value;
		$return = false;
	}
	else
		return false;

return $return;

}
public function convert(&$value)
{

$value = (int)$value;
if (isset($this->opt["numeric_signed"]) && $this->opt["numeric_signed"] == false && $value < 0)
	$value = -$value;

}

public function form_field_disp($print=true, $options=array())
{

$attrib_size = " size=\"".($this->opt["size"]+1)."\"";
$attrib_maxlength = " maxlength=\"".($this->opt["size"]+1)."\"";

$return = "<input type=\"text\" name=\"$this->name\" value=\"$this->value\"$attrib_size$attrib_maxlength class=\"".get_called_class()."\" />";

if ($print)
	print $return;
else
	return $return;

}

public function increment()
{

$this->value++;

}

public function decrement()
{

$this->value--;

}

}

/**
 * Float (numeric)
 *
 */
class data_float extends data_integer
{

protected $opt = array
(
	"size"=>11,
	"numeric_signed"=>true,
	"numeric_precision"=>2
);

public function db_field_create()
{

$return = array
(
	"type" => "float",
	"size" => $this->sopt["size"],
	"precision" => $this->opt["numeric_precision"],
	
);
if ($this->opt["numeric_signed"])
{
	$return["signed"]=true;
}

return $return;

}

/* Convert */
public function verify(&$value, $convert=false, $options=array())
{

$return = true;

if (!is_numeric($value) || (float)$value != $value)
	if ($convert)
	{
		$value = str_replace(",", ".", $value);
		$value = (float)$value;
		$return = false;
	}
	else
		return false;

if (isset($this->opt["numeric_precision"]) && ($precision=$this->opt["numeric_precision"]) && $value != round($value, $precision))
	if ($convert)
	{
		$value = round($value, $precision);
		$return = false;
	}
	else
		return false;

if (isset($this->opt["numeric_signed"]) && $this->opt["numeric_signed"] == false && $value < 0)
	if ($convert)
	{
		$value = -$value;
		$return = false;
	}
	else
		return false;

return $return;

}
function convert(&$value)
{

if (!preg_match('/^?[-]?([0-9]*)(\.([0-9]*)){0,1}$/', $value))
	$value = null;

}

public function form_field_disp($print=true, $options=array())
{

$attrib_size = " size=\"".($this->opt["size"]+2)."\"";
$attrib_maxlength = " maxlength=\"".($this->opt["size"]+2)."\"";

$return = "<input type=\"text\" name=\"$this->name\" value=\"$this->value\"$attrib_size$attrib_maxlength class=\"".get_called_class()."\" />";

if ($print)
	print $return;
else
	return $return;

}

}

/**
 * Boolean
 * 
 * Yes/No field ^^
 * 
 * Integer unsigned size 1
 * 
 */
class data_boolean extends data_string
{

protected $empty_value = null;

protected $opt = array("boolean"=>array("NO","YES"));

function __construct($name, $value, $label="Boolean", $options=array())
{

data::__construct($name, $value, $label, $options);

}

public function db_field_create()
{

return array("type"=>"boolean");

}

/* Convert */
public function value_from_db($value)
{

$this->value = ($value) ? true : false;

}
public function value_to_db()
{

if ($this->value === null)
	return null;
else
	return ($this->value) ? "1" : "0";

}
public function value_to_form()
{

if ($this->value === null)
	return "";
else
	return ($this->value) ? "1" : "0";

}
public function verify(&$value, $convert=false, $options=array())
{

if ($value !== true || $value !== false)
{
	if ($convert)
	{
		if (empty($value))
			$value = false;
		else
			$value = true;
	}
	return false;
}

return true;

}
public function convert(&$value)
{

if (empty($value))
	$value = false;
else
	$value = true;

}

/* View */
public function form_field_disp($print=true, $options=array())
{

$return = "<input type=\"radio\" name=\"$this->name\" value=\"0\"".(($this->value === false)?" checked":"")." class=\"".get_called_class()."\" />&nbsp;".$this->opt["boolean"][0]." <input name=\"$this->name\" type=\"radio\" value=\"1\"".(($this->value === true)?" checked":"")." class=\"".get_called_class()."\" />&nbsp;".$this->opt["boolean"][1];

if ($print)
	print $return;
else
	return $return;

}
public function __tostring()
{

if ($this->value === null)
	return "";
else
	return $this->opt["boolean"][($this->value)?1:0];

}

}

/**
 * Text
 * 
 * Plain text which can contains everything
 * Usefull for long text
 *
 */
class data_text extends data_string
{

protected $opt = array();

public function db_field_create()
{

return array("type"=>"string");

}

/* View */
public function form_field_disp($print=true, $options=array())
{

$return = "<textarea name=\"$this->name\" class=\"".get_called_class()."\">$this->value</textarea>";

if ($print)
	print $return;
else
	return $return;

}
public function form_field_select_disp($print=true, $options=array())
{

$return = data_string::form_field_disp();

if ($print)
	print $return;
else
	return $return;

}
public function __tostring()
{

if ($this->value === null)
	return "";
else
	return nl2br((string)$this->value, true);

}

}

/**
 * Date
 * 
 * using strftime() for the displaying
 * Stored in french format but can be changed
 * Associated to a jquery datepickerUI form and a date DB field
 * 
 */
class data_date extends data_string
{

protected $empty_value = "00/00/0000";

protected $opt = array
(
	"date_format" => "%A %d %B %G", // Defined for strftime()
	"size" => 10,
);

public function db_field_create()
{

return array("type" => "date");

}

/* Convert */
public function verify(&$value, $convert=false, $options=array())
{

if (!is_string($value) || !preg_match('/^(0?[1-9]|[12][0-9]|3[01])[\/](0?[1-9]|1[0-2])[\/](19|20)\d{2}$/', $value))
{
	if ($convert)
		$value = $this->empty_value;
	return false;
}

return true;

}
function convert(&$value)
{

if (!is_string($value) || !preg_match('/^(0?[1-9]|[12][0-9]|3[01])[\/](0?[1-9]|1[0-2])[\/](19|20)\d{2}$/', $value))
	$value = $this->empty_value;


}
function value_to_db()
{

if ($this->value === null)
	return null;
else
	return implode("-",array_reverse(explode("/",$this->value)));

}
function value_to_form()
{

if ($this->value === null)
	return "";
else
	return $this->value;
	
}
function value_from_db($value)
{

if ($value !== null)
	$this->value = implode("/",array_reverse(explode("-",$value)));
else
	$this->value = null;

}

function view($style="")
{

if (!$style)
	$style = $this->opt["date_format"];

if ($this->nonempty())
	return strftime($style, $this->timestamp());
else
	return "";

}
public function __tostring()
{

if ($this->nonempty())
	return strftime($this->opt["date_format"], $this->timestamp());
else
	return "";

}

/**
 * Returns the timestamp calculated from the stored value
 */
public function timestamp()
{

if ($this->nonempty())
{
	$date_e = explode("/", $this->value);
	return mktime(0, 0, 0, $date_e[1], $date_e[0], $date_e[2]);
}
else
	return null;

}
/**
 * Compare date timestamps and returns if the stored value is larger, smaller or equal to the passed value
 * @param timestamp $value
 */
public function compare($value)
{

if ($this->value !== null)
{
	$time_1 = $this->timestamp();
	$time_2 = $value;
	if ($time_1 < $time_2)
	{
		return "<";
	}
	elseif ($time_1 == $time_2)
	{
		return "=";
	}
	else
	{
		return ">";
	}
}
else
	return false;
}

}

/**
 * Year
 * 
 * Associated to a year DB field
 * 
 */
class data_year extends data_string
{

protected $empty_value = "0000";

protected $opt = array
(
	"size"=>4
);

public function db_field_create()
{

return array("type" => "year");

}

/* Convert */
public function verify(&$value, $convert=false, $options=array())
{

if (!is_string($value) || !preg_match("([0-9]{4})", $value))
{
	if ($convert)
		$value = $this->empty_value;
	return false;
}

return true;

}
function convert(&$value)
{

if (!is_string($value) || !preg_match("([0-9]{4})", $value))
	$value = $this->empty_value;

}

public function __tostring()
{

if ($this->nonempty())
	return $this->value;
else
	return "";

}

}

/**
 * Time
 * 
 */
class data_time extends data_string
{

protected $empty_value = "00:00:00";

protected $opt = array("size"=>8);

public function db_field_create()
{

return array("type" => "time");

}

/* Convert */
public function verify(&$value, $convert=false, $options=array())
{

if (!is_string($value) || !preg_match("/^(([01][0-9])|(2[0-3])):([0-5][0-9]):([0-5][0-9])$/",$value))
{
	if ($convert)
		$value = $this->empty_value;
	return false;
}

return true;

}
public function convert(&$value)
{

if (!is_string($value) || !preg_match("/^(([01][0-9])|(2[0-3])):([0-5][0-9]):([0-5][0-9])$/",$value))
	$value = $this->empty_value;

}

}

/**
 * Datetime (timestamp)
 * 
 * Associated to a datetime/timestamp DB field
 * 
 */
class data_datetime extends data_string
{

protected $empty_value = "0";
protected $opt = array
(
	"datetime_format" => "%A %d %B %G à %H:%M:%S"
);

public function db_field_create()
{

return array( "type" => "datetime" );

}

/* Convert */
public function verify(&$value, $convert=false, $options=array())
{

if (!is_string($value) || !$value)
{
	if ($convert)
		$value = null;
	return false;
}

$return = true;

$e = explode(" ", $value);
if (!data_date::verify($e[0], true))
	$return = false;

if (!count($e) == 2)
{
	$e = array($e[0], "00:00:00");
	$return = false;
}
elseif (!data_time::verify($e[1], true))
	$return = false;

if ($convert)
	$value = implode(" ", $e);

return $return;

}
public function convert(&$value)
{

if (!is_string($value) || count($e=explode(" ", $value)) != 2 || count($d=explode("/", $e[0])) != 3 || count($t=explode(":", $e[1])) != 3)
	$value = null;

}
public function convert_after(&$value)
{

if ($value !== null)
{
	$e = explode(" ", $value);
	$d = explode("/", $e[0]);
	$t = explode(":", $e[1]);
	$value = mktime($t[0], $t[1], $t[2], $d[1], $d[0], $d[2]);
}

}
public function value_from_db($value)
{

$this->convert($value);

if ($this->nonempty())
	$this->value = null;
else
{
	$e = explode(" ", $value);
	$d = explode("-", $e[0]);
	$t = explode(":", $e[1]);
	$this->value = mktime($t[0], $t[1], $t[2], $d[1], $d[2], $d[0]);
}

}
public function value_to_db()
{

return date("Y-m-d H:i:s", $this->value);

}

/* View */
public function __tostring()
{

if ($this->nonempty())
	return strftime($this->opt["datetime_format"], $this->value);
else
	return "";

}
public function form_field_disp($print=true, $options=array())
{

if ($this->nonempty())
	$value = date("d/m/Y H:i:s", $this->value);
else
	$value = "";

$return = "<input type=\"text\" name=\"".$this->name."\" value=\"$value\" size=\"19\" maxlength=\"19\" class=\"".get_called_class()."\" />";

if ($print)
	print $return;
else
	return $return;

}
public function date($str="")
{

if (!$this->value)
	return "";
elseif (!$str)
	return date("d/m/Y H:i:s", $this->value);
else
	return date($str, $this->value);

}
public function strftime($str="")
{

if (!$this->value)
	return "";
elseif (!$str)
	return strftime($this->opt["datetime_format"], $this->value);
else
	return strftime($str, $this->value);

}

}

/**
 * Select from a list
 * 
 * An element from an exhaustive given list
 *
 */
class data_select extends data_string
{

protected $empty_value = "";

protected $opt = array
(
	"value_list" => array(),
);

public function db_field_create()
{

$value_list = array();
foreach($this->opt["value_list"] as $name=>$label)
	$value_list[] = $name;
return array("type" => "select", "value_list" => $value_list);

}

/* Convert */
public function verify(&$value, $convert=false, $options=array())
{

if (!isset($this->opt["value_list"][$value]))
{
	if ($convert)
		$value = "";
	return false;
}

return true;

}
function convert(&$value)
{

if (!isset($this->opt["value_list"][$value]))
	$value = "";

}

/* View */
public function form_field_disp($print=true, $options=array())
{

$return = "<select name=\"$this->name\" class=\"".get_called_class()."\">";
$return .= "<option value=\"\"></option>";
foreach ($this->opt["value_list"] as $i=>$j)
	if ($this->value == $i)
		$return .= "<option value=\"$i\" selected=\"selected\">$j</option>";
	else
		$return .= "<option value=\"$i\">$j</option>";
$return .= "</select>";

if ($print)
	echo $return;
else
	return $return;

}
public function form_field_select_disp($print=true, $options=array())
{

$return = "<select name=\"$this->name\">";
$return .= "<option value=\"\"></option>";
foreach ($this->opt["value_list"] as $i=>$j)
	if ($options == $i)
		$return .= "<option value=\"$i\" selected=\"selected\">$j</option>";
	else
		$return .= "<option value=\"$i\">$j</option>";
$return .= "</select>";

if ($print)
	print $return;
else
	return $return;

}
function __tostring()
{

if ($this->value && isset($this->opt["value_list"][$this->value]))
	return "".$this->opt["value_list"][$this->value];
else
	return "";

}

}

/**
 * List (array)
 * 
 * Can be indexed or ordered
 * 
 * The content is a set en elements of the same type
 * TODO : Use the data_list_mixed to put different datatypes inside
 * 
 * Displaying uses jquery, and data is stored in json in a text DB field
 *
 */
class data_list extends data
{

protected $opt = array
(
	"db_ref_field" => "", // champ à récupérer
	"db_ref_table" => "", // table de liaison
	"db_ref_id" => "", // champ de liaison
);

/* Convert */
public function verify(&$value, $convert=false, $options=array())
{

if (!is_array($value))
{
	if ($convert)
		$value = array();
	return false;
}

return true;

}
public function convert(&$value)
{

if (!is_array($value))
	$value = array();

}
public function value_from_db($value)
{

if (is_array($value))
	$this->value = $value;
else
	$this->value = null;

}
public function value_to_db()
{

if (is_array($this->value))
	return $this->value;
else
	return null;

}

/* View */
public function __tostring()
{

if (is_array($this->value))
	return (string)implode(", ", $this->value);
else
	return "";

}
function form_field_disp($print=true)
{

if (isset($this->opt["db_order_field"]))
	$order = array($this->opt["db_order_field"]=>"asc");
else
	$order = array();

// Pas beaucoup de valeurs : liste simple
if (!is_array($this->value) || count($this->value) == 0)
{
	$return = "NADA";
}
elseif (true || ($nb=count($this->value)) < 20)
{
	if ($nb<10)
		$size = $nb;
	else
		$size = 5;
	$return = "<input name=\"$this->name\" type=\"hidden\" />";
	$return .= "<select name=\"".$this->name."[]\" title=\"$this->label\" multiple size=\"$size\" class=\"".get_called_class()."\">\n";
	if (is_array($this->value)) foreach ($this->value as $id)
			$return .= "<option value=\"$id\" selected>$id</option>";
	$return .= "</select>\n";
}
else
{
	// TODO : liste ajax
}

}
function form_field_select_disp($print=true)
{

$return = "<input name=\"$this->name\"  />";

// DISP
if ($print)
	echo $return;
else
	return $return;

}

}

/**
 * Select multiple like
 * 
 * A set of elements from a given exhaustive list
 * Associated to a select multiple form, and a 'set' DB field
 *
 */
class data_fromlist extends data_list
{

protected $opt = array
(
	"value_list" => array(),
);

public function db_field_create()
{

return array("type" => "fromlist", "value_list" => array_keys($this->opt["value_list"]));

}

/* Convert */
public function value_from_db($value)
{

$this->value = array();
if (is_array($value)) foreach ($value as $i)
	if (isset($this->opt["value_list"][$i]))
		$this->value[] = $i;

}
public function value_to_db()
{

if (is_array($this->value))
	return implode(",", $this->value);
else
	return null;

}
public function verify(&$value, $convert=false, $options=array())
{

if (!is_array($value))
{
	if ($convert)
		$value = array();
	return false;
}

$return = true;

foreach ($value as $nb=>$i)
{
	if (!isset($this->opt["value_list"][$i]))
	{
		if ($convert)
		{
			unset($value[$nb]);
			$return = false;
		}
		else
			return false;
	}
}

return $return;

}
public function convert(&$value)
{

if (!is_array($value))
	$value = array();

foreach ($value as $i=>$j)
	if (!isset($this->opt["value_list"][$j]))
		unset($value[$i]);

}

/* View */
public function __tostring()
{

if (is_array($this->value))
	return implode(", ", $this->value);
else
	return "";

}
public function form_field_disp($print=true, $options=array())
{

$return = "<select name=\"".$this->name."[]\" multiple class=\"".get_called_class()."\">";
foreach ($this->opt["value_list"] as $i=>$j)
	if (in_array($i, $this->value))
		$return .= "<option value=\"$i\" selected=\"selected\">$j</option>";
	else
		$return .= "<option value=\"$i\">$j</option>";
$return .= "</select>";

if ($print)
	echo $return;
else
	return $return;

}

}


/**
  * Extended data types
  *
  * - ID (for auto-incremented unique ID's)
  * - Numbers
  * - Name (label, title)
  */


/**
 * Number
 * 
 * Used to count objects, with method to help
 * Integer unsigned
 * 
 * TODO : supress
 * 
 */
class data_number extends data_integer
{

protected $opt = array
(
	"integer_signed"=>false,
	"size"=>10,
	"auto_increment"=>true,
);

function __construct($name, $value, $label="Number", $options=array())
{

data_integer::__construct($name, $value, $label, $options);

}

}

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


/**
  * Rich data types
  * 
  * - Percentage
  * - Measure width unit
  * - Amount of money
  * - Rich text (HTML)
  * - URL's
  * - Email addresses

  */


/**
 * Rich Text (XHTML)
 * 
 * Can limit the use of tags to a list.
 * 
 */
class data_richtext extends data_text
{

protected $opt = array
(
	"string_tag_attrib_authorized" => array("b"=>array(), "i"=>array(), "u"=>array(), "strong"=>array(), "a"=>array("href"), "p"=>array(), "img"=>array("src", "alt")),
	"string_tag_authorized" => "<p><b><i><u><font><strong><a><img><ul><li>"
);

public function db_field_create()
{

return array("type" => "richtext");

}

/* Conversion */
public function convert_before(&$value)
{

$value = strip_tags($value, $this->opt["string_tag_authorized"]);

}

/* View */
public function __tostring()
{

if ($this->value === null)
	return "";
else
	return (string)$this->value;

}
public function form_field_disp($print=true, $options=array())
{

$return = "<textarea name=\"$this->name\" class=\"".get_called_class()."\">$this->value</textarea>";

if ($print)
	print $return;
else
	return $return;

}

}

/**
 * Priority
 * 
 * Integer unsigned
 * 
 */
class data_priority extends data_integer
{

protected $opt = array
(
	"signed"=>false,
	"size"=>1,
);

function __construct($name, $value, $label="Priority", $options)
{

data_integer::__construct($name, $value, $label, $options);

}

}

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
	"signed"=>false,
	"size"=>10,
	"number_precision"=>4
);

function __construct($name, $value, $label="Measure", $options=array())
{

data_float::__construct($name, $value, $label, $options);

}

}

/**
 * Amount of money
 * 
 * Float unsigned
 * 
 */
class data_money extends data_float
{

protected $opt = array
(
	"signed"=>true,
	"size"=>8,
	"number_precision"=>2,
	"numeric_type"=>"amount"
);

function __construct($name, $value, $label="Amount", $options=array())
{

data_float::__construct($name, $value, $label, $options);

}

function __tostring()
{

if ($this->nonempty())
	return "";
else
	return $this->value." &euro;";

}

}

/**
 * Percent
 */
class data_percent extends data_float
{

protected $empty_value = null;

function __construct($name, $value, $label="Percent", $options=array())
{

data::__construct($name, $value, $label, $options);

}

public function db_field_create()
{

return array("type" => "float", "size" => 5, "precision" => 2);

}

public function verify(&$value, $convert=false, $options=array())
{

if (!is_numeric($value) || $value < 0 || $value > 1)
{
	if ($convert)
	{
		if ($value)
			$value = 1;
		else
			$value = 0;
	}
	return false;
}

return true;

}
public function convert(&$value)
{

if ($value)
	$value = 1;
else
	$value = 0;

}
function value_to_db()
{

if ($this->value === null)
	return null;
else
	return $this->value*100;

}
function value_from_db($value)
{

if ($value === null)
	$this->value = null;
else
	$this->value = $value/100;

}

public function form_field_disp($print=true, $options=array())
{

$return = "<input type=\"text\" name=\"$this->name\" value=\"".($this->value*100)."\" size=\"4\" maxlength=\"5\" class=\"".get_called_class()."\" />";

if ($print)
	print $return;
else
	return $return;

}
function __tostring()
{

if ($this->value === null)
	return "";
else
	return ($this->value*100)." %";

}

}

/**
 * Email
 *
 * Preg verified
 * Maxlength fixed to 64
 *
 */
class data_email extends data_string
{

protected $opt = array
(
	"size"=>128,
	"email_strict"=>false
);

function __construct($name, $value, $label="Email", $options=array())
{

data_string::__construct($name, $value, $label, $options);

}

function link($protect=false)
{

if ($protect)
{
	$id = rand(1,10000);
	list($nom, $domain) = explode("@", $this->value);
	return "<div id=\"id_$id\" style=\"diaplay:inline;\"></div><script type=\"text/javascript\">email_replace('$id', '$domain', '$nom');</script>";
}
else
	return "<a href=\"mailto:$this->value\">$this->value</a>";

}

public function verify(&$value, $convert=false, $options=array())
{

$regex = ($this->opt["email_strict"]) ? '/^([.0-9a-z_-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i' : '/^([*+!.&#$¦\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i';

if (!is_string($value) || !preg_match($regex, $value, $match) || !checkdnsrr($match[2], "MX"))
{
	if ($convert)
		$value = "";
	return false;
}

return false;

}

public function convert(&$value)
{

$regex = ($this->opt["email_strict"]) ? '/^([.0-9a-z_-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i' : '/^([*+!.&#$¦\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i';

if (!is_string($value) || !preg_match($regex, $value, $match) || !checkdnsrr($match[2], "MX"))
	$value = "";

}

}

/**
 * URL
 * 
 * Preg verified
 * Maxlength fixed to the standard data_string size (actually 256)
 * 
 */
class data_url extends data_string
{

protected $opt = array
(
	"regex" => '/^([a-zA-Z]+[:\/\/]+)*([A-Za-z0-9\-_]+\\.+[A-Za-z0-9\.\/%&=\?\-_]+)$/i',
	"urltype" => "http"
);

function __construct($name, $value, $label="URL", $options=array())
{

data_string::__construct($name, $value, $label, array_merge(array("url"=>array()), $options));

}

/* Convert */
public function verify(&$value, $convert=false, $options=array())
{

if (!is_string($value) || !preg_match($this->opt["regex"], $value))
{
	if ($convert)
		$value = "";
	return false;
}

return true;

}
public function convert(&$value)
{

if (!is_string($value) || !preg_match($this->opt["regex"], $value))
	$value = "";

}
public function convert_after(&$value)
{

$value = preg_replace($this->opt["regex"], "$2", $value);

}

/* View */
function link($target="_blank")
{
	if ($target)
		return "<a href=\"".$this->opt["urltype"]."://$this->value\" target=\"$target\">$this->value</a>";
	else
		return "<a href=\"".$this->opt["urltype"]."://$this->value\">$this->value</a>";
}

}


/** 
  * Media data types :
  * 
  * - file
  * - stream
  * - Sounds
  * - Pictures, Photos, etc.
  * - Videos
  * - Word, Excel, Openoffice, etc. files
  */


/**
 * Donnée de type fichier
 * 
 * Dans ce cas, form_field() doit permettre de visualiser & modifier le fichier.
 * Pas évident... on doit pouvoir le modifier parmis une liste existante,
 * en charger un autre, et la visualisation va dépendre de pas mal de choses !
 * Il faut donc lui donner les paramètres nécessaires à ce bon fonctionnement...
 * 
 * Quitte à créer un autre datatype pour d'autres besoins,
 * le data_file doit passer par un gestionnaire de fichier indépendant,
 * et la donnée sera effectivement stoquée sur le disque.
 * 
 * Cette classe sera surchargée de nombreuses fois.
 * Elle fournit les méthodes de base de téléchargement, lien, etc.
 * 
 */
class data_file extends data
{

protected $opt = array("fileformat"=>"");

public function download_link_disp()
{

echo "<a href=\"$this->filelocation/$this->filename\">$this->filename</a>";

}

}

/**
 * Stream
 *
 */
class data_stream extends data
{

}

/**
 * Image/Picture
 *
 */
class data_image extends data_file
{

static protected $format_list = array( "jpg"=>"image/jpeg" , "png"=>"image/png" , "gif"=>"image/gif" );

protected $opt = array("fileformat"=>"jpg", "imgquality"=>90);

/*
 * A TRAVAILLER... PAS EVIDENT
 * On doit pouvoir forcer un format en entr�e, convertir en un format donn� au besoin.
 */

function format_convert($format)
{

if (isset(self::$format_list[$format]))
	$this->format = $format;

}

}

/**
 * Audio
 *
 */
class data_audio extends data_file
{

static protected $format_list = array( "mp3"=>"audio/mpeg-1" , "wav"=>"audio/wave" , "wma"=>"audio/wma" , "ogg"=>"audio/ogg" , "ogg"=>"image_gif" );

protected $opt = array("fileformat" => "mp3");

function format_convert($format)
{

if (isset(self::$format_list[$format]))
	$this->format = $format;

}

}

/**
 * Video
 *
 */
class data_video extends data_file
{

static protected $format_list = array( "wmv"=>"video/wmv" , "avi"=>"video/avi" , "ogg"=>"video/ogg" , "flv"=>"video/flv" , "mp4"=>"video/mpeg-4" , "mov"=>"video/quicktime" );

protected $opt = array("fileformat" => "flv");

function format_convert($format)
{

if (isset(self::$format_list[$format]))
	$this->format = $format;

}

}


/**
  * Complex data types
  * 
  * - Object
  * - Dataobject
  * - Dataobject list
  */

/**
 * Object
 * 
 */
class data_object extends data
{

protected $opt = array
(
	"object_type" => "objecttype",
	"db_table" => "",
	"db_fieldname" => "",
	"db_format" => ""
);

/* Dire qu'on veut en entr�e un objet r�pondant � une interface donn�e
 * Pour cela on cr�� une classe abstraite r�pondant � la question
 * et tout objet instanci� doit en �tre surcharg�.
 */

}

/**
 * Agregat/Datamodel
 * 
 * Used to store an object created using a datamodel
 *
 */
class data_agregat extends data_object
{

protected $opt = array
(
	"datamodel" => "datamodel_name",
	"db_tablename" => "",
	"db_fieldname" => "",
	"db_type" => "blob",
);

}

/**
 * Dataobject/Databank
 * 
 * Dataobjects a specific agregats of class data_bank_agregat,
 * corresponding to a datamodel associated to a database.
 * Thoses objects needs only an ID to be retrieved
 */
class data_dataobject extends data_agregat
{

protected $opt = array
(
	"datamodel" => 0,
	"ref_field_disp" => "", // field to display if needed
);

function __construct($name, $value, $label="Object", $options=array())
{

data::__construct($name, $value, $label, $options);

}

function __tostring()
{

if ($this->value && ($datamodel=datamodel($this->opt["datamodel"])) && ($object=$datamodel($this->value)))
{
	if (($fieldname=$this->opt["ref_field_disp"]) && isset($datamodel->{$fieldname}))
	{
		return (string)$object->{$fieldname};
	}
	else
		return (string)$object;
}
else
	return "";

}

/**
 * Returns the object
 */
function object()
{

if ($this->value)
	return datamodel($this->opt["datamodel"])->get($this->value);
else
	return null;

}

public function verify(&$value, $convert=false, $options=array())
{

if (!is_numeric($value) || !datamodel($this->opt["datamodel"])->exists($value))
{
	if ($convert)
		$value = null;
	return false;
}

return true;

}

function convert(&$value)
{

if (!is_numeric($value) || !datamodel($this->opt["datamodel"])->exists($value))
	$value = null;

}

function form_field_disp($print=true, $option=array())
{

// Pas beaucoup de valeurs : liste simple
if (($databank=datamodel($this->opt["datamodel"])) && (($nb=$databank->count()) <= 50))
{
	if (isset($option["order"]))
		$query = $databank->query(array(), array(), $option["order"]);
	else
		$query = $databank->query();

	$return = "<select name=\"$this->name\" title=\"$this->label\" class=\"".get_called_class()."\">\n";
	$return .= "<option></option>";
	foreach($query as $object)
	{
		if ($this->value == $object->id)
		{
			$return .= "<option value=\"$object->id\" selected=\"selected\">$object</option>";
		}
		else
			$return .= "<option value=\"$object->id\">$object</option>";
	}
	$return .= "</select>\n";
}
// Beaucoup de valeurs : liste Ajax complexe
else
{
	$return = "<div style=\"display:inline;\"><input name=\"$this->name\" value=\"$this->value\" type=\"hidden\" class=\"q_id\" />";
	if ($this->value)
		$value = (string)datamodel()->get($this->opt["datamodel"], $this->value, true);
	else
		$value = "";
	$return .= "<select class=\"q_type\"><option value=\"like\">Approx.</option><option value=\"fulltext\">Precis</option></select><input class=\"q_str\" value=\"$value\" onkeyup=\"object_list_query(".$this->opt["datamodel"].", [{'type':$('.q_type', this.parentNode).val(),'value':this.value}], $(this).parent().get(0));\" onblur=\"object_list_hide($(this).parent().get(0))\" onfocus=\"this.select();if(this.value) object_list_query(".$this->opt["datamodel"].", [{'type':$('.q_type', this.parentNode).val(),'value':this.value}], $(this).parent().get(0));\" />";
	$return .= "<div class=\"q_select\"></div>";
	$return .= "</div>";
}

if ($print)
	echo $return;
else
	return $return;

}

function form_field_disp_all($print=true)
{

$return = "<div id=\"".$this->name."_list\">\n";
$return .= "<select name=\"".$this->name."\" title=\"$this->label\" class=\"".get_called_class()."\">\n";

$query = datamodel($this->opt["databank"])->query();
foreach ($query as $object)
{
	if ($this->value == "$object->id")
		$return .= "<option value=\"$object->id\" selected=\"selected\">$object</option>";
	else
		$return .= "<option value=\"$object->id\">$object</option>";
}

$return .= "</select>\n";
$return .= "</div>\n";


if ($print)
	echo $return;
else
	return $return;

}

public function db_field_create()
{

return array("type" => "integer", "size" => 10, "signed"=>false);

}

}

/**
 * Donnée de type dataobject avec choix du type de dataobject en param
 */
class data_dataobject_select extends data_agregat
{

protected $value_empty = array();

protected $opt = array
(
	"datamodel_list" => array(), // liste des databank concern�es
	"databank_field" => "",
	"db_field" => ""
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

function value_from_db($value) // ICI on r�cup�re un champ string de la forme "datatype,id"
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

/**
 * List of dataobjects
 * 
 * Set of dataobjects from the same databank
 */
class data_dataobject_list extends data_list
{

protected $value_empty = array();

protected $opt = array
(
	"datamodel" => "databank_name",
	"db_ref_field" => "", // id du dataobject à récupérer
	"db_ref_table" => "", // table de liaison
	"db_ref_id" => "", // champ de liaison
	"db_order_field" => "", // champ qui gère ordre (optinonel)
	"ref_field_disp" => "", // field to display if needed
);

function __construct($name, $value, $label="Object list", $options=array())
{

data::__construct($name, $value, $label, $options);

}

function __tostring()
{

if ($this->opt["db_order_field"])
	$order = array($this->opt["db_order_field"]=>"asc");
else
	$order = array();

if (!is_array($this->value) || !count($this->value))
{
	return "";
}
elseif ($this->opt["ref_field_disp"])
{
	$query = datamodel($this->opt["datamodel"])->query(array(array("name"=>"id", "value"=>$this->value)), true, $order);
	$return = array();
	foreach($query as $object)
	{
		$return[] = $object->{$this->opt["ref_field_disp"]};
	}
	return implode(", ", $return);
}
else
{
	return implode(", ", datamodel($this->opt["datamodel"])->query(array(array("name"=>"id", "value"=>$this->value)), true, $order));
}

}

/**
 * Returns objets in a list
 */
function object_list()
{

if ($this->opt["db_order_field"])
	$order = array($this->opt["db_order_field"]=>"asc");
else
	$order = array();

if (is_array($this->value) && count($this->value))
{
	// Retrieve objects in databank
	datamodel($this->opt["datamodel"])->query(array(array("name"=>"id", "value"=>$this->value)), true);
	// Sort by order
	$return = array();
	foreach ($this->value as $nb=>$id)
		$return[] = datamodel($this->opt["datamodel"])->get($id);
	return $return;
}
else
{
	return array();
}

}

public function verify(&$value, $convert=false, $options=array())
{

if (!is_array($value))
{
	if ($convert)
		$value = array();
	return false;
}

$return = true;

foreach($value as $nb=>$id)
{
	if (!datamodel($this->opt["datamodel"])->exists($id))
		if ($convert)
		{
			unset($value[$nb]);
			$return = false;
		}
		else
			return false;
}

return $return;

}

function convert(&$value)
{

if (!is_array($value))
	$value = array();

foreach($value as $n=>$id)
{
	if (!datamodel($this->opt["datamodel"])->exists($id))
		unset($value[$n]);
}

}

function form_field_disp($print=true)
{

if ($this->opt["db_order_field"])
	$order = array($this->opt["db_order_field"]=>"asc");
else
	$order = array();

// Pas beaucoup de valeurs : liste simple
if (($nb=datamodel($this->opt["datamodel"])->db_count()) < 20)
{
	$query = datamodel($this->opt["datamodel"])->query();
	if ($nb<10)
		$size = $nb;
	else
		$size = 5;
	$return = "<input name=\"$this->name\" type=\"hidden\" />";
	$return .= "<select name=\"".$this->name."[]\" title=\"$this->label\" multiple size=\"$size\" class=\"".get_called_class()."\">\n";
	if (is_array($this->value)) foreach ($this->value as $id)
			$return .= "<option value=\"$id\" selected>".datamodel($this->opt["datamodel"])->get($id)."</option>";
	foreach($query as $object)
	{
		if (!is_array($this->value) || !in_array($object->id, $this->value))
			$return .= "<option value=\"$object->id\">$object</option>";
	}
	$return .= "</select>\n";
}
// Beaucoup de valeurs : liste Ajax complexe
else
{
	$return = "<div style=\"display:inline;\">";
	$return .= "<input name=\"$this->name\" type=\"hidden\" />";
	$return .= "<select class=\"q_type\"><option value=\"like\">Approx.</option><option value=\"fulltext\">Precis</option></select><input class=\"q_str\" onkeyup=\"object_list_query(".$this->opt["datamodel"].", [{'type':$('.q_type', this.parentNode).val(),'value':this.value}], $(this).parent().get(0));\" onblur=\"object_list_hide($(this).parent().get(0))\" onfocus=\"this.select();if(this.value) object_list_query(".$this->opt["datamodel"].", [{'type':$('.q_type', this.parentNode).val(),'value':this.value}], $(this).parent().get(0));\" />";
	$return .= "<div><select name=\"".$this->name."[]\" title=\"$this->label\" multiple class=\"".get_called_class()." q_id\">";
	if (is_array($this->value) && count($this->value))
	{
		datamodel($this->opt["datamodel"])->query(array(array("name"=>"id", "value"=>$this->value)));
		foreach ($this->value as $id)
			$return .= "<option value=\"$id\" selected>".datamodel($this->opt["datamodel"])->get($id)."</option>";
	}
	$return .= "</select></div>";
	$return .= "<div class=\"q_select\"></div>";
	$return .= "</div>";
}

// DISP
if ($print)
	echo $return;
else
	return $return;

}

public function db_query_param($value, $type="=")
{

$type_list = array( "=", "LIKE", "<", ">", "<=", ">=", "NOT LIKE" );  
if (!in_array($type, $type_list))
	$type = "=";

$fieldname = $this->opt["db_ref_id"];

if (is_array($value) && count($value))
	return "`".$fieldname."` IN (".implode(", ",$this->value).")";
else
	return "`".$fieldname."` $type '".db()->string_escape($value)."'";

}

/**
 * Data to create the associated database 
 */
public function db_create()
{

if ($this->opt["db_ref_table"])
{
	$return = array
	(
		"name" => $this->opt["db_ref_table"],
		"options" => array(),
	);
	if ($this->opt["db_order_field"])
		$return["fields"] = array
		(
			$this->opt["db_ref_id"] => array("type"=>"integer", "size"=>10, "signed"=>false, "null"=>false, "key"=>true),
			$this->opt["db_order_field"] = array("type"=>"integer", "size"=>3, "signed"=>false, "null"=>false, "key"=>true),
			$this->opt["db_ref_field"] => array("type"=>"integer", "size"=>10, "signed"=>false, "null"=>false, "key"=>false)
		);
	else
		$return["fields"] = array
		(
			$this->opt["db_ref_id"] => array("type"=>"integer", "size"=>10, "signed"=>false, "null"=>false, "key"=>true),
			$this->opt["db_ref_field"] => array("type"=>"integer", "size"=>10, "signed"=>false, "null"=>false, "key"=>true)
		);
}

return $return;

}

}


/**
 * Data types access function
 */
function data($id=null)
{

if (!isset($GLOBALS["data_gestion"]))
{
	if (OBJECT_CACHE)
	{
		if (!($GLOBALS["data_gestion"]=object_cache_retrieve("datatype_gestion")))
			$GLOBALS["data_gestion"] = new data_gestion();
	}
	// Session
	else
	{
		if (!isset($_SESSION["data_gestion"]))
			$_SESSION["data_gestion"] = new data_gestion();
		$GLOBALS["data_gestion"] = $_SESSION["data_gestion"];
	}
}

if (is_numeric($id))
	return $GLOBALS["data_gestion"]->get($id);
elseif (is_string($id))
	return $GLOBALS["data_gestion"]->get_name($id);
else
	return $GLOBALS["data_gestion"];

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
