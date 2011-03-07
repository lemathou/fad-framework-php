<?php

/**
  * $Id: session_refresh.js.php 28 2011-01-17 07:50:38Z lemathoufou $
  * 
  * Copyright 2008-2011 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * http://sourceforge.net/projects/phpfadframework/
  * 
  * Licence : http://www.gnu.org/copyleft/gpl.html  GNU General Public License
  * 
  */

define("ADMIN_LOAD",false);
define("HEADER_LOAD","full");
define("SESSION_START",true);
include "config/config.inc.php";

include PATH_FRAMEWORK."/_session_refresh.js.php";

?>