<?

/**
  * $Id: classes.inc.php 59 2009-03-03 15:48:26Z mathieu $
  * 
  * Copyright 2008, 2010 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [begin]");

/**
 * The singleton abstract class, which cannot be extended because of the static problem in PHP 5.2,
 * so I may use it when the bug will be solved... but i'm not sure because I found a faster way to do the same...
 */
abstract class singleton
{

private static $instance;

private function __construct()
{
}

private function __clone()
{
}

public static function getInstance()
{

if (!isset(self::$instance) || !self::$instance)
{ 
	self::$instance = new self;
}
return self::$instance;

}

}

/**
 * This class permit to save in session only a few properties
 * of an object it is extended, and it works with protected and private ones.
 */
abstract class session_select
{

/**
 * Property list to be saved in session
 *
 * @var mixed[]
 */
private $serialize_list = array();
public $serialize_save_list = array();

public function __sleep($list=array())
{

$this->serialize_save_list = array();
foreach($list as $name)
{
	if (isset($this->{$name}))
		$this->serialize_save_list[$name] = $this->{$name};
}
return array("serialize_save_list");

}

public function __wakeup()
{

foreach ($this->serialize_save_list as $name => $value)
{
	$this->{$name} = $value;
}
$this->serialize_save_list = array();

}

}

/**
 * Database gestion
 * Default object data bank
 * @author mathieu
 */
