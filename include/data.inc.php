<?

/**
  * $Id: agregat.inc.php 40 2008-10-01 07:37:20Z mathieu $
  * 
  * Copyright 2008 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  * location : /include : global include folder
  * 
  * Types de données & conteneurs
  * 
  * Agrégats de données de base pour les databank, les formulaires,
  * les méthodes de mise en page, la partie CMS, etc. 
  * 
  */

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [begin]");

/**
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
 * 
 * Liste des types data de base :
 * - string
 * - integer
 * - float
 * - date
 * - time
 * - datetime
 * - list
 * - fromlist
 * 
 * Liste des types data enrichis :
 * - number : integer
 * - measure : float
 * - id : number
 * - name : string
 * - richtext (html) : string
 * 
 * Liste des types médias :
 * - file
 * - stream
 * - audio
 * - video
 * 
 * Liste des types complexes :
 * - object
 * - agregat
 * - dataobject
 * - dataobject_list
 * 
 */

/**
 * "Abstract" base datatype
 * 
 * This is the main datatype, from which we will create the others :
 * - String (with maxlength)
 * - Rich text (HTML)
 * - Numbers : Integer, Float, etc.
 * - Date/Time
 * - Lists (width single or multiple selection)
 * - Tables (complex arrays)
 * - Binary and executables
 * - Word, Excel, Openoffice, etc. files
 * - Pictures, Photos, etc.
 * - Videos
 * - Sounds
 * - etc.
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
 * Nom à utiliser comme identifiant (unique dans un contexte car sert de référence les agrégats)
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
 * Type de donnée (audio, texte, video, file, etc.)
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

/**
 * Options de structure et par extension de Verification & Conversion (Voir les classes de conversion associées)
 *
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
public static $disp_opt_list = array("mime_type", "ref_field_disp");

/**
 * Précisions pour les formulaires, tout sera généré via la classe form (à voir)
 *
 * @var array
 */
protected $form_opt = array();
public static $form_opt_list = array("type", "tabindex", "accesskey", "size", "cols", "rows", "width", "height");

/**
 * Constructor
 *
 * @param string $name
 * @param mixed $value
 * @param array $options
 */
public function __construct($name, $value, $label, $structure_opt=array(), $db_opt=array(), $disp_opt=array(), $form_opt=array())
{

$this->name = $name;
$this->label = $label;

if (is_array($structure_opt))
	foreach ($structure_opt as $i=>$j)
		$this->structure_opt_set($i,$j);
if (is_array($db_opt))
	foreach ($db_opt as $i=>$j)
		$this->db_opt_set($i,$j);
if (is_array($disp_opt))
	foreach ($disp_opt as $i=>$j)
		$this->disp_opt_set($i,$j);
if (is_array($form_opt))
	foreach ($form_opt as $i=>$j)
		$this->form_opt_set($i,$j);

// Par défaut on force la valeur à l'initialisation
$this->value_set($value, true);

}

/**
 * Read access to data
 */
public function __get($name)
{

if (in_array($name, array("name", "type", "label", "value", "structure_opt", "db_opt", "disp_opt", "form_opt")))
	return $this->{$name};
elseif ($name == "datamodel")
	return datamodel($this->datamodel_id);
else
	return NULL;

}

public function datamodel_set($datamodel_id=0)
{

// TODO : Cannot verify the consistence of the datamodel, the fields are constructed at the same time... 
$this->datamodel_id = $datamodel_id;

}
public function datamodel()
{

return datamodel($this->datamodel_id);

}

/**
 * Set structure options.
 *
 * @param string $name
 * @param mixed $value
 */
public function structure_opt_set($name, $value)
{

// TODO : verify the type of the value given in each case, using data verify
if (in_array($name, self::$structure_opt_list))
{
	//echo "<br />DATA field $this->name, DATAMODEL #ID$this->datamodel_id, structure_opt : #$name";
	//print_r($value);
	$this->structure_opt[$name] = $value;
	return true;
}
else
	return false;

}

/**
 * View structure_opt
 * 
 * To store the value in the database if needed.
 * 
 * @param string $name
 * @param mixed $value
 */
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

/**
 * Set database options.
 * 
 * To store the value in the database if needed.
 * 
 * @param string $name
 * @param mixed $value
 */
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

/**
 * Set database options.
 * 
 * To store the value in the database if needed.
 * 
 * @param string $name
 * @param mixed $value
 */
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


/**
 * Set display options.
 *
 * @param string $name
 * @param mixed $value
 */
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

/**
 * Get display options.
 *
 * @param string $name
 * @return mixed $value
 */
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


/**
 * Set form options.
 *
 * @param string $name
 * @param mixed $value
 */
public function form_opt_set($name, $value)
{

if (in_array($name,self::$form_opt_list))
{
	$this->form_opt[$name] = $value;
	return true;
}
else
	return false;

}

/**
 * Get form options.
 *
 * @param string $name
 * @return mixed $value
 */
public function form_opt($name)
{

if (isset($this->form_opt[$name]))
{
	return $this->form_opt[$name];
}

}
public function form_opt_list_get()
{

return $this->form_opt;

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

/**
 * Return value
 *
 * @return string
 */
public function __tostring()
{

return (string)$this->value;

}

function disp_url()
{
	
return $this->__tostring();

}

/**
 * Return true if the value is empty (or not set)
 */
public function null()
{

if ($this->value === null)
	return false;
else
	return true;

}

public function nonempty()
{

if ($this->value)
	return true;
else
	return false;

}

/**
 * Update the value and force conversion if needed
 * 
 * @param mixed value
 * @param boolean force
 * @return boolean
 */
public function value_set($value, $force=false)
{

//echo "<p>$this->name : $value / ".$this->verify($value)."</p>\n";

// Verify and update
if ($this->verify($value))
{
	//echo "<p>Mise à jour OK pour valeur : $value</p>";
	$this->value = $value;
	return true;
}
// Convert
elseif ($force)
{
	$this->value = $this->convert($value, $this->structure_opt);
	return true;
}
// Verification failure
else
{
	return false;
}

}

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
	$this->value_set($value, true);
elseif ($name == "datamodel" && is_a($value, "datamodel"))
	$this->datamodel_id = $value->id();
	
}

/**
 * Verify a potential value
 * 
 * @param mixed verify
 * @return boolean
 */
public function verify($value)
{

if ($value !== null || $this->required==true)
{
	$return = true;
	//echo "<br/>Verifying step 1 ok";
	foreach($this->structure_opt as $name=>$parameters)
	{
		//echo $name."<br />\n";
		if ($return == true)
		{
			$name = "data_verify_$name";
			//print_r($parameters);
			if ($name::verify($value, $parameters) == false)
			{
				//echo "<br />Verify : ".$name::convert($value, $parameters);
				$return = false;
			}
		}
	}
	return $return;
}
else
{
	return false;
}

}

/**
 * Convert a value with the structure options
 * 
 * @param mixed value
 * @return mixed
 */
public function convert($value, $options=array())
{

foreach($this->structure_opt as $name=>$parameters)
{
	$name = "data_verify_$name";
	// Les classes de conversion seront � modifier en statique d�s que je passe en PHP 5.3
	$conversion = new $name();
	// Debug :
	//echo "<br/>$name : $parameters";
	$value = $conversion->convert($value, $parameters);
}

return $value;

}

