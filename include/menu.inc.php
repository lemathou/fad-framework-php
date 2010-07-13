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
	$return = "<table class=\"menu_$this->id\"><tr>";
	foreach ($this->list as $page)
		if (is_a($page, "page"))
			$return .= "<td>".$page->link()."</td>";
	$return .= "</tr></table>";
	return $return;
}
elseif ($method == "ul")
{
	$return = "<ul class=\"menu_$this->id\">";
	foreach ($this->list as $page)
		if (is_a($page, "page"))
			$return .= "<li>".$page->link()."</li>";
	$return .= "</ul>";
	return $return;
}
elseif ($method == "div")
{
	$return = "<div class=\"menu_$this->id\">";
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
	return "<span class=\"menu_$this->id\">".implode(" , ",$return)."</span>";
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

public function query()
{

$this->list = array();
$query_string = " SELECT _page.id as id , _page.name as name , _page.template_id as template_id, _page.redirect_url as redirect_url, _page.alias_page_id as alias_page_id , _page_lang.titre as titre , _page_lang.titre_court as titre_court , _page_lang.url as url FROM _page , _page_lang , _page_perm_ref WHERE _page.id=_page_lang.id AND _page_lang.lang_id = '".SITE_LANG_ID."' AND _page_perm_ref.page_id = _page.id AND _page_perm_ref.perm_id IN ( ".implode(" , ",login()->perm_list())." )";
if (DEBUG_MENU)
	echo "<br />PAGE_GESTION : ".$query_string;
$query = db()->query($query_string);
while ($page = $query->fetch_assoc())
{
	if (DEBUG_MENU)
		echo "<br />$page[id] : $page[name]\n";
	$this->list[$page["id"]] = new page($page["id"], false , $page);
}

}

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
	if (isset($this->list[$i]))
		define("PAGE_ID", $i);
	elseif ($this->exists($i))
		define("PAGE_ID", 3);
	else
		define("PAGE_ID", 2);
}

$this->page_id = PAGE_ID;
$this->get(PAGE_ID)->set($url_params);

}

public function get($id=0)
{

if (isset($this->list[$id]))
	return $this->list[$id];
elseif (!$id && $this->page_id)
	return $this->list[$this->page_id];
else
	return false;

}

public function current_get()
{

if ($this->page_id)
	return $this->list[$this->page_id];
else
	return false;

}

/**
 * La page existe en base de donnée
 * Mais elle n'est pas nécéssairement accessible (question de droits)
 * 
 * @param int $id
 */
public function exists($id)
{

list($return) = db()->query("SELECT count(*) FROM _page WHERE id = '".db()->string_escape($id)."'")->fetch_row();

if ($return)
	return true;
else
	return false;

}

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
protected $name = "";
protected $titre = "";
protected $titre_court = "";
protected $description = "";
protected $keywords = "";

// Gestion template
protected $template_id = null;
protected $template = null;
protected $params_url = array();
protected $params_get = array();
protected $params = array();
protected $params_default = array();

// Gestion redirection
protected $url = ""; // Ca sert à quoi cette variable ?
protected $redirect_url = null;

// Gestion alias
protected $alias_page_id = null;

protected static $info_list = array ("name", "url", "template_id", "titre", "titre_court", "redirect_url", "alias_page_id");

function __construct($id, $query=true, $infos=array())
{

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
			
}

/**
 * Set the page as default, so create the associated template
 *
 */
public function set($params)
{

$this->params_load();
$this->params_update_url($params);
$this->params_update_get();
$this->params_update_post();

}

/**
 * Charge les paramètres pour le template
 */
public function params_load()
{

$query = db()->query("SELECT `name` , `value` , `update_pos` FROM `_page_params` WHERE `page_id`='$this->id'");
while (list($i,$j,$k)=$query->fetch_row())
{
	$this->params[$i] = $j;
	if (is_numeric($k))
	{
		$this->params_url[$k] = $i;
	}
}

}

public function params_update_url($params)
{

foreach($params as $i=>$value)
{
	if (DEBUG_TEMPLATE)
		echo "<p>PAGE->params_update_url : $i : $value</p>";
	if (isset($this->params_url[$i]) && ($name=$this->params_url[$i]))
	{
		if (DEBUG_TEMPLATE)
			echo "<p>PAGE->params_update_url : $name : $value</p>";
		$this->params[$name] = $value;
	}
}

}
public function params_update_get()
{

foreach($_GET as $name=>$value)
{
	if (in_array($name, $this->params_url))
	{
		$this->params[$name] = $value;
	}
}

}
public function params_update_post()
{

foreach($_POST as $name=>$value)
{
	if (in_array($name, $this->params_url))
	{
		$this->params[$name] = $value;
	}
}

}

/**
 * Applique les paramètres au template
 */
protected function params_apply()
{

// Création du template
$this->template = clone template($this->template_id);

// Paramètres globaux à tout le site
// Voir si on l'utilise, je ne suis pas bien convaincu...
foreach (globals()->get_list() as $name=>$value)
{
	$this->template->{$name} = $value;
}
// Paramètres particuliers à cette page
$this->template->titre = $this->titre;
// Paramètres liés au rewriting
foreach ($this->params as $name=>$value)
{
	if (DEBUG_TEMPLATE)
		echo "<p>PAGE->params_apply : $name : $value</p>\n";
	$this->template->{$name} = $value;
}

}

public function params_list()
{

return $this->params;

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
 * Get a property
 *
 * @param string $name
 * @return unknown
 */
public function get($name)
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
 * Retourne le template associé
 */
function tpl()
{

$this->params_apply();
return $this->template;

}

/**
 * Construct the url
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
public function url_rewrite($ref, $params=array(), $concat=false)
{

if ($concat)
	$ref = "$this->url-$ref";

if (count($params))
	return SITE_BASEPATH.SITE_LANG."/$ref,$this->id,".implode(",",$params).".html";
else
	return SITE_BASEPATH.SITE_LANG."/$ref,$this->id.html";

}

public function url_html($text="")
{

return SITE_BASEPATH.SITE_LANG."/$this->url,$this->id.html";

}

public function link($params=array(), $text="", $text2="")
{

if ($text2)
	return "<a href=\"".$this->url($params, $text)."\">$text2</a>";
else
	return "<a href=\"".$this->url($params, $text)."\">$this->titre_court</a>";

}

public function action()
{

$filename = "page/$this->name.inc.php";

if (file_exists($filename))
	include $filename;

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
