<?

/**
  * $Id: menu.inc.php 76 2009-10-15 09:24:20Z mathieu $
  * 
  * Copyright 2008 Mathieu Moulin - iProspective - lemathou@free.fr
  * 
  * This file is part of FTNGroupWare.
  * 
  */

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [begin]");

/**
 * Gestion des menus
 */
class menu_gestion
{

protected $list = array();

function get($id)
{

if (isset($this->list[$id]))
{
	return $this->list[$id];
}
elseif ($menu = new menu($id))
{
	return $this->list[$id] = $menu;
}
else
	return null;

}

}

/**
 * Menu
 */
class menu
{

protected $id="0";

protected $list = array();

public function __construct($id)
{

$this->id = $id;

return $this->query();

}

protected function query()
{

$this->list = array();
$query = db()->query("SELECT `_menu_page_ref`.`page_id` FROM `_menu_page_ref` WHERE `_menu_page_ref`.`menu_id` = '$this->id' ORDER BY _menu_page_ref.pos");
if ($query->num_rows())
{
	while (list($page_id)=$query->fetch_row())
	{
		if (is_a($page=page($page_id), "page"))
			$this->list[$page_id] = page($page_id);
		elseif (DEBUG_MENU)
			trigger_error("Menu ID#$this->id : Page ID#$page_id access denied");
	}
}

}

public function disp($method="")
{

if ($method == "table")
{
	$return = "<table class=\"menu menu_$this->id\"><tr>";
	foreach ($this->list as $page)
		if (is_a($page, "page"))
			$return .= "<td>".$page->link()."</td>";
	$return .= "</tr></table>";
	return $return;
}
elseif ($method == "ul")
{
	$return = "<ul class=\"menu menu_$this->id\">";
	foreach ($this->list as $page)
		if (is_a($page, "page"))
			$return .= "<li>".$page->link()."</li>";
	$return .= "</ul>";
	return $return;
}
elseif ($method == "div")
{
	$return = "<div class=\"menu menu_$this->id\">";
	foreach ($this->list as $page)
		if (is_a($page, "page"))
			$return .= "<span>".$page->link()."</span>";
	$return .= "</div>";
	return $return;
}
else // $method == "span"
{
	$return=array();
	foreach ($this->list as $page)
		if (is_a($page, "page"))
			$return[] = $page->link();
	return "<span class=\"menu menu_$this->id\">".implode(" , ",$return)."</span>";
}

}

}

/**
 * Defines the accessible pages
 * 
 * Requiert l'objet $login pour connaître les permissions.
 *
 */
