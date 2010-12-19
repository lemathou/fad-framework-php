<?

/**
  * $Id: page.inc.php 76 2009-10-15 09:24:20Z mathieu $
  * 
  * Copyright 2008 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [begin]");

/**
 * Defines the accessible pages
 */
class page_gestion extends gestion
{

protected $type = "page";

protected $page_id = 0;

protected $info_list = array("name", "type", "template_id", "redirect_url", "alias_page_id", "perm");
protected $info_lang_list = array("label", "description", "url", "shortlabel");

protected function query_info_more()
{

// Permissions
$query = db()->query("SELECT `page_id`, `perm_id` FROM `_page_perm_ref`");
while (list($page_id, $perm_id)=$query->fetch_row())
{
	if (isset($this->list_detail[$page_id]))
		$this->list_detail[$page_id]["perm_list"][] = $perm_id;
}

// Params
$this->params_list = array();
$query = db()->query("SELECT `page_id`, `name` , `value` , `update_pos` FROM `_page_params`");
while ($param = $query->fetch_assoc())
{
	$this->list_detail[$param["page_id"]]["params_list"][] = array("name"=>$param["name"], "value"=>json_decode($param["value"], true), "update_pos"=>$param["update_pos"]);
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
	if (!isset($this->list_detail[$i]))
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

/**
 * Add a new page
 * 
 * @param $name
 * @param $infos
 */
function add_more($infos)
{

$query_perm_list = array();
if (isset($infos["perm_list"]) && is_array($infos["perm_list"])) foreach($infos["perm_list"] as $perm_id)
{
	$query_perm_list[] = "('$id', '$perm_id')";
}
if (count($query_perm_list)>0)
{
	db()->query("INSERT INTO `_page_perm_ref` (`page_id`, `perm_id`) VALUES ".implode(" , ",$query_perm_list));
}

}

function del_more($id)
{

db()->query("DELETE INTO `_page_perm_ref` WHERE `page_id` = '$id'");

}

}

/**
 * Defines an element of the menu, accessible via an specific url
 *
 */
class page extends object_gestion
{

protected $_type = "page";

protected $perm = "";

protected $type = "";
protected $url = "";
protected $shortlabel = "";

// Template and params
protected $template_id = 0;
protected $params_list = array();
protected $params_default = array();
protected $params_url = array();
protected $params_get = array();
// Effective parameters
protected $params = array();
 
// Permissions
protected $perm_list = array();

// Redirect URL
protected $redirect_url = null;

// Page alias
protected $alias_page_id = null;

function __sleep()
{

return array("id", "name", "label", "description", "perm", "type", "url", "shortlabel", "template_id", "params_list", "perm_list", "redirect_url", "alias_page_id");

}
function __wakeup()
{

$this->construct_params();

}

protected function construct_more($infos)
{

$this->construct_params();

}

protected function construct_params()
{

$this->params = array();
$this->params_default = array();
$this->params_url = array();
foreach($this->params_list as $param)
{
	$this->params[$param["name"]] = $param["value"];
	$this->params_default[$param["name"]] = $param["value"];
	if (is_numeric($param["update_pos"]))
		$this->params_url[$param["update_pos"]] = $param["name"];
}

}

protected function query_info_more()
{

// Permissions
$this->perm_list = array();
$query = db()->query("SELECT `perm_id` FROM `_page_perm_ref` WHERE `page_id`='$this->id'");
while (list($perm_id)=$query->fetch_row())
{
	$this->perm_list[] = $perm_id;
}

// Params
$this->params_list = array();
$query = db()->query("SELECT `name` , `value` , `update_pos` FROM `_page_params` WHERE `page_id`='$this->id'");
while ($param = $query->fetch_assoc())
{
	$this->params_list[] = array("name"=>$param["name"], "value"=>json_decode($param["value"], true), "update_pos"=>$param["update_pos"]);
}


}

protected function update_more($infos)
{

if (isset($infos["perm_list"]) && is_array($infos["perm_list"]))
{
	db()->query("DELETE FROM `_page_perm_ref` WHERE `page_id`='$this->id'");
	$query_perm_list = array();
	foreach($infos["perm_list"] as $perm_id)
		$query_perm_list[] = "('$this->id', '$perm_id')";
	if (count($query_perm_list)>0)
		db()->query("INSERT INTO `_page_perm_ref` (`page_id`, `perm_id`) VALUES ".implode(", ",$query_perm_list));
}

// Template optionnal script file
if (isset($infos["script"]))
{
	$filename = "page/$this->name.inc.php";
	if ($infos["script"])
	{
		fwrite(fopen($filename,"w"), htmlspecialchars_decode($infos["script"]));
	}
	elseif (file_exists($filename))
	{
		unlink($filename);
	}
}

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

foreach(login()->perm_list() as $perm_id)
	if (in_array($perm_id, $this->perm_list))
		$return = true;

return $return;

}

/**
 * Access the associated template
 */
function template()
{

if (false)
	echo "<p>page(ID#$this->id) : Accessing to template ID#$this->template_id</p>\n";

return template($this->template_id);

}

/**
 * Add a param
 * @param unknown_type $name
 * @param unknown_type $infos
 */
public function param_add($name, $infos)
{

$this->params_default[$name] = json_decode($infos["value"]);

db()->query("INSERT INTO `_page_params` (`page_id`, `name`, `value`, `update_pos`) VALUES ('$this->id', '$name', '".db()->string_escape($infos["value"])."', NULL)");

if (is_numeric($n=$infos["update_pos"]))
{
	for ($i=count($this->params_url)-1;$i=$n;$i--)
	{
		$this->params_url[$i+1] = $this->params_url[$i];
		unset($this->params_url[$i]);
	}
	$this->params_url[$n] = $name;
	db()->query("UPDATE `_page_params` SET `update_pos`=`update_pos`+1 WHERE `page_id`='$this->id' AND `update_pos` >= $n");
	db()->query("UPDATE `_page_params` SET `update_pos`='$n' WHERE `page_id`='$this->id' AND `name`='$name'");
}

}
/**
 * Update a param
 * @param unknown_type $name
 * @param unknown_type $infos
 */
public function param_update($name, $infos)
{

$this->params_default[$name] = json_decode($infos["value"]);

db()->query("UPDATE `_page_params` SET `value`='".db()->string_escape($infos["value"])."', `update_pos`= NULL WHERE `page_id`='$this->id' AND `name`='$name'");
if ($n=array_search($name, $this->params_url))
{
	unset($this->params_url[$n]);
	for ($i=$n+1;$i=count($this->params_url)-1;$i++)
	{
		$this->params_url[$i-1] = $this->params_url[$i];
		unset($this->params_url[$i]);
	}
	db()->query("UPDATE `_page_params` SET `update_pos`=`update_pos`-1 WHERE `page_id`='$this->id' AND `update_pos` >= $n");
}

if (is_numeric($n=$infos["update_pos"]))
{
	for ($i=count($this->params_url)-1;$i=$n;$i--)
	{
		$this->params_url[$i+1] = $this->params_url[$i];
		unset($this->params_url[$i]);
	}
	$this->params_url[$n] = $name;
	db()->query("UPDATE `_page_params` SET `update_pos`=`update_pos`+1 WHERE `page_id`='$this->id' AND `update_pos` >= $n");
	db()->query("UPDATE `_page_params` SET `update_pos`='$n' WHERE `page_id`='$this->id' AND `name`='$name'");
}

}
/**
 * Delete a param
 * @param $name
 */
public function param_delete($name)
{

unset($this->params_default[$name]);
foreach ($this->params_url as $nb=>$i)
	if ($name == $i)
		unset($this->params_url[$nb]);
db()->query("DELETE FROM `_page_params` WHERE `page_id`='$this->id' AND `name`='$name'");

}

/**
 * Returns list of actual params
 */
public function params_list()
{

return $this->params;

}
/**
 * Returns list of default params
 */
public function params_default_list()
{

return $this->params_default;

}

/**
 * Param exists ?
 * @param unknown_type $name
 */
public function __isset($name)
{

return isset($this->params[$name]);

}
/**
 * Get a param value
 *
 * @param string $name
 * @return unknown
 */
public function __get($name)
{

if (isset($this->params[$name]))
	return $this->params[$name];
else
{
	//trigger_error("PARAM $name not defined");
	return null;
}

}
/**
 * Set a param value
 * @param unknown_type $name
 */
public function __set($name, $value)
{

if (isset($this->params[$name]))
	$this->params[$name] = $value;
else
{
	//trigger_error("PARAM $name not defined");
}

}

/**
 * Update params from URL, GET and POST
 * @param unknown_type $params
 */
public function params_update_url($params=array())
{

//trigger_error("coucou");

// Retrieved from the URL
foreach($params as $pos=>$value)
{
	if (isset($this->params_url[$pos]) && ($name=$this->params_url[$pos]))
	{
		if (DEBUG_TEMPLATE)
			echo "<p>page(ID#$this->id)::params_update_url() : URL $name => $value</p>";
		$this->params[$name] = $value;
	}
}

// Retrieved from _GET
foreach($_GET as $name=>$value)
{
	if (in_array($name, $this->params_url))
	{
		if (DEBUG_TEMPLATE)
			echo "<p>page(ID#$this->id)::params_update_url() : GET $name => $value</p>";
		$this->params[$name] = $value;
	}
}

// Retrieved from _POST
foreach($_POST as $name=>$value)
{
	if (in_array($name, $this->params_url))
	{
		if (DEBUG_TEMPLATE)
			echo "<p>page(ID#$this->id)::params_update_url() : POST $name => $value</p>";
		$this->params[$name] = $value;
	}
}

}

/**
 * Set the page as default, so create the associated template
 *
 */
public function set($params)
{

$this->params_update_url($params);

}

/**
 * Apply page parameters to the associated template
 */
protected function params_apply()
{

// Sends params to the template
foreach ($this->params as $name=>$value)
{
	if (DEBUG_TEMPLATE)
		echo "<p>page(ID#$this->id)::params_apply() to template ID#$this->template_id : $name => $value</p>\n";
	$this->template()->__set($name, $value);
}

}

/**
 * Returns the associated template
 */
function tpl()
{

$this->template()->params_reset();
$this->params_apply();

return $this->template();

}

/**
 * Execute scripts to verify/update params, set new, etc.
 */
public function action()
{

if (file_exists("page/$this->name.inc.php"))
{
	extract($this->params);
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

/**
 * Access the pages
 *
 * @return page_databank or page
 */
function page($id=0)
{

if (!isset($GLOBALS["page_gestion"]))
{
	// APC
	if (APC_CACHE)
	{
		if (!($GLOBALS["page_gestion"]=apc_fetch("page_gestion")))
		{
			$GLOBALS["page_gestion"] = new page_gestion();
			apc_store("page_gestion", $GLOBALS["page_gestion"], APC_CACHE_GESTION_TTL);
		}
	}
	// Session
	else
	{
		if (!isset($_SESSION["page_gestion"]))
			$_SESSION["page_gestion"] = new page_gestion();
		$GLOBALS["page_gestion"] = $_SESSION["page_gestion"];
	}
}

if ($id)
	return $GLOBALS["page_gestion"]->get($id);
else
	return $GLOBALS["page_gestion"];

}

/**
 * Access the current page
 *
 * @return menu
 */
function page_current()
{

return page(PAGE_ID);

}

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [end]");

?>
