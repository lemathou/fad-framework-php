<?

/**
  * $Id: mysql.inc.php 74 2009-07-03 06:41:02Z mathieu $
  * 
  * Copyright 2008 Mathieu Moulin - iProspective - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */

/**
  * MySQL abstraction layer
  */
class db extends session_select implements db_i
{

protected $id = NULL;

protected $infos=array();
protected $options=array();

protected $errno = NULL;
protected $error = NULL;

// Purement statistique donc publique, en plus c'est plus pratique pour db_query
public $query_list = array();
public $queries = 0;
public $queries_total = 0;
public $fetch_results = 0;
public $fetch_results_total = 0;
public $time = 0;
public $time_total = 0;

// Données à sauver en session
private $serialize_list = array( "infos" , "options" , "queries_total" , "fetch_results_total" , "time_total" );
public $serialize_save_list = array();

function __construct($infos=array(), $options=array())
{

$this->infos = $infos;
$this->options = $options;

$this->connect();

}

function connect()
{

// On ne se connecte qu'une seule fois
if (in_array("permanent", $this->options))
	$this->id = mysql_pconnect($this->infos["hostname"], $this->infos["username"], $this->infos["password"]);
else
	$this->id = mysql_connect($this->infos["hostname"], $this->infos["username"], $this->infos["password"]);
// Choix base de donnée
mysql_select_db($this->infos["database"], $this->id);
// Encoding
if ($this->infos["charset"] == "UTF8")
{
	mysql_query("SET NAMES 'UTF8'");
}

}

public function query($query_string)
{

$this->queries++;
$this->query_list[] = $query_string;
return new db_query($query_string, $this->id);

}

public function select($fields, $join, $where, $order, $groupby, $limit)
{

$this->queries++;
return new db_query_select($select, $where, $order, $limit, $this->id);

}

public function insert($table, $fields)
{

if (!is_array($fileds) || count($fields) == 0)
	return NULL;
else
{
	$this->queries++;
	$fieldnames = array();
	foreach ($fields as $key=>$value)
		$filednames[] = $key;
	return new db_query("INSERT INTO `$table` ( `".implode("` , `",$fieldnames)."` ) VALUES ( '".implode("' , '",$fields)."' )", $this->id);
}

}

public function update($table, $update, $where="", $order="", $limit="")
{

$this->queries++;
return new db_query(" UPDATE `$table` SET $update $where $order $limit", $this->id);

}

public function delete($table, $where, $order, $limit)
{

$this->queries++;
return new db_query(" DELETE FROM `$table` $where $order $limit", $this->id);

}

public function queries()
{

return $this->queries;

}

public function time()
{

return $this->time;

}

public function error()
{

return mysql_error();

}

public function error_log()
{

echo "Erreur enregistrée";

}

public function last_id()
{

return mysql_insert_id($this->id);

}

public function __destruct()
{

if ($this->id)
{
	mysql_close($this->id);
	$this->id = NULL;
}

}

public function __sleep()
{

$this->queries_total += $this->queries;
$this->fetch_results_total += $this->fetch_results;
$this->time_total += $this->time;

if ($this->id)
{
	mysql_close($this->id);
	$this->id = NULL;
}

return parent::__sleep($this->serialize_list);

}

public function __wakeup()
{

session_select::__wakeup();

$this->connect();

}

public function table_create($tablename, $fields, $options=array())
{

$fieldstruct = array();
$tableoption = array();
$key_list = array();

foreach($fields as $fieldname=>$field)
{
	//echo "<p>$fieldname : $field[type]</p>\n";
	if (!empty($field["key"]))
		$key_list[] = "`$fieldname`";
	$fieldstruct[] = $this->field_struct($fieldname, $field);
}

if (count($key_list))
	$fieldstruct[] = "PRIMARY KEY ( ".implode(" , ",$key_list)." )";

if (isset($options["engine"]))
	$tableoption[] = "ENGINE = $options[engine]";
else
	$tableoption[] = "ENGINE = ".DB_ENGINE;
if (isset($options["charset"]))
	$tableoption[] = "DEFAULT CHARSET = $options[charset]";
else
	$tableoption[] = "DEFAULT CHARSET = ".DB_CHARSET;

//echo "CREATE TABLE IF NOT EXISTS `$tablename` ( ".implode(" , ",$fieldstruct)." ) ".implode(" ",$tableoption);
$this->query("CREATE TABLE IF NOT EXISTS `$tablename` ( ".implode(" , ",$fieldstruct)." ) ".implode(" ",$tableoption));
//$this->query("ALTER TABLE `$tablename` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");

}

public function field_struct($fieldname, $field)
{

$field["null"] = empty($field["null"]) ? " NOT NULL" : " NULL";
$field["default"] = isset($field["default"]) ? " default '".addslashes($field["default"])."'" : "";

switch ($field["type"])
{
	case ($field["type"]=="string" && !empty($field["size"]) && isset($field["autocomplete"])) :
		return "`$fieldname` char($field[size])$field[null]$field[default]";
		break;
	case ($field["type"]=="string" && !empty($field["size"])) :
		return "`$fieldname` varchar($field[size])$field[null]$field[default]";
		break;
	case "string" :
		return "`$fieldname` text$field[null]$field[default]";
		break;
	case "richtext" :
		return "`$fieldname` text$field[null]$field[default]";
		break;
	case "integer" :
		return "`$fieldname` int($field[size])".(empty($field["signed"])?" unsigned":"").$field["null"].$field["default"].(!empty($field["auto_increment"])?" auto_increment":"");
		break;
	case "float" :
		return "`$fieldname` float(".($field["size"]+$field["precision"]).",$field[precision])".(empty($field["signed"])?" unsigned":"").$field["null"].$field["default"];
		break;
	case "date" :
		return "`$fieldname` date$field[null]$field[default]";
		break;
	case "year" :
		return "`$fieldname` year$field[null]$field[default]";
		break;
	case "time" :
		return "`$fieldname` time$field[null]$field[default]";
		break;
	case "datetime" :
		return "`$fieldname` datetime$field[null]$field[default]";
		break;
	case "select" :
		return "`$fieldname` enum ('".implode("' , '",$field["value_list"])."')$field[null]$field[default]";
		break;
	case "fromlist" :
		return "`$fieldname` set ('".implode("' , '",$field["value_list"])."')$field[null]$field[default]";
		break;
	case "boolean" :
		return "`$fieldname` BOOLEAN$field[null]$field[default]";
		break;
}

}

public function field_update($tablename, $fieldname_from, $fieldname_to, $field, $position="")
{

//echo "ALTER TABLE `$tablename` CHANGE `$fieldname_from` ".$this->field_struct($fieldname_to, $field)." $position";

$this->query("ALTER TABLE `$tablename` CHANGE `$fieldname_from` ".$this->field_struct($fieldname_to, $field)." $position"); 

}

public function field_create($tablename, $fieldname, $field, $position="")
{

$this->query("ALTER TABLE `$tablename` ADD ".$this->field_struct($fieldname, $field)." $position"); 

}

public function field_delete($tablename, $fieldname)
{
	
$this->query("ALTER TABLE `$tablename` DROP `$fieldname`"); 

}

/*
 * Delete a database table
 */
public function table_delete($tablename)
{

return $this->query(" DROP TABLE `$tablename` ");

}

public function string_escape($string)
{

return mysql_real_escape_string($string);

}

}