class page_gestion
{

protected $page_id = 0;
protected $list = array();

function __construct()
{

$this->query();

}

/**
 * Retrieve basic infos on all pages
 */
public function query()
{

$this->list = array();
$query_string = " SELECT `_page`.`id` as id , `_page`.`name` as name , `_page`.`template_id` as template_id, `_page`.`redirect_url` as redirect_url, `_page`.`alias_page_id` as alias_page_id, `_page_lang`.`titre` as titre, `_page_lang`.`titre_court` as titre_court, `_page_lang`.`url` as url FROM `_page`, `_page_lang` WHERE `_page`.`id`=`_page_lang`.`id` AND `_page_lang`.`lang_id`='".SITE_LANG_ID."'";
$query = db()->query($query_string);
while ($page = $query->fetch_assoc())
{
	if (DEBUG_MENU)
		echo "<p>page_gestion::query() : $page[id] : $page[name]</p>\n";
	$this->list[$page["id"]] = new page($page["id"], false , $page);
}

}

/**
 * Set the default page
 * 
 * ID#1 : Homepage
 * ID#2 : Page does not exists (HTTP 404)
 * ID#3 : Page unavailable (HTTP 401)
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
	if (!isset($this->list[$i]))
		define("PAGE_ID", 2);
	elseif (!$this->list[$i]->perm_login())
		define("PAGE_ID", 3);
	else
		define("PAGE_ID", $i);
}

$this->page_id = PAGE_ID;
$this->get(PAGE_ID)->set($url_params);

}

/**
 * Get a page
 * @param unknown_type $id
 */
public function get($id=0)
{

if (isset($this->list[$id]))
	return $this->list[$id];
elseif (!$id && $this->page_id)
	return $this->list[$this->page_id];
else
	return null;

}

/**
 * Get the current page
 * @param unknown_type $id
 */
public function current_get()
{

if ($this->page_id)
	return $this->list[$this->page_id];
else
	return null;

}

/**
 * returns if the page exists
 * 
 * @param int $id
 */
public function exists($id)
{

if (isset($this->list[$id]))
	return true;
else
	return false;

}

/**
 * Returns if the user logged-in have the rights to see the page
 *  
 * @param int $id
 */
public function perm($id)
{

if (!isset($this->list[$i]))
	return false;
else
	return $this->list[$i]->perm_login();

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
	while(list(,$page)=each($this->list))
	{
		$return[] = $page->url();
	}
	print "<table class=\"menu\"><tr>\n<td>".implode("</td>\n<td>",$return)."</td>\n</tr></table>";
}
else
{
	while(list(,$page)=each($this->list))
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
class page
{

protected $id = 0;
protected $type = "";
protected $url = "";
protected $name = "";
protected $titre = "";
protected $titre_court = "";
protected $description = "";

// Template and params
protected $template_id = null;
protected $template = null;
protected $params_default = array();
protected $params_url = array();
protected $params_get = array();
// Effective parameters
protected $params = array();
 
// Permissions
protected $perm_list = array();

// Gestion redirection
protected $redirect_url = null;

// Gestion alias
protected $alias_page_id = null;

// Scripts
protected $script_list = array();

protected static $info_list = array("name", "url", "template_id", "titre", "titre_court", "redirect_url", "alias_page_id", "script_list");

protected static $infos = array("type", "name", "description", "template_id", "redirect_url", "alias_page_id");
protected static $infos_lang = array("url", "titre", "titre_court");

function __construct($id, $query=true, $infos=array())
{

if (DEBUG_MENU)
	echo "<p class=\"debug\">page(ID#$id)::__construct()</p>\n";

$this->id = $id;

if ($query) // on récupère les données avec les params $infos
	$this->query($infos);
elseif (count($infos) > 0) // on intègre les données passées par infos
	while (list($i,$j)=each($infos))
	{
		if (in_array($i, self::$info_list))
		{
			if (DEBUG_MENU)
				echo "<br />Page $id __construct : $i = $j\n";
			$this->{$i} = $j;
		}
	}

$this->query_infos();

}

function query_infos()
{

$this->script_list = array();
$query = db()->query("SELECT `pos`, `script_name` FROM `_page_scripts` WHERE `page_id`='$this->id' ORDER BY `pos`");
while (list($pos, $script_name)=$query->fetch_row())
{
	$this->script_list[$pos] = $script_name;
}

$this->perm_list = array();
$query = db()->query("SELECT `perm_id` FROM `_page_perm_ref` WHERE `page_id`='$this->id'");
while (list($perm_id)=$query->fetch_row())
{
	$this->perm_list[] = $perm_id;
}

}

/**
 * Update template (warning, there is a dedicated function to update each param)
 * 
 * @param integer $id
 * @param array $template
 */
public function update($infos=array())
{

foreach(self::$infos as $name)
	if (isset($infos[$name]))
		$this->{$name} = $infos[$name];
foreach(self::$infos_lang as $name)
	if (isset($infos[$name]))
		$this->{$name} = $infos[$name];

if (isset($infos["script_list"]) && is_array($infos["script_list"]))
{
	$this->script_list = array();
	foreach ($infos["script_list"] as $pos=>$script_name)
		$this->script_list[$pos] = $library_id;
}

if (isset($infos["perm_list"]) && is_array($infos["perm_list"]))
{
	$this->perm_list = array();
	foreach ($infos["perm_list"] as $perm)
		$this->perm_list[] = $perm;
}

$this->db_update();

}
/**
 * Update template base infos in database
 */
public function db_update()
{

// Base infos
$l = array();
foreach (self::$infos as $name)
	$l[] = "`_page`.`$name`='".db()->string_escape($this->{$name})."'";
// Language infos
foreach (self::$infos_lang as $name)
	$l[] = "`_page_lang`.`$name`='".db()->string_escape($this->{$name})."'";

//echo "UPDATE `_page`, `_page_lang` SET ".implode(", ", $l)." WHERE `_page`.`id`='$this->id' AND `_page`.`id`=`_page_lang`.`id` AND `_page_lang`.`lang_id`=".SITE_LANG_DEFAULT_ID;

db()->query("UPDATE `_page`, `_page_lang` SET ".implode(", ", $l)." WHERE `_page`.`id`='$this->id' AND `_page`.`id`=`_page_lang`.`id` AND `_page_lang`.`lang_id`=".SITE_LANG_DEFAULT_ID);

// Scripts
db()->query("DELETE FROM `_page_scripts` WHERE `page_id`='$this->id'");
$query_script_list = array();
foreach($this->script_list as $pos=>$script_name)
{
	$query_script_list[] = "('$this->id', '$pos', '$script_name')";
}
if (count($query_script_list)>0)
{
	db()->query("INSERT INTO `_page_scripts` (`page_id`, `pos`, `script_name`) VALUES ".implode(" , ",$query_script_list));
}

// Permissions
db()->query("DELETE FROM `_page_perm_ref` WHERE `page_id`='$this->id'");
$query_perm_list = array();
foreach($this->perm_list as $perm_id)
{
	$query_perm_list[] = "('$this->id', '$perm_id')";
}
if (count($query_perm_list)>0)
{
	db()->query("INSERT INTO `_page_perm_ref` (`page_id`, `perm_id`) VALUES ".implode(" , ",$query_perm_list));
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
 * Charge les paramètres pour le template
 */
public function params_load()
{

$this->params = array();
$query = db()->query("SELECT `name` , `value` , `update_pos` FROM `_page_params` WHERE `page_id`='$this->id'");
while (list($i,$j,$k)=$query->fetch_row())
{
	$this->params[$i] = $j;
	$this->params_default[$i] = $j;
	if (is_numeric($k))
	{
		$this->params_url[$k] = $i;
	}
}

}

/**
 * Add a param
 * @param unknown_type $name
 * @param unknown_type $infos
 */
public function param_add($name, $infos)
{

$this->params_default[$name] = $infos[value];
db()->query("INSERT INTO `_page_params` (`page_id`, `name`, `value`, `update_pos`) VALUES ('$this->id', '$name', '$infos[value]', NULL)");

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

$this->params_default[$name] = $infos["value"];
db()->query("UPDATE `_page_params` SET `value`='$infos[value]', `update_pos`= NULL WHERE `page_id`='$this->id' AND `name`='$name'");
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
foreach ($this->params_url as $n=>$i)
	if ($name == $i)
		unset($this->params_url[$n]);
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
 * Get a property
 *
 * @param string $name
 * @return unknown
 */
public function __get($name)
{

if (in_array($name, self::$info_list))
	return $this->{$name};

}

/**
 * Returns the id, usefull for redirecting, forms, etc.
 *
 * @return integer
 */
public function id()
{

return $this->id;

}
public function name()
{

return $this->name;

}

/**
 * Update params from URL, GET and POST
 * @param unknown_type $params
 */
public function params_update_url($params=array())
{

// Retrieved from the URL
foreach($params as $i=>$value)
{
	if (isset($this->params_url[$i]) && ($name=$this->params_url[$i]))
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

$this->params_load();
$this->params_update_url($params);

}

/**
 * Template creation
 */
protected function template_create()
{

// Create the template
$this->template = clone template($this->template_id);

}
/**
 * Apply page parameters to the associated template
 */
protected function params_apply()
{

// Sends some predefined parameters specifics to a "container" template ..? Not convinced...
$this->template->titre = $this->titre;

// Sends params to the template
foreach ($this->params as $name=>$value)
{
	if (DEBUG_TEMPLATE)
		echo "<p>page(ID#$this->id)::params_apply() : $name => $value</p>\n";
	$this->template->{$name} = $value;
}

}

/**
 * Returns the associated template
 */
function tpl()
{

/*
if ($this->template)
{
	return $this->template;
}
else
{
	$this->template_create();
	$this->params_apply();
	return $this->template;
}
*/

$this->template_create();
$this->params_apply();
return $this->template;

}

/**
 * Returns the url to the page
 *
 * @return string
 */
public function url($params=array(), $text="")
{

if ($this->alias_page_id)
{
	if (count($params))
		return SITE_BASEPATH.SITE_LANG."/$this->url,$this->alias_page_id,".implode(",",$params).".html";
	else
		return SITE_BASEPATH.SITE_LANG."/$this->url,$this->alias_page_id.html";
}
elseif ($this->redirect_url)
{
	return $this->redirect_url;
}
else // template
{
	if (count($params))
		return SITE_BASEPATH.SITE_LANG."/$this->url,$this->id,".implode(",",$params).".html";
	else
		return SITE_BASEPATH.SITE_LANG."/$this->url,$this->id.html";
}

}
/**
 * Returns a rewritten url to the page
 * @param unknown_type $ref
 * @param unknown_type $params
 * @param unknown_type $concat
 */
public function url_rewrite($ref, $params=array(), $concat=false)
{

if ($concat)
	$ref = "$this->url-$ref";

if (count($params))
	return SITE_BASEPATH.SITE_LANG."/$ref,$this->id,".implode(",",$params).".html";
else
	return SITE_BASEPATH.SITE_LANG."/$ref,$this->id.html";

}
/**
 * Returns a rewritten url to the page
 * @param unknown_type $text
 */
public function url_html($text="")
{

return SITE_BASEPATH.SITE_LANG."/$this->url,$this->id.html";

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
	return "<a href=\"".$this->url($params, $text)."\">$this->titre_court</a>";

}

/**
 * Execute scripts to update params, etc.
 */
public function action()
{

foreach($this->params as $_name=>&$_value)
	${$_name} = $_value;

foreach($this->script_list as $_filename)
{
	if (file_exists("page/scripts/$_filename"))
	{
		include "page/scripts/$_filename";
	}
}

}

}

/**
 * Access the menus
 *
 * @return page_databank or page
 */
function menu($id=0)
{

if (!isset($GLOBALS["menu_gestion"]))
{
	$GLOBALS["menu_gestion"] = $_SESSION["menu_gestion"] = new menu_gestion();
}

if (is_numeric($id) && $id>0)
	return $GLOBALS["menu_gestion"]->get($id);
else
	return $GLOBALS["menu_gestion"];

}

/**
 * Access the pages
 *
 * @return menu
 */
function page($id=0)
{

if (!isset($GLOBALS["page_gestion"]))
{
	$GLOBALS["page_gestion"] = $_SESSION["page_gestion"] = new page_gestion();
}

if (is_numeric($id) && $id>0)
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