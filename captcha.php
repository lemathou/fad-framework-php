<?php

session_start();

function captcha_aff()
{

$_SESSION["captcha_code"] = "";
$liste = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123456789";
while(strlen($_SESSION["captcha_code"]) < 6)
	$_SESSION["captcha_code"] .= $liste[rand(0,60)];

header("Content-type: image/jpeg");
header("Cache-Control: cache, must-revalidate");

$img = imagecreate(65, 22);
$bg_color = imageColorAllocate($img , 0 , 0 , 0);
$text_color = imageColorAllocate($img , 255 , 255 , 255);
$police = 5; // L'ID de la police (entre 1 et 5, pré-inclues dans PHP)

imageString($img , $police , 5 , 3 , $_SESSION["captcha_code"] , $text_color);
imagejpeg($img , "" , 25); // Mauvaise qualité pour tromper les robots

imageDestroy($img);

}

captcha_aff();

?>