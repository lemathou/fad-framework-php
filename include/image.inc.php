<?php

/**
  * $Id: image.inc.php 31 2011-01-24 05:52:20Z lemathoufou $
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


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