abstract class gestion
{

protected $type = "";

protected $list = array();
protected $list_detail = array();
protected $list_name = array();

protected $info_list = array("name"); // Keep at least name !
protected $info_lang_list = array("label", "description"); // Keep at least label !
protected $info_save_list = array("name", "label");

protected $retrieve_all = false;

public function info_list()
{

return $this->info_list;

}
public function info_lang_list()
{

return $this->info_lang_list;

}

/*
 * Sauvegarde/Restauration de la session
 */
function __sleep()
{

if ($this->retrieve_all)
	return array("list_detail", "list");
else
	return array("list_detail");
}
function __wakeup()
{

foreach($this->list_detail as $id=>$info)
{
	if (isset($info["name"]))
		$this->list_name[$info["name"]] = $id;
}

}

function __construct()
{

$this->query_info();
$this->construct_more();

}
protected function construct_more()
{

// To be extended if needed !

}

/**
 * Query required info
 */
function query_info($retrieve_all=false)
{

$this->list = array();
$this->list_name = array();
$this->list_detail = array();

$query_fields = array("`t1`.`id`");
foreach($this->info_list as $field)
	$query_fields[] = "`t1`.`$field`";
foreach($this->info_lang_list as $field)
	$query_fields[] = "`t2`.`$field`";

$query_string = "SELECT ".implode(", ", $query_fields)." FROM `_$this->type` as t1 LEFT JOIN `_".$this->type."_lang` as t2 ON t1.`id`=t2.`id` AND t2.`lang_id`=".SITE_LANG_ID;
$query = db()->query($query_string);
while($info = $query->fetch_assoc())
{
	$this->list_detail[$info["id"]] = $info;
	$this->list_name[$info["name"]] = $info["id"];
}

$this->query_info_more();

if ($retrieve_all || $this->retrieve_all)
	$this->retrieve_all();

apc_store($this->type."_gestion", $this, APC_CACHE_GESTION_TTL);

}
protected function query_info_more()
{

// To be extended if needed !

}

function retrieve_all()
{

$type = $this->type;
$this->list = array();
foreach ($this->list_detail as $id=>$info)
{
	$this->list[$id] = new $type($id, false, $info);
}

}

/**
 * Returns an object using its ID
 * @param int $id
 */
function get($id)
{

if (isset($this->list[$id]))
{
	return $this->list[$id];
}
elseif (APC_CACHE && !$this->retrieve_all && ($object=apc_fetch($this->type."_$id")))
{
	return $this->list[$id] = $object;
}
elseif (isset($this->list_detail[$id]))
{
	$object = new $this->type($id, false, $this->list_detail[$id]);
	if (APC_CACHE && !$this->retrieve_all)
		apc_store($this->type."_$id", $object, APC_CACHE_GESTION_TTL);
	return $this->list[$id] = $object;
}
elseif (DEBUG_PERMISSION)
{
	trigger_error("Cannot create $this->type ID#$id");
}
else
{
	return null;
}

}
/**
 * Retrieve an object using its unique name
 * @param unknown_type $name
 */
function __get($name)
{

if (isset($this->list_name[$name]))
{
	return $this->get($this->list_name[$name]);
}
else
{
	return null;
}

}
function get_name($name)
{

return $this->__get($name);

}

/**
 * Returns if an object exists
 * @param int $id
 */
function exists($id)
{

return isset($this->list_detail[$id]);

}
/**
 * 
 * Returns if an object exists using its unique name
 * @param string $name
 */
function __isset($name)
{

return isset($this->list_name[$name]);

}
function exists_name($name)
{

return isset($this->list_name[$name]);

}

/**
 * Returns the list
 */
public function list_get()
{

return $this->list;

}
public function list_name_get($name=null)
{

if ($name)
	return $this->list_name[$name];
else
	return $this->list_name;

}
public function list_detail_get($id=null)
{

if ($id)
	return $this->list_detail[$id];
else
	return $this->list_detail;

}

/**
 * Delete an object
 * @param int $id
 */
public function del($id)
{

if (!login()->perm(6)) // TODO : send email to admin
	die("ONLY ADMIN CAN DELETE DATAMODEL");

if (isset($this->list_detail[$id]))
{
	db()->query("DELETE FROM `_$this->type` WHERE `id`='$id'");
	db()->query("DELETE FROM `_".$this->type."_lang` WHERE `id`='$id'");
	$this->del_more($id);
	$this->query_info();
	return true;
}
else
{
	return false;
}

}
protected function del_more($id)
{

// To be extended if needed !

}

/**
 * Add an object
 * @param array $infos
 */
public function add($infos)
{

if (!login()->perm(6))
	die("ONLY ADMIN CAN ADD $this->type");

$query_fields = array();
$query_values = array();
$query_fields_lang = array();
$query_values_lang = array();

if (!is_array($infos))
	die();
foreach ($this->info_list as $name)
{
	if (!isset($infos[$name]) || !is_string($infos[$name]))
		die();
	else
	{
		$query_fields[] = "`$name`";
		$query_values[] = "`".db()->string_escape($infos[$name])."`";
	}
}
foreach ($this->info_lang_list as $name)
{
	if (!isset($infos[$name]))
		die();
	else
	{
		$query_fields_lang[] = "`$name`";
		$query_values_lang[] = "`".db()->string_escape($infos[$name])."`";
	}
}

db()->query("INSERT INTO `_".$this->type."` (".implode(", ", $query_fields).") VALUES (".implode(", ", $query_values).")");
$id = db()->last_id();
db()->query("INSERT INTO `_".$this->type."_lang` (`id`, `lang_id`, ".implode(", ", $query_fields_lang).") VALUES ('$id', '".SITE_LANG_ID."', ".implode(", ", $query_values_lang).")");

$this->add_more($id, $infos);

$this->query_infos();

if (APC_CACHE)
	apc_store($this->type."_gestion", $this, APC_CACHE_GESTION_TTL);

}
/**
 * Specific fields for an object
 * @param integer $id
 * @param array $infos
 */
protected function add_more($id, $infos)
{

// To be extended if needed !

}

/**
 * Display a list
 * @param array params : filtering parameters
 * @param array field_list : fields to display (automatically adds id and name)
 */
public function table_list($params=array(), $field_list=array())
{



?>
<table width="100%" cellspacing="1" border="1" cellpadding="1">
<tr style="font-weight:bold;">
	<td>[id] name</td>
<?
foreach($field_list as $name)
	echo "<td>$name</td>\n";
?>
</tr>
<?
foreach ($this->list_detail as $info)
{

echo "<tr>\n";
	print "<td><a href=\"?id=$info[id]\">[$info[id]] $info[name]</a></td>\n";
foreach($field_list as $name)
	echo "<td>".$info[$name]."</td>\n";
echo "</tr>\n";

}
?>
</table>
<?

}

}

/**
 * Default object type
 */
