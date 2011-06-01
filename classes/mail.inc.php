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
	gentime(__FILE__." [begin]");


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
// suppress header
$message_text = preg_replace("/\<head\>([^%]*)\<\/head\>/", "", $message_html);
// convert <br />
// Warning ! No xhtml but html in emails <br /> => <br>
$message_text = str_replace("\r\n<br />", "\r\n", $message_text);
$message_text = str_replace("<br />\r\n", "\r\n", $message_text);
$message_text = str_replace("<br />", "\r\n", $message_text);
// other tags
$message_text = strip_tags($message_text);
// unify spaces
$message_text = preg_replace("/([[:space:]]+)/", " ", $message_text);
// encode
$message_text = wordwrap(imap_8bit($message_text));
$message .= $message_text."\r\n";
$message .= "\r\n";

$message .= "\r\n--$boundary--\r\n";

self::common($to, $subject, $message, "MIME-Version: 1.0\r\nContent-Type: multipart/alternative; charset=\"".SITE_CHARSET."\"; boundary=\"$boundary\"\r\n$headers");

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
