<?php

session_start();

$_SESSION["captcha_code"] = "";
$liste = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123456789";
while(strlen($_SESSION["captcha_code"]) < 6)
	$_SESSION["captcha_code"] .= $liste[rand(0,60)];

header("Content-type: image/jpeg");
header("Cache-Control: cache, must-revalidate");

$img = imageCreate(65, 22); // Créér une image de 40x15 pixels
$fond = imageColorAllocate($img , 0 , 0 , 0); // On choisit la couleur du fond (en RVB)
$texte = imageColorAllocate($img , 255 , 255 , 255); // Idem, mais pour la couleur du texte
$police = 5; // L'ID de la police (entre 1 et 5, pré-inclues dans PHP)

imageString($img , $police , 5 , 3 , $_SESSION["captcha_code"] , $texte);  // Ecrire le code sur l'image
imagejpeg($img , "" , 30); // Image de mauvaise qualité histoire de tromper les bots

imageDestroy($img);


?>