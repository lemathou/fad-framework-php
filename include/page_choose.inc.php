<?php

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [begin]");

// Choix de la page à partir de l'url
page()->set();
gentime("PAGE_SET");

// Actions sur la page
page_current()->action();
gentime("PAGE_ACTION");

// Destruction des variables temporaires créees dans lang.inc.php
unset($url); unset($url_e);

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [end]");

?>