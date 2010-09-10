<?

/**
  * $Id: classes.inc.php 59 2009-03-03 15:48:26Z mathieu $
  * 
  * Copyright 2008, 2010 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */

if (DEBUG_GENTIME ==  true)
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

mail($to, $subject, $message, "X-Originating-IP: ".$_SERVER["REMOTE_ADDR"]."\r\nX-PHP-TopGones-AccountID: ".login()->id()."\r\n$headers");

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

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [end]");

?>