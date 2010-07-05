<?

/**
  * $Id: login.inc.php 76 2009-10-15 09:24:20Z mathieu $
  * 
  * Copyright 2008 Mathieu Moulin - iProspective - lemathou@free.fr
  * 
  * This file is part of FTNGroupWare.
  * 
  */

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [begin]");

/**
 * Compte utilisateur
 * 
 */
class account
{

protected $id = 0;
protected $email = "";
protected $password_crypt = "";

protected $type;
protected $perm_list = array();

protected $actif = "";

protected $lang_id = SITE_LANG_DEFAULT_ID;
protected $sid = "";

protected $create_datetime = "";
protected $update_datetime = "";

public function __construct($id, $load=true, $infos=array())
{

$query = db()->query("SELECT * FROM `_account` WHERE `id` = '".db()->string_escape($id)."'");
if ($query->num_rows())
{
	$account = $query->fetch_assoc($query);
	foreach ($account as $i=>$j)
		$this->{$i} = $j;
}

}

function __get($name)
{

if (isset($this->{$name}))
	return $this->{$name};
else
{
	trigger_error("ACCOUNT(ID#$this->id)->__get('$name') : does not exists  ");
	return null;
}

}

function __set($name, $value)
{

if (isset($this->{$name}))
{
	$this->{$name} = $value;
	return true;
}
else
{
	return false;
}

}

function query()
{

$query = db()->query("SELECT * FROM `account` WHERE `id`='$this->id'");
if ($infos = $query->fetch_assoc())
{
	foreach($infos as $i=>$j)
		$this->{$i} = $j;
}

}

function get($infos)
{

}

function update($infos)
{

}

function update_form()
{

}

function password_create()
{

$liste = "ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789";
$password= "";
while (strlen($password) < 8)
{
	$password .= $liste[rand(0,34)];
}
return $password;
	
}

function exists($email)
{

return db()->query("SELECT `id` FROM `_account` WHERE `email` LIKE '".db()->string_escape($email)."'")->num_rows();

}

}

// Login

