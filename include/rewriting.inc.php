<?

/**
  * $Id: rewriting.inc.php 75 2009-08-24 15:12:38Z mathieu $
  * 
  * � Copyright 2008 Mathieu Moulin - iProspective - lemathou@free.fr �
  * 
  * This file is part of FTNGroupWare.
  * 
  */

//include "rewriting/".URL_REWRITING.".inc.php";

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [begin]");

function url($page_id=0, $options=array())
{

if (!menu()->exists($page_id))
	return SITE_BASEPATH."/"; // accueil
elseif (!is_array($options) || count($options) == 0)
	return SITE_BASEPATH."/".menu($page_id)->get("name").".html";
else
	return SITE_BASEPATH."/".menu($page_id)->get("name").".html?".implode("&amp;",$options);

}

function url_html($page_id, $options=array(), $text="", $lang="")
{

$url = SITE_BASEPATH."/";

if (SITE_MULTILANG && $lang)
	$url .= SITE_LANG."/";
elseif (SITE_MULTILANG)
	$url .= SITE_LANG."/";

if (is_array($options) && count($options) > 0)
{
	$opt_list = array();
	$opt2_list = array();
	foreach ($options as $i=>$j)
	{
		if (is_numeric($i))
			$opt2_list[] = "$j";
		else
			$opt_list[] = "$i=$j";
	}
	if (count($opt_list))
		$opt = "?".implode("&amp;",$opt_list);
	else
		$opt = "";
	if (count($opt2_list))
		$opt2 = ";".implode(",",$opt2_list);
	else
		$opt2 = "";
}
else
{
	$opt = "";
	$opt2 = "";
}

if (!menu()->exists($page_id))
	return $url;
elseif ($text)
	return $url.$text.";$page_id$opt2.html$opt";
else
	return $url.menu($page_id)->get("url").";$page_id$opt2.html$opt";

}

if (DEBUG_GENTIME ==  true)
	gentime(__FILE_." [end]");

?>