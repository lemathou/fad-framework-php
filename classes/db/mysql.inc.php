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

/**
  * MySQL abstraction layer
  */
class _db implements _db_i
{

protected $id = null;

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

return array("queries_total", "fetch_results_total", "time_total");

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
function __construct()
{

$this->connect();

}

function connect()
{

// On ne se connecte qu'une seule fois
if (DB_PERSISTANT == true)
	$this->id = mysql_pconnect(DB_HOST, DB_USERNAME, DB_PASSWORD);
else
	$this->id = mysql_connect(DB_HOST, DB_USERNAME, DB_PASSWORD);

// Choix base de donnée
mysql_select_db(DB_BASE, $this->id);

// Encoding
if (DB_CHARSET == "UTF8")
{
	mysql_query("SET NAMES 'UTF8'");
}

}

public function query($query_string)
{

$this->queries++;
$this->query_list[] = $query_string;
return new _db_query($query_string, $this->id);

}

public function select($fields, $join, $where, $order, $groupby, $limit)
{

$this->queries++;
return new _db_query_select($select, $where, $order, $limit, $this->id);

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
	return new _db_query("INSERT INTO `".$this->string_escape($table)."` ( `".implode("` , `",$fieldnames)."` ) VALUES ( '".implode("' , '",$fields)."' )", $this->id);
}

}

public function update($table, $update, $where="", $order="", $limit="")
{

$this->queries++;
return new _db_query("UPDATE `".$this->string_escape($table)."` SET $update $where $order $limit", $this->id);

}

public function delete($table, $where, $order, $limit)
{

$this->queries++;
return new _db_query("DELETE FROM `".$this->string_escape($table)."` $where $order $limit", $this->id);

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

if (!is_string($tablename) || !$tablename)
	return false;
if (!is_array($fields))
	return false;

// Fields
foreach($fields as $fieldname=>$field)
{
	if (!empty($field["key"]))
		$key_list[] = "`".$this->string_escape($fieldname)."`";
	$fieldstruct[] = $this->field_struct($fieldname, $field);
}
// Primary Key
if (count($key_list))
	$fieldstruct[] = "PRIMARY KEY (".implode(", ",$key_list).")";
// Engine
if (isset($options["engine"]))
	$tableoption[] = "ENGINE = $options[engine]";
else
	$tableoption[] = "ENGINE = ".DB_ENGINE;
// Charset
if (isset($options["charset"]))
	$tableoption[] = "DEFAULT CHARSET = $options[charset]";
else
	$tableoption[] = "DEFAULT CHARSET = ".DB_CHARSET;

$query_string = "CREATE TABLE IF NOT EXISTS `".$this->string_escape($tablename)."` (".implode(", ", $fieldstruct).") ".implode(" ", $tableoption);
$this->query($query_string);
echo "<p>$query_string</p>\n";
//$this->query("ALTER TABLE `$tablename` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");

}

