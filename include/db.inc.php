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

if (DEBUG_GENTIME == true)
	gentime(__FILE__);

// Interface de base de donnée

/**
  * Interface décrivant la connexion et le choix de la db
  * Elle est reliée à la classe db_query qui gère les requêtes
  * 
  * Une fois db::connect() effectué avec succès, db::query() renvoie l'id
  * de connexion à l'instance de db_query générée.
  * 
  * L'initialisation est définitive une fois effectué avec succès,
  * sauf en accédant à l'objet en $GLOBALS.
  * 
  * De cette façon les requêtes sont des instances de db_query,
  * faisant toutes référence à la même instantce de db.
  * 
  * Si le besoin se fait de se connecter d'autres bases, c'est tout à fait possible.
  * 
  */
interface db_i
{

function __construct();

function connect();

function query($query_string);

function select($fields, $join, $where, $order, $groupby, $limit);

function insert($table, $fields);

function update($table, $fields, $where, $order, $limit);

function delete($table, $where, $order, $limit);

/*
 * Create a database table
 */
function table_create($tablename, $fields, $options=array());

/**
 * Génère un champ pour insertion / mise à jour
 * 
 * @param string $fieldname
 * @param array $field
 * @return string
 */
function field_struct($fieldname, $field);

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

// On effectue la requête
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


include PATH_INCLUDE."/db/".DB.".inc.php";


/**
 * Database access function
 */
function db($query=null)
{

if (!isset($GLOBALS["db"]))
{
	// TODO : Mettre les données de session de db() dans login()
	/*
	if (!isset($_SESSION["db"]))
		$_SESSION["db"] = new db();
	$GLOBALS["db"] = $_SESSION["db"];
	*/
	$GLOBALS["db"] = new db();
	if (DEBUG_GENTIME == true)
		gentime("retrieve db()");
}

if (is_string($query))
{
	return $GLOBALS["db"]->query($query);
}
else
{
	return $GLOBALS["db"];
}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__);

?>