/**
 * Return the associated form field
 *
 * @param array $options
 * @return unknown
 */
public function form_field($options=array())
{

$form_field = "form_field_".$this->form_opt["type"];
return new $form_field($this->name, $this->value, array_merge($this->disp_opt, $this->form_opt, $options));

}

public function form_field_disp($options=array(), $print=true)
{

if ($print)
	print "";
else
	return "";

}

public function form_field_select_disp($options=array(), $print=true)
{

if ($print)
	print "";
else
	return "";

}

/**
 * Convert the value in database format
 * A MODIFIER !!!
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
 * @return unknown_type
 */
public function db_query_param($value, $type="=")
{

$type_list = array( "=", "LIKE", "<", ">", "<=", ">=", "NOT LIKE" );  
if (!in_array($type, $type_list))
	$type = "=";

if (!isset($this->db_opt["field"]) || !($fieldname = $this->db_opt["field"]))
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
 * Convert the value from an HTML form format
 *
 * @param unknown_type $value
 */
public function value_from_form($value)
{

$this->value_set($value, true);

}


/**
 * Convert the value in HTML form format
 *
 * @param unknown_type $value
 * @return unknown
 */
public function value_to_form()
{

return $this->value;

}

}

/* BASE DATA TYPES */

/**
 * String
 * 
 * With a maxlength
 * Associated to an input/text form, and a varchar db field
 *
 */
