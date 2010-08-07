<?

/**
  * $Id: classes.inc.php 59 2009-03-03 15:48:26Z mathieu $
  * 
  * Copyright 2008 Mathieu Moulin - iProspective - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [begin]");

/**
 * The singleton abstract class, which cannot be extended because of the static problem in PHP 5.2,
 * so I may use it when the bug will be solved...
 */
abstract class singleton
{

private static $instance;

private function __construct()
{
}

private function __clone()
{
}

public static function getInstance()
{

if (!isset(self::$instance) || !self::$instance)
{ 
	self::$instance = new self;
}
return self::$instance;

}

}

/**
 * This class permit to save in session only a few properties
 * of an object it is extended, and it works with protected and private ones.
 */
abstract class session_select
{

/**
 * Property list to be saved in session
 *
 * @var mixed[]
 */
private $serialize_list = array();
public $serialize_save_list = array();

public function __sleep($list=array())
{

$this->serialize_save_list = array();
foreach($list as $name)
{
	if (isset($this->{$name}))
		$this->serialize_save_list[$name] = $this->{$name};
}
return array("serialize_save_list");

}

public function __wakeup()
{

foreach ($this->serialize_save_list as $name => $value)
{
	$this->{$name} = $value;
}
$this->serialize_save_list = array();

}

}

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [end]");

?>