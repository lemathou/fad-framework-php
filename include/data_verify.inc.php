<?

/**
 * Data verification classes
 *
 */

if (DEBUG_GENTIME == true)
	gentime(__FILE__);

/**
 * Verification interface
 *
 */
interface data_verify_i
{

/*
 * Verify the value
 */
static function verify($value,$params=array());

/*
 * Convert the value into the right type
 */
static function convert($value,$params=array());

}

/**
 * Maxlength
 *
 */
class data_verify_size implements data_verify_i
{

public static function verify($value,$maxlength=0)
{

if (is_numeric($maxlength) && $maxlength > 0 && strlen((string)$value) > $maxlength)
	return false;
elseif (is_numeric($value))
	return true;
elseif (!is_string($value))
	return false;
else
	return true;

}

public static function convert($value,$maxlength=0)
{

if (is_numeric($maxlength) && $maxlength > 0)
	return substr((string) $value, 0, $maxlength);
else
	return (string) $value;

}

}

/**
 * Maxlength
 *
 */
class data_verify_string_tag_authorized implements data_verify_i
{

public static function verify($value,$tags=array())
{

return true;

}

public static function convert($value,$maxlength=0)
{

return (string)$value;

}

}

/**
 * Integer
 *
 */
class data_verify_integer implements data_verify_i
{

public static function verify($value,$params=array())
{

if (!is_array($params) || !isset($params["signed"]))
	$params["signed"] = false;

// Should I use is_int ?
if (!is_numeric($value) || ((int)$value != $value) || ($params["signed"] == false && $value < 0))
{
	return false;
}
else
{
	return true;
}

}

public static function convert($value,$params=array())
{

if (!is_array($params) || !isset($params["signed"]))
	$params["signed"] = false;

if (!$params["signed"] && (int)$value < 0)
	return - (int) $value;
else
	return (int) $value;

}

}

/**
 * Float
 *
 */
class data_verify_float implements data_verify_i
{

public static function verify($value,$params=array())
{

//echo "<br/>$value";
//print_r($params);

// Should I use is_float ?
if (!is_numeric($value))
{
	return false;
}
elseif ($params["signed"] && !preg_match('/^?[-]?([1-9][0-9]*)?[0-9](\.[0-9]{0,'.($params["precision"]-1).'}[1-9]){0,1}$/', $value))
{
	return false;
}
elseif (!$params["signed"] && !preg_match('/^([1-9][0-9]*)?[0-9](\.[0-9]{0,'.($params["precision"]-1).'}[1-9]){0,1}$/', $value))
{
	//echo "<p>$value PAS OK</p>";
	return false;
}
else
{
	//echo "<p>$value OK</p>";
	return true;
}

}

public static function convert($value,$params=array())
{

//echo "<br/>$value";

if (!$params["signed"] && $value < 0)
	return round(-(float) $value, $params["precision"]);
else
	return round((float) $value, $params["precision"]);

}

}

/*
 * Percent
 */
class data_verify_percent implements data_verify_i
{

public static function verify($value,$params=array())
{

if (is_numeric($value) && $value>=0 && $value<=1)
	return true;
else
	return false;

}

public static function convert($value,$params=array())
{

if ($value)
	return 1;
else
	return 0;

}

}

/**
 * Boolean
 */
class data_verify_boolean implements data_verify_i
{

public static function verify($value,$params=array())
{

if ($value === true || $value === false)
	return true;
else
	return false;

}

public static function convert($value,$params=array())
{

if ($value)
	return true;
else
	return false;

}

}

/**
 * Array verify
 */
class data_verify_array implements data_verify_i
{

public static function verify($value,$params=array())
{

if (!is_array($value))
	return false;
else
	return true;

}

public static function convert($value,$params=array())
{

return array($value);

}

}

/**
 * Array verify
 */
class data_verify_list implements data_verify_i
{

public static function verify($value,$params=array())
{

if (!is_array($value))
	return false;
else
	return true;

}

public static function convert($value,$params=array())
{

if (!is_array($value))
	return array();
else
	return $value;

}

}

/**
 * Number compare verify
 */
class data_verify_compare implements data_verify_i
{

public static function verify($value,$params=array())
{

$params = explode(" ",$params);

if (!is_numeric($value))
	return false;
elseif ($params[0] ==  "<" && $value >= $params[1])
	return false;
elseif ($params[0] ==  "<=" && $value > $params[1])
	return false;
elseif ($params[0] ==  ">" && $value <= $params[1])
	return false;
elseif ($params[0] ==  ">=" && $value < $params[1])
	return false;
elseif ($params[0] ==  "!=" && $value == $params[1])
	return false;
elseif ($params[0] ==  "==" && $value != $params[1])
	return false;
else
	return true;

}

public static function convert($value,$params=array())
{

return $value;

}

}

/**
 * Array count verify
 */
class data_verify_count implements data_verify_i
{

public static function verify($value,$params=array())
{

return data_verify_compare::verify(count($value),$params);

}

public static function convert($value,$params=array())
{

return array($value);

}

}

/**
 * A value in a list
 * The default value is the first element
 *
 */
