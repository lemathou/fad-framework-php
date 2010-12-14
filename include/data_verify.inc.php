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