/**
 *  Classe pour les requètes
 *  
 */
class db_query implements db_query_i
{

// id de la connexion au serveur
protected $db_id; 
// id de la requ�te
protected $id;

// On effectue la requète
function __construct($query_string, $db_id)
{

//print $query_string;
$this->db_id = $db_id;
$time1 = microtime(true);
$this->id = mysql_query($query_string, $this->db_id);
$time2 = microtime(true);
db()->time += ($time2 - $time1);
if (mysql_error())
{
	print "<p>".$query_string." : ".mysql_error()."</p>";
}

}

public function num_rows()
{

$time1 = microtime(true);
$return = mysql_num_rows($this->id);
$time2 = microtime(true);
db()->time += ($time2 - $time1);
return $return;

}

// Méthode globale
public function fetch($type="row")
{

switch($type)
{
	case "array":
		return $this->fetch_array();
		break;
	case "assoc":
		return $this->fetch_assoc();
		break;
	case "object":
		return $this->fetch_object();
		break;
	case "row":
		return $this->fetch_row();
		break;
	default:
		return $this->fetch_row();
		break;
}

}

// Méthode globale
public function fetch_all($type="row", $return="list")
{

switch($return)
{
	case "list":
		$return = array();
		while ($i = $this->fetch($type))
			$return[] = $i;
		return $return;
		break;
	default:
		$return = array();
		while ($i = $this->fetch_row())
			$return[] = $i;
		return $return;
		break;
}

}

public function fetch_row()
{

$time1 = microtime(true);
if ($return = mysql_fetch_row($this->id))
{
	db()->fetch_results++;
}
$time2 = microtime(true);
db()->time += ($time2 - $time1);
return $return;

}

public function fetch_array()
{

$time1 = microtime(true);
if ($return = mysql_fetch_array($this->id, MYSQL_ASSOC))
{
	db()->fetch_results++;
}
$time2 = microtime(true);
db()->time += ($time2 - $time1);
return $return;

}

public function fetch_assoc()
{

$time1 = microtime(true);
if ($return = mysql_fetch_assoc($this->id))
{
	db()->fetch_results++;
}
$time2 = microtime(true);
db()->time += ($time2 - $time1);
return $return;

}

public function affected_rows()
{

return mysql_affected_rows($this->db_id);

}

public function last_id()
{

return mysql_insert_id($this->db_id);

}

public function free()
{

return mysql_free_result($this->id);

}

}

/**
 * Select query
 * This class delegates every query to an instance of db_query
 *
 * @return unknown
 */
class db_query_select
{

protected $query;

function __construct($select, $where, $order, $limit, $db_id)
{

$from_querystr = array();
$select_querystr = array();
foreach($select as $table=>$fields)
{
	foreach($fields as $field)
		$select_querystr[] = "$table.$field";
	if (!in_array($table, $from_querystr))
		$from_querystr[] = "$table";
}
print $query_string = "SELECT ".implode(" , ",$select_querystr)." FROM ".implode(" , ",$from_querystr);
$this->query = new db_query($query_string, $db_id);

}

function __get($name)
{

return $this->query->{$name};

}

function __set($name, $value)
{

$this->query->{$name} = $value;

}

function __call($name, $arguments)
{

// A AMELIORER !!
$args = array();
for ($i=0;$i<count($arguments);$i++)
	$args[] = "\$arguments[\$i]";
$str = "return \$this->query->".$name."(".implode(" , ",$args).");";
eval ($str);

}

}

// accès

function db()
{

if (!isset($GLOBALS["db"]))
{
	if (!isset($_SESSION["db"]))
		$_SESSION["db"] = new db(array("hostname"=>DB_HOST, "username"=>DB_USERNAME, "password"=>DB_PASSWORD, "database"=>DB_BASE, "charset"=>DB_CHARSET));
	$GLOBALS["db"] = $_SESSION["db"];
	// TODO : destroy db password & co and so do not.
}

return $GLOBALS["db"];

}

?>
