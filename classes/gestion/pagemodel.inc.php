<?php

/**
  * $Id: pagemodel.inc.php 30 2011-01-18 23:29:06Z lemathoufou $
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


/**
 * Defines the accessible pages
 */
class __pagemodel_gestion extends _gestion
{

protected $type = "pagemodel";

protected $retrieve_details = false;

protected $info_detail = array
(
	"name"=>array("label"=>"Nom (unique)", "type"=>"string", "size"=>32, "lang"=>false),
	"label"=>array("label"=>"Label", "type"=>"string", "size"=>128, "lang"=>true),
	"description"=>array("label"=>"Description", "type"=>"text", "lang"=>true),
	"script"=>array("label"=>"Script", "type"=>"script", "folder"=>PATH_PAGE, "filename"=>"{name}.inc.php")
);

protected function query_info_more()
{

// Params
$query = db()->query("SELECT `page_id`, `name`, `datatype`, `value`, `update_pos` FROM `_page_params`");
while ($param = $query->fetch_assoc())
{
	$this->list_detail[$param["page_id"]]["param_list"][$param["name"]] = array
	(
		"value"=>json_decode($param["value"], true),
		"datatype"=>$param["datatype"],
		"update_pos"=>$param["update_pos"],
		"opt"=>array()
	);
}
$query_opt = db()->query("SELECT `page_id`, `name`, `optname`, `optvalue` FROM `_page_params_opt`");
while ($opt = $query_opt->fetch_row())
{
	$this->list_detail[$opt[0]]["param_list"][$opt[1]]["opt"][$opt[2]] = json_decode($opt[3], true);
}

}

}

/**
 * Defines a model of page
 *
 */
