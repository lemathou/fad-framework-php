<?php

define("ADMIN_LOAD",false);
define("SITE_LANG",SITE_LANG_DEFAULT);
define("SITE_LANG_ID",SITE_LANG_DEFAULT_ID);
define("REDIRECT",false);

include PATH_INCLUDE."/header.inc.php";

// Démarrage de la session
session_start();
// Rafraichissement du login
login()->refresh();

datamodel();

function action()
{

foreach ($_GET as $i=>$j)
	$_POST[$i] = $j;

// Datamodel
if (!isset($_POST["datamodel"]) || !($datamodel=datamodel($_POST["datamodel"])))
	die("[]\n");

if (!isset($_POST["params"]) || !is_array($_POST["params"]))
	$_POST["params"] = array();

if (!isset($_POST["order"]) || !is_array($_POST["order"]))
	$_POST["order"] = array();

if (isset($_POST["q"]))
{
	if (isset($_POST["type"]) && $_POST["type"] == "fulltext")
	{
		$_POST["params"][] = array("value"=>$_POST["q"], "type"=>"fulltext");
	}
	else
	{
		$_POST["params"][] = array("value"=>$_POST["q"]);
	}
}
foreach($_POST["params"] as &$param)
{
	if (isset($param["type"]) && $param["type"]=="fulltext")
	{
		if (!count($_POST["order"]))
			$_POST["order"] = array("relevance"=>"desc");
	}
}

if (!isset($_POST["fields"]))
	$_POST["fields"] = array();

echo $datamodel->json_query($_POST["params"], $_POST["fields"], $_POST["order"], 10);

}

header("Content-type: text/html; charset=".SITE_CHARSET);
//header("Content-type: application/json; charset=".SITE_CHARSET);
action();

?>