abstract class object_gestion
{

protected $_type = "";

protected $id=0;
protected $name="";
protected $label="";
protected $description="";

public function __construct($id, $query=true, $infos=array())
{

$type = $this->_type;
$this->id = $id;

foreach ($infos as $name=>$value)
	if (isset($this->{$name}))
		$this->{$name} = $value;

$this->construct_more($infos);

if ($query)
	$this->query_info();

}
protected function construct_more($infos)
{

// To be extended if needed !

}

/**
 * Query required info
 */
function query_info()
{

$type = $this->_type;
$info_list = $type()->info_list();
$info_lang_list = $type()->info_lang_list();

$query_fields = array("`t1`.`id`");
foreach($info_list as $field)
	$query_fields[] = "`t1`.`$field`";
foreach($info_lang_list as $field)
	$query_fields[] = "`t2`.`$field`";

$query_string = "SELECT ".implode(", ", $query_fields)." FROM `_$type` as t1 LEFT JOIN `_".$type."_lang` as t2 ON t1.`id`=t2.`id` AND t2.`lang_id`='".SITE_LANG_ID."' WHERE t1.id='$this->id'";
$query = db()->query($query_string);
while($infos = $query->fetch_assoc())
{
	foreach($infos as $name=>$value)
		$this->{$name} = $value;
}

$this->query_info_more();

}
protected function query_info_more()
{

// To be extended if needed

}

/**
 * Update the object
 * @param array $infos
 */
public function update($infos)
{

if (!is_array($infos))
	$infos = array();
$type = $this->_type;
$info_list = $type()->info_list();
$info_lang_list = $type()->info_lang_list();

$query_info = array();
$query_info_lang = array();
foreach ($info_list as $name)
	if (isset($infos[$name]))
		$query_info[] = "`$name`='".db()->escape_string($infos[$name])."'";
foreach ($info_lang_list as $name)
	if (isset($infos[$name]))
		$query_info_lang[] = "`$name`='".db()->escape_string($infos[$name])."'";

if (count($query_info))
	db()->query("UPDATE `_$type` SET ".implode(", ",$query_info)." WHERE `id`='$this->id'");
if (count($query_info_lang))
	db()->query("UPDATE `_".$type."_lang` SET ".implode(", ",$query_info_lang)." WHERE `id`='$this->id' AND `lang_id`='".SITE_LANG_ID."'");

$this->update_more($infos);

$this->query_info();
$type()->query_info();

if (APC_CACHE)
{
	if (defined(strtoupper($type."_AUTOLOADALL")))
		apc_store($type."_gestion", $type(), APC_CACHE_GESTION_TTL);
	apc_store($type."_$this->id", $this, APC_CACHE_GESTION_TTL);
}

}
protected function update_more($infos)
{

// To be extended if needed

}

function __tostring()
{

return "$this->name : $this->description";

}
public function id()
{

return $this->id;

}
public function name()
{

return $this->name;

}
public function label()
{

return $this->label;

}
public function info($name)
{

if (isset($this->{$name}))
	return $this->{$name};

}

}

/**
 * Page listing
 *
 * @author mathieu
 */
