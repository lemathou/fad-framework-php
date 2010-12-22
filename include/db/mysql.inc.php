<?

/**
  * $Id$
  * 
  * Copyright 2008 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */

/**
  * MySQL abstraction layer
  */
class db implements db_i
{

protected $id = null;

protected $infos=array();
protected $options=array();

protected $errno = null;
protected $error = null;

// Purement statistique donc publique, en plus c'est plus pratique pour db_query
public $query_list = array();
public $queries = 0;
public $queries_total = 0;
public $fetch_results = 0;
public $fetch_results_total = 0;
public $time = 0;
public $time_total = 0;

function __sleep()
{

$this->queries_total += $this->queries;
$this->fetch_results_total += $this->fetch_results;
$this->time_total += $this->time;

return array("infos", "options", "queries_total", "fetch_results_total", "time_total");

}
public function __wakeup()
{

$this->connect();

}
public function __destruct()
{

if ($this->id)
{
	mysql_close($this->id);
	$this->id = null;
}

}
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
return new db_query("UPDATE `$table` SET $update $where $order $limit", $this->id);

}

public function delete($table, $where, $order, $limit)
{

$this->queries++;
return new db_query("DELETE FROM `$table` $where $order $limit", $this->id);

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

// server connection ID
protected $db_id; 
// server request ID
protected $id;
// query_string
protected $query_string="";
// num_rows
protected $num_rows=null;

protected function log_error($error="")
{

echo "<p>DEBUG : ".mysql_error()."</p>\n";
fwrite(fopen(LOG_DB_ERROR_PATH, "a"), date("Y-m-d H:i:s")." : $error\n");

}

public function __destruct()
{

if (false && $this->id)
{
	$this->free();
}

}
function __construct($query_string, $db_id)
{

$this->db_id = $db_id;
$this->query_string = $query_string;
//echo "<p>$query_string</p>\n";

if (!$this->id)
	$this->execute();

}

public function execute()
{

$time1 = microtime(true);
$this->id = mysql_query($this->query_string, $this->db_id);
$time2 = microtime(true);
db()->time += ($time2 - $time1);

if (($error=mysql_error()) && LOG_DB_ERROR)
	$this->log_error($error);

}

public function num_rows()
{

if (!$this->id)
	$this->execute();

if (!is_numeric($this->num_rows))
{
	$time1 = microtime(true);
	$this->num_rows = mysql_num_rows($this->id);
	$time2 = microtime(true);
	db()->time += ($time2 - $time1);
}

return $this->num_rows;

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

if (!$this->id)
	$this->execute();

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

if (!$this->id)
	$this->execute();

$time1 = microtime(true);
if ($return = mysql_fetch_row($this->id))
	db()->fetch_results++;
$time2 = microtime(true);
db()->time += ($time2 - $time1);

if (mysql_error() && LOG_DB_ERROR)
	$this->log_error();

return $return;

}

public function fetch_array()
{

if (!$this->id)
	$this->execute();

$time1 = microtime(true);
if ($return = mysql_fetch_array($this->id, MYSQL_ASSOC))
	db()->fetch_results++;
$time2 = microtime(true);
db()->time += ($time2 - $time1);

if (mysql_error() && LOG_DB_ERROR)
	$this->log_error();

return $return;

}

public function fetch_assoc()
{

if (!$this->id)
	$this->execute();

$time1 = microtime(true);
if ($return = mysql_fetch_assoc($this->id))
	db()->fetch_results++;
$time2 = microtime(true);
db()->time += ($time2 - $time1);

if (mysql_error() && LOG_DB_ERROR)
	$this->log_error();

return $return;

}

public function affected_rows()
{

if (!$this->id)
	$this->execute();

return mysql_affected_rows($this->db_id);

}

public function last_id()
{

if (!$this->id)
	$this->execute();

return mysql_insert_id($this->db_id);

}

public function free()
{

if ($this->id)
{
	$return = mysql_free_result($this->id);
	$this->id = null;
	return $return;
}

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

function __construct($db_id, $select, $where, $order, $limit)
{

$select_list = array();
$from_list = array();
foreach($select as $table=>$fields)
{
	foreach($fields as $field)
		$select_list[] = "`$table`.`$field`";
	if (!in_array($table, $from_list))
		$from_list[] = "`$table`";
}
$where_list = array();
foreach ($where as $w=>$l)
{
	
}

$this->query_string = "SELECT ".implode(" , ",$select_list)." FROM ".implode(" , ",$from_list)." WHERE ";

$this->execute();

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