class data_string extends data
{

protected static $id = 1;

protected $type = "string";

protected $structure_opt = array
(
	"size" => 256
);

protected $disp_opt = array
(
	"mime_type" => "text/plain"
);

// Form specific properties
protected $form_opt = array
(
	"type" => "text",
	"size" => 32
);

public function form_field_disp($print=true, $options=array())
{

$attrib_size = ( isset($this->form_opt["size"]) && $this->form_opt["size"] > 0 )
	? " size=\"".$this->form_opt["size"]."\""
	: "";
$attrib_maxlength = ( isset($this->structure_opt["size"]) && $this->structure_opt["size"] > 0 )
	? " maxlength=\"".$this->structure_opt["size"]."\""
	: "";
$attrib_readonly = ( isset($this->form_opt["readonly"]) && $this->form_opt["readonly"] == true )
	? " readonly"
	: "";

$return = "<input type=\"".$this->form_opt["type"]."\" name=\"$this->name\" value=\"$this->value\"$attrib_size$attrib_maxlength$attrib_readonly />";

if ($print)
	print $return;
else
	return $return;

}

public function form_field_disp_update($print=true, $options=array())
{

$attrib_size = ( isset($this->form_opt["size"]) && $this->form_opt["size"] > 0 )
	? " size=\"".$this->form_opt["size"]."\""
	: "";
$attrib_maxlength = ( isset($this->structure_opt["size"]) && $this->structure_opt["size"] > 0 )
	? " maxlength=\"".$this->structure_opt["size"]."\""
	: "";
$attrib_readonly = ( isset($this->form_opt["readonly"]) && $this->form_opt["readonly"] == true )
	? " readonly"
	: "";

$return = "<input type=\"".$this->form_opt["type"]."\" id=\"$this->name\" onchange=\"javascript:this.name = this.id;\" value=\"$this->value\"$attrib_size$attrib_maxlength$attrib_readonly />";

if ($print)
	print $return;
else
	return $return;

}

public function db_field_create()
{

return array( "type" => "string", "size" => $this->structure_opt["size"] );

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

protected static $id = 2;

protected $type = "password";

protected $structure_opt = array
(
	"size" => 64,
	"enctype" => "md5",
);

protected $form_opt = array
(
	"type" => "password",
	"size" => 12,
);

/* penser à la conversion en md5=>voir comment modifier par la suite
 * le mieux est peut-être de stocker directement )à l'insertion en md5
 * pour pouvoir plus aisément comparer... a voir !!
 */

}

/**
 * Integer (numeric)
 *
 */
class data_integer extends data_string
{

protected static $id = 3;

// TODO : static
protected $type = "integer";

protected $structure_opt = array
(
	"integer" => array( "signed" => true , "size" => 11 ),
);

protected $form_opt = array
(
	"type" => "text",
	"size" => 6,
);

public function form_field_disp($print=true, $options=array())
{

// ($this->structure_opt["integer"]["size"]+1) en prenant en compte "-"

$attrib_size = ( isset($this->form_opt["size"]) && $this->form_opt["size"] <= $this->structure_opt["integer"]["size"] )
	? " size=\"".$this->form_opt["size"]."\""
	: " size=\"".($this->structure_opt["integer"]["size"]+1)."\"";
$attrib_maxlength = " maxlength=\"".($this->structure_opt["integer"]["size"]+1)."\"";
$attrib_readonly = ( isset($this->form_opt["readonly"]) && $this->form_opt["readonly"] == true )
	? " readonly"
	: "";

$return = "<input type=\"".$this->form_opt["type"]."\" name=\"$this->name\" value=\"$this->value\"$attrib_size$attrib_maxlength$attrib_readonly />";

if ($print)
	print $return;
else
	return $return;

}

public function db_field_create()
{

print_r($this->structure_opt);

$return = array
(
	"type" => "integer",
	"size" => $this->structure_opt["integer"]["size"],
	
);
if ($this->structure_opt["integer"]["signed"])
{
	$return["signed"]=true;
}

return $return;

}

}

/**
 * Float (numeric)
 *
 */
class data_float extends data_string
{

protected static $id = 4;

protected $type = "float";

protected $structure_opt = array
(
	"float" => array( "signed" => true , "size" => 11 , "precision" => 2 ),
);

public function form_field_disp($print=true, $options=array())
{

// ($this->structure_opt["float"]["size"]+2) en prenant en compte "-" et "."

$attrib_size = ( isset($this->form_opt["size"]) && $this->form_opt["size"] <= $this->structure_opt["float"]["size"]+1 )
	? " size=\"".$this->form_opt["size"]."\""
	: " size=\"".($this->structure_opt["float"]["size"]+2)."\"";
$attrib_maxlength = " maxlength=\"".($this->structure_opt["float"]["size"]+2)."\"";
$attrib_readonly = ( isset($this->form_opt["readonly"]) && $this->form_opt["readonly"] == true )
	? " readonly"
	: "";

$return = "<input type=\"".$this->form_opt["type"]."\" name=\"$this->name\" value=\"$this->value\"$attrib_size$attrib_maxlength$attrib_readonly />";

if ($print)
	print $return;
else
	return $return;

}

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

protected static $id = 5;

protected $type = "text";

protected $structure_opt = array ();

protected $form_opt = array
(
	"type" => "textarea",
	"width" => "400px",
	"height" => "50px",
);

public function form_field_disp($print=true, $options=array())
{

$width = ( isset($this->form_opt["width"]) && $this->form_opt["width"] > 0 ) ? "width:".$this->form_opt["width"].";" : "";
$height = ( isset($this->form_opt["height"]) && $this->form_opt["height"] > 0 ) ? "height:".$this->form_opt["height"].";" : "";
$attrib_maxlength = ( isset($this->structure_opt["size"]) && $this->structure_opt["size"] > 0 ) ? " maxlength=\"".$this->structure_opt["size"]."\"" : "";

if ($width || $height)
	$style = " style=\"$width$height\"";
else
	$style = "";

$return = "<textarea name=\"$this->name\"$style$attrib_maxlength>$this->value</textarea>";

if ($print)
	print $return;
else
	return $return;

}

public function form_field_disp_update($print=true, $options=array())
{

$width = ( isset($this->form_opt["width"]) && $this->form_opt["width"] > 0 ) ? "width:".$this->form_opt["width"].";" : "";
$height = ( isset($this->form_opt["height"]) && $this->form_opt["height"] > 0 ) ? "height:".$this->form_opt["height"].";" : "";
$attrib_maxlength = ( isset($this->structure_opt["size"]) && $this->structure_opt["size"] > 0 ) ? " maxlength=\"".$this->structure_opt["size"]."\"" : "";

if ($width || $height)
	$style = " style=\"$width$height\"";
else
	$style = "";

$return = "<textarea id=\"$this->name\" onchange=\"javascript:this.name = this.id;\"$style$attrib_maxlength>$this->value</textarea>";

if ($print)
	print $return;
else
	return $return;

}

public function db_field_create()
{

return array( "type" => "string" );

}

}

/**
 * Rich Text (HTML)
 * 
 * Can limit the use of some tags.
 * 
 */
class data_richtext extends data_text
{

protected static $id = 6;

protected $type = "richtext";
protected $mime_type = "text/html";

protected $structure_opt = array
(
	"string_tag_authorized" => array ( "b" , "i" , "u" , "font" , "strong" , "a" , "p" ),
);

protected $form_opt = array
(
	"type" => "textarea",
	"width" => 400,
	"height" => 300,
);

public function form_field_disp($print=true, $options=array())
{

$oFCKeditor = new FCKeditor("$this->name");
$oFCKeditor->Value = "$this->value";
$oFCKeditor->ToolbarSet = 'Basic';
$oFCKeditor->Width = $this->form_opt["width"];
$oFCKeditor->Height = $this->form_opt["height"];
$return = $oFCKeditor->CreateHtml() ;

if ($print)
	print $return;
else
	return $return;

}

public function db_field_create()
{

return array( "type" => "richtext" );

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

protected static $id = 7;

protected $type = "select";

protected $structure_opt = array
(
	"select" => array(),
);

protected $form_opt = array
(
	"type" => "select",
);

public function form_field($options=array())
{

$form_field = "form_field_".$this->form_opt["type"];
return new $form_field($this->name, $this->value, array_merge($this->form_opt,$this->disp_opt,array("value_list"=>$this->structure_opt["select"])));

}

public function form_field_disp($print=true, $options=array())
{

$return = "<select name=\"$this->name\">";
foreach ($this->structure_opt["select"] as $i=>$j)
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

public function form_field_disp_update($print=true, $options=array())
{

$return = "<select id=\"$this->name\" onchange=\"javascript:this.name = this.id;\">";
foreach ($this->structure_opt["select"] as $i=>$j)
	if ($this->value == $i)
		$return .= "<option value=\"$i\" selected=\"selected\">$j</option>";
	else
		$return .= "<option value=\"$i\">$j</option>";
$return .= "</select>";

if ($print)
	print $return;
else
	return $return;

}

public function form_field_select_disp($print=true, $options=array())
{

$return = "<select id=\"params[$this->name]\" name=\"params[$this->name]\">";
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

if (isset($this->structure_opt["select"][$this->value]))
	return $this->structure_opt["select"][$this->value];
else
	return "<i>undefined</i>";

}

public function db_field_create()
{

$value_list = array();
foreach($this->structure_opt["select"] as $name=>$label)
	$value_list[] = $name;
return array("type" => "select", "value_list" => $value_list);

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

protected static $id = 8;

protected $type = "date";

protected $structure_opt = array
(
	"date" => "%A %d %B %G", // Defined for strftime()
	"size" => 10,
);

protected $db_opt = array
(
	"tablename" => "",
	"fieldname" => "",
	"type" => "date",
);

protected $form_opt = array
(
	"type" => "text",
	"size" => 10,
);

public function __tostring()
{

if ($this->value && $this->value != "00/00/0000")
	return strftime($this->structure_opt["date"], strtotime($this->value));
else
	return "<i>undefined</i>";

}

public function nonempty()
{

if ($this->value && $this->value != '00/00/0000')
	return true;
else
	return false;

}

function view($style="%A %d %B %G")
{

if ($this->value && $this->value != "00/00/0000")
	return strftime($style, strtotime($this->value));
else
	return "<i>undefined</i>";

}

public function form_field_disp($print=true, $options=array())
{

$attrib_size = ( isset($this->form_opt["size"]) && $this->form_opt["size"] > 0 ) ? " size=\"".$this->form_opt["size"]."\"" : "";
$attrib_maxlength = ( isset($this->structure_opt["size"]) && $this->structure_opt["size"] > 0 ) ? " maxlength=\"".$this->structure_opt["size"]."\"" : "";
$attrib_readonly = ( isset($this->form_opt["readonly"]) && $this->form_opt["readonly"] == true ) ? " readonly" : "";

$return = "<input type=\"".$this->form_opt["type"]."\" name=\"$this->name\" class=\"date_input\" value=\"$this->value\"$attrib_size$attrib_maxlength$attrib_readonly />";

if ($print)
	print $return;
else
	return $return;

}

public function form_field_disp_update($print=true, $options=array())
{

$attrib_size = ( isset($this->form_opt["size"]) && $this->form_opt["size"] > 0 ) ? " size=\"".$this->form_opt["size"]."\"" : "";
$attrib_maxlength = ( isset($this->structure_opt["size"]) && $this->structure_opt["size"] > 0 ) ? " maxlength=\"".$this->structure_opt["size"]."\"" : "";
$attrib_readonly = ( isset($this->form_opt["readonly"]) && $this->form_opt["readonly"] == true ) ? " readonly" : "";

$return = "<input type=\"".$this->form_opt["type"]."\" id=\"$this->name\" onchange=\"javascript:this.name = this.id;\" class=\"date_input\" value=\"$this->value\"$attrib_size$attrib_maxlength$attrib_readonly />";

if ($print)
	print $return;
else
	return $return;

}

function value_to_db()
{

if ($this->value)
{
	return implode("-",array_reverse(explode("/",$this->value)));
}
else
{
	return "0000-00-00";
}

}

function value_from_db($value)
{

if ($value !== null)
{
	$this->value = implode("-",array_reverse(explode("/",$value)));
}
else
	return null;

}

public function db_field_create()
{

return array( "type" => "date" );

}

/**
 * Returns the timestamp calculated from the stored value
 */
public function timestamp()
{

return strtotime(implode("-",array_reverse(explode("/",$this->value))));

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

protected static $id = 9;

protected $type = "year";

protected $structure_opt = array
(
	"year" => "0000", // A CORRIGER
);

protected $db_opt = array
(
	"type" => "year",
);

public function value_from_form($value)
{

$this->value = $value;

}

public function __tostring()
{

if ($this->value && $this->value != "0000")
	return $this->value;
else
	return "";

}

public function form_field_disp($print=true, $options=array())
{

$attrib_readonly = ( isset($this->form_opt["readonly"]) && $this->form_opt["readonly"] == true ) ? " readonly" : "";

$return = "<input type=\"".$this->form_opt["type"]."\" name=\"$this->name\" value=\"$this->value\" size=\"4\" maxlength=\"4\"$attrib_readonly />";

if ($print)
	print $return;
else
	return $return;

}

public function form_field_disp_update($print=true, $options=array())
{

$attrib_readonly = ( isset($this->form_opt["readonly"]) && $this->form_opt["readonly"] == true ) ? " readonly" : "";

$return = "<input type=\"".$this->form_opt["type"]."\" id=\"$this->name\" onchange=\"javascript:this.name = this.id;\" value=\"$this->value\" size=\"4\" maxlength=\"4\"$attrib_readonly />";

if ($print)
	print $return;
else
	return $return;

}

public function db_field_create()
{

return array( "type" => "year" );

}

}

/**
 * Time
 * 
 */
class data_time extends data_string
{

protected static $id = 10;

protected $type = "time";

protected $structure_opt = array
(
	"time" => "", // A CORRIGER
	"size" => 8,
);

protected $db_opt = array
(
	"tablename" => "",
	"fieldname" => "",
	"type" => "time",
);

public function db_field_create()
{

return array( "type" => "time" );

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
	"datetime" => "%A %d %B %G", // Defined for strftime()
	"size" => 19,
);

protected $db_opt = array
(
	"tablename" => "",
	"fieldname" => "",
	"type" => "datetime",
);

public function __tostring()
{

if ($this->value)
	return strftime($this->structure_opt["datetime"], $this->value);
else
	return "<i>undefined</i>";

}

public function value_from_db($value)
{

return strtotime($value);

}

public function value_to_db()
{

return date("Y-m-d H:i:s", $this->value);

}

public function db_field_create()
{

return array( "type" => "datetime" );

}

}

/**
 * List (array)
 * 
 * Can be indexed or ordered
 * 
 * The content is a set en elements of the same type
 * Use the data_list_mixed to put different datatypes inside
 * 
 * Displaying uses jquery, and data is stored in json in a text DB field
 *
 */
class data_list extends data
{

protected $type = "list";

protected $value = array();

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

protected $disp_opt = array
(
	"mime_type" => "text/csv",
	"sort" => "key",
);

public function __tostring()
{

//return (string)implode(" , ",$this->value);
return (string)count($this->value);

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

public function value_to_form()
{

print_r($this->value); 

return $this->value;

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
protected $value = array();

protected $structure_opt = array
(
	"fromlist" => array(),
);

protected $form_opt = array
(
	"type" => "select",
	"multiple" => "true",
);

public function __tostring()
{

return implode(",",$this->value);

}

public function form_field_disp($print=true, $options=array())
{

$return = "<select name=\"".$this->name."[]\" multiple=\"true\">";
foreach ($this->structure_opt["fromlist"] as $i=>$j)
	if (in_array($i,$this->value))
		$return .= "<option value=\"$i\" selected=\"selected\">$j</option>";
	else
		$return .= "<option value=\"$i\">$j</option>";
$return .= "</select>";

if ($print)
	echo $return;
else
	return $return;

}

public function form_field_disp_update($print=true, $options=array())
{

$return = "<select id=\"$this->name\" onchange=\"javascript:this.name = this.id+'[]';\" multiple=\"true\">";
foreach ($this->structure_opt["fromlist"] as $i=>$j)
	if (in_array($i,$this->value))
		$return .= "<option value=\"$i\" selected=\"selected\">$j</option>";
	else
		$return .= "<option value=\"$i\">$j</option>";
$return .= "</select>";

if ($print)
	echo $return;
else
	return $return;

}

public function value_from_db($value)
{

$this->value = explode(",",$value);

}

public function value_to_db()
{

if (is_array($this->value))
	return implode(",",$this->value);
else
	return null;

}

public function db_field_create()
{

return array( "type" => "fromlist" , "value_list" => $this->structure_opt["fromlist"] );

}

}

/**
 * Donnée de type tableau (2 dimensions sinon on va pas s'en sortir hein !)
 * 
 * On commence à s'amuser... pour l'instant le contenu est simplement du texte
 *
 */
class data_table extends data
{

// Type
protected $type = "table";

protected $value = array();

protected $structure_opt = array
(
	"array" => array ( 0, 0 )
);

protected $disp_opt = array
(
	"mime_type" => "text/csv",
	"colnames" => true,
	"colaltbg" => true,
	"border" => 1,
	"cellspacing" => 1,
	"cellpadding" => 1,
);

protected $debug = array();

protected function structure_set($name, $value)
{

if ($name == "array")
{
	if (is_array($value) && count($value) == 2 && isset($value[0]) && isset($value[1]) && is_numeric($value[0]) && is_numeric($value[1]) && $value[0] >= 0 && $value[1] >= 0)
	{
		$this->structure_opt["array"] = $value;
		return true;
	}
	else
		return false;
}
else
	data::structure_set($name, $value);

}

public function __tostring()
{

return $this->disp();

}

public function convert($value, $options)
{

return $value;

}

public function verify($value)
{

// Initialisation de la liste des erreurs
$this->debug = array();
// initialisation du tableau de sortie proprement index�
$return = array();
// Il reste � traiter les clefs... pfff ! Voir si on peut simplifier
if (is_array($value))
{
	foreach($value as $n => $row)
		if (is_array($row))
		{
			if ($this->cols && (($m=count($row)) != $this->cols))
				$this->debug[] = "row n�$n : got $m cols, should have $this->cols";
			else
				$return[] = $row;
		}
		else
			$this->debug[] = "row n�$n : not an array";
	if (count($this->debug) == 0)
		if (!$this->rows || ($this->rows == ($n=count($value))))
			return true;
		else
		{
			$this->debug[] = "got $n rows, should have $this->rows";
			return false;
		}
	else
		return false;
}
else
{
	$this->debug[] = "not an array";
	return false;
}

}

public function debug()
{

return $this->debug;

}

public function disp($aff=true)
{

$tableattrib = "";
$tableattrib .= " cellpadding=\"$this->cellpadding\"";
$tableattrib .= " cellspacing=\"$this->cellspacing\"";
$tableattrib .= " border=\"$this->border\"";
$return = "<table$tableattrib>";
foreach($this->value as $n => $row)
{
	if ($n == 0 && $this->colnames)
		$return .= "<tr class=\"colnames\">";
	elseif ($n%2 && $this->colaltbg)
		$return .= "<tr class=\"colodd\">";
	else
		$return .= "<tr>";
	foreach($row as $m => $cell)
		$return .= "<td>$cell</td>";
	$return .= "</tr>";
}
$return .= "</table>";

if ($aff)
	echo $return;
else
	return $return;

}

}

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

protected $value = array
(
	"location" => "",
	"name" => "", // Nom sans extension
	"mime_type"=> "", // Pour le chargement, voir si �a peut suffir pour d�finir le filetype
);

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

/* More complex and specific data fields */

/**
 * Number
 * 
 * Used to count objects, with method to help
 * Integer unsigned
 * 
 */
class data_number extends data_integer
{

function __construct($name, $value, $label="Number", $size="10", $db_opt=array(), $disp_opt=array(), $form_opt=array())
{

data_integer::__construct($name, $value, $label, array("integer"=>array("signed"=>false, "size"=>$size), "size"=>$size), $db_opt, $disp_opt, $form_opt);

}

}

/**
 * Percent
 */
class data_percent extends data_float
{

protected $structure_opt = array("percent"=>array());
protected $form_opt = array("size"=>5);

function __construct($name, $value=0, $label="Percent")
{

data::__construct($name, $value, $label, array());

}

function __tostring()
{

return ($this->value*100)." %";

}
function value_to_db()
{

return $this->value*100;

}
function value_from_db($value)
{

$this->value = $value/100;

}
public function form_field_disp($print=true, $options=array())
{

$attrib_readonly = ( isset($this->form_opt["readonly"]) && $this->form_opt["readonly"] == true )
	? " readonly"
	: "";

$return = "<input type=\"text\" name=\"$this->name\" value=\"".($this->value*100)."\" size=\"4\" maxlength=\"5\"$attrib_readonly />";

if ($print)
	print $return;
else
	return $return;

}

public function value_from_form($value)
{

$this->value_set($value/100, true);

}

public function db_field_create()
{

return array("type" => "float", "size" => 5, "precision" => 2);

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

function __construct($name, $value=0, $label="Priority", $size=1)
{

data_integer::__construct($name, $value, $label, array("integer"=>array("signed"=>false, "size"=>$size)), array(), array(), array("size"=>$size));

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
class data_boolean extends data_number
{

protected $structure_opt = array("boolean"=>array("NO","YES"));

function __construct($name, $value=0, $label="Boolean")
{

data::__construct($name, $value, $label);

}

public function db_field_create()
{

return array( "type" => "boolean" );

}

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
public function value_from_form($value)
{

$this->value = ($value) ? true : false;

}
public function value_to_form()
{

if ($this->value === null)
	return null;
else
	return ($this->value) ? "1" : "0";

}

public function form_field_disp($print=true, $options=array())
{

if ($this->value)
	$return = "<input type=\"radio\" name=\"$this->name\" value=\"0\" />&nbsp;".$this->structure_opt["boolean"][0]." <input name=\"$this->name\" type=\"radio\" value=\"1\" checked />&nbsp;".$this->structure_opt["boolean"][1];
else
	$return = "<input type=\"radio\" name=\"$this->name\" value=\"0\" checked />&nbsp;".$this->structure_opt["boolean"][0]." <input name=\"$this->name\" type=\"radio\" value=\"1\" />&nbsp;".$this->structure_opt["boolean"][1];

if ($print)
	print $return;
else
	return $return;

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

function __construct($name, $value, $label="Measure", $precision=4, $db_opt=array(), $disp_opt=array(), $form_opt=array())
{

data_float::__construct($name, $value, $label, array("float"=>array("signed"=>false, "size"=>10, "precision"=>$precision)), $db_opt, $disp_opt, $form_opt);

}

}


/**
 * Amount
 * 
 * Used for money 
 * Float unsigned
 * 
 */
class data_money extends data_float
{

function __construct($name, $value, $label="Measure", $type="", $limit=null)
{

data_float::__construct($name, $value, $label, array("float"=>array("signed"=>false, "size"=>8, "precision"=>2), "size"=>8), array(), array());

}

function __tostring()
{

return $this->value." &euro;";

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
class data_id extends data_number
{

protected $db_opt = array
(
	"auto_increment"=>true,
);

protected $form_opt = array
(
	"type" => "text",
	"size" => 6,
	"readonly" => true,
);
	
function __construct()
{

data_number::__construct("id", 0, "ID", 6, array("auto_increment"=>true));

}

public function form_field_disp($print=true, $options=array())
{

$attrib_size = ( isset($this->form_opt["size"]) && $this->form_opt["size"] > 0 ) ? " size=\"".$this->form_opt["size"]."\"" : "";
$attrib_maxlength = ( isset($this->structure_opt["size"]) && $this->structure_opt["size"] > 0 ) ? " maxlength=\"".$this->structure_opt["size"]."\"" : "";
$attrib_readonly = ( isset($this->form_opt["readonly"]) && $this->form_opt["readonly"] == true ) ? " readonly" : "";

$return = "<input type=\"".$this->form_opt["type"]."\" name=\"$this->name\" value=\"$this->value\"$attrib_size$attrib_maxlength$attrib_readonly />";

if ($print)
	print $return;
else
	return $return;

}

public function db_field_create()
{

$return = array
(
	"type" => "integer",
	"size" => $this->structure_opt["integer"]["size"],
	
);
if (isset($this->db_opt["auto_increment"]))
{
	$return["auto_increment"]=true;
}

return $return;

}

}

/**
 * Name
 *
 * Maxlength fixed to 64
 * Field size fixed to 20
 *
 */
class data_name extends data_string
{

function __construct($name, $value, $label="Name", $db_opt=array(), $disp_opt=array(), $form_opt=array())
{

data_string::__construct($name, $value, $label, array("size"=>64), $db_opt, $disp_opt, $form_opt);

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

function __construct($name, $value, $label="Email", $db_opt=array(), $disp_opt=array(), $form_opt=array())
{

data_string::__construct($name, $value, $label, array("email"=>array("strict"=>false), "size"=>128), $db_opt, $disp_opt, $form_opt);

}

function link()
{
	return "<a href=\"mailto:$this->value\">$this->value</a>";
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

function __construct($name, $value, $label="URL", $db_opt=array(), $disp_opt=array(), $form_opt=array())
{

data_string::__construct($name, $value, $label, array("url"=>array()), $db_opt, $disp_opt, $form_opt);

}

function link($target="_blank")
{
	if ($target)
		return "<a href=\"$this->value\" target=\"$target\">$this->value</a>";
	else
		return "<a href=\"$this->value\">$this->value</a>";
}

}

/**
 * Name field
 *
 * Maxlength fixed to 32
 * Field size fixed to 20
 *
 */
class data_description extends data_text
{

function __construct($name, $value, $label="Description", $size=256, $db_opt=array(), $disp_opt=array(), $form_opt=array())
{

data_text::__construct($name, $value, $label, array("size"=>$size), $db_opt=array(), $disp_opt=array(), $form_opt=array());

}

}

/**
 * Object
 * 
 */
class data_object extends data
{

protected $type = "object";

protected $structure_opt = array
(
	"object" => array ( "type" => "objecttype" ),
);

protected $db_opt = array
(
	"tablename" => "",
	"fieldname" => "",
	"type" => "blob",
);

protected $form_opt = array
(
	"type" => "text",
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
	"databank" => "databank_name",
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

function __construct($name, $value, $label="Name", $databank=0, $db_opt=array(), $disp_opt=array(), $form_opt=array())
{

data::__construct($name, $value, $label, array("databank"=>$databank), $db_opt, $disp_opt, $form_opt=array());

}

function __tostring()
{

//print_r($this->disp_opt);
//echo (string)$this->value->{$this->disp_opt["ref_disp_field"]};

if (is_a($object=databank($this->structure_opt["databank"],$this->value), "data_bank_agregat"))
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

function __get($name)
{

if ($name == "value")
{
	if ($this->value)
		return databank($this->structure_opt["databank"],$this->value);
	else
		return null;
}
else
	return data::__get($name);

}

/**
 * Returns the object
 */
function object()
{

if ($this->value)
		return databank($this->structure_opt["databank"], $this->value);
	else
		return null;

}

function __set($name, $value)
{

if ($name == "value")
{
	if (is_a($value, "data_bank_agregat"))
		$this->value = $value->id->value;
	else
		$this->value = null;
}
else
	return data::__set($name, $value);

}

function disp_url()
{

if (is_a($object=databank($this->structure_opt["databank"],$this->value), "data_bank_agregat"))
	return "<a href=\"http://".SITE_DOMAIN.SITE_BASEPATH."/".$this->structure_opt["databank"]."/".$this->value."/\">".$object."</a>";
else
	return "";

}

function value_from_db($value)
{

// No need to verify, it's from db ^^
if (is_numeric($value))
	$this->value = $value;
else
	$this->value = null;

}

function value_to_db()
{

if (is_a($object=databank($this->structure_opt["databank"],$this->value), "data_bank_agregat"))
	return $this->value;
else
	return null;

}

function value_from_form($value)
{

if (is_numeric($value) && is_a(($object=databank($this->structure_opt["databank"],$value)), "data_bank_agregat"))
	$this->value = $value;
else
	$this->value = null;

}

function form_field_disp($print=true)
{

// Pas beaucoup de valeurs : liste simple
if (($databank=databank($this->structure_opt["databank"])) && (($nb=$databank->count()) <= 20))
{
	$query = $databank->query();
	{
		$return = "<select name=\"$this->name\">\n";
		if (!$this->required)
			$return .= "<option value=\"\"><i>-- Aucune valeur --</i></option>";
		foreach($query as $object)
		{
			if (is_a($o=databank($this->structure_opt["databank"],$this->value), "data_bank_agregat") && ($o->id->value == $object->id->value))
			{
				$return .= "<option value=\"$object->id\" selected=\"selected\">$object</option>";
			}
			else
				$return .= "<option value=\"$object->id\">$object</option>";
		}
		$return .= "</select>\n";
	}
}
// Beaucoup de valeurs : liste Ajax complexe
else
{
	if (is_a(($object=databank($this->structure_opt["databank"],$this->value)), "data_bank_agregat"))
	{
		$return = "<input type=\"hidden\" id=\"$this->name\" value=\"$this->value\" /><input type=\"text\" id=\"".$this->name."_input\" value=\"".$this->__tostring()."\" onkeyup=\"lookup('$this->name', this.value);\" onfocus=\"fill_empty('$this->name');\" onblur=\"fill_old('$this->name');\" /><!--<input type=\"button\" value=\"UPDATE\" onclick=\"update('$this->name')\" />-->";
	}
	else
	{
		$return = "<input type=\"hidden\" id=\"$this->name\" value=\"\" /><input type=\"text\" id=\"".$this->name."_input\" value=\"Undefined\" class=\"undefined\" onkeyup=\"lookup('$this->name', this.value);\" onfocus=\"fill_empty('$this->name');\" onblur=\"fill_old('$this->name');\" />";
	}
	$return .= "<div class=\"suggestionsBox\" id=\"".$this->name."_suggestions\" style=\"display: none;\"><div class=\"suggestionList\" id=\"".$this->name."_autoSuggestionsList\">&nbsp;</div></div>";
	$return .= "<script type=\"text/javascript\">";
	$return .= "fields['$this->name'] = new Array;";
	$return .= "fields['$this->name']['type'] = 'dataobject';";
	$return .= "fields['$this->name']['name_current'] = '".$this->__tostring()."';";
	$return .= "fields['$this->name']['id_current'] = '$id';";
	$return .= "fields['$this->name']['filled'] = true;";
	$return .= "fields['$this->name']['databank'] = '".$this->structure_opt["databank"]."';";
	$k=array();
	if (isset($this->db_opt["select_params"]))
	{
		foreach ($this->db_opt["select_params"] as $i=>$j)
		{
			$k[] = "'$i' : '$j'";
		}
	}
	$return .= "fields['$this->name']['query_params'] = { ".implode(" , ",$k)." };";
	$return .= "</script>\n";
}

if ($print)
	echo $return;
else
	return $return;

}

function form_field_disp_all($print=true)
{

$return = "<div id=\"".$this->name."_list\">\n";
$return .= "<select name=\"".$this->name."\">\n";

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

protected $value = null;

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

function __construct($name, $value, $label="Name", $databank_list, $db_opt=array())
{

data::__construct($name, $value, $label, array("databank_select"=>$databank_list), $db_opt);

}

function __tostring()
{

if (is_a($this->value, "data_bank_agregat"))
	return (string) $this->value;
else
	return "";

}

function disp_url()
{

if (is_a($this->value, "data_bank_agregat"))
	return "<a href=\"http://".SITE_DOMAIN.SITE_BASEPATH."/".$this->value->databank()->name()."/".$this->value->id."/\">".$this->value."</a>";
else
	return "";

}

function value_from_db($value) // ICI on r�cup�re un champ string de la forme "datatype,id"
{

if (!is_string($value) || count($list=explode(",",$value)) != 2)
{
	trigger_error("Data field '$this->name' : Bad value type");
	$this->value = null;
}
elseif (!in_array(($databank=$list[0]),$this->structure_opt["databank_select"]))
{
	trigger_error("Data field '$this->name' : Undefined databank '$databank' in value");
	$this->value = null;
}
elseif(!($object = databank($databank,$list[1])))
{
	trigger_error("Data field '$this->name' : Undefined object in value");
	$this->value = null;
}
else
{
	$this->value = $object;
}

}

function value_to_db()
{

if (is_a($this->value, "data_bank_agregat"))
{
	return array($this->value->datamodel()->name(), $this->value->id);
}
else
{
	return array(null, null);
}

}

function value_from_form($value)
{

//print_r($value);
if (is_array($value) && isset($value[0]) && isset($value[1]) && in_array(($databank=$value[0]),$this->structure_opt["databank_select"]) && ($object = databank($databank,$value[1])))
{
	$this->value = $object;
}
else
{
	$this->value = null;
}

}

function form_field_disp($print=true)
{

if (is_a($this->value, "agregat"))
{
	$databank = $this->value->datamodel()->name();
	$return = "<select id=\"".$this->name."[0]\" onchange=\"databank_change('$this->name',this.value)\">";
	foreach ($this->structure_opt["databank_select"] as $name)
		if ($databank == $name)
			$return .= "<option value=\"$name\" selected>$name</option>";
		else
			$return .= "<option value=\"$name\">$name</option>";
	$return .= "</select>";
	$id = $this->value->id;
	$return .= "<input type=\"hidden\" id=\"".$this->name."[1]\" value=\"$id\" /><input type=\"text\" id=\"".$this->name."_input\" value=\"$this->value\" onkeyup=\"lookup('$this->name', this.value);\" onfocus=\"fill_empty('$this->name');\" onblur=\"fill_old('$this->name');\" /><!--<input type=\"button\" value=\"UPDATE\" onclick=\"update('$this->name')\" />-->";
}
else
{
	$databank = $this->structure_opt["databank_select"][0];
	$return = "<select id=\"".$this->name."[0]\" onchange=\"databank_change('$this->name',this.value)\">";
	foreach ($this->structure_opt["databank_select"] as $name)
			$return .= "<option value=\"$name\">$name</option>";
	$return .= "</select>";
	$id = "";
	$return .= "<input type=\"hidden\" id=\"".$this->name."[1]\" value=\"$id\" /><input type=\"text\" id=\"".$this->name."_input\" value=\"Undefined\" class=\"undefined\" onkeyup=\"lookup('$this->name', this.value);\" onfocus=\"fill_empty('$this->name');\" onblur=\"fill_old('$this->name');\" />";
}
$return .= "<div class=\"suggestionsBox\" id=\"".$this->name."_suggestions\" style=\"display: none;\"><div class=\"suggestionList\" id=\"".$this->name."_autoSuggestionsList\">&nbsp;</div></div>";
$return .= "<script type=\"text/javascript\">";
$return .= "fields['$this->name'] = new Array;";
$return .= "fields['$this->name']['type'] = 'dataobject_select';";
$return .= "fields['$this->name']['name_current'] = '$this->value';";
$return .= "fields['$this->name']['id_current'] = '$id';";
$return .= "fields['$this->name']['filled'] = true;";
$return .= "fields['$this->name']['databank'] = '".$databank."';";
$k=array();
if (isset($this->db_opt["select_params"]))
{
	foreach ($this->db_opt["select_params"] as $i=>$j)
	{
		$k[] = "'$i' : '$j'";
	}
}
$return .= "fields['$this->name']['query_params'] = { ".implode(" , ",$k)." };";
$return .= "</script>\n";

if ($print)
	echo $return;
else
	return $return;

}

function form_field_select_disp($print=true, $options=array())
{

if ($options)
	$return = "<select name=\"params[$this->name]\">";
else
	$return = "<select id=\"params[$this->name]\" onchange=\"this.name = this.id;\">";
$return .= "<option value=\"\"></option>";
foreach($this->structure_opt["databank_select"] as $databank)
	if ($options == $databank)
		$return .= "<option value=\"$databank\" selected>$databank</option>";
	else
		$return .= "<option value=\"$databank\">$databank</option>";
$return .= "</select>";

if ($print)
	echo $return;
else
	return $return;

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

protected $value = array();

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

function __construct($name, $value, $label="Name", $db_opt=array(), $disp_opt=array(), $form_opt=array())
{

data::__construct($name, $value, $label, array("databank"=>$name), $db_opt, $disp_opt, $form_opt);

}

function __tostring()
{

if (is_array($this->value) && $this->disp_opt["ref_field_disp"] && isset(datamodel($this->structure_opt["databank"])->{$this->disp_opt["ref_field_disp"]}))
{
	$return = array();
	foreach($this->value as $object)
		$return[] = $object->{$this->disp_opt["ref_field_disp"]};
	return implode(" , ",$return);
}
if (is_array($this->value))
	return (string)implode(" , ",$this->value);
else
	return "";

}

function disp_url()
{

$return = array();
foreach($this->value as $object)
{
	$return[] = "<a href=\"http://".SITE_DOMAIN.SITE_BASEPATH."/".$this->structure_opt["databank"]."/".$object->id."/\">$object</a>";
}
return implode(" , ",$return);

}

function value_from_db($value)
{

$this->value = array();
foreach($value as $id)
{
	if (is_a($object=databank($this->structure_opt["databank"])->get($id), "agregat"))
	{
		$this->value[] = $object;
	}
}

}

function value_to_db()
{

$return = array();
foreach ($this->value as $nb=>$object)
{
	$return[$nb] = "$object->id";
}
return $return;

}

function value_from_form($value)
{

$this->value = array();
if (is_array($value))
{
	foreach($value as $nb=>$id)
	{
		if (is_a($object=databank($this->structure_opt["databank"])->get($id), "agregat"))
		{
			//echo "<p>$object</p>";
			$this->value[$nb] = $object;
		}
	}
}

}

function form_field_disp($print=true)
{

/*
 * Plusieurs possibilités :
 * 
 * Objets liés par une table tiers :
 * - A la création on choisit l'objet à lier et les paramètres de liaison éventuels
 * - A la modification on modifie les paramètres de liaison (pas de bouton si pas de paramètres)
 * - A la suppression on efface la ligne dans la table de liaison
 * 
 */

// Pas beaucoup de valeurs : liste simple
if (($nb=datamodel($this->structure_opt["databank"])->db_count()) < 20)
{
	$query = databank($this->structure_opt["databank"])->query();
	{
		$values = array();
		if (is_array($this->value))
			foreach ($this->value as $value)
			{
				$values[] = "$value->id";
			}
		if ($nb<10)
			$size = $nb;
		else
			$size = 5;
		$return = "<select name=\"".$this->name."[]\" multiple size=\"$size\">\n";
		foreach($query as $object)
		{
			if (in_array("$object->id", $values))
			{
				$return .= "<option value=\"$object->id\" selected=\"selected\">$object</option>";
			}
			else
				$return .= "<option value=\"$object->id\">$object</option>";
		}
		$return .= "</select>\n";
	}
}
// Beaucoup de valeurs : liste Ajax complexe
else
{
	$return = "<div id=\"".$this->name."_list\"></div>\n";
	$return .= "<div id=\"".$this->name."_add\">";
	$return .= "<input type=\"hidden\" id=\"$this->name\" value=\"\" /><input type=\"text\" id=\"".$this->name."_input\" value=\"\" class=\"\" onkeyup=\"databank_lookup('$this->name');\" />\n";
	$return .= "</div>\n";
	$return .= "<div class=\"suggestionsBox\" id=\"".$this->name."_suggestions\" style=\"display: none;\"><div class=\"suggestionList\" id=\"".$this->name."_autoSuggestionsList\">&nbsp;</div></div>";
	$return .= "<script type=\"text/javascript\">\n";
	$return .= "if (!fields) var fields = new Array();\n";
	$return .= "fields['$this->name'] = new Array();\n";
	$return .= "fields['$this->name']['type'] = 'link';\n";
	$return .= "fields['$this->name']['nb'] = 0;\n";
	$return .= "fields['$this->name']['name_current'] = '';\n";
	$return .= "fields['$this->name']['id_current'] = '';\n";
	$return .= "fields['$this->name']['filled'] = true;\n";
	$return .= "fields['$this->name']['databank'] = ".$this->structure_opt["databank"].";\n";
	$k=array();
	if (isset($this->db_opt["select_params"]) && is_array($this->db_opt["select_params"]))
	{
		foreach ($this->db_opt["select_params"] as $i=>$j)
		{
			$k[] = "'$i' : '$j'";
		}
	}
	$return .= "fields['$this->name']['query_params'] = { ".implode(" , ",$k)." };\n";
	$return .= "fields['$this->name']['value'] = new Array();\n";
	$return .= "$(document).ready(function(){\n";
	if ($this->value !== null && is_array($this->value))
	{
		foreach ($this->value as $nb=>$object)
		{
			if (is_a($object, "data_bank_agregat"))
			{
				$return .= "databank_list_add('$this->name', $object->id, \"$object\");\n";
			}
		}
	}
	$return .= "});\n";
	$return .= "</script>\n";
}

// DISP
if ($print)
	echo $return;
else
	return $return;

}

function form_field_disp_all($print=true, $params=array())
{

$return = "<div id=\"".$this->name."_list\">\n";

$databank = $this->structure_opt("databank");
$query = $databank()->query($params);
foreach ($query as $object)
{
	if (!$this->value)
		$this->value = array();
	$checked = "";
	foreach ($this->value as $o)
	{
		if ("$object->id" == "$o->id")
			$checked = " checked=\"checked\"";
	}
	$return .= "<input type=\"checkbox\" name=\"".$this->name."[]\" value=\"$object->id\"$checked /> $object<br />";
}

$return .= "</div>\n";


if ($print)
	echo $return;
else
	return $return;

}

function form_field_disp_light($print=true)
{



}

function form_field_disp_old($print=true)
{

$return = "<div id=\"$this->name\">\n";
$return .= "<div><input type=\"button\" value=\"ADD\" onclick=\"add('$this->name')\" /></div>\n";
$nb=0;
if ($this->value !== null) foreach ($this->value as $nb=>$data_bank_agregat)
{
	$return .= "<div id=\"".$this->name."_$nb\">";
	$return .= "<input type=\"text\" id=\"$this->name[$nb]\" onchange=\"change('$this->name')\" value=\"".$data_bank_agregat->id."\" size=\"6\" />";
	if ($this->disp_opt["ref_disp_field"] && isset(datamodel($this->structure_opt["databank"])->{$this->disp_opt["ref_disp_field"]}))
		$value = (string)$data_bank_agregat->{$this->disp_opt["ref_disp_field"]};
	else
		$value = (string)$data_bank_agregat;
	$return .= "<input type=\"text\" value=\"$value\" readonly />";
	$return .= "<input type=\"button\" value=\"UPDATE\" onclick=\"update('$this->name','$nb')\" />";
	$return .= "<input type=\"button\" value=\"DEL\" onclick=\"remove('$this->name','$nb')\" />";
	$return .= "</div>\n";
}
$return .= "</div>\n";
$return .= "<script type=\"text/javascript\">";
$return .= "fields['$this->name'] = new Array;";
if ($this->db_opt["ref_table"])
	$return .= "fields['$this->name']['type'] = 'link';";
else
	$return .= "fields['$this->name']['type'] = 'self';";
$return .= "fields['$this->name']['databank'] = '".$this->structure_opt["databank"]."';";
$return .= "fields['$this->name']['nb_max'] = $nb;";
$return .= "</script>\n";
if ($print)
	echo $return;
else
	return $return;

}

public function db_query_param($value, $type="=")
{

//echo $this->db_opt["ref_id"];

$type_list = array( "=", "LIKE", "<", ">", "<=", ">=", "NOT LIKE" );  
if (!in_array($type, $type_list))
	$type = "=";

$fieldname = $this->db_opt["ref_id"];

if (is_array($value))
{
	$q = array();
	foreach($value as $i)
		$q[] = "'".db()->string_escape($i)."'";
	return "`".$fieldname."` IN (".implode(" , ",$q).")";
}
else
	return "`".$fieldname."` $type '".db()->string_escape($value)."'";
	

}

}

/**
 * Seconde possibilité
 * 
 * Objets liés intrinsèquement :
 * - A l'ajout on créé un nouvel objet lié
 * - A la modification on mofidie l'objet lié
 * - A la suppression : on propose de supprimer l'objet lié, de le réaffecter (le lier à un autre) ou encore de laisser vide le champ de liaison s'il n'est pas requis.
 * 
 */
class data_dataobject_list_ref extends data_dataobject_list
{

protected $type = "dataobject_list";

protected $value = array();

function form_field_disp($print=true)
{

$return = "<div id=\"".$this->name."_list\">\n";
$nb=0;
if ($this->value !== null && is_array($this->value))
	foreach ($this->value as $nb=>$object)
	{
		if (is_a($object, "data_bank_agregat"))
		{
			$return .= "<div id=\"".$this->name."_$nb\">";
			$return .= "<input type=\"hidden\" id=\"$this->name[$nb]\" value=\"".$object->id."\" />";
			if ($this->disp_opt["ref_field_disp"] && isset(datamodel($this->structure_opt["databank"])->{$this->disp_opt["ref_field_disp"]}))
				$value = (string)$object->{$this->disp_opt["ref_field_disp"]};
			else
				$value = (string)$object;
			$return .= "<input type=\"text\" value=\"$value\" readonly/>";
			$return .= "<input type=\"button\" value=\"UPDATE\" onclick=\"update('$this->name','$nb')\" />";
			$return .= "</div>\n";
		}
	}
$return .= "</div>\n";
{
	$return .= "<div id=\"".$this->name."_add\">";
	$return .= "<input type=\"button\" value=\"ADD\" onclick=\"dataobject_add('$this->name')\" />";
	$return .= "</div>\n";
}
$return .= "<script type=\"text/javascript\">";
$return .= "fields['$this->name'] = new Array;";
$return .= "fields['$this->name']['type'] = 'dataobject_list';";
$return .= "fields['$this->name']['nb'] = '$nb';";
$return .= "fields['$this->name']['name_current'] = '';";
$return .= "fields['$this->name']['id_current'] = '';";
$return .= "fields['$this->name']['filled'] = true;";
$return .= "fields['$this->name']['databank'] = '".$this->structure_opt["databank"]."';";
$k=array();
if (isset($this->db_opt["select_params"]))
{
	foreach ($this->db_opt["select_params"] as $i=>$j)
	{
		$k[] = "'$i' : '$j'";
	}
}
$return .= "fields['$this->name']['query_params'] = { ".implode(" , ",$k)." };";
$return .= "</script>\n";

if ($print)
	echo $return;
else
	return $return;

}

}

/**
 * Data types global container class
 */
class data_gestion
{

protected $list = array();
protected $list_name = array();

public function __construct()
{

$query = db()->query("SELECT t1.id, t1.name , t2.title FROM _datatype as t1 LEFT JOIN _datatype_lang as t2 ON t1.id=t2.datatype_id ORDER BY t2.title");
while(list($id, $name, $title)=$query->fetch_row())
{
	$this->list[$id] = array("name"=>$name, "title"=>$title);
	$this->list_name[$name] = $id;
}

}

public function get($id)
{

if (isset($this->list[$id]))
{
	$datatype = "data_".$this->list[$id]["name"];
	return new $datatype($this->list[$id]["name"], null);
}
else
	return null;

}

public function id($name)
{

if (isset($this->list_name[$name]))
	return $this->list_name[$name];
else
	return null;

}

public function title($name)
{

if (isset($this->list_name[$name]))
	return $this->list[$this->list_name[$name]]["title"];
else
	return null;

}

public function list_get()
{

return $this->list;

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
		if (!($GLOBALS["data_gestion"]=apc_fetch("data_gestion")))
		{
			$GLOBALS["data_gestion"] = new data_gestion();
			apc_store("data_gestion", $GLOBALS["data_gestion"], APC_CACHE_GESTION_TTL);
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
	return $GLOBALS["data"]->get($id);
else
	return $GLOBALS["data"];

}

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>