class page_listing
{

// Number of records
protected $nb = 0;
// List of avalaible number of records per page
protected $page_nb_list = array( 10 );
// default number of records per page
protected $page_nb_default = 10;
// Url
protected $url = "";
// Url page_nb param
protected $url_page_nb_param = "page";
// Url page param
protected $url_page_param = "";
// Default page
protected $page_default = 0;

// Max number of pages
protected $page_max = 0;

// Current number of records per page
protected $page_nb = 0;
// Current displayed page
protected $page = 0;

function __construct($nb, $page_nb_list=array(10), $page_nb_default=10, $page_default=1, $url="")
{

if (!is_numeric($nb) || $nb < 0)
	$this->nb = 0;
else
	$this->nb = (int)$nb;

$this->page_nb_list_set($page_nb_list);
$this->page_nb_default_set($page_nb_default);
$this->page_nb_set($page_nb_default);
$this->page_default_set($page_default);
$this->page_set($page_default);
$this->url_set($url);

}

// Setter
function page_nb_list_set($page_nb_list)
{

if (!is_array($page_nb_list))
	$this->page_nb_list = array( 10 );
else
{
	$this->page_nb_list = array();
	foreach ($page_nb_list as $i=>$j)
		if (is_numeric($j) || $j >= 1)
			$this->page_nb_list[] = (int)$j;
	if (count($this->page_nb_list) == 0)
		$this->page_nb_list[] = 10;
}

}

// Setter
function page_nb_default_set($page_nb_default)
{

if (is_numeric($page_nb_default) && in_array($page_nb_default, $this->page_nb_list))
	$this->$page_nb_default = $page_nb_default;
else
	$this->$page_nb_default = $this->page_nb_list[0];

}

// Setter
function page_default_set($page_default)
{

if (!is_numeric($page_default) || $page_default<0 || $page_default>$this->page_max)
	$this->page_default = 0;
else
	$this->page_default = (int)$page_default;

}

// Setter
function url_set($url)
{

$this->url = (string)$url;

}

// Setter
function page_nb_set($page_nb)
{

if (is_numeric($page_nb) && in_array($page_nb, $this->page_nb_list))
	$this->page_nb = $page_nb;
else
	$this->page_nb = $this->$page_nb_default;

$this->page_max = ceil($this->nb/$this->page_nb);

}

// Page Setter
function page_set($page)
{

if (!is_numeric($page) || $page < 0)
	$this->page = $this->page_default;
elseif ($page > $this->page_max)
	$this->page = $this->page_max;
else
	$this->page = (int)$page;

}

function page_list($page=null)
{

if ($page !== null)
	$this->page_set($page);

// page_min + 4 < page < page_max - 4
if ($this->page > 5 && ($this->page + 4) < $this->page_max)
{
	$page_list = array(1,"");
	for ($i=$this->page-2; $i<=$this->page+2; $i++)
		$page_list[] = $i;
	$page_list[] = "";
	$page_list[] = $this->page_max;
}
// page_min + 4 < page_max - 4 <= page
elseif ($this->page > 5 && $this->page_max > 10)
{
	$page_list = array(1,"");
	for ($i=$this->page-2; $i<=$this->page_max; $i++)
		$page_list[] = $i;
}
// page <= page_min + 4 < page_max - 4
elseif (($this->page+4) < $this->page_max && $this->page_max > 10)
{
	$page_list = array();
	for ($i=1; $i<=($this->page+2); $i++)
		$page_list[] = $i;
	$page_list[] = "";
	$page_list[] = $this->page_max;
}
// page_min <= page <= page_max <= 10
else
{
	$page_list = array();
	for ($i=1; $i<=$this->page_max; $i++)
		$page_list[] = $i;
}

return $page_list;

}

function link_list($page=null)
{

$page_list = $this->page_list($page);

$link_list = array();

if (is_numeric(strpos($this->url, "?")))
	$url = $this->url."&";
else
	$url = $this->url."?";

foreach ($page_list as $i)
{
	if (!$i)
		$link_list[] = "...";
	elseif ($this->page == $i)
		$link_list[] = "<span class=\"selected\">$i</span>";
	else
		$link_list[] = "<a href=\"".$url."page_nb=$this->page_nb&page=$i\">$i</a>";
}

return $link_list;

}

function nb_start()
{

if ($this->page > 0)
	return ($this->page - 1) * $this->page_nb;
else
	return 0;

}

function nb_end()
{

if ($this->page > 0)
	return min($this->page*$this->page_nb - 1, $this->nb-1);
else
	return -1;

}

function __get($name)
{

$list = array("page", "page_default", "page_nb", "page_max");

if (in_array($name, $list))
	return $this->{$name};

}

}

/**
 * String manipulation
 *
 * @author mathieu
 */
