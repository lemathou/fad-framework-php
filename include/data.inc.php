<?

/**
  * $Id$
  * 
  * Copyright 2008 Mathieu Moulin - lemathou@free.fr
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
 * Datatype (audio, text, video, file, etc.)
 * 
 * @var string
 */
protected $type="";

/**
 * Données brutes dans le format dééfini et "contraint" le plus adapté
 * 
 * @var mixed
 */
protected $value=null;

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
	"size", "ereg" , "numeric" , "array" , "boolean", "percent", "select" , "fromlist" , "date" , "time" , "datetime" , "object" , "databank", "databank_list", "email", "url",
	// db
	"table", "field" , "type" , "ref_table" , "ref_field" , "ref_id", "order_field" , "databank_field" , "select_params" , "lang" , "auto_increment",
	// disp
	"mime_type", "ref_field_disp", "preg_replace", "template_datamodel",
	// form
	
);

/**
 * Options de structure et par extension de Verification & Conversion (Voir les classes de conversion associées)
 *dataobject
 * @var array
 */
protected $structure_opt = array();
public static $structure_opt_list = array("size", "ereg" , "integer" , "float" , "array" , "boolean", "percent", "compare" , "count" , "select" , "fromlist" , "date" , "time" , "datetime" , "object" , "datamodel", "databank", "databank_select", "email", "url");

/**
 * Type en base de donnée (SQL) à renvoyer à la classe db (voir les classes associées)
 *
 * @var array
 */
protected $db_opt = array();
public static $db_opt_list = array("table", "field" , "type" , "ref_table" , "ref_field" , "ref_id", "order_field" , "databank_field" , "select_params" , "lang" , "auto_increment");

/**
 * Pour l'affichage, tout seta défini via la classe container (à voir)
 *
 * @var unknown_type
 */
protected $disp_opt = array();
/*
 * Paramètres de feuille de style
 * 	"css_id" => "",
 * 	"css_class" => "",
 * 	"css_stype" => "",
 */
public static $disp_opt_list = array("mime_type", "ref_field_disp", "preg_replace", "template_datamodel", "");

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
	$this->structure_opt_set($i, $j);
	//$this->opt_set($i, $j);
}

}

