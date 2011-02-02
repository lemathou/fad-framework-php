<?

/**
  * $Id$
  * 
  * « Copyright 2008 Mathieu Moulin - iProspective - lemathou@free.fr »
  * 
  * This file is part of FTNGroupWare.
  * 
  */

// MySQLi

class db_mysqli extends db
{

var $connect_fct = "mysqli_connect";
var $select_db_fct = "mysqli_select_db";
var $query_fct = "mysqli_query";
var $fetch_row_fct = "mysqli_fetch_row";
var $fetch_array_fct = "mysqli_fetch_array";

}

$db = new db_mysqli(DB_BASE, array("host" => DB_HOST, "username" => DB_USERNAME, "password" => DB_PASSWORD, "port" => DB_PORT));
$db->connect();

?>
