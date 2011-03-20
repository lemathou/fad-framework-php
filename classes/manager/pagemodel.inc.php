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


/**
 * Defines the accessible pages
 */
class __pagemodel_manager extends _manager
{

protected $type = "pagemodel";

protected $retrieve_details = false;

protected $info_detail = array
(
	"name"=>array("label"=>"Nom (unique)", "type"=>"string", "size"=>32, "lang"=>false),
	"label"=>array("label"=>"Label", "type"=>"string", "size"=>128, "lang"=>true),
	"description"=>array("label"=>"Description", "type"=>"text", "lang"=>true),
	"shortlabel"=>array("label"=>"Titre court (pour liens)", "type"=>"string", "size"=>64, "lang"=>true),
	"url"=>array("label"=>"URL", "type"=>"string", "size"=>128, "lang"=>true),
	"type"=>array("label"=>"Type", "type"=>"select", "lang"=>false, "default"=>"template", "select_list"=>array("static_html"=>"Page HTML statique", "php"=>"Utilisation d'un script PHP", "template"=>"Utilisation d'un template")),
	"script"=>array("label"=>"Script", "type"=>"script", "folder"=>PATH_PAGE, "filename"=>"{name}.inc.php")
);

protected function query_info_more()
{

// Params
$query = db()->query("SELECT t1.`pagemodel_id`, t1.`name`, t1.`datatype`, t1.`value`, t2.`label` FROM `_pagemodel_params` as t1 LEFT JOIN `_pagemodel_params_lang` as t2 ON t1.`pagemodel_id`=t2.`pagemodel_id` AND t1.`name`=t2.`name` AND t2.`lang_id`='".SITE_LANG_ID."'");
while ($param = $query->fetch_assoc())
{
	$this->list_detail[$param["pagemodel_id"]]["param_list"][$param["name"]] = array
	(
		"label"=>$param["label"],
		"datatype"=>$param["datatype"],
		"value"=>json_decode($param["value"], true),
		"opt"=>array()
	);
}
$query_opt = db()->query("SELECT `pagemodel_id`, `name`, `optname`, `optvalue` FROM `_pagemodel_params_opt`");
while ($opt = $query_opt->fetch_row())
{
	$this->list_detail[$opt[0]]["param_list"][$opt[1]]["opt"][$opt[2]] = json_decode($opt[3], true);
}

// Views
$this->view_list = array();
$query = db()->query("SELECT `pagemodel_id`, `name`, `template_id`, `subtemplates_map`, `params_map` FROM `_pagemodel_view`");
while ($row = $query->fetch_assoc())
{
	$this->list_detail[$row["pagemodel_id"]]["view_list"][$row["name"]] = array($row["template_id"], json_decode($row["params_map"], true), json_decode($row["subtemplates_map"], true));
}

}

}

/**
 * Defines a model of page
 *
 */