class __pagemodel extends _object_gestion
{

protected $_type = "page";

protected $type = "";

// Params
protected $param_list = array();
protected $params_url = array();
// Effective parameters
protected $param = array();

// Views and templates
protected $view_list = array();
// Actual template
protected $template = null;

function __sleep()
{

return array("id", "name", "label", "description", "param_list", "view_list");

}
function __wakeup()
{

$this->construct_params();

}

protected function construct_more($infos)
{

$this->construct_params();

}

protected function query_info_more()
{

$this->query_params();
$this->query_view();

}

/**
 * Retrieve the parameters list from database
 */
public function query_params()
{

// Params
$this->param_list = array();
$this->param = array();
$this->params_url = array();
$query = db()->query("SELECT `name`, `datatype`, `value`, `update_pos` FROM `_page_params` WHERE `page_id`='$this->id'");
while ($param = $query->fetch_assoc())
{
	// Update position may be null !
	// Value is the default value, which is fixed if the parameter is not designed to be overloaded
	$this->param_list[$param["name"]] = array
	(
		"value"=>json_decode($param["value"], true),
		"datatype"=>$param["datatype"],
		"update_pos"=>$param["update_pos"],
		"opt"=>array()
	);
}
$query_opt = db()->query("SELECT `name`, `optname`, `optvalue` FROM `_page_params_opt` WHERE `page_id`='".$this->id."'");
while ($opt = $query_opt->fetch_row())
{
	$this->param_list[$opt[0]]["opt"][$opt[1]] = json_decode($opt[2], true);
}

}

/**
 * Constructs the parameters list
 */
public function construct_params()
{

$this->param = array();
$this->params_url = array();
foreach($this->param_list as $name=>$param)
{
	//echo "<p>DEBUG page::construct_param : $name</p>\n";
	if ($param["datatype"])
		$datatype = "data_".$param["datatype"];
	else
		$datatype = "data";
	$this->param[$name] = new $datatype($name, $param["value"], $name);
	if (isset($param["opt"]) && is_array($param["opt"])) foreach ($param["opt"] as $i=>$j)
		$this->param[$name]->opt_set($i, $j);
	if (is_numeric($param["update_pos"]))
		$this->params_url[$param["update_pos"]] = $name;
}

}
/**
 * Returns list of actual params
 */
public function param_list()
{

return $this->param;

}
/**
 * Returns list of actual params
 */
public function param_list_detail()
{

return $this->param_list;

}
/**
 * Returns if a param exists
 * @param $name
 */
public function param_exists($name)
{

return (is_string($name) && array_key_exists($name, $this->param_list));

}
/**
 * Param exists ?
 * @param unknown_type $name
 */
public function __isset($name)
{

return $this->param_exists($name);

}
/**
 * Get a param value
 *
 * @param string $name
 * @return unknown
 */
public function __get($name)
{

if (is_string($name) && array_key_exists($name, $this->param))
	return $this->param[$name];

}
/**
 * Set a param value
 * @param unknown_type $name
 */
public function __set($name, $value)
{

if (is_string($name) && array_key_exists($name, $this->param))
	$this->param[$name]->value = $value;

}

function params_reset()
{

foreach ($this->param_list as $name=>$param)
{
	$this->param[$name]->value = $param["value"];
}

}

/**
 * Retrieve the associated views
 */
public function query_view()
{

// Views
$this->view_list = array();
$query = db()->query("SELECT `name`, `template_id`, `subtemplates_map`, `params_map` FROM `_page_view` WHERE `page_id`='$this->id'");
while ($row = $query->fetch_assoc())
{
	$this->view_list[$row["name"]] = array($row["template_id"], json_decode($row["subtemplates_map"], true), json_decode($row["params_map"], true));
}

}

public function view_exists($name)
{

return array_key_exists($name, $this->view_list);

}

public function view_list()
{

return $this->view_list;

}

public function view_get($name)
{

if ($this->view_exists($name))
	return $this->view_list[$name];

}

public function view_set($name="")
{

if ($view = $this->view_get($name))
{
	$this->template = clone template($view[0]);
	$this->params_apply($this->template, $view[1]);
	$this->subtemplates_apply($this->template, $view[2]);
}

}

/**
 * Apply page parameters to the associated template
 */
protected function params_apply(_template $template, $map=true)
{

// Sends params to the template
$template->params_reset();
foreach ($this->param as $name=>$param)
{
	if ($map === true || (is_array($map) && in_array($name, $map)))
	{
		if (DEBUG_TEMPLATE)
			echo "<p>page(ID#$this->id)::params_apply() to template ID#$this->template_id : $name => $param->value</p>\n";
		$template->__set($name, $param->value);
	}
}

}

/**
 * Apply page parameters to the associated template
 */
protected function subtemplates_apply(_template $template, array $map)
{

// Define subtemplates
foreach($map as $nb=>$subtemplate)
{
	$template->subtemplate_set($nb, $subtemplate);
}

}

function view($name="")
{

if (!$name)
	$name = "default";
if (!$this->template)
	$this->view_set($name);

return $this->template;

}

function view_disp($name="")
{

if (DEBUG_GENTIME == true)
	gentime("page::view_disp() [begin]");

if ($template=$this->view($name))
	$template->disp();

if (DEBUG_GENTIME == true)
	gentime("page::view() [end]");

}

/**
 * Execute scripts to verify/update params, set new, etc.
 */
public function action()
{

if (DEBUG_GENTIME == true)
	gentime("page::action() [begin]");

// TODO : use attribute script
if (file_exists("page/$this->name.inc.php"))
{
	extract($this->param, EXTR_REFS);
	include "page/$this->name.inc.php";
}

if (DEBUG_GENTIME == true)
	gentime("page::action() [end]");

}

/**
 * Permission for this page
 * Using global page perm, specific group page, and specific user page
 */
public function perm($type="")
{

$_type = $this->_type;

if ($type)
{
	//echo "<p>[DEBUG] page(ID#$this->id)::perm($type)</p>\n";
	// Work only for cumulative permissions
	$return = false;
	// Default perm
	if (is_numeric(strpos($this->perm, $type)))
		$return = true;
	//echo "<p>[DEBUG] page(ID#$this->id)::perm($type) : $return</p>\n";
	// Specific perm
	if ($return == false)
	{
		$perm_list = login()->perm_list();
		//print_r($perm_list);
		while ($return==false && (list($nb,$perm_id)=each($perm_list)))
		{
			//echo "<p>$perm_id : ".permission($perm_id)->$_type($this->id)."</p>\n";
			if (is_numeric(strpos(permission($perm_id)->$_type($this->id), $type)))
				$return = true;
		}
	}
	// Specific perm for user
	if ($return == false)
	{
		if (is_numeric(strpos(login()->user_perm($_type, $this->id), $type)))
			$return = true;
	}
	return $return;
}
else
{
	// Default perm (all)
	$perm = new permission_info($this->perm);
	// Specific perm
	foreach(login()->perm_list() as $perm_id)
		$perm->update(permission($perm_id)->$_type($this->id));
	// Specific perm for user
	if ($account_perm=login()->user_perm($_type, $this->id))
		$perm->update($account_perm);
	return $perm;
}

}
public function perm_login()
{

// Default Access Permission
if ($this->perm)
	return true;

$return = false;

reset($this->perm_list);
while(!$return && (list($nb, $perm_id)=each($this->perm_list)))
{
	if (login()->perm($perm_id))
		$return = true;
}

return $return;

}

}

/*
 * Specific classes for admin
 */
if (ADMIN_LOAD == true)
{
	include PATH_CLASSES."/admin/page.inc.php";
}
else
{
	class _pagemodel_gestion extends __pagemodel_gestion {};
	class _pagemodel extends __pagemodel {};
}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
