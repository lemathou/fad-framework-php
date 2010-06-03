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

protected $list = array();

public function __construct($id)
{

$query = db()->query("SELECT `_menu_page_ref`.`page_id` FROM `_menu_page_ref` WHERE `_menu_page_ref`.`menu_id` = '$id'");
if ($query->num_rows())
{
	while (list($page_id)=$query->fetch_row())
		$this->list[$page_id] = page($page_id);
	return true;
}
else
{
	return false;
}

}

public function disp()
{

foreach($this->list as $page)
	$return[] = $page->link();
 
echo implode(" , ",$return);

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
$query_string = " SELECT _page.id as id , _page.name as name , _page.template_id as template_id , _page_lang.titre as titre , _page_lang.titre_court as titre_court , _page_lang.url as url FROM _page , _page_lang , _page_perm_ref WHERE _page.id = _page_lang.id AND _page_lang.lang_id = '".SITE_LANG_ID."' AND _page_perm_ref.page_id = _page.id AND _page_perm_ref.perm_id IN ( ".implode(" , ",login()->perm_list())." ) ";
$query = db()->query($query_string);
while ($page = $query->fetch_assoc())
	$this->list[$page["id"]] = new page($page["id"], false , $page);

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

protected static $info_list = array ( "name" , "url" , "template_id" , "titre" , "titre_court" );

protected $id = 0;
protected $name = "";
protected $titre = "";
protected $titre_court = "";
protected $description = "";
protected $keywords = "";

protected $template_id = 0;
protected $template = null;

protected $url = "";

protected $params_url = array();
protected $params_get = array();
protected $params = array();

function __construct($id, $query=true, $infos=array())
{

$this->id = $id;

if ($query) // on récupère les données avec les params $infos
	$this->query($infos);
elseif (count($infos) > 0) // on intègre les données passées par infos
	while (list($i,$j)=each($infos))
		if (isset($this->{$i}))
			$this->{$i} = $j;

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
	if (isset($this->params_url[$i]) && ($name=$this->params_url[$i]))
	{
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
	//echo "<p>$name : $value</p>\n";
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
public function url($text="")
{

return SITE_BASEPATH.SITE_LANG."/$this->url,$this->id.html";

}
public function url_html($text="")
{

return SITE_BASEPATH.SITE_LANG."/$this->url,$this->id.html";

}

public function link($text1="", $text2="")
{

if ($text1)
	return "<a href=\"".$this->url()."\">$text1</a>";
else
	return "<a href=\"".$this->url()."\">$this->titre_court</a>";

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

return $GLOBALS["page_gestion"]->get(PAGE_ID);

}

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [end]");

?>
