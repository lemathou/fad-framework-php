<?php

if (!defined("PATH_INCLUDE"))
	die("Config file not loaded");

define("FRAMEWORK_INCDIR", "../Framework/include/");

$file_list=array
(
	"config/config.inc.php",
	FRAMEWORK_INCDIR."gentime.inc.php",
	FRAMEWORK_INCDIR."classes.inc.php",
	FRAMEWORK_INCDIR."db.inc.php",
	FRAMEWORK_INCDIR."library.inc.php",
	FRAMEWORK_INCDIR."data.inc.php",
	FRAMEWORK_INCDIR."data_verify.inc.php",
	FRAMEWORK_INCDIR."data_model.inc.php",
	FRAMEWORK_INCDIR."data_display.inc.php",
	FRAMEWORK_INCDIR."data_bank.inc.php",
	FRAMEWORK_INCDIR."globals.inc.php",
	FRAMEWORK_INCDIR."menu.inc.php",
	FRAMEWORK_INCDIR."login.inc.php",
	FRAMEWORK_INCDIR."template.inc.php",
	FRAMEWORK_INCDIR."lang.inc.php",
	FRAMEWORK_INCDIR."session_start.inc.php",
);

$fp = fopen("header_full.inc.php","w");

$contents = "";

foreach($file_list as $file)
{

$fp2 = fopen($file,"r");
$contents .= fread($fp2, filesize($file));

}

fwrite($fp, $contents);

?>
