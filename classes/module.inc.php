<?php

/**
  * $Id: module.inc.php 28 2011-01-17 07:50:38Z lemathoufou $
  * 
  * Copyright 2008 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * http://sourceforge.net/projects/phpfadframework/
  * Licence : http://www.gnu.org/copyleft/gpl.html  GNU General Public License
  * 
  */

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [begin]");


// global module class

class module_gestion
{

protected $list = array();

function get($name)
{

if (!isset($this->list[$name]))
	if ($this->list[$name] = new module($name))
	{
		return $this->list[$name];
	}
	else
	{
		unset($this->list[$name]);
		return false;
	}
else
	return $this->list[$name];

}

}

// Pour chaque module charg�

class module
{

protected $name = "";
protected $version = 0;
protected $dep_list = array();

protected $loaded = false;

function __construct($name)
{

if (file_exists("module/$name/module.php"))
{
	$this->name = $name;
	return true;
}
else
	return false;

}

function load()
{

//print "<p>Chargement module : <b>$this->name</b></p>\n";
if (!$this->loaded)
{
	require_once "module/".$this->name."/module.php";
	return true;
}
else
{
	return false;
}

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>