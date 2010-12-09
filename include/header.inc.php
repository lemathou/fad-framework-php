<?

/**
  * $Id: header.inc.php 74 2009-07-03 06:41:02Z mathieu $
  * 
  * Copyright 2008 Mathieu Moulin - iProspective - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */

// Performance & Optimisation
include PATH_INCLUDE."/gentime.inc.php";

// Classes de base
include PATH_INCLUDE."/classes.inc.php";

// Base de donnée
include PATH_INCLUDE."/db.inc.php";

// Gestion des librairies de fonctions
include PATH_INCLUDE."/library.inc.php";

// Types de donnée, datamodels, agrégats et banques de données
include PATH_INCLUDE."/data.inc.php";
include PATH_INCLUDE."/data_verify.inc.php";
include PATH_INCLUDE."/data_model.inc.php";
include PATH_INCLUDE."/data_display.inc.php";
include PATH_INCLUDE."/data_bank.inc.php";

// Conteneurs de maquettes
//require_once "include/container.inc.php";

// Formulaires
//include "include/forms.inc.php";

// Variables globales
include PATH_INCLUDE."/globals.inc.php";

// Erreurs, exceptions
//include "include/error.inc.php";
//include "include/exceptions.inc.php";

// Sécurité : banissement d'IP, logs, etc.
//include "include/security.inc.php";

// Réécriture d'URL obsolète pour l'instant
//include "include/rewriting.inc.php";

// Permissions
include PATH_INCLUDE."/permission.inc.php";

// Menu & Pages
include PATH_INCLUDE."/menu.inc.php";

// Login
include PATH_INCLUDE."/login.inc.php";

// Templates
include PATH_INCLUDE."/template.inc.php";

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [end]");

?>
