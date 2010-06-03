<?

/**
  * $Id: db.inc.php 59 2009-03-03 15:48:26Z mathieu $
  * 
  * Copyright 2008 Mathieu Moulin - iProspective - lemathou@free.fr
  * 
  * This file is part of FTNGroupWare.
  * 
  */

// Interface de base de donnée

interface db_i
{

function __construct($infos=array(), $options=array());

function connect();

function query($query_string);

function select($fields, $join, $where, $order, $groupby, $limit);

function insert($table, $fields);

function update($table, $fields, $where, $order, $limit);

function delete($table, $where, $order, $limit);

function queries();

function time();

function error();

function error_log();

function last_id();

function __destruct();

function __sleep();

function __wakeup();

}

// Interface de requête de base de donnée

interface db_query_i
{

// On effectue la requ�te
function __construct($query_string, $db_id);

function num_rows();

function fetch($type="row");

function fetch_all($type="row", $return="list");

function fetch_row();

function fetch_array();

function fetch_assoc();

function affected_rows();

function last_id();

function free();

}

?>
