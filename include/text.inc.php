<?php

/**
  * $Id: text.inc.php 31 2011-01-24 05:52:20Z lemathoufou $
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
 * String manipulation
 *
 * @author mathieu
 */
class text
{

const ACCENT = "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿ";
const NOACCENT = "AAAAAAACEEEEIIIIDNOOOOOOUUUUYBsaaaaaaaceeeeiiiidnoooooouuuyyby";

function remove_accent($str)
{

$a = array('À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Æ', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ð', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'Ø', 'Ù', 'Ú', 'Û', 'Ü', 'Ý', 'ß', 'à', 'á', 'â', 'ã', 'ä', 'å', 'æ', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ø', 'ù', 'ú', 'û', 'ü', 'ý', 'ÿ', 'Ā', 'ā', 'Ă', 'ă', 'Ą', 'ą', 'Ć', 'ć', 'Ĉ', 'ĉ', 'Ċ', 'ċ', 'Č', 'č', 'Ď', 'ď', 'Đ', 'đ', 'Ē', 'ē', 'Ĕ', 'ĕ', 'Ė', 'ė', 'Ę', 'ę', 'Ě', 'ě', 'Ĝ', 'ĝ', 'Ğ', 'ğ', 'Ġ', 'ġ', 'Ģ', 'ģ', 'Ĥ', 'ĥ', 'Ħ', 'ħ', 'Ĩ', 'ĩ', 'Ī', 'ī', 'Ĭ', 'ĭ', 'Į', 'į', 'İ', 'ı', 'Ĳ', 'ĳ', 'Ĵ', 'ĵ', 'Ķ', 'ķ', 'Ĺ', 'ĺ', 'Ļ', 'ļ', 'Ľ', 'ľ', 'Ŀ', 'ŀ', 'Ł', 'ł', 'Ń', 'ń', 'Ņ', 'ņ', 'Ň', 'ň', 'ŉ', 'Ō', 'ō', 'Ŏ', 'ŏ', 'Ő', 'ő', 'Œ', 'œ', 'Ŕ', 'ŕ', 'Ŗ', 'ŗ', 'Ř', 'ř', 'Ś', 'ś', 'Ŝ', 'ŝ', 'Ş', 'ş', 'Š', 'š', 'Ţ', 'ţ', 'Ť', 'ť', 'Ŧ', 'ŧ', 'Ũ', 'ũ', 'Ū', 'ū', 'Ŭ', 'ŭ', 'Ů', 'ů', 'Ű', 'ű', 'Ų', 'ų', 'Ŵ', 'ŵ', 'Ŷ', 'ŷ', 'Ÿ', 'Ź', 'ź', 'Ż', 'ż', 'Ž', 'ž', 'ſ', 'ƒ', 'Ơ', 'ơ', 'Ư', 'ư', 'Ǎ', 'ǎ', 'Ǐ', 'ǐ', 'Ǒ', 'ǒ', 'Ǔ', 'ǔ', 'Ǖ', 'ǖ', 'Ǘ', 'ǘ', 'Ǚ', 'ǚ', 'Ǜ', 'ǜ', 'Ǻ', 'ǻ', 'Ǽ', 'ǽ', 'Ǿ', 'ǿ');
$b = array('A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'D', 'N', 'O', 'O', 'O', 'O', 'O', 'O', 'U', 'U', 'U', 'U', 'Y', 's', 'a', 'a', 'a', 'a', 'a', 'a', 'ae', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'y', 'y', 'A', 'a', 'A', 'a', 'A', 'a', 'C', 'c', 'C', 'c', 'C', 'c', 'C', 'c', 'D', 'd', 'D', 'd', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'E', 'e', 'G', 'g', 'G', 'g', 'G', 'g', 'G', 'g', 'H', 'h', 'H', 'h', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'I', 'i', 'IJ', 'ij', 'J', 'j', 'K', 'k', 'L', 'l', 'L', 'l', 'L', 'l', 'L', 'l', 'l', 'l', 'N', 'n', 'N', 'n', 'N', 'n', 'n', 'O', 'o', 'O', 'o', 'O', 'o', 'OE', 'oe', 'R', 'r', 'R', 'r', 'R', 'r', 'S', 's', 'S', 's', 'S', 's', 'S', 's', 'T', 't', 'T', 't', 'T', 't', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'W', 'w', 'Y', 'y', 'Y', 'Z', 'z', 'Z', 'z', 'Z', 'z', 's', 'f', 'O', 'o', 'U', 'u', 'A', 'a', 'I', 'i', 'O', 'o', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'U', 'u', 'A', 'a', 'AE', 'ae', 'O', 'o');
return str_replace($a, $b, $str);

} 

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


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