class __pagemodel extends _object
{

protected $_type = "pagemodel";

protected $type = "";

protected $description = "";
protected $shortlabel = "";
protected $url = "";

// Params
protected $param_list = array();
// Effective parameters
protected $param = array();

// Views and templates
protected $view_list = array();
// Actual template
protected $template = null;

function __sleep()
{

return array("id", "type", "name", "label", "description", "param_list", "view_list");

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
$query = db()->query("SELECT t1.`name`, t1.`datatype`, t1.`value`, t2.`label` FROM `_pagemodel_params` as t1 LEFT JOIN `_pagemodel_params_lang` as t2 ON t1.`pagemodel_id`=t2.`pagemodel_id` AND t1.`name`=t2.`name` AND t2.`lang_id`='".SITE_LANG_ID."' WHERE t1.`pagemodel_id`='$this->id'");
while ($param = $query->fetch_assoc())
{
	// Update position may be null !
	// Value is the default value, which is fixed if the parameter is not designed to be overloaded
	$this->param_list[$param["name"]] = array
	(
		"label"=>$param["label"],
		"datatype"=>$param["datatype"],
		"value"=>json_decode($param["value"], true),
		"opt"=>array()
	);
}
$query_opt = db()->query("SELECT `name`, `optname`, `optvalue` FROM `_pagemodel_params_opt` WHERE `pagemodel_id`='".$this->id."'");
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
	$this->param[$name] = new $datatype($name, $param["value"], $param["label"]);
	if (isset($param["opt"]) && is_array($param["opt"])) foreach ($param["opt"] as $i=>$j)
		$this->param[$name]->opt_set($i, $j);
}

}
function params_reset()
{

foreach ($this->param_list as $name=>$param)
{
	$this->param[$name]->value = $param["value"];
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
 * @param string $name
 */
public function param_exists($name)
{

return (is_string($name) && array_key_exists($name, $this->param_list));

}
/**
 * Param exists ?
 * @param string $name
 */
public function __isset($name)
{

return $this->param_exists($name);

}
/**
 * Get a param value
 *
 * @param string $name
 * @return data
 */
public function __get($name)
{

if (is_string($name) && array_key_exists($name, $this->param))
	return $this->param[$name];

}
/**
 * Set a param value
 * @param string $name
 * @param mixed $value
 */
public function __set($name, $value)
{

if (is_string($name) && array_key_exists($name, $this->param))
	$this->param[$name]->value = $value;

}

/**
 * Retrieve the associated views
 */
public function query_view()
{

// Views
$this->view_list = array();
$query = db()->query("SELECT `name`, `template_id`, `params_map`, `subtemplates_map` FROM `_pagemodel_view` WHERE `pagemodel_id`='$this->id'");
while ($row = $query->fetch_assoc())
{
	$this->view_list[$row["name"]] = array($row["template_id"], json_decode($row["params_map"], true), json_decode($row["subtemplates_map"], true));
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

if (DEBUG_GENTIME == true)
	gentime("pagemodel(#ID$this->id)::view_set() [begin]");
//echo "<p>pagemodel(ID#$this->id)::view_set() : $name</p>\n";

if (!$name)
	$name = "default";

if (($view=$this->view_get($name)) && ($template=template($view[0])))
{
	$this->template = clone $template;
	$this->params_apply($this->template, $view[1]);
	$this->subtemplates_apply($this->template, $view[2]);
}

if (DEBUG_GENTIME == true)
	gentime("pagemodel(#ID$this->id)::view_set() [end]");

}

/**
 * Apply page parameters to the associated template
 */
protected function params_apply(_template $template, $map=true)
{

if (DEBUG_GENTIME == true)
	gentime("pagemodel(#ID$this->id)::params_apply() [begin]");

// Sends params to the template
//$template->params_reset();
foreach ($this->param as $name=>$param)
{
	if ($map === true || (is_array($map) && in_array($name, $map)))
	{
		if (DEBUG_TEMPLATE)
			echo "<p>pagemodel(ID#$this->id)::params_apply() to template ID#$this->template_id : $name => $param->value</p>\n";
		$template->__set($name, $param->value);
	}
}

if (DEBUG_GENTIME == true)
	gentime("pagemodel(#ID$this->id)::params_apply() [end]");

}

/**
 * Apply page parameters to the associated template
 */
protected function subtemplates_apply(_template $template, $map=null)
{

if (DEBUG_GENTIME == true)
	gentime("pagemodel(#ID$this->id)::subtemplates_apply() [begin]");
//echo "<p>pagemodel(#ID$this->id)::subtemplates_apply()</p>";

// Define subtemplates
if (is_array($map)) foreach($map as $nb=>$subtemplate)
{
	//echo "<p>$nb : $subtemplate</p>";
	$template->subtemplate_set($nb, $subtemplate);
}

if (DEBUG_GENTIME == true)
	gentime("pagemodel(#ID$this->id)::subtemplates_apply() [end]");

}

function view()
{

if (!$this->template)
	$this->view_set("default");

return $this->template;

}

function view_disp()
{

if (DEBUG_GENTIME == true)
	gentime("pagemodel(#ID$this->id)::view_disp() [begin]");

if ($template=$this->view())
	$template->disp();

if (DEBUG_GENTIME == true)
	gentime("pagemodel(#ID$this->id)::view_disp() [end]");

}

/**
 * Execute scripts to verify/update params, set new, etc.
 */
public function action()
{

if (DEBUG_GENTIME == true)
	gentime("pagemodel(#ID$this->id)::action() [begin]");

// TODO : use attribute script
if (file_exists("page/$this->name.inc.php"))
{
	extract($this->param);
	include "page/$this->name.inc.php";
}

if (DEBUG_GENTIME == true)
	gentime("pagemodel(#ID$this->id)::action() [end]");

}

}

/*
 * Specific classes for admin
 */
if (ADMIN_LOAD == true)
{
	include PATH_CLASSES."/manager/admin/pagemodel.inc.php";
}
else
{
	class _pagemodel_manager extends __pagemodel_manager {};
	class _pagemodel extends __pagemodel {};
}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