class data_verify_select implements data_verify_i
{

public static function verify($value,$params=array())
{

if (!isset($params[$value]))
	return false;
else
	return true;

}

public static function convert($value,$params=array())
{

if (!isset($params[$value]))
	return null;
else
	return $value;

}

}

/**
 * An email address
 *
 */
class data_verify_email implements data_verify_i
{

public static function verify($value,$params=array())
{

$regex = ($params["strict"]) ? '/^([.0-9a-z_-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i' : '/^([*+!.&#$¦\'\\%\/0-9a-z^_`{}=?~:-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,4})$/i';

if (is_string($value) && preg_match($regex, $value, $match))
{
	if (checkdnsrr($match[2], "MX"))
		return true;
	else
		return false;
}
else
	return false;

}

public static function convert($value,$params=array())
{

return "";

}

}

/**
 * En URL
 */
class data_verify_url implements data_verify_i
{

public static function verify($value,$params=array())
{

$regex = '/^[a-zA-Z]+[:\/\/]+[A-Za-z0-9\-_]+\\.+[A-Za-z0-9\.\/%&=\?\-_]+$/i';

if (!is_string($value) || preg_match($regex, $value))
	return true;
else
	return false;

}

public static function convert($value,$params=array())
{

return "";

}

}

/**
 * A set of values in a list
 *
 */
class data_verify_fromlist implements data_verify_i
{

public static function verify($value,$params=array())
{

if (!is_array($value))
{
	return false;
}
else
{
	$return = true;
	foreach($value as $index=>$element)
	{
		if (!isset($params[$element]))
			$return = false;
	}
	return $return;
}

}

public static function convert($value,$params=array())
{

if (!is_array($value))
{
	return array();
}
else
{
	foreach($value as $index=>$element)
		if (!isset($params[$element]))
			unset($value[$index]);
	return $value;
}

}

}


/**
 * Date verify
 */
class data_verify_date implements data_verify_i
{

public static function verify($value,$params=array())
{

if (is_string($value) && preg_match('/^(0?[1-9]|[12][0-9]|3[01])[\/](0?[1-9]|1[0-2])[\/](19|20)\d{2}$/', $value))
	return true;
else
	return false;

}

public static function convert($value,$params=array())
{

if (self::verify($value))
	return $value;
elseif ($value)
	return "00/00/0000";
else
	return null;

}

}

/**
 * Date verify
 */
class data_verify_year implements data_verify_i
{

public static function verify($value,$params=array())
{

if (!is_string($value) || !preg_match("([0-9]{4})",$value))
	return false;
else
	return true;

}

public static function convert($value,$params=array())
{

return "0000";

}

}

/**
 * Time verify
 */
class data_verify_time implements data_verify_i
{

public static function verify($value,$params=array())
{

if (!is_string($value) || !preg_match("(([01][0-9])|(2[0-3])):([0-5][0-9]):([0-5][0-9])",$value))
	return false;
else
	return true;

}

public static function convert($value,$params=array())
{

return "00:00:00";

}

}

/**
 * Datetime verify
 */
class data_verify_datetime implements data_verify_i
{

public static function verify($value,$params=array())
{

if (!is_numeric($value))
	return false;
else
	return true;

}

public static function convert($value,$params=array())
{

return time();

}

}

/**
 * POSIX ereg verification
 */
class data_verify_ereg implements data_verify_i
{

public static function verify($value,$params=array())
{

if (is_array($params) && isset($params["ereg"]) && $params["ereg"] && !preg_match($params["ereg"],$value))
	return false;
else
	return true;

}

public static function convert($value, $params=array())
{

if (isset($params["default"]))
	return $params["default"];
else
	return null;

}

}

/**
 * Object verification
 */
class data_verify_object implements data_verify_i
{


public static function verify($value, $params=array())
{

if (!is_a($value, $params["objecttype"]))
	return false;
else
	return true;

}


public static function convert($value, $params=array())
{

$objecttype = $params["objecttype"];
// A CORRIGER
return new $objecttype();

}

}

/**
 * Object verification
 */
class data_verify_datamodel implements data_verify_i
{

public static function verify($value, $params=array())
{

if (is_a($value, "agregat") && ($value->datamodel()->name() == $params))
	return true;
else
	return false;

}

public static function convert($value, $params=array())
{

// A COMPLETER
if (self::verify($value, $params))
	return $value;
else
	return null;

}

}

/**
 * Databank verification
 */
class data_verify_databank implements data_verify_i
{

public static function verify($value, $params=array())
{

if (is_array($value))
{
	$return = true;
	foreach($value as $i)
		if (!databank($params)->get($i))
			$return = false;
	return $return;
}
elseif (!is_numeric($value) || !is_a(databank($params)->get($value),"data_bank_agregat"))
	return false;
else
	return true;

}

public static function convert($value, $params=array())
{

return 0;

}

}

/**
 * Databank select verification
 */
class data_verify_databank_select implements data_verify_i
{

public static function verify($value, $params=array())
{

if (!is_a($value, "databank_agregat"))
	return false;
elseif (!in_array(($databank=$value->datamodel()->name()), $params) || !databank($databank,$value->id))
	return false;
else
	return true;

}

public static function convert($value,$params=array())
{

return null;

}

}

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__);


?>
