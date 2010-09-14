<?

/**
  * $Id: menu.inc.php 76 2009-10-15 09:24:20Z mathieu $
  * 
  * Copyright 2008 Mathieu Moulin - iProspective - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
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
protected $list_detail = array();
protected $list_id = array();

public function __construct()
{

$this->query();

}

public function query()
{

$this->list = array();
$this->list_id = array();
$query = db()->query("SELECT `_menu`.`id`, `_menu`.`name`, `_menu_lang`.`title` FROM `_menu` LEFT JOIN `_menu_lang` ON `_menu`.`id`=`_menu_lang`.`id` AND `_menu_lang`.`lang_id`='".SITE_LANG_ID."'");
while (list($id, $name, $title)=$query->fetch_row())
{
	$this->list_id[$id] = $name;
	$this->list_detail[$id] = array("name"=>$name, "title"=>$title);
}

}

function get($id)
{

if (isset($this->list[$id]))
{
	return $this->list[$id];
}
elseif (APC_CACHE && isset($this->list_detail[$id]) && ($menu=apc_fetch("menu_$id")))
{
	return $this->list[$id] = $menu;
}
elseif (isset($this->list_detail[$id]))
{
	$this->list[$id] = new menu($id, false, $this->list_detail[$id]);
	if (APC_CACHE)
		apc_store("menu_$id", $this->list[$id], APC_CACHE_MENU_TTL);
	return $this->list[$id];
}
else
	return null;

}

function del($id)
{
	
}

function add($name, $title="")
{

db()->query("INSERT INTO `_menu` (`name`) VALUES ('".db()->string_escape($name)."')");
if ($id=db()->last_id())
{
	db()->query("INSERT INTO `_menu_lang` (`id`, `lang_id`, `title`) VALUES ('$id', '".SITE_LANG_DEFAULT_ID."', '".db()->string_escape($title)."')");
	$this->list_id[$id] = $name;
}

}

function list_get()
{

return $this->list_id;

}

}

/**
 * Menu
 */
