<?php

function install($config)
{

// Database
$sql_import = "mysql -u ".$config["DB_USERNAME"]["value"]." -p".$config["DB_PASSWORD"]["value"]." ".$config["DB_BASE"]["value"]." < ".PATH_FRAMEWORK."/install/db/mysql/";
exec($sql_import."structure.sql");
exec($sql_import."data.sql");
	
// Config file
$config_filename = $config["PATH_ROOT"]["value"]."/config/config.inc.php";

if (false && isset($_SESSION["install_structure"]) && $_SESSION["install_structure"])
	@copy(PATH_FRAMEWORK."/install/project/config/config.inc.php", $config_filename);
elseif (!@filesystem::copydir(PATH_FRAMEWORK."/install/project", $config["PATH_ROOT"]["value"]))
	return false;
else
	$_SESSION["install_structure"] = true;

$replace_from = array();
$replace_to = array();
foreach($config as $name=>$info)
{
	$replace_from[] = "\${".$name."}";
	if ($info["type"] == "boolean")
		$replace_to[] = ($info["value"]) ? "true" : "false";
	elseif (is_numeric($info["value"]))
		$replace_to[] = $info["value"];
	else
		$replace_to[] = "\"".$info["value"]."\"";
}
$replace_from[] = "\${CACHE}";
if ($config["CACHE_TYPE"]["value"])
	$replace_to[] = "true";
else
	$replace_to[] = "false";
$replace_from[] = "\${TEMPLATE_CACHE}";
if ($config["TEMPLATE_CACHE_TYPE"]["value"])
	$replace_to[] = "true";
else
	$replace_to[] = "false";

$config_file = str_replace($replace_from, $replace_to, file_get_contents(PATH_FRAMEWORK."/install/project/config/config.inc.php"));
fwrite(fopen($config_filename, "w"), $config_file);
if ($config["PATH_ROOT"]["value"] != $config["PATH_FRAMEWORK"]["value"])
{
	symlink($config["PATH_FRAMEWORK"]["value"]."/_css", $config["PATH_ROOT"]["value"]."/_css");
	symlink($config["PATH_FRAMEWORK"]["value"]."/_js", $config["PATH_ROOT"]["value"]."/_js");
}

return true;

}

?>