public function field_struct($fieldname, $field)
{

$field["null"] = empty($field["null"]) ? " NOT NULL" : " NULL";
$field["default"] = isset($field["default"]) ? " DEFAULT '".$this->string_escape($field["default"])."'" : "";

// http://dev.mysql.com/doc/refman/5.0/fr/silent-column-changes.html
switch ($field["type"])
{
	case ($field["type"]=="string" && !empty($field["size"]) && isset($field["autocomplete"])) :
		return "`".$this->string_escape($fieldname)."` CHAR($field[size])$field[null]$field[default]";
		break;
	case ($field["type"]=="string" && !empty($field["size"])) :
		return "`".$this->string_escape($fieldname)."` VARCHAR($field[size])$field[null]$field[default]";
		break;
	case "string" :
	case "text" :
	case "richtext" :
		return "`".$this->string_escape($fieldname)."` TEXT$field[null]$field[default]";
		break;
	case "integer" :
		if ($field["size"]>=11) // limit is 19 digits...
			$type = "BIGINT";
		elseif ($field["size"]>=5)
			$type = "INT";
		elseif ($field["size"]>=3) // TODO : not optimal... in many cases a tinyint should be OK with a length of 3
			$type = "SMALLINT";
		else
			$type = "TINYINT";
		return "`".$this->string_escape($fieldname)."` $type($field[size])".(empty($field["signed"])?" unsigned":"").$field["null"].$field["default"].(!empty($field["auto_increment"])?" auto_increment":"");
		break;
	case "float" :
		if ($field["precision"]>=24) // limit is 19 digits...
			$type = "DOUBLE";
		else
			$type = "FLOAT";
		return "`".$this->string_escape($fieldname)."` $type(".($field["size"]+$field["precision"]).",".$field["precision"].")".(empty($field["signed"])?" unsigned":"").$field["null"].$field["default"];
		break;
	case ($field["type"]=="datetime" && !empty($field["autoupdate"])) :
		return "`".$this->string_escape($fieldname)."` TIMESTAMP ON UPDATE CURRENT_TIMESTAMP$field[null] DEFAULT CURRENT_TIMESTAMP";
		break;
	case "date" :
	case "year" :
	case "time" :
	case "datetime" :
		return "`".$this->string_escape($fieldname)."` ".strtoupper($field["type"]).$field["null"].$field["default"];
		break;
	case "select" :
		return "`".$this->string_escape($fieldname)."` ENUM('".implode("' , '",$field["value_list"])."')$field[null]$field[default]";
		break;
	case "fromlist" :
		return "`".$this->string_escape($fieldname)."` SET('".implode("' , '",$field["value_list"])."')$field[null]$field[default]";
		break;
	case "boolean" :
		return "`".$this->string_escape($fieldname)."` BOOLEAN$field[null]$field[default]";
		break;
}

}

public function field_update($tablename, $fieldname_from, $fieldname_to, $field, $position="")
{

//echo "ALTER TABLE `$tablename` CHANGE `$fieldname_from` ".$this->field_struct($fieldname_to, $field)." $position";

$this->query("ALTER TABLE `".$this->string_escape($tablename)."` CHANGE `".$this->string_escape($fieldname_from)."` ".$this->field_struct($fieldname_to, $field)." $position"); 

}

public function field_create($tablename, $fieldname, $field, $position="")
{

$this->query("ALTER TABLE `".$this->string_escape($tablename)."` ADD ".$this->field_struct($fieldname, $field)." $position"); 

}

public function field_delete($tablename, $fieldname)
{
	
$this->query("ALTER TABLE `".$this->string_escape($tablename)."` DROP `".$this->string_escape($fieldname)."`"); 

}

/*
 * Delete a database table
 */
public function table_delete($tablename)
{

return $this->query("DROP TABLE `".$this->string_escape($tablename)."`");

}

/*
 * EMpty a database table
 */
public function table_empty($tablename)
{

return $this->query("TRUNCATE TABLE `".$this->string_escape($tablename)."`");

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
class _db_query implements _db_query_i
{

// server connection ID

protected $db_id = null; 
// server request ID
protected $id = null;
// query_string
protected $query_string = "";
// num_rows
protected $num_rows = null;

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

//var_dump($this);

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
{
	//echo "<p>$error</p>\n";
	$this->log_error($error);
}

//var_dump($this);

}

public function num_rows()
{

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

public function fetch_all_row($nb=0)
{

$return = array();
while ($row = $this->fetch_row())
	$return[] = $row[$nb];

return $return;

}

public function fetch_row()
{

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

$time1 = microtime(true);
if ($return = mysql_fetch_assoc($this->id))
	db()->fetch_results++;
$time2 = microtime(true);
db()->time += ($time2 - $time1);

if (LOG_DB_ERROR && mysql_error())
	$this->log_error();

return $return;

}

public function fetch_assoc()
{

$time1 = microtime(true);
if ($return = mysql_fetch_assoc($this->id))
	db()->fetch_results++;
$time2 = microtime(true);
db()->time += ($time2 - $time1);

if (LOG_DB_ERROR && mysql_error())
	$this->log_error();

return $return;

}

public function affected_rows()
{

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
class _db_query_select
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

?>
