<?php

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [begin]");

// Langues
$lang = array("fr"=>2);

if (substr($_SERVER["REDIRECT_URL"], -5) == ".html")
	$url = substr($_SERVER["REDIRECT_URL"], 1, -5);
else
	$url = $_SERVER["REDIRECT_URL"];
$url_e = explode("/",$url);
if (!$url_e[0])
        unset($url_e[0]);
$l = array_shift($url_e);

if (!$l || !isset($lang[$l]))
{
	define("SITE_LANG",SITE_LANG_DEFAULT);
	define("SITE_LANG_ID",SITE_LANG_DEFAULT_ID);
	define("REDIRECT",true);
}
else
{
	define("SITE_LANG",$l);
	define("SITE_LANG_ID",$lang[$l]);
	define("REDIRECT",false);
}

unset($l);

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [end]");

?>
