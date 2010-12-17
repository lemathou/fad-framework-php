<?php

$path_root = "";

/**
 * 
 * Enter description here ...
 * @param string $source
 * @param string $dest
 */
function recurscopy($source, $dest)
{

if (!is_string($source) || !is_string($dest))
	die("Must specify strings");

$e = explode("/", $source);

// test d'existance de la source
$exists = true;
$exists_file = "";
foreach ($e as $file)
{
	if ($exists_file)
		$exists_file .= "/";
	if (!file_exists($file))
		$exists = false;
}
if ($exists)


}

?>