public function datamodel_set($datamodel_id)
{

if (datamodel()->exists($datamodel_id))
{
	$this->datamodel_id = $datamodel_id;
	return true;
}
else
	return false;

}
public function datamodel()
{

if ($this->datamodel_id)
	return datamodel($this->datamodel_id);
else
	return false;

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
public function structure_opt_set($name, $value)
{

if (in_array($name, self::$structure_opt_list))
{
	$this->structure_opt[$name] = $value;
	return true;
}
else
	return false;

}
public function structure_opt($name)
{

if (isset($this->structure_opt[$name]))
{
	return $this->structure_opt[$name];
}

}
public function structure_opt_list_get()
{

return $this->structure_opt;

}
public function db_opt_set($name, $value)
{

if (in_array($name, self::$db_opt_list))
{
	$this->db_opt[$name] = $value;
	return true;
}
else
	return false;

}
public function db_opt($name)
{

if (isset($this->db_opt[$name]))
{
	return $this->db_opt[$name];
}

}
public function db_opt_list_get()
{

return $this->db_opt;

}
public function disp_opt_set($name, $value)
{

if (in_array($name,self::$disp_opt_list))
{
	$this->disp_opt[$name] = $value;
	return true;
}
else
	return false;
	
}
public function disp_opt($name)
{

if (isset($this->disp_opt[$name]))
{
	return $this->disp_opt[$name];
}

}
public function disp_opt_list_get()
{

return $this->disp_opt;

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

return (boolean) $this->value;

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

if (is_string($name) && in_array($name, array("name", "type", "label", "value", "opt_list", "datamodel_id", "structure_opt", "db_opt", "disp_opt")) && isset($this->{$name}))
	return $this->{$name};
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

if (!isset($this->db_opt["field"]) || !($fieldname=$this->db_opt["field"]))
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

protected $type = "string";

protected $structure_opt = array
(
	"size" => 256
);

public function db_field_create()
{

return array( "type" => "string", "size" => $this->structure_opt["size"] );

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

if (isset($this->structure_opt["size"]) && ($maxlength=$this->structure_opt["size"]) && strlen($value) > $maxlength)
{
	if ($convert)
	{
		$value = substr($value, 0, $maxlength);
		$return = false;
	}
	else
		return false;
}

if (isset($this->structure_opt["ereg"]) && ($ereg=$this->structure_opt["ereg"]) && !preg_match($ereg, $value))
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
if (isset($this->structure_opt["size"]) && ($maxlength=$this->structure_opt["size"]) && strlen($value) > $maxlength)
	$value = substr($value, 0, $maxlength);
if (isset($this->structure_opt["ereg"]) && ($ereg=$this->structure_opt["ereg"]) && !preg_match($ereg, $value))
	$value = null;

}

/* View */
public function __tostring()
{

if ($this->value === null)
	return "";
elseif (isset($this->disp_opt["preg_replace"]) && is_array($opt=$this->disp_opt["preg_replace"]) && isset($opt["pattern"]) && isset($opt["replace"]) && preg_match($opt["pattern"], $this->value))
{
	return preg_replace($opt["pattern"], $opt["replace"], (string)$this->value);
}
else
	return (string)$this->value;

}
public function form_field_disp($print=true, $options=array())
{

$attrib_size = ( isset($this->structure_opt["size"]) && $this->structure_opt["size"] > 0 && $this->structure_opt["size"] < 32 )
	? " size=\"".$this->structure_opt["size"]."\""
	: "";
$attrib_maxlength = ( isset($this->structure_opt["size"]) && $this->structure_opt["size"] > 0 )
	? " maxlength=\"".$this->structure_opt["size"]."\""
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

protected $type = "password";

protected $structure_opt = array
(
	"size" => 64,
	"enctype" => "md5"
);

/* TODO : penser à la conversion en md5=>voir comment modifier par la suite
 * le mieux est peut-être de stocker directement à l'insertion en md5
 * pour pouvoir plus aisément comparer... a voir !!
 */

function __tostring()
{

if ($this->value === null)
	return "";
else
	return (string)$this->value;

}

}

/**
 * Integer (numeric)
 *
 */
class data_integer extends data_string
{

protected $type = "integer";

protected $structure_opt = array
(
	"integer" => array( "signed" => true , "size" => 11 ), // TODO : use "numeric" opt name
);

public function db_field_create()
{

$return = array
(
	"type" => "integer",
	"size" => $this->structure_opt["integer"]["size"],
	
);
if ($this->structure_opt["integer"]["signed"])
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

if (isset($this->structure_opt["integer"]["signed"]) && $this->structure_opt["integer"]["signed"] == false && $value < 0)
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
if (isset($this->structure_opt["integer"]["signed"]) && $this->structure_opt["integer"]["signed"] == false && $value < 0)
	$value = -$value;

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

$attrib_size = " size=\"".($this->structure_opt["integer"]["size"]+1)."\"";
$attrib_maxlength = " maxlength=\"".($this->structure_opt["integer"]["size"]+1)."\"";

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

}

/**
 * Float (numeric)
 *
 */
class data_float extends data_string
{

protected $type = "float";

protected $structure_opt = array
(
	"float" => array("signed"=>true, "size"=>11, "precision"=>2) // TODO : use "numeric" opt name
);

public function db_field_create()
{

$return = array
(
	"type" => "float",
	"size" => $this->structure_opt["float"]["size"],
	"precision" => $this->structure_opt["float"]["precision"],
	
);
if ($this->structure_opt["float"]["signed"])
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

if (isset($this->structure_opt["float"]["precision"]) && ($precision=$this->structure_opt["float"]["precision"]) && $value != round($value, $precision))
	if ($convert)
	{
		$value = round($value, $precision);
		$return = false;
	}
	else
		return false;

if (isset($this->structure_opt["float"]["signed"]) && $this->structure_opt["float"]["signed"] == false && $value < 0)
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

/* View */
function __tostring()
{

if ($this->value === null)
	return "";
else
	return (string)$this->value;

}
public function form_field_disp($print=true, $options=array())
{

$attrib_size = " size=\"".($this->structure_opt["float"]["size"]+2)."\"";
$attrib_maxlength = " maxlength=\"".($this->structure_opt["float"]["size"]+2)."\"";

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

protected $structure_opt = array("boolean"=>array("NO","YES"));

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

$return = "<input type=\"radio\" name=\"$this->name\" value=\"0\"".($this->value === false)?" checked":""." class=\"".get_called_class()."\" />&nbsp;".$this->structure_opt["boolean"][0]." <input name=\"$this->name\" type=\"radio\" value=\"1\"".($this->value === true)?" checked":""." class=\"".get_called_class()."\" />&nbsp;".$this->structure_opt["boolean"][1];

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
	return $this->structure_opt["boolean"][($this->value)?1:0];

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

protected $type = "text";

protected $structure_opt = array();

public function db_field_create()
{

return array("type"=>"string");

}

/* View */
public function form_field_disp($print=true, $options=array())
{

$attrib_maxlength = ( isset($this->structure_opt["size"]) && $this->structure_opt["size"] > 0 ) ? " maxlength=\"".$this->structure_opt["size"]."\"" : "";

$return = "<textarea name=\"$this->name\"$attrib_maxlength class=\"".get_called_class()."\">$this->value</textarea>";

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

protected $type = "date";

protected $structure_opt = array
(
	"date" => "%A %d %B %G", // Defined for strftime()
	"size" => 10,
);

public function db_field_create()
{

return array( "type" => "date" );

}

public function nonempty()
{

if ($this->value && $this->value != '00/00/0000')
	return true;
else
	return false;

}

/* Convert */
public function verify(&$value, $convert=false, $options=array())
{

if (!is_string($value) || !preg_match('/^(0?[1-9]|[12][0-9]|3[01])[\/](0?[1-9]|1[0-2])[\/](19|20)\d{2}$/', $value))
{
	if ($convert)
		$value = "00/00/0000";
	return false;
}

return true;

}
function convert(&$value)
{

if (!is_string($value) || !preg_match('/^(0?[1-9]|[12][0-9]|3[01])[\/](0?[1-9]|1[0-2])[\/](19|20)\d{2}$/', $value))
	$value = "00/00/0000";


}
function value_to_db()
{

if ($this->value === null || !$this->value)
	return null;
else
	return implode("-",array_reverse(explode("/",$this->value)));

}
function value_to_form()
{

if ($this->value === null || !$this->value)
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

/* View */
public function form_field_disp($print=true, $options=array())
{

$attrib_size = ( isset($this->structure_opt["size"]) && $this->structure_opt["size"] > 0 ) ? " size=\"".$this->structure_opt["size"]."\"" : "";
$attrib_maxlength = ( isset($this->structure_opt["size"]) && $this->structure_opt["size"] > 0 ) ? " maxlength=\"".$this->structure_opt["size"]."\"" : "";

$return = "<input type=\"text\" name=\"$this->name\" class=\"".get_called_class()."\" value=\"$this->value\"$attrib_size$attrib_maxlength />";

if ($print)
	print $return;
else
	return $return;

}
function view($style="%A %d %B %G")
{

if ($this->value && $this->value != "00/00/0000")
	return strftime($style, $this->timestamp());
else
	return "";

}
public function __tostring()
{

if ($this->value && $this->value != "00/00/0000")
	return strftime($this->structure_opt["date"], $this->timestamp());
else
	return "";

}

/**
 * Returns the timestamp calculated from the stored value
 */
public function timestamp()
{

if ($this->value && $this->value != "00/00/0000")
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

if ($this->value)
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

protected $type = "year";

protected $structure_opt = array
(
	"year" => "0000", // A CORRIGER
);

public function db_field_create()
{

return array( "type" => "year" );

}

/* Convert */
public function verify(&$value, $convert=false, $options=array())
{

if (!is_string($value) || !preg_match("([0-9]{4})", $value))
{
	if ($convert)
		$value = "0000";
	return false;
}

return true;

}
function convert(&$value)
{

if (!is_string($value) || !preg_match("([0-9]{4})", $value))
	$value = "0000";

}

/* View */
public function form_field_disp($print=true, $options=array())
{

$return = "<input type=\"text\" name=\"$this->name\" value=\"$this->value\" size=\"4\" maxlength=\"4\" class=\"".get_called_class()."\" />";

if ($print)
	print $return;
else
	return $return;

}
public function __tostring()
{

if ($this->value && $this->value != "0000")
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

protected $type = "time";

protected $structure_opt = array
(
	"time" => ""
);

public function db_field_create()
{

return array("type" => "time");

}

/* Convert */
public function verify(&$value, $convert=false, $options=array())
{

if (!is_string($value) || !preg_match("(([01][0-9])|(2[0-3])):([0-5][0-9]):([0-5][0-9])",$value))
{
	if ($convert)
		$value = "00:00:00";
	return false;
}

return true;

}
public function convert(&$value)
{

if (!is_string($value) || !preg_match("(([01][0-9])|(2[0-3])):([0-5][0-9]):([0-5][0-9])",$value))
	$value = "00:00:00";

}

/* View */
public function form_field_disp($print=true, $options=array())
{

$return = "<input type=\"text\" name=\"".$this->name."[time]\" value=\"$this->value\" size=\"8\" maxlength=\"8\" class=\"".get_called_class()."\" />";

if ($print)
	print $return;
else
	return $return;

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

protected $type = "datetime";

protected $structure_opt = array
(
	"datetime" => "%A %d %B %G à %H:%M:%S", // Defined for strftime()
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

if (!is_string($value) || !$value || count($e=explode(" ", $value)) != 2 || count($d=explode("/", $e[0])) != 3 || count($t=explode("/", $e[1])) != 3)
	$value = null;

}
public function convert_after(&$value)
{

if ($value !== null)
{
	$e = explode(" ", $value);
	$d = explode("/", $e[0]);
	$t = explode("/", $e[1]);
	$value = mktime($t[0], $t[1], $t[2], $d[1], $d[0], $d[2]);
}

}
public function value_from_db($value)
{

if ($value === null || $value == "0000-00-00 00:00:00")
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

if ($this->value)
	return strftime($this->structure_opt["datetime"], $this->value);
else
	return "";

}
public function form_field_disp($print=true, $options=array())
{

if ($this->value)
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
	return strftime($this->structure_opt["datetime"], $this->value);
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

protected $type = "select";

protected $structure_opt = array
(
	"select" => array(),
);

public function db_field_create()
{

$value_list = array();
foreach($this->structure_opt["select"] as $name=>$label)
	$value_list[] = $name;
return array("type" => "select", "value_list" => $value_list);

}

/* Convert */
public function verify(&$value, $convert=false, $options=array())
{

if (!isset($this->structure_opt["select"][$value]))
{
	if ($convert)
		$value = "";
	return false;
}

return true;

}
function convert(&$value)
{

if (!isset($this->structure_opt["select"][$value]))
	$value = "";

}

/* View */
public function form_field_disp($print=true, $options=array())
{

$return = "<select name=\"$this->name\" class=\"".get_called_class()."\">";
$return .= "<option value=\"\"></option>";
foreach ($this->structure_opt("select") as $i=>$j)
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
foreach ($this->structure_opt["select"] as $i=>$j)
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

if ($this->value && isset($this->structure_opt["select"][$this->value]))
	return "".$this->structure_opt["select"][$this->value];
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

protected $type = "list";

protected $structure_opt = array
(
	"list" => array("datatype"=>"string"),
);

protected $db_opt = array
(
	"ref_field" => "", // champ à récupérer
	"ref_table" => "", // table de liaison
	"ref_id" => "", // champ de liaison
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

if ($this->value === null)
	return "";
else
	return (string)count($this->value);

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

protected $type = "fromlist";

protected $structure_opt = array
(
	"fromlist" => array(),
);

public function db_field_create()
{

return array( "type" => "fromlist" , "value_list" => array_keys($this->structure_opt["fromlist"]) );

}

/* Convert */
public function value_from_db($value)
{

$this->value = array();
if (is_array($value)) foreach ($value as $i)
	if (isset($this->structure_opt["fromlist"][$i]))
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
	if (!isset($this->structure_opt["fromlist"][$i]))
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
	if (!isset($this->structure_opt["fromlist"][$j]))
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
foreach ($this->structure_opt["fromlist"] as $i=>$j)
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
 */
class data_number extends data_integer
{

function __construct($name, $value, $label="Number", $size="10", $options=array())
{

data_integer::__construct($name, $value, $label, array_merge(array("integer"=>array("signed"=>false, "size"=>$size), "size"=>$size), $options));

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

protected $structure_opt = array
(
	"integer"=>array("signed"=>false, "size"=>6)
);

protected $db_opt = array
(
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
	"size"=>$this->structure_opt["integer"]["size"],
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

protected $structure_opt = array( "size"=>64 );

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

protected $structure_opt = array( "size"=>256 );
	
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

protected $type = "richtext";

protected $structure_opt = array
(
	"string_tag_attrib_authorized" => array("b"=>array(), "i"=>array(), "u"=>array(), "strong"=>array(), "a"=>array("href"), "p"=>array(), "img"=>array("src", "alt")),
	"string_tag_authorized" => "<p><b><i><u><font><strong><a><img>"
);

public function db_field_create()
{

return array("type" => "richtext");

}

/* Conversion */
public function convert_before(&$value)
{

$value = strip_tags($value, $this->structure_opt["string_tag_authorized"]);

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
class data_priority extends data_number
{

function __construct($name, $value, $label="Priority", $size=1)
{

data_integer::__construct($name, $value, $label, array("integer"=>array("signed"=>false, "size"=>$size)));

}

}

/**
 * Auto counter (silly..?)
 */
class data_meter extends data_integer
{

public function increment()
{

$this->value++;

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

function __construct($name, $value, $label="Measure", $precision=4, $options=array())
{

data_float::__construct($name, $value, $label, array_merge(array("float"=>array("signed"=>false, "size"=>10, "precision"=>$precision)), $options));

}

function __tostring()
{

if ($this->value === null)
	return "";
else
	return (string)$this->value;

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

protected $structure_opt = array(
	"float" => array("signed"=>true, "size"=>8, "precision"=>2)
);

function __construct($name, $value, $label="Amount", $type="", $limit=null)
{

data_float::__construct($name, $value, $label);

}

function __tostring()
{

if ($this->value === null)
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

protected $structure_opt = array("percent"=>array());

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

function __construct($name, $value, $label="Email", $options=array())
{

data_string::__construct($name, $value, $label, array_merge(array("email"=>array("strict"=>false), "size"=>128), $options));

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

$regex = ($this->structure_opt["email"]["strict"]) ? '/^([.0-9a-z_-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i' : '/^([*+!.&#$¦\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i';

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

$regex = ($this->structure_opt["email"]["strict"]) ? '/^([.0-9a-z_-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i' : '/^([*+!.&#$¦\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i';

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

function __construct($name, $value, $label="URL", $options=array())
{

data_string::__construct($name, $value, $label, array_merge(array("url"=>array()), $options));

}

function link($target="_blank")
{
	if ($target)
		return "<a href=\"$this->value\" target=\"$target\">$this->value</a>";
	else
		return "<a href=\"$this->value\">$this->value</a>";
}

public function verify(&$value, $convert=false, $options=array())
{

$regex = '/^[a-zA-Z]+[:\/\/]+[A-Za-z0-9\-_]+\\.+[A-Za-z0-9\.\/%&=\?\-_]+$/i';

if (!is_string($value) || !preg_match($regex, $value))
{
	if ($convert)
		$value = "";
	return false;
}

return true;

}

public function convert(&$value)
{

$regex = '/^[a-zA-Z]+[:\/\/]+[A-Za-z0-9\-_]+\\.+[A-Za-z0-9\.\/%&=\?\-_]+$/i';

if (!is_string($value) || !preg_match($regex, $value))
	$value = "";

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

protected $type = "file";

protected $disp_opt = array
(
	//"mime_type"=> "", // On doit pouvoir forcer la visualisation dans un autre format...
	"protocol" => "http",
);

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

protected $type = "stream";

}

/**
 * Image/Picture
 *
 */
class data_image extends data_file
{

protected $type = "image";

static protected $format_list = array( "jpg"=>"image/jpeg" , "png"=>"image/png" , "gif"=>"image/gif" );

protected $format = "jpg";
protected $quality = 90;

/*
 * A TRAVAILLER... PAS EVIDENT
 * On doit pouvoir forcer un format en entr�e, convertir en un format donn� au besoin.
 */

protected $disp_opt = array
(
	"width" => 0,
	"height" => 0,
);

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

protected $type = "audio";

static protected $format_list = array( "mp3"=>"audio/mpeg-1" , "wav"=>"audio/wave" , "wma"=>"audio/wma" , "ogg"=>"audio/ogg" , "ogg"=>"image_gif" );

protected $format = "mp3";

// Display options
protected $disp_opt = array
(
	"autostart" => false,
);

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

protected $type = "video";

static protected $format_list = array( "wmv"=>"video/wmv" , "avi"=>"video/avi" , "ogg"=>"video/ogg" , "flv"=>"video/flv" , "mp4"=>"video/mpeg-4" , "mov"=>"video/quicktime" );

protected $format = "flv";

// Display options
protected $disp_opt = array
(
	"autostart" => false,
);

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

protected $type = "object";

protected $structure_opt = array
(
	"object" => array("type" => "objecttype"),
);

protected $db_opt = array
(
	"tablename" => "",
	"fieldname" => "",
	"type" => "blob",
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

protected $type = "agregat";

protected $structure_opt = array
(
	"datamodel" => "datamodel_name",
);

protected $db_opt = array
(
	"tablename" => "",
	"fieldname" => "",
	"type" => "blob",
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

protected $type = "dataobject";

protected $structure_opt = array
(
	"databank" => 0,
);

protected $db_opt = array
(
	"type" => "integer",
);

protected $disp_opt = array
(
	"label" => "",
	"ref_field_disp" => "", // field to display if needed
);

function __construct($name, $value, $label="Name", $databank=0, $options=array())
{

data::__construct($name, $value, $label, array_merge(array("databank"=>$databank), $options));

}

function __tostring()
{

//print_r($this->disp_opt);
//echo (string)$this->value->{$this->disp_opt["ref_disp_field"]};

if (is_a($object=datamodel($this->structure_opt["databank"], $this->value), "data_bank_agregat"))
{
	if (isset($this->disp_opt["ref_field_disp"]) && ($fieldname=$this->disp_opt["ref_field_disp"]) && isset(datamodel($this->structure_opt["databank"])->{$fieldname}))
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
	return datamodel($this->structure_opt["databank"])->get($this->value);
else
	return null;

}

public function verify(&$value, $convert=false, $options=array())
{

if (!is_numeric($value) || !datamodel($this->structure_opt["databank"])->exists($value))
{
	if ($convert)
		$value = null;
	return false;
}

return true;

}

function convert(&$value)
{

if (!is_numeric($value) || !datamodel($this->structure_opt["databank"])->exists($value))
	$value = null;

}

function form_field_disp($print=true, $option=array())
{

// Pas beaucoup de valeurs : liste simple
if (($databank=datamodel($this->structure_opt["databank"])) && (($nb=$databank->count()) <= 50))
{
	if (isset($option["order"]))
		$query = $databank->query(array(), array(), $option["order"]);
	else
		$query = $databank->query();

	$return = "<select name=\"$this->name\" title=\"$this->label\" class=\"".get_called_class()."\">\n";
	$return .= "<option value=\"\"></option>";
	foreach($query as $object)
	{
		if ($this->value == $object->id->value)
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
		$value = (string)datamodel($this->structure_opt["databank"], $this->value, true);
	else
		$value = "";
	$return .= "<input class=\"q_str\" value=\"$value\" onkeyup=\"object_list_query(".$this->structure_opt["databank"].", [{'type':'like','value':this.value}], $(this).parent().get(0));\" onblur=\"object_list_hide($(this).parent().get(0))\" onfocus=\"this.select();if(this.value) object_list_query(".$this->structure_opt["databank"].", [{'type':'like','value':this.value}], $(this).parent().get(0));\" />";
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

$databank = $this->structure_opt("databank");
$query = $databank()->query();
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

return array( "type" => "integer", "size" => 10, "signed"=>false );

}

}

/**
 * Donnée de type dataobject avec choix du type de dataobject en param
 */
class data_dataobject_select extends data_agregat
{

protected $type = "dataobject_select";

protected $structure_opt = array
(
	"databank_select" => array(), // liste des databank concern�es
);

protected $db_opt = array
(
	"type" => "integer",
	"databank_field" => "",
	"field" => ""
);

function __construct($name, $value, $label="Name", $databank_list, $options=array())
{

data::__construct($name, $value, $label, array_merge(array("databank_select"=>$databank_list), $options));

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
elseif (!in_array(($databank=$list[0]),$this->structure_opt["databank_select"]))
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
if (is_array($value) && isset($value[0]) && isset($value[1]) && in_array(($databank=$value[0]),$this->structure_opt["databank_select"]) && ($object = datamodel($databank,$value[1])))
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

protected $type = "dataobject_list";

protected $structure_opt = array
(
	"databank" => "databank_name",
);

protected $db_opt = array
(
	"ref_field" => "", // id du dataobject à récupérer
	"ref_table" => "", // table de liaison
	"ref_id" => "", // champ de liaison
	"order_field" => "", // champ qui gère ordre (optinonel)
);

protected $disp_opt = array
(
	"ref_field_disp" => "", // field to display if needed
);

function __construct($name, $value, $label="Name", $databank=null, $options=array())
{

data::__construct($name, $value, $label, array_merge(array("databank"=>$databank), $options));

}

function __tostring()
{

if ($this->db_opt["order_field"])
	$order = array($this->db_opt["order_field"]=>"asc");
else
	$order = array();

if (!is_array($this->value) || !count($this->value))
{
	return "";
}
elseif ($this->disp_opt["ref_field_disp"])
{
	$query = datamodel($this->structure_opt["databank"])->query(array(array("name"=>"id", "value"=>$this->value)), true, $order);
	$return = array();
	foreach($query as $object)
	{
		$return[] = $object->{$this->disp_opt["ref_field_disp"]};
	}
	return implode(", ", $return);
}
else
{
	return implode(", ", datamodel($this->structure_opt["databank"])->query(array(array("name"=>"id", "value"=>$this->value)), true, $order));
}

}

/**
 * Returns objets in a list
 */
function object_list()
{

if ($this->db_opt["order_field"])
	$order = array($this->db_opt["order_field"]=>"asc");
else
	$order = array();

if (is_array($this->value) && count($this->value))
{
	// Retrieve objects in databank
	datamodel($this->structure_opt["databank"])->query(array(array("name"=>"id", "value"=>$this->value)), true);
	// Sort by order
	$return = array();
	foreach ($this->value as $nb=>$id)
		$return[] = datamodel($this->structure_opt["databank"])->get($id);
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
	if (!datamodel($this->structure_opt["databank"])->exists($id))
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
	if (!datamodel($this->structure_opt["databank"])->exists($id))
		unset($value[$n]);
}

}

function form_field_disp($print=true)
{

if ($this->db_opt["order_field"])
	$order = array($this->db_opt["order_field"]=>"asc");
else
	$order = array();

// Pas beaucoup de valeurs : liste simple
if (($nb=datamodel($this->structure_opt["databank"])->db_count()) < 20)
{
	$query = datamodel($this->structure_opt["databank"])->query();
	if ($nb<10)
		$size = $nb;
	else
		$size = 5;
	$return = "<input name=\"$this->name\" type=\"hidden\" />";
	$return .= "<select name=\"".$this->name."[]\" title=\"$this->label\" multiple size=\"$size\" class=\"".get_called_class()."\">\n";
	if (is_array($this->value)) foreach ($this->value as $id)
			$return .= "<option value=\"$id\" selected>".datamodel($this->structure_opt["databank"])->get($id)."</option>";
	foreach($query as $object)
	{
		if (!is_array($this->value) || !in_array($object->id->value, $this->value))
			$return .= "<option value=\"$object->id\">$object</option>";
	}
	$return .= "</select>\n";
}
// Beaucoup de valeurs : liste Ajax complexe
else
{
	$return = "<div style=\"display:inline;\">";
	$return .= "<input name=\"$this->name\" type=\"hidden\" />";
	$return .= "<div><select name=\"".$this->name."[]\" title=\"$this->label\" multiple class=\"".get_called_class()." q_id\">";
	if (is_array($this->value) && count($this->value))
	{
		datamodel($this->structure_opt["databank"])->query(array(array("name"=>"id", "value"=>$this->value)));
		foreach ($this->value as $id)
			$return .= "<option value=\"$id\" selected>".datamodel($this->structure_opt["databank"])->get($id)."</option>";
	}
	$return .= "</select></div>";
	$return .= "<input class=\"q_str\" onkeyup=\"object_list_query(".$this->structure_opt["databank"].", [{'type':'like','value':this.value}], $(this).parent().get(0));\" onblur=\"object_list_hide($(this).parent().get(0))\" onfocus=\"this.select();if(this.value) object_list_query(".$this->structure_opt["databank"].", [{'type':'like','value':this.value}], $(this).parent().get(0));\" />";
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

$fieldname = $this->db_opt["ref_id"];

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

if ($this->db_opt["ref_table"])
{
	$return = array
	(
		"name" => $this->db_opt["ref_table"],
		"options" => array(),
	);
	if ($this->db_opt["order_field"])
		$return["fields"] = array
		(
			$this->db_opt["ref_id"] => array("type"=>"integer", "size"=>10, "signed"=>false, "null"=>false, "key"=>true),
			$this->db_opt["order_field"] = array("type"=>"integer", "size"=>3, "signed"=>false, "null"=>false, "key"=>true),
			$this->db_opt["ref_field"] => array("type"=>"integer", "size"=>10, "signed"=>false, "null"=>false, "key"=>false)
		);
	else
		$return["fields"] = array
		(
			$this->db_opt["ref_id"] => array("type"=>"integer", "size"=>10, "signed"=>false, "null"=>false, "key"=>true),
			$this->db_opt["ref_field"] => array("type"=>"integer", "size"=>10, "signed"=>false, "null"=>false, "key"=>true)
		);
}

return $return;

}

}


/**
 * Data types access function
 */
function data($id=0)
{

if (!isset($GLOBALS["data_gestion"]))
{
	// APC
	if (APC_CACHE)
	{
		if (!($GLOBALS["data_gestion"]=apc_fetch("datatype_gestion")))
		{
			$GLOBALS["data_gestion"] = new data_gestion();
			apc_store("datatype_gestion", $GLOBALS["data_gestion"], APC_CACHE_GESTION_TTL);
		}
	}
	// Session
	else
	{
		if (!isset($_SESSION["data_gestion"]))
			$_SESSION["data_gestion"] = new data_gestion();
		$GLOBALS["data_gestion"] = $_SESSION["data_gestion"];
	}
}

if ($id)
	return $GLOBALS["data_gestion"]->get($id);
else
	return $GLOBALS["data_gestion"];

}

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