class text
{

const ACCENT = "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿ";
const NOACCENT = "AAAAAAACEEEEIIIIDNOOOOOOUUUUYBsaaaaaaaceeeeiiiidnoooooouuuyyby";

/**
 * Returns a string without any special char, with "-" in place of " ", perfectly designed for url's ;-)
 */
static function rewrite_ref($string)
{

$reecriture=strtr(trim(utf8_decode($string)), utf8_decode(self::ACCENT), self::NOACCENT);
$url=preg_replace('/[^0-9a-zA-Z]/', ' ', $reecriture);
$url=preg_replace('/ +/', '-', trim($url));
return $url;

}

/**
 * Protects PHP scripts while including string.
 */
function php_protect($string)
{

return str_replace( array("<?", "?>"), array("&lt;?", "&gt;?"), $string);

}

/**
 * Convert a string so it is displayed "as-it", without HTML
 */
function html_protect($string)
{

return htmlspecialchars(htmlspecialchars_decode($string));

}

/**
 * Convert a string so it is displayed "as-it", with most entities as possible
 */
function html_convert($string)
{

return html_entity_encode(html_entity_decode($string));

}

// nl2br() : met des <br /> là où il y a des sauts de ligne !
// html_entity_decode()

/**
 * Retrieve only keywords from a sentense
 */
function keyword_extract($string)
{

$list = array
(
// * Préposition
	// Cause
	"à cause de",
	"à la suite de",
	"en raison de",
	"grâce à",
	"du fait de",
	// Conséquence ou but
	"au point de",
	"de peur de",
	"assez... pour",
	"assez",
	"pour",
	"afin de",
	"en vue de",
	// Addition
	"outre",
	"en plus de",
	"en sus de",
	// Concession ou opposition
	"malgré",
	"en dépit de",
	"loin de",
	"contre",
	"au contraire de",
	"au lieu de",
	// Hypothèse
	"en cas de",
// * Conjonctions de coordination et adverbes
	// Cause
	"car",
	"en effet",
	// Conséquence ou but
	"de là",
	"d’où",
	"donc",
	"aussi",
	"par conséquent",
	"en conséquence",
	"c’est pourquoi",
	"ainsi",
	"dès lors",
	// Addition
	"et",
	"en plus",
	"de plus",
	"en outre",
	"par ailleurs",
	"ensuite",
	"d’une part... d’autre part",
	"aussi",
	"également",
	// Concession ou opposition
	"mais",
	"or",
	"néanmoins",
	"cependant",
	"pourtant",
	"toutefois",
	//"au contraire",
	"inversement",
	"en revanche",
// * Conjonctions de subordination
	// Cause
	"parce que",
	"puisque",
	"comme",
	"étant donné que",
	// Conséquence ou but
	"pour que",
	"afin que",
	"si bien que",
	"de façon que",
	"de sorte que",
	"dès lors que",
	"tellement que",
	"tant que",
	"au point que",
	// Addition
	"outre que",
	"sans compter que",
	"et",
	// Concession ou opposition
	"bien que",
	"quoique",
	"même si",
	"alors que",
	"tandis que",
	"tout... que...",
	"quelque... que...",
	// Hypothèse
	"si",
	"au cas où",
	"pour le cas où",
	"selon que",
	"suivant que",
// * Verbes et locutions verbales
	// Cause
	//"venir de",
	//"découler de",
	//"résulter de",
	//"provenir",
	// Conséquence ou but
	//"causer",
	//"impliquer",
	//"entraîner",
	//"provoquer",
	//"susciter",
	//etc.
	// Addition
	//"s’ajouter",
	"s",
	//"marier",
	//etc.
	// Concession ou opposition
	//"s’opposer à",
	"à",
	//"contredire",
	//"avoir beau (+ verbe)",
	//"réfuter",
	//etc.
	// Hypothèse
	//"à supposer que",
	"que",
// * Divers
	// Articles
	"un",
	"une",
	"des",
	"le",
	"la",
	"les",
	"je",
	"tu",
	"il",
	"elle",
	"nous",
	"vous",
	"ils",
	"ce",
	"cette",
	"ces",
	"mon",
	"ton",
	"son",
	"leur",
	"leurs",
	// Autre
	"l",
	"n",
	"a",
	"t",
	"d",
	"de",
	"de la",
	"du",
	"au",
	"avec",
	"qui",
	"qu",
	"y",
	"dans",
	"entre",
	"ne"
);

$l = array();
foreach ($list as $t)
{
	$t = self::rewrite_ref($t);
	$t2 = explode("-", $t);
	foreach ($t2 as $t3) if (!in_array($t3, $l))
		$l[] = $t3;
}

print_r($l);

$s = explode("-", self::rewrite_ref($string));
foreach ($s as $nb=>$s2)
{
	if (in_array($s2, $l))
		unset($s[$nb]);
}
return implode("-",$s);

}


}

/**
 * Global class to send emails.
 * 
 * @author mathieu
 */