class menu
{

protected $id = "0";
protected $name = "";
protected $title = "";

protected $list = array();

public function __construct($id, $query=true, $fields=array())
{

$this->id = $id;

foreach ($fields as $i=>$j)
	$this->{$i} = $j;

$this->query();

}

protected function query()
{

//$query = db()->query("SELECT `_menu`.`name`, `_menu_lang`.`title` FROM `_menu` LEFT JOIN `_menu_lang` ON `_menu`.`id`=`_menu_lang`.`id` WHERE `_menu`.`id`='$this->id' AND `_menu_lang`.`lang_id`='".SITE_LANG_DEFAULT_ID."'");
//list($this->name, $this->title)=$query->fetch_row();

$this->list = array();
$query = db()->query("SELECT `_menu_page_ref`.`pos`, `_menu_page_ref`.`page_id` FROM `_menu_page_ref` WHERE `_menu_page_ref`.`menu_id` = '$this->id' ORDER BY `_menu_page_ref`.`pos`");
while (list($pos, $page_id)=$query->fetch_row())
{
	$this->list[$pos] = $page_id;
}

}

public function list_get()
{

return $this->list;

}

public function add($page_id, $pos=null)
{

if (page()->exists($page_id))
{
	if (!is_numeric($pos) || $pos<0 || $pos>count($this->list))
		$pos = count($this->list);
	$this->list[] = $page_id;
	db()->query("INSERT INTO `_menu_page_ref` (`menu_id`, `page_id`, `pos`) VALUES ('$this->id', '$page_id', '$pos')");
}

}

public function del($pos)
{

if (isset($this->list[$pos]))
{
	unset($this->list[$pos]);
	db()->query("DELETE FROM `_menu_page_ref` WHERE `_menu_page_ref`.`menu_id` = '$this->id' AND `_menu_page_ref`.`pos`='$pos'");
	db()->query("UPDATE `_menu_page_ref` SET `pos`=`pos`-1 WHERE `_menu_page_ref`.`menu_id` = '$this->id' AND `_menu_page_ref`.`pos`>'$pos'");
}

}

public function pos_change($pos_from, $pos_to)
{

if (isset($this->list[$pos_from]) && isset($this->list[$pos_to]))
{
	$page_id = $this->list[$pos_from];
	db()->query("DELETE FROM `_menu_page_ref` WHERE `_menu_page_ref`.`menu_id` = '$this->id' AND `_menu_page_ref`.`pos`='$pos_from'");
	db()->query("UPDATE `_menu_page_ref` SET `pos`=`pos`-1 WHERE `_menu_page_ref`.`menu_id` = '$this->id' AND `_menu_page_ref`.`pos`>'$pos_from'");
	db()->query("UPDATE `_menu_page_ref` SET `pos`=`pos`+1 WHERE `_menu_page_ref`.`menu_id` = '$this->id' AND `_menu_page_ref`.`pos`>='$pos_to'");
	db()->query("INSERT INTO `_menu_page_ref` (`menu_id`, `page_id`, `pos`) VALUES ('$this->id', '$page_id', '$pos_to')");
	$this->query();
}

}

public function disp($method="")
{

if ($method == "table")
{
	$return = "<table class=\"menu menu_$this->id\"><tr>";
	foreach ($this->list as $page_id)
		if (page()->exists($page_id))
			$return .= "<td>".page($page_id)->link()."</td>";
	$return .= "</tr></table>";
	return $return;
}
elseif ($method == "ul")
{
	$return = "<ul class=\"menu menu_$this->id\">";
	foreach ($this->list as $page_id)
		if (page()->exists($page_id))
			$return .= "<li>".page($page_id)->link()."</li>";
	$return .= "</ul>";
	return $return;
}
elseif ($method == "div")
{
	$return = "<div class=\"menu menu_$this->id\">";
	foreach ($this->list as $page_id)
		if (page()->exists($page_id))
			$return .= "<span>".page($page_id)->link()."</span>";
	$return .= "</div>";
	return $return;
}
else // $method == "span"
{
	$return=array();
	foreach ($this->list as $page_id)
		if (page()->exists($page_id))
			$return[] = page($page_id)->link();
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
protected $list_detail = array();
protected $list_name = array();

protected static $infos = array("type", "name", "description", "template_id", "redirect_url", "alias_page_id");
protected static $infos_lang = array("url", "titre", "titre_court");

function __construct()
{

$this->query();

}

/**
 * Retrieve basic infos on all pages
 */
public function query($retrieve_all=false)
{

$this->list = array();
$query_string = " SELECT `_page`.`id` as id , `_page`.`name` as name , `_page`.`template_id` as template_id, `_page`.`redirect_url` as redirect_url, `_page`.`alias_page_id` as alias_page_id, `_page_lang`.`titre` as titre, `_page_lang`.`titre_court` as titre_court, `_page_lang`.`url` as url FROM `_page`, `_page_lang` WHERE `_page`.`id`=`_page_lang`.`id` AND `_page_lang`.`lang_id`='".SITE_LANG_ID."'";
$query = db()->query($query_string);
while ($page = $query->fetch_assoc())
{
	if (DEBUG_MENU)
		echo "<p>page_gestion::query() : ID#$page[id] : $page[name]</p>\n";
	$this->list_detail[$page["id"]] = $page;
	$this->list_name[$page["name"]] = $page["id"];
	if ($retrieve_all)
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
	if (!isset($this->list_detail[$i]))
		define("PAGE_ID", 2);
	elseif (!$this->get($i)->perm_login())
		define("PAGE_ID", 3);
	else
		define("PAGE_ID", $i);
}

$this->page_id = PAGE_ID;
$this->get(PAGE_ID)->set($url_params);

}

/**
 * Retrieve a page using its ID
 * @param unknown_type $id
 */
public function get($id=0)
{

if (isset($this->list[$id]))
{
	return $this->list[$id];
}
elseif (APC_CACHE && isset($this->list_detail[$id]) && ($page=apc_fetch("page_$id")))
{
	return $this->list[$id] = $page;
}
elseif (isset($this->list_detail[$id]))
{
	$this->list[$id] = new page($id, false , $this->list_detail[$id]);
	if (APC_CACHE)
		apc_store("page_$id", $this->list[$id], APC_CACHE_PAGE_TTL);
	return $this->list[$id];
}
elseif (!$id && $this->page_id)
{
	return $this->get($this->page_id);
}
else // Bad ID given
{
	return null;
}

}

/**
 * Retrieve a page using its (unique) name
 * @param unknown_type $name
 */
public function __get($name)
{

if (isset($this->list_name[$name]))
{
	return $this->get($this->list_name[$name]);
}
else
{
	return null;
}

}
public function list_get()
{

return $this->list;

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
 * returns if the page exists
 * 
 * @param int $id
 */
public function exists($id)
{

if (isset($this->list_detail[$id]))
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

if (!isset($this->list_detail[$id]))
	return false;
else
	return $this->get($id)->perm_login();

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

/**
 * Add a new page
 * 
 * @param $name
 * @param $infos
 */
function add($name, $infos=array())
{

$query_fields_1 = array();
$query_values_1 = array();
print_r($infos);
// Base infos
foreach (self::$infos as $name)
{
	$query_fields_1[] = "`$name`"; 
	if (!isset($infos[$name]) || $infos[$name] === null)
		$query_values_1[] = "`_page`.`$name`=null";
	else
		$query_values_1[] = "`_page`.`$name`='".db()->string_escape($infos[$name])."'";
}

$query_fields_2 = array();
$query_values_2 = array();
// Language infos
foreach (self::$infos_lang as $name)
{
	$query_fields_2[] = "`$name`"; 
	if (!isset($infos[$name]) || $infos[$name] === null)
		$query_values_2[] = "`_page_lang`.`$name`=null";
	else
		$query_values_2[] = "`_page_lang`.`$name`='".db()->string_escape($infos[$name])."'";
}

db()->query("INSERT INTO `_page` (".implode(", ",$query_fields_1).") VALUES (".implode(", ",$query_values_1).")");

if ($id=db()->last_id())
{
	// Language infos
	$query_fields_2[] = "`id`";
	$query_values_2[] = "'$id'";
	$query_fields_2[] = "`lang_id`";
	$query_values_2[] = "'".SITE_LANG_DEFAULT_ID."'";
	db()->query("INSERT INTO `_page_lang` (".implode(", ",$query_fields_2).") VALUES (".implode(", ",$query_values_2).")");
	// Permissions
	$query_perm_list = array();
	if (isset($infos["perm_list"])) foreach($infos["perm_list"] as $perm_id)
	{
		$query_perm_list[] = "('$id', '$perm_id')";
	}
	if (count($query_perm_list)>0)
	{
		db()->query("INSERT INTO `_page_perm_ref` (`page_id`, `perm_id`) VALUES ".implode(" , ",$query_perm_list));
	}
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

// DB infos
protected static $infos = array("type", "name", "description", "template_id", "redirect_url", "alias_page_id");
protected static $infos_lang = array("url", "titre", "titre_court");

function __construct($id, $query=true, $infos=array())
{

if (DEBUG_MENU)
	echo "<p class=\"debug\">page(ID#$id)::__construct()</p>\n";

$this->id = $id;

if ($query) // on récupère les données avec les params $infos
	$this->query($infos);
foreach ($infos as $i=>$j)
{
	if (in_array($i, array_merge(self::$infos, self::$infos_lang)))
	{
		if (DEBUG_MENU)
			echo "<br />Page $id __construct : $i = $j\n";
		$this->{$i} = $j;
	}
}

$this->query_infos();
$this->params_load();

}

/**
 * Retrieve other infos :
 * - Permissions
 */
function query_infos()
{

// Permissions
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

if (isset($infos["perm_list"]) && is_array($infos["perm_list"]))
{
	$this->perm_list = array();
	foreach ($infos["perm_list"] as $perm)
		$this->perm_list[] = $perm;
}

// Template optionnal script file
if (isset($infos["script"]))
{
	$filename = "page/scripts/$this->name.inc.php";
	if ($infos["script"])
	{
		fwrite(fopen($filename,"w"), htmlspecialchars_decode($infos["script"]));
	}
	elseif (file_exists($filename))
	{
		unlink($filename);
	}
}

$this->db_update();

}
/**
 * Update template base infos in database
 */
public function db_update()
{

$l = array();
// Base infos
foreach (self::$infos as $name)
	if ($this->{$name} === null)
		$l[] = "`_page`.`$name`=null";
	else
		$l[] = "`_page`.`$name`='".db()->string_escape($this->{$name})."'";
// Language infos
foreach (self::$infos_lang as $name)
	if ($this->{$name} === null)
		$l[] = "`_page_lang`.`$name`=null";
	else
		$l[] = "`_page_lang`.`$name`='".db()->string_escape($this->{$name})."'";
	
//echo "UPDATE `_page`, `_page_lang` SET ".implode(", ", $l)." WHERE `_page`.`id`='$this->id' AND `_page`.`id`=`_page_lang`.`id` AND `_page_lang`.`lang_id`=".SITE_LANG_DEFAULT_ID;

db()->query("UPDATE `_page`, `_page_lang` SET ".implode(", ", $l)." WHERE `_page`.`id`='$this->id' AND `_page`.`id`=`_page_lang`.`id` AND `_page_lang`.`lang_id`=".SITE_LANG_DEFAULT_ID);

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
 * Returns the id : usefull for redirecting, forms, etc.
 *
 * @return integer
 */
public function id()
{

return $this->id;

}
/**
 * Returns the name : may be usefull too...
 */
public function name()
{

return $this->name;

}
public function label()
{

return $this->titre_court;

}
/**
 * Access the associated template
 */
function template()
{

return template($this->template_id);

}

/**
 * Load all params infos from database
 */
public function params_load()
{

$this->params = array();
$this->params_default = array();
$this->params_url = array();

$query = db()->query("SELECT `name` , `value` , `update_pos` FROM `_page_params` WHERE `page_id`='$this->id'");
while (list($i, $j, $k)=$query->fetch_row())
{
	$this->params[$i] = json_decode($j);
	$this->params_default[$i] = json_decode($j);
	if (is_numeric($k))
		$this->params_url[$k] = $i;
}

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
		echo "<p>page(ID#$this->id)::params_apply() : $name => $value</p>\n";
	$this->template()->{$name} = $value;
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

foreach($this->params as $_name=>&$_value)
	${$_name} = $_value;

if (file_exists("page/scripts/$this->name.inc.php"))
{
	include "page/scripts/$this->name.inc.php";
}

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

}

/**
 * Access the menus
 *
 * @return menu_databank or menu
 */
function menu($id=0)
{

if (!isset($GLOBALS["menu_gestion"]))
{
	// APC
	if (APC_CACHE)
	{
		if (!($GLOBALS["menu_gestion"]=apc_fetch("menu_gestion")))
		{
			$GLOBALS["menu_gestion"] = new menu_gestion();
			apc_store("menu_gestion", $GLOBALS["menu_gestion"], APC_CACHE_GESTION_TTL);
		}
	}
	// Session
	else
	{
		if (!isset($_SESSION["menu_gestion"]))
			$_SESSION["menu_gestion"] = new menu_gestion();
		$GLOBALS["menu_gestion"] = $_SESSION["menu_gestion"];
	}
}

if ($id)
	return $GLOBALS["menu_gestion"]->get($id);
else
	return $GLOBALS["menu_gestion"];

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