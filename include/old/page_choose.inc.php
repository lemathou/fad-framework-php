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


// Choix de la page à partir de l'url
page()->set();
if (DEBUG_GENTIME == true)
	gentime("PAGE_SET");

// Actions sur la page
page_current()->action();
if (DEBUG_GENTIME == true)
	gentime("PAGE_ACTION");

// Destruction des variables temporaires créees dans lang.inc.php
unset($url); unset($url_e);


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
