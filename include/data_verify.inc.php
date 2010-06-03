<?

/**
 * V�rification de donn�e
 *
 */

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__);

/**
 * Interface de v�rification
 *
 */
interface data_verify_i
{

/*
 * Verify the value
 */
function verify($value,$params=array());

/*
 * Convert the value into the right type
 */
function convert($value,$params=array());

}

/**
 * Maxlength
 *
 */
class data_verify_size implements data_verify_i
{

public function verify($value,$maxlength=0)
{

if (!is_string($value) || (is_numeric($maxlength) && $maxlength > 0 && strlen($value) > $maxlength))
	return false;
else
	return true;

}

public function convert($value,$maxlength=0)
{

if (is_numeric($maxlength) && $maxlength > 0)
	return substr((string)$value,0,$maxlength);
else
	return (string)$value;

}

}

/**
 * Maxlength
 *
 */
class data_verify_string_tag_authorized implements data_verify_i
{

public function verify($value,$tags=array())
{

return true;

}

public function convert($value,$maxlength=0)
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

public function verify($value,$params=array())
{

if (!is_array($params) || !isset($params["signed"]))
	$params["signed"] = false;

// Should I use is_int ?
if ((!is_numeric($value) || ((int)$value != $value)) && ($params["signed"] || $value>=0))
	return false;
else
	return true;

}

public function convert($value,$params=array())
{

if (!is_array($params) || !isset($params["signed"]))
	$params["signed"] = false;

if ($params["signed"])
	return (int) $value;
elseif ((int)$value < 0)
	return -(int)$value;
else
	return (int)$value;

}

}

/**
 * Float
 *
 */
class data_verify_float implements data_verify_i
{

public function verify($value,$params=array())
{

//echo "<br/>$value";
//print_r($params);

// Should I use is_float ?
if (!is_numeric($value))
{
	return false;
}
elseif ($params["signed"] && !ereg($value, "[-]+[1-9]{0,1}[0-9]*(\.[0-9]{0,".($params["precision"]-1)."}[1-9]){0,1}"))
{
	return false;
}
elseif (!$params["signed"] && !ereg($value, "[1-9]{0,1}[0-9]*(\.[0-9]{0,".($params["precision"]-1)."}[1-9]){0,1}"))
{
	return false;
}
else
{
	return true;
}

}

public function convert($value,$params=array())
{

//echo "<br/>$value";

if (!$params["signed"] && $value < 0)
	return round(-(float) $value, $params["precision"]);
else
	return round((float) $value, $params["precision"]);

}

}

/**
 * Array verify
 */

class data_verify_array implements data_verify_i
{

public function verify($value,$params=array())
{

if (!is_array($value))
	return false;
else
	return true;

}

public function convert($value,$params=array())
{

return array($value);

}

}

/**
 * Array verify
 */

class data_verify_list implements data_verify_i
{

public function verify($value,$params=array())
{

if (!is_array($value))
	return false;
else
	return true;

}

public function convert($value,$params=array())
{

if (!is_array($value))
	return array($value);
else
	return $value;

}

}

/**
 * Number compare verify
 */

class data_verify_compare implements data_verify_i
{

public function verify($value,$params=array())
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

public function convert($value,$params=array())
{

return $value;

}

}

/**
 * Array count verify
 */

class data_verify_count implements data_verify_i
{

public function verify($value,$params=array())
{

return data_verify_compare::verify(count($value),$params);

}

public function convert($value,$params=array())
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

public function verify($value,$params=array())
{

if (!isset($params[$value]))
	return false;
else
	return true;

}

public function convert($value,$params=array())
{

if (!isset($params[$value]))
{
	// Y'aurait pas plus simple ..?
	list($i,)=each($params);
	return $i;
}
else
	return $value;

}

}


/**
 * A set of values in a list
 *
 */
class data_verify_fromlist implements data_verify_i
{

public function verify($value,$params=array())
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

public function convert($value,$params=array())
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

public function verify($value,$params=array())
{

if (!is_string($value) || !ereg("([0-9]{4})-((0[0-9])|(1[0-2]))-(([0-2][0-9])|(3[0-1]))",$value))
	return false;
else
	return true;

}

public function convert($value,$params=array())
{

return "0000-00-00";

}

}

/**
 * Date verify
 */

class data_verify_year implements data_verify_i
{

public function verify($value,$params=array())
{

if (!is_string($value) || !ereg("([0-9]{4})",$value))
	return false;
else
	return true;

}

public function convert($value,$params=array())
{

return "0000";

}

}

/**
 * Time verify
 */

class data_verify_time implements data_verify_i
{

public function verify($value,$params=array())
{

if (!is_string($value) || !ereg("(([01][0-9])|(2[0-3])):([0-5][0-9]):([0-5][0-9])",$value))
	return false;
else
	return true;

}

public function convert($value,$params=array())
{

return "00:00:00";

}

}

/**
 * Datetime verify
 */

class data_verify_datetime implements data_verify_i
{

public function verify($value,$params=array())
{

if (!is_string($value) || !ereg("([0-9]{4})-((0[0-9])|(1[0-2]))-(([0-2][0-9])|(3[0-1])) (([01][0-9])|(2[0-3])):([0-5][0-9]):([0-5][0-9])",$value))
	return false;
else
	return true;

}

public function convert($value,$params=array())
{

return "0000-00-00 00:00:00";

}

}
/**
 * POSIX ereg verification
 */

class data_verify_ereg implements data_verify_i
{


public function verify($value,$params=array())
{

if (is_array($params) && isset($params["ereg"]) && $params["ereg"] && !ereg($params["ereg"],$value))
	return false;
else
	return true;

}


public function convert($value, $params=array())
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


public function verify($value, $params=array())
{

if (!is_a($value, $params["objecttype"]))
	return false;
else
	return true;

}


public function convert($value, $params=array())
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


public function verify($value, $params=array())
{

if (!is_a($value, "agregat") || ($params && ($value->datamodel()->name() != $params)) || !$value->verify())
	return false;
else
	return true;

}


public function convert($value, $params=array())
{

// A COMPLETER
if (is_a($value, "agregat"))
	if ($params && ($value->datamodel()->name() != $params))
		return new agregat(datamodel($params));
	elseif (!$value->verify())
	{
		$value->convert();
		return $value;
	}
	else
		return $value();
else
	if ($params)
		return new agregat(datamodel($params));
	else
		return new agregat();

}

}

/**
 * Databank verification
 */

class data_verify_databank implements data_verify_i
{


public function verify($value, $params=array())
{

/*
/if (!databank($params))
	return false;
*/
if (is_array($value))
{
	$return = true;
	foreach($value as $i)
		if (!$params($i))
			$return = false;
	return $return;
}
elseif (!is_numeric($value) || !$params($value))
	return false;
else
	return true;

}


public function convert($value,$params=array())
{

// A COMPLETER
if (!is_numeric($value) || !$params($value))
	return 0;
else
	return $value;

}

}


class data_verify_databank_select implements data_verify_i
{


public function verify($value, $params=array())
{

if (!is_a($value, "databank_agregat"))
	return false;
elseif (!in_array(($databank=$value->datamodel()->name()), $params) || !databank($databank,$value->id))
	return false;
else
	return true;

}


public function convert($value,$params=array())
{

return null;

}

}

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__);


?>