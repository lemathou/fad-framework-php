<?php

/**
  * $Id: _cache_clean.php 28 2011-01-17 07:50:38Z lemathoufou $
  * 
  * Copyright 2008-2011 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * http://sourceforge.net/projects/phpfadframework/
  * Licence : http://www.gnu.org/copyleft/gpl.html  GNU General Public License
  * 
  */

if (!defined("PATH_INCLUDE"))
	die("Config file not loaded");

function action()
{

$totalsize = 0;
$cleansize = 0;
$time = time();

$fl = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f");
foreach($fl as $i)
{
	$dp = opendir(PATH_CACHE."/$i");
	while($file=readdir($dp)) if (strpos($file, ".") === false && ($filename=PATH_CACHE."/$i/$file") && is_file($filename))
	{
		$filetime = filemtime($filename);
		$filesize = filesize($filename);
		if ($filetime + TEMPLATE_CACHE_MAX_TIME < $time)
		{
			if (isset($_GET["clean"]))
				unlink($filename);
			echo "<p><b>[DELETE]</b> $filename : ".date("d/m/Y", $filetime)." - $filesize octets</p>\n";
			$cleansize += $filesize;
		}
		else
		{
			echo "<p>$filename : ".date("d/m/Y", $filetime)." - $filesize octets</p>\n";
		}
		$totalsize += $filesize;
	}
}

echo "<h3>Taille totale : ".number_format($totalsize/1000, 3, ".", " ")." ko</h3>\n";
echo "<h3>Taille à nettoyer : ".number_format($cleansize/1000, 3, ".", " ")." ko</h3>\n";

echo "<p><a href=\"?clean\">NETTOYER</a></p>\n";

}

if (TEMPLATE_CACHE == true && TEMPLATE_CACHE_TYPE == "file")
{

header("Content-type: text/html; charset=".SITE_CHARSET);
action();

}

?>