class login extends account
{

// Statistique
public $page_count=0;

/*
0 = normal
1 = invalid username
2 = invalid sid
3 = retrieve account problem
4 = invalid password
5 = inactif
*/
protected $disconnect_reason = 0;

// paramètres navigateur
protected $sid="";
protected $os="???";

public function __construct()
{

$this->sid = session_id();
$this->client_info_query();

//$this->perm_query();
$this->perm_list = array( 1, 3 );

}

public function refresh()
{

$this->disconnect_reason = 0;

if (isset($_GET["_session_restart"]))
{
	$_SESSION = array();
	session_regenerate_id();
	session_destroy();
	header("Location: ".$_SERVER["REDIRECT_URL"]);
}
elseif (isset($_GET["_session_kill"]))
{
	$_SESSION = array();
	session_regenerate_id();
	session_destroy();
	die("Session successfully Killed. <a href=\"/".SITE_BASEPATH.SITE_LANG_DEFAULT."/\">Back to Home page</a>");
}
elseif (isset($_POST["_login"]["username"]) && isset($_POST["_login"]["password_crypt"])) // Login par formulaire
{
	$this->connect($_POST["_login"]["username"], $_POST["_login"]["password_crypt"], (isset($_POST["_login"]["permanent"]))?array("permanent"):array());
}
elseif (isset($_POST["_login"]["disconnect"])) // déconnexion
{
	$this->disconnect();
}
elseif (!$this->id && isset($_COOKIE["sid"]) && is_string($_COOKIE["sid"]) && strlen($_COOKIE["sid"]) == "64")
{
	$this->connect_sid($_COOKIE["sid"]);
}

}

protected function connect($email, $password_crypt, $options=array())
{

$query = db()->query("SELECT id , actif , password , lang_id , email FROM _account WHERE email LIKE '".db()->string_escape($email)."' ");

if (!$query->num_rows() || !($account = $query->fetch_assoc()))
{
	if (DEBUG_LOGIN)
		echo "<p>EMAIL NOT FOUND</p>\n";
	$this->disconnect(1);
	return false;
}
elseif (!$account["actif"])
{
	if (DEBUG_LOGIN)
		echo "<p>INACTIVE ACCOUNT</p>\n";
	$this->disconnect(5);
	return false;
}
elseif (md5($this->sid."".$account["password"]) != $password_crypt)
{
	if (DEBUG_LOGIN)
		echo "<p>PASSWORD ERROR</p>\n";
	$this->disconnect(4);
	return false;
}
else
{
	if (DEBUG_LOGIN)
		echo "<p>LOGIN OK</p>\n";
	$this->id = $account["id"];
	$this->lang_id = $account["lang_id"];
	$this->email = $account["email"];
	$this->perm_query();
	// Memorize
	if (in_array("permanent", $options))
	{
		db()->query("UPDATE _account SET `sid`='".($sid=md5(rand()))."' WHERE id='$this->id' ");
		setcookie("sid", $sid, time()+60*60*24*30); // Durée 30 jours
	}
	return true;
}

}

protected function connect_sid($sid)
{

$query = db()->query("SELECT id , username , actif , password , lang_id , email FROM _account WHERE sid LIKE '".db()->string_escape($sid)."' ");

if (!$query->num_rows())
{
	$this->disconnect(1);
	return false;
}
elseif (!($account = $query->fetch_assoc()))
{
	$this->disconnect(2);
	return false;
}
elseif (!$account["actif"])
{
	$this->disconnect(5);
	return false;
}
else
{
	$this->id = $account["id"];
	$this->username = $account["id"];
	$this->lang_id = $account["lang_id"];
	$this->email = $account["email"];
	$this->perm_query();
	return true;
}

}

protected function reconnect()
{

// VOIR si ça vaut le coup de reconsidérer les permissions.
// Au pire pourquoi pas vérifier si elles n'ont pas changé !

//$this->perm_query();
//databank()->query();

}

protected function disconnect($disconnect_reason=0)
{

// Destroy cookie references
if ($this->id)
	db()->query("UPDATE _account SET `sid`='' WHERE id='$this->id' ");
if (isset($_COOKIE["sid"]))
	setcookie ("sid", "", time()-3600);

$this->id = 0;

$this->type = "";
$this->username = "";
$this->contact_id = 0;
$this->lang_id = 0;
$this->email = "";

$this->perm_query();

$this->disconnect_reason = $disconnect_reason;

}

private function client_info_query()
{

// Liste mobiles : (iPhone|BlackBerry|Android|HTC|LG|MOT|Nokia|Palm|SAMSUNG|SonyEricsson)

if (strstr($_SERVER["HTTP_USER_AGENT"],"Windows") !== FALSE)
	$this->os = "WIN";
elseif (strstr($_SERVER["HTTP_USER_AGENT"],"Mac") !== FALSE)
	$this->os = "MAC";
elseif (strstr($_SERVER["HTTP_USER_AGENT"],"Linux") !== FALSE)
	$this->os = "LIN";
elseif (strstr($_SERVER["HTTP_USER_AGENT"],"BSD") !== FALSE)
	$this->os = "BSD";
elseif (strstr($_SERVER["HTTP_USER_AGENT"],"iPhone") !== FALSE)
	$this->os = "IPH";
else
	$this->os = "???";

}

function language_query()
{



}

function HttpAcceptLanguage($str=NULL)
{
	global $lang_list;
	// getting http instruction if not provided
	$str=$str?$str:$_SERVER['HTTP_ACCEPT_LANGUAGE'];
	// exploding accepted languages 
	$langs=explode(',',$str);
	// creating output list
	$accepted=array();
	foreach ($langs as $lang)
	{
		// parsing language preference instructions
		// 2_digit_code[-longer_code][;q=coefficient]
		ereg('([a-z]{1,2})(-([a-z0-9]+))?(;q=([0-9\.]+))?',$lang,$found);
		// 2 digit lang code
		$code=$found[1];
		// lang code complement
		$morecode=$found[3];
		if (isset($lang_list[$code]) && !in_array($code,$accepted))
			$accepted[sprintf('%3.1f',$found[5]?$found[5]:'1')]=$code;
		elseif (isset($lang_list[$morecode]) && !in_array($code,$accepted))
			$accepted[sprintf('%3.1f',$found[5]?$found[5]:'1')]=$morecode;
	}
	// sorting the list by coefficient desc
	ksort($accepted);
	if (count($accepted)>0)
		return array_pop($accepted);
	else
		return "";
}

private function perm_query()
{

// Il faut faire en sorte qu'aucune permission par défaut ne soit donnée !
// Don cà revoir, mais il faut tenir compte de l'utilisateur non connecté...
// Comment gérer ses permissions ??

// All users
$this->perm_list = array( 3 );
// Registered users
if ($this->id)
	$this->perm_list[] = 4;
// Anonymous users
else
	$this->perm_list[] = 1;

// Special perms
$query = db()->query("SELECT `perm_id` FROM `_account_perm_ref` WHERE `account_id` = '".$this->id."'");
while (list($perm_id) = $query->fetch_row())
	$this->perm_list[] = $perm_id;

// Régénération du menu puisque nouvelles permissions
page()->query();

// Régénération databank
//	databank()->query();

}

public function perm($perm)
{

return in_array($perm, $this->perm_list);

}
public function perm_list()
{

return $this->perm_list;

}

public function id()
{

return $this->id;

}

public function info_get($info)
{

if (isset($this->{$info}))
	return $this->{$info};
else
{
	error()->add("login", "Undefined variable '$info'");
	return false;
}

}

public function lang()
{

return $this->lang_id;

}

}

/**
 * Accès à l'objet login (unique) 
 */
function login()
{

if (!isset($GLOBALS["login"]))
{
	$GLOBALS["login"] = $_SESSION["login"] = new login();
}

return $GLOBALS["login"];

}

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [end]");

?>
