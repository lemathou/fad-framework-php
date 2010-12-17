<?

/**
  * $Id$
  * 
  * Copyright 2008 Mathieu Moulin - iProspective - lemathou@free.fr
  * 
  * This file is part of FTNGroupWare.
  * 
  */

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [begin]");

// Classes de base
include "include/classes.inc.php";

// Base de donnée
include "include/db.inc.php";

// Gestion des librairies de fonctions
include "include/library.inc.php";

// Types de donnée, datamodels, agrégats et banques de données
include "include/data.inc.php";
include "include/data_verify.inc.php";
include "include/data_model.inc.php";
include "include/data_display.inc.php";
include "include/data_bank.inc.php";

// Conteneurs de maquettes
//require_once "include/container.inc.php";

// Formulaires
include "include/forms.inc.php";

// Variables globales
include "include/globals.inc.php";

// Erreurs, exceptions
//include "include/error.inc.php";
//include "include/exceptions.inc.php";

// Sécurité : banissement d'IP, logs, etc.
//include "include/security.inc.php";

// Réécriture d'URL obsolète pour l'instant
//include "include/rewriting.inc.php";

// Menu & Pages
include "include/menu.inc.php";

// Login
include "include/login.inc.php";

// Templates
include "include/template.inc.php";

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [end]");

?>