class mail
{

/**
 * Send emails, adding usefull header infos...
 * 
 * @param unknown_type $to
 * @param unknown_type $subject
 * @param unknown_type $message
 * @param unknown_type $headers
 */
static function common($to, $subject, $message, $headers="")
{

mail($to, $subject, $message, "X-Originating-IP: ".$_SERVER["REMOTE_ADDR"]."\r\nX-WebSite: ".SITE_DOMAIN."\r\nX-WebSite-AccountID: ".login()->id()."\r\n$headers");

}

/**
 * Send text/plain email
 * 
 * @param unknown_type $to
 * @param unknown_type $subject
 * @param unknown_type $message
 * @param unknown_type $headers
 */
static function text($to, $subject, $message, $headers="")
{

self::common($to, $subject, imap_8bit($message), "Content-Type: text/plain; charset=\"".SITE_CHARSET."\"\r\nContent-Transfer-Encoding: quoted-printable\r\n$headers");

}

/**
 * Send text/html email
 * 
 * @param unknown_type $to
 * @param unknown_type $subject
 * @param unknown_type $message_html
 * @param unknown_type $headers
 */
static function html($to, $subject, $message_html, $headers="")
{

$boundary = "-----=".md5(uniqid(rand()));

$message = "Ceci est un message au format MIME 1.0 multipart/alternative.\r\n";

$message .= "--$boundary\r\n";
$message .= "Content-Type: text/html; charset=\"".SITE_CHARSET."\"\r\n";
$message .= "Content-Transfer-Encoding: quoted-printable\r\n";
$message .= "\r\n";
$message .= wordwrap(imap_8bit($message_html))."\r\n";
$message .= "\r\n";

$message .= "--$boundary\r\n";
$message .= "Content-Type: text/plain; charset=\"".SITE_CHARSET."\"\r\n";
$message .= "Content-Transfer-Encoding: quoted-printable\r\n";
$message .= "\r\n";
$message .= wordwrap(imap_8bit(strip_tags($message_html)))."\r\n";
$message .= "\r\n";

$message .= "\r\n--$boundary--\r\n";

self::common($to, $subject, $message, "MIME-Version: 1.0\r\nContent-Type: multipart/alternative; charset=\"".SITE_CHARSET."\"; boundary=\"$boundary\"\r\n$headers");

}

}

/**
 * Image manipulation
 */
class img
{

static function resize($filename, $options=array())
{

if (strtolower(substr($filename, -3)) == "png")
{
	$read_fct = "imagecreatefrompng";
	$save_fct = "imagepng";
}
elseif (strtolower(substr($filename, -3)) == "jpg")
{
	$read_fct = "imagecreatefromjpeg";
	$save_fct = "imagejpeg";
}
elseif (strtolower(substr($filename, -3)) == "gif")
{
	$read_fct = "imagecreatefromgif";
	$save_fct = "imagegif";
}

$img_r = $read_fct($filename);
list($width, $height, $type, $attr) = getimagesize($filename);

// Maxwidth
if (isset($options["maxwidth"]) && is_numeric($maxwidth=$options["maxwidth"]) && ($width > $maxwidth))
{
	$maxheight = round($height*$maxwidth/$width);
	$dst_r = ImageCreateTrueColor($maxwidth, $maxheight);
	echo "<p>Image Retaillée : Largeur de ".$maxwidth."px et hauteur de ".$maxheight."px.</p>";
	imagecopyresampled($dst_r, $img_r, 0, 0, 0, 0, $maxwidth, $maxheight, $width, $height);
	$save_fct($dst_r, $filename);
}

// Width + Height
if (isset($options["width"]) && is_numeric($width2=$options["width"]) && isset($options["height"]) && is_numeric($height2=$options["height"]))
{
	$maxheight = round($height*$maxwidth/$width);
	$dst_r = ImageCreateTrueColor($maxwidth, $maxheight);
	echo "<p>Image Retaillée : Largeur de ".$maxwidth."px et hauteur de ".$maxheight."px.</p>";
	imagecopyresampled($dst_r, $img_r, 0, 0, 0, 0, $maxwidth, $maxheight, $width, $height);
	$save_fct($dst_r, $filename);
}

}

}

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [end]");

?>
