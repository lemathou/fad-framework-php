<?php

/**
  * $Id: page.inc.php 76 2009-10-15 09:24:20Z mathieu $
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
class __page_manager extends _manager
{

protected $type = "page";

protected $retrieve_details = false;

protected $info_detail = array
(
	"name"=>array("label"=>"Nom (unique)", "type"=>"string", "size"=>32, "lang"=>false),
	"label"=>array("label"=>"Label", "type"=>"string", "size"=>128, "lang"=>true),
	"shortlabel"=>array("label"=>"Titre court (pour liens)", "type"=>"string", "size"=>64, "lang"=>true),
	"url"=>array("label"=>"URL", "type"=>"string", "size"=>128, "lang"=>true),
	"pagemodel_id"=>array("label"=>"Modèle de page", "type"=>"object", "object_type"=>"pagemodel", "lang"=>false),
	"type"=>array("label"=>"Type", "type"=>"select", "lang"=>false, "default"=>"pagemodel", "select_list"=>array("static_html"=>"Page HTML statique", "pagemodel"=>"Utilisation d'un modèle de page (valeur par défaut)", "redirect"=>"Redirection vers une page extérieure", "alias"=>"Alias d'une autre page du site", "static_html"=>"Page HTML statique", "php"=>"Script PHP")),
	"perm"=>array("label"=>"Permission par défaut", "type"=>"boolean", "default"=>0, "value_list"=>array("Protégé", "Accès pour tous"), "lang"=>false),
	"perm_list"=>array("label"=>"Permissions spécifiques", "type"=>"object_list", "object_type"=>"permission", "db_table"=>"_page_perm_ref", "db_id"=>"page_id", "db_field"=>"perm_id"),
);

/**
 * Retrieve the parameters list from database
 */
public function query_info_more()
{

// Params
$query = db()->query("SELECT `page_id`, `name`, `value`, `update_pos` FROM `_page_params`");
while ($param = $query->fetch_assoc())
{
	$this->list_detail[$param["page_id"]]["param_list"][$param["name"]] = array
	(
		"value"=>json_decode($param["value"], true),
		"update_pos"=>$param["update_pos"]
	);
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

//var_dump(page_current());

page_current()->set($url_params);

if (DEBUG_GENTIME == true)
	gentime("page_gestion::set() [end]");

}

}


/**
 * Defines a model of page
 *
 */
class __page extends _object
{

protected $_type = "page";

protected $pagemodel_id = null;
protected $pagemodel = null;

protected $perm = "";

protected $type = "";
protected $url = "";
protected $shortlabel = "";

// Params
protected $param_list = array();
protected $params_url = array();
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

return array("id", "type", "pagemodel_id", "name", "label", "url", "shortlabel", "param_list", "perm", "perm_list", "redirect_url", "alias_page_id");

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
 * Retrieve the parameters list from database
 */
public function query_params()
{

// Params
$this->param_list = array();
$this->param = array();
$this->params_url = array();
$query = db()->query("SELECT `name`, `value`, `update_pos` FROM `_page_params` WHERE `page_id`='$this->id'");
while ($param = $query->fetch_assoc())
{
	$this->param_list[$param["name"]] = array
	(
		"value"=>json_decode($param["value"], true),
		"update_pos"=>$param["update_pos"]
	);
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
	$this->param[$name] = $param["value"];
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
	$this->param[$name] = $value;

}

/**
 * Update params from URL, GET and POST
 * @param unknown_type $params
 */
public function params_update_url($url_params=array())
{

// Retrieved from the URL
foreach($url_params as $pos=>$value)
{
	if (array_key_exists($pos, $this->params_url) && ($name=$this->params_url[$pos]))
	{
		$this->param[$name] = $value;
	}
}

// Retrieved from $_GET
foreach($_GET as $name=>$value)
{
	if (in_array($name, $this->params_url))
	{
		$this->param[$name] = $value;
	}
}

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

public function pagemodel()
{

if (!$this->pagemodel && $this->pagemodel_id)
	$this->pagemodel = pagemodel()->get($this->pagemodel_id);

return $this->pagemodel;

}

public function pagemodel_params_send(_pagemodel $pagemodel)
{

foreach($this->param as $name=>$value)
{
	$pagemodel->__set($name, $value); // TODO : verify if needed to use value_from_form();
}

}

/**
 * Execute the asociated page model
 */
public function execute()
{

if ($this->type == "pagemodel")
	$this->execute_pagemodel();

}

protected function execute_pagemodel()
{

if (!($pagemodel = $this->pagemodel()))
	return;

$this->pagemodel_params_send($pagemodel);
$pagemodel->action();
$pagemodel->view_disp();
//var_dump($pagemodel);

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
public function url($params=array(), $text=null)
{

if (!is_string($text))
	$text = $this->url;

if ($this->alias_page_id)
{
	if (count($params))
		return SITE_BASEPATH.SITE_LANG."/$text,$this->alias_page_id,".implode(",", $params).".html";
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
public function link($params=array(), $text=null, $text2=null)
{

if (is_string($text2))
	return "<a href=\"".$this->url($params, $text)."\">$text2</a>";
else
	return "<a href=\"".$this->url($params, $text)."\">$this->shortlabel</a>";

}

}


{
	class _page_manager extends __page_manager {};
	class _page extends __page {};
}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>