<?php

/**
  * $Id: page.inc.php 30 2011-01-18 23:29:06Z lemathoufou $
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
class __page_gestion extends _gestion
{

protected $type = "page";

protected $page_id = 0;

protected $retrieve_details = false;

protected $info_detail = array
(
	"name"=>array("label"=>"Nom (unique)", "type"=>"string", "size"=>32, "lang"=>false),
	"label"=>array("label"=>"Label", "type"=>"string", "size"=>128, "lang"=>true),
	"shortlabel"=>array("label"=>"Titre court (pour liens)", "type"=>"string", "size"=>64, "lang"=>true),
	"url"=>array("label"=>"URL", "type"=>"string", "size"=>128, "lang"=>true),
	"description"=>array("label"=>"Description", "type"=>"text", "lang"=>true),
	"type"=>array("label"=>"Type", "type"=>"select", "lang"=>false, "default"=>"template", "select_list"=>array("static_html"=>"Page HTML statique", "template"=>"Utilisation d'un template (valeur par défaut)", "redirect"=>"Redirection vers une page extérieure", "alias"=>"Alias d'une autre page du site", "static_html"=>"Page HTML statique", "php"=>"Script PHP")),
	"template_id"=>array("label"=>"Template", "type"=>"object", "object_type"=>"template", "lang"=>false),
	"perm"=>array("label"=>"Permission par défaut", "type"=>"boolean", "default"=>0, "value_list"=>array("Protégé", "Accès pour tous"), "lang"=>false),
	"perm_list"=>array("label"=>"Permissions spécifiques", "type"=>"object_list", "object_type"=>"permission", "db_table"=>"_page_perm_ref", "db_id"=>"page_id", "db_field"=>"perm_id"),
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

/**
 * Set the default page
 * This is like a dispatcher
 * 
 * ID#1 : home :  Homepage
 * ID#2 : notfound : Page does not exists (HTTP 404)
 * ID#3 : unavailable : Page unavailable (HTTP 401)
 * 
 * TODO : include here all retrieved infos from the request url (language and params list)
 */
public function set()
{

if (DEBUG_GENTIME == true)
	gentime("page_gestion::set() [begin]");

$i = array_pop($GLOBALS["url_e"]);

$url_params = array();

// No page => Default page
if (!$i)
{
	define("PAGE_ID", PAGE_DEFAULT_ID);
}
else
{
	// Premier coup : la page
	if (($j = strpos($i,",")) != null)
	{
		$i = substr($i,$j+1);
		// Second coup : les paramètres
		if (($j = strpos($i,",")) != null)
		{
			$url_params = explode(",",substr($i,$j+1));
			$i = substr($i,0,$j);
		}
	}
	// Page exists
	if (!array_key_exists($i, $this->list_detail))
	{
		define("PAGE_ID", PAGE_UNDEFINED_ID);
	}
	elseif (!$this->get($i)->perm_login()) // perm("r")
	{
		define("PAGE_ID", PAGE_UNAUTHORIZED_ID);
	}
	else
	{
		define("PAGE_ID", $i);
	}
}

$this->page_id = PAGE_ID;
$this->get(PAGE_ID)->set($url_params);

if (DEBUG_GENTIME == true)
	gentime("page_gestion::set() [end]");

}

/**
 * Get the current page
 * @param unknown_type $id
 */
public function current_get()
{

if ($this->page_id)
	return $this->get($this->page_id);
else
	return null;

}

}

/**
 * Defines an element of the menu, accessible via an specific url
 *
 */
class __page extends _object_gestion
{

protected $_type = "page";

protected $perm = "";

protected $type = "";
protected $url = "";
protected $shortlabel = "";

// Params
protected $param_list = array();
protected $params_url = array();
// Effective parameters
protected $param = array();

// Views and templates
protected $view_list = array();
// Actual template
protected $template = null;
protected $template_id = 0;  // TODO : depreacated, to be deleted

// Permissions
protected $perm_list = array();

// Redirect URL
protected $redirect_url = null;

// Page alias
protected $alias_page_id = null;

function __sleep()
{

return array("id", "name", "label", "description", "perm", "type", "url", "shortlabel", "template_id", "param_list", "view_list", "perm_list", "redirect_url", "alias_page_id");

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

/**
 * Update params from URL, GET and POST
 * @param unknown_type $params
 */
public function params_update_url($params=array())
{

// Retrieved from the URL
foreach($params as $pos=>$value)
{
	if (array_key_exists($pos, $this->params_url) && ($name=$this->params_url[$pos]))
	{
		if (DEBUG_TEMPLATE)
			echo "<p>page(ID#$this->id)::params_update_url() : URL $name => $value</p>";
		$this->param[$name]->value_from_form($value);
	}
}

// Retrieved from $_GET
foreach($_GET as $name=>$value)
{
	if (in_array($name, $this->params_url))
	{
		if (DEBUG_TEMPLATE)
			echo "<p>page(ID#$this->id)::params_update_url() : GET $name => $value</p>";
		$this->param[$name]->value_from_form($value);
	}
}

// TODO : I think $_POST may only be used in script, not in template... Needs some work !

}

/**
 * Set the page as default, so create the associated template
 *
 */
public function set($params=array())
{

if (DEBUG_GENTIME == true)
	gentime("page::set() [begin]");

$this->params_update_url($params);

if (DEBUG_GENTIME == true)
	gentime("page::set() [end]");

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
elseif ($this->template_id)
{
	$this->template = clone template($this->template_id);
	$this->params_apply($this->template);
}

}

/**
 * Access the associated template
 */
function template()
{

if (false)
	echo "<p>page(ID#$this->id) : Accessing to template ID#$this->template_id</p>\n";

if (!$this->template)
	$this->view_set();

return $this->template;

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

/**
 * Display the associated template
 */
function template_disp()
{

if (DEBUG_GENTIME == true)
	gentime("page::tpl_disp() [begin]");

if ($template=$this->view())
	$template->disp();

if (DEBUG_GENTIME == true)
	gentime("page::tpl_disp() [end]");

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

/**
 * Returns the url to the page
 *
 * @return string
 */
public function url($params=array(), $text="")
{

if (!$text)
	$text = $this->url;

if ($this->alias_page_id)
{
	if (count($params))
		return SITE_BASEPATH.SITE_LANG."/$text,$this->alias_page_id,".implode(",",$params).".html";
	else
		return SITE_BASEPATH.SITE_LANG."/$text,$this->alias_page_id.html";
}
elseif ($this->redirect_url)
{
	return $this->redirect_url;
}
else // template
{
	if (count($params))
	{
		// TODO : se retaper le passage de paramètre ..? Gros soucis car il va falloir le préciser pour toutes les pages concernées !
		// Une fois chose faite, suffit de tester si c'est du dataobject et balancer la sauce ;-)
		return SITE_BASEPATH.SITE_LANG."/$text,$this->id,".implode(",",$params).".html";
	}
	else
		return SITE_BASEPATH.SITE_LANG."/$text,$this->id.html";
}

}

/**
 * Returns an HTML link to the page
 * @param unknown_type $params
 * @param unknown_type $text
 * @param unknown_type $text2
 */
public function link($params=array(), $text="", $text2="")
{

if ($text2)
	return "<a href=\"".$this->url($params, $text)."\">$text2</a>";
else
	return "<a href=\"".$this->url($params, $text)."\">$this->shortlabel</a>";

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
	class _page_gestion extends __page_gestion {};
	class _page extends __page {};
}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
