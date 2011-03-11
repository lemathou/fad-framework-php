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
class __page_gestion extends _gestion
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
	"script"=>array("label"=>"Script", "type"=>"script", "folder"=>PATH_PAGE, "filename"=>"{name}.inc.php")
);

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

$this->get(PAGE_ID)->set($url_params);

if (DEBUG_GENTIME == true)
	gentime("page_gestion::set() [end]");

}

}


/**
 * Defines a model of page
 *
 */
class __page extends _object_gestion
{

protected $_type = "page";

protected $pagemodel_id = null;

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

return array("id", "pagemodel_id", "name", "label", "perm", "type", "url", "shortlabel", "param_list", "perm_list", "redirect_url");

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

/**
 * Execute the asociated page model
 */
public function execute()
{

if ($pagemodel = pagemodel()->get($this->pagemodel_id))
{
	foreach($this->param as $name->$value)
	{
		$pagemodel->__set($name, $value); // TODO : verify if needed to use value_from_form();
	}
	$pagemodel->action();
	$pagemodel->view_disp();
}

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
	class _pagemodel_gestion extends __pagemodel_gestion {};
	class _pagemodel extends __pagemodel {};
}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>