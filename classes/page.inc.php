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
class _page_gestion extends gestion
{

protected $type = "page";

protected $page_id = 0;

protected $info_list = array("name", "type", "template_id", "redirect_url", "alias_page_id", "perm");
protected $info_lang_list = array("label", "description", "url", "shortlabel");

protected $retrieve_details = false;

protected $info_detail = array
(
	"name"=>array("label"=>"Nom (unique)", "type"=>"string", "size"=>64, "lang"=>false),
	"label"=>array("label"=>"Label", "type"=>"string", "size"=>128, "lang"=>true),
	"shortlabel"=>array("label"=>"Titre court (pour liens)", "type"=>"string", "size"=>64, "lang"=>true),
	"url"=>array("label"=>"URL", "type"=>"string", "size"=>128, "lang"=>true),
	"description"=>array("label"=>"Description", "type"=>"text", "lang"=>true),
	"type"=>array("label"=>"Type", "type"=>"select", "lang"=>false, "default"=>"template", "select_list"=> array("static_html"=>"Page HTML statique", "template"=>"Utilisation d'un template (valeur par défaut)", "redirect"=>"Redirection vers une page extérieure", "alias"=>"Alias d'une autre page du site", "static_html"=>"Page HTML statique", "php"=>"Script PHP")),
	"template_id"=>array("label"=>"Template", "type"=>"object", "object_type"=>"template", "lang"=>false),
	"perm_list"=>array("label"=>"Permissions", "type"=>"object_list", "object_type"=>"permission", "db_table"=>"_page_perm_ref", "db_id"=>"page_id", "db_field"=>"perm_id"),
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
 * 
 * ID#1 : home :  Homepage
 * ID#2 : notfound : Page does not exists (HTTP 404)
 * ID#3 : unavailable : Page unavailable (HTTP 401)
 * 
 * TODO : include here all retrieved infos from the request url (language and params list)
 */
public function set()
{

$i = array_pop($GLOBALS["url_e"]);

$url_params = array();

if (!$i)
{
	define("PAGE_ID", 1);
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
	// Test existance
	if (!$this->exists($i))
	{
		define("PAGE_ID", 2);
	}
	elseif (!$this->get($i)->perm_login()) // perm("r")
	{
		define("PAGE_ID", 3);
	}
	else
	{
		define("PAGE_ID", $i);
	}
}

if (DEBUG_GENTIME == true)
	gentime("PAGE_SET [1]");

$this->page_id = PAGE_ID;
$this->get(PAGE_ID)->set($url_params);

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

/**
 * Affichage d'un set de pages
 * 
 * @param unknown_type $options
 */
public function disp($options=array())
{

$return = array();

if (in_array("table",$options))
{
	foreach ($this->list as $page)
	{
		$return[] = $page->url();
	}
	print "<table class=\"menu\"><tr>\n<td>".implode("</td>\n<td>",$return)."</td>\n</tr></table>";
}
else
{
	foreach ($this->list as $page)
	{
		$return[] = $page->url();
	}
	print "<ul class=\"menu\">\n<li>".implode("</li>\n<li>",$return)."</li>\n</ul>";
}

}

}

/**
 * Defines an element of the menu, accessible via an specific url
 *
 */
class _page extends object_gestion
{

protected $_type = "page";

protected $perm = "";

protected $type = "";
protected $url = "";
protected $shortlabel = "";

// Template and params
protected $template_id = 0;
protected $param_list = array();
protected $params_url = array();
protected $params_get = array();
// Effective parameters
protected $param = array();
 
// Permissions
protected $perm_list = array();

// Redirect URL
protected $redirect_url = null;

// Page alias
protected $alias_page_id = null;

function __sleep()
{

return array("id", "name", "label", "description", "perm", "type", "url", "shortlabel", "template_id", "param_list", "perm_list", "redirect_url", "alias_page_id");

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
 * Returns if a param exists
 * @param $name
 */
public function param_exists($name)
{

if (is_string($name) && array_key_exists($name, $this->param_list))
	return true;
else
	return false;

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
 * Param exists ?
 * @param unknown_type $name
 */
public function __isset($name)
{

return array_key_exists($name, $this->param);

}
/**
 * Get a param value
 *
 * @param string $name
 * @return unknown
 */
public function __get($name)
{

if (array_key_exists($name, $this->param))
	return $this->param[$name];

}
/**
 * Set a param value
 * @param unknown_type $name
 */
public function __set($name, $value)
{

if (array_key_exists($name, $this->param))
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
	if (isset($this->params_url[$pos]) && ($name=$this->params_url[$pos]))
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
public function set($params)
{

$this->params_update_url($params);

}

function params_reset()
{

foreach ($this->param_list as $name=>$param)
{
	$this->param[$name]->value = $param["value"];
}

}

/**
 * Apply page parameters to the associated template
 */
protected function params_apply()
{

// Sends params to the template
if ($template=$this->template()) foreach ($this->param as $name=>$param)
{
	if (DEBUG_TEMPLATE)
		echo "<p>page(ID#$this->id)::params_apply() to template ID#$this->template_id : $name => $param->value</p>\n";
	//var_dump($template);
	$template->{$name} = $param->value;
}

}

/**
 * Access the associated template
 */
function template()
{

if (false)
	echo "<p>page(ID#$this->id) : Accessing to template ID#$this->template_id</p>\n";

if ($this->template_id && ($template=template($this->template_id)))
	return $template;

}

/**
 * Returns the associated template
 */
function tpl()
{

if ($template=$this->template())
{
	$template->params_reset();
	$this->params_apply();
	return $template;
}

}

/**
 * Display the associated template
 */
function tpl_disp()
{

if ($template=$this->template())
{
	//var_dump($template);
	$template->params_reset();
	//var_dump($this->param);
	foreach ($this->param as $name=>$param)
	{
		//echo "<p>$name : $param->value</p>\n";
		$template->__set($name, $param->value);
	}
	$template->disp();
}

}

/**
 * Execute scripts to verify/update params, set new, etc.
 */
public function action()
{

// TODO : use attribute script
if (file_exists("page/$this->name.inc.php"))
{
	extract($this->param, EXTR_REFS);
	include "page/$this->name.inc.php";
}

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
	class page_gestion extends _page_gestion {};
	class page extends _page {};
}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
