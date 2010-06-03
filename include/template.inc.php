<?

/**
  * $Id: template.inc.php 76 2009-10-15 09:24:20Z mathieu $
  * 
  * Copyright 2008 Mathieu Moulin - iProspective - lemathou@free.fr
  * 
  * This file is part of FTNGroupWare.
  * 
  */


if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [begin]");

class template_databank
{

protected $default_id = 0;
protected $list = array();

public function get($id=0)
{

if ($this->exists($id))
	return $this->list[$id];
elseif (!$id && TEMPLATE_ID)
	return $this->list[TEMPLATE_ID];
elseif ($this->query($id))
	return $this->list[$id];
else
	return false;

}

function query($id, $retrieve=true)
{

if (db()->query("SELECT id FROM _template WHERE id='$id'")->num_rows())
{
	if ($retrieve)
		$this->list[$id] = new template($id);
	return true;
}
else
	return false;

}

function exists($id)
{

return $this->query($id);

}

}

/**
 * Defines the display of the page, based on database infos and a template file
 * 
 */
class template
{

protected $id=0;

protected $name="";

protected $library_list = array();

/*
 * Complete list of params
 */
protected $param_list = array();
protected $param_list_detail = array();
/*
 * Effective values of params
 */
protected $params = array();

/*
 * Cache
 */
protected $cache_id = "";

function __construct($id, $query=true, $params=array())
{

$this->id = $id;

if ($query)
	$this->query();
if (is_array($params) && count($params)>0)
	foreach ($params as $name=>$value)
			$this->params[$name] = $value;

// Voir si c'est bien utile, je pense que oui pour certaines biblio de fonctions
//$this->library_query();
//$this->library_load();

}

function query($infos=array())
{

list($this->name) = db()->query("SELECT `name` FROM `_template` WHERE `id`='".$this->id."'")->fetch_row();
$query = db()->query("SELECT `order`, `name`, `datatype` FROM `_template_params` WHERE `template_id`='".$this->id."' ORDER BY `order` ASC");
while($param = $query->fetch_row())
{
	$this->param_list[$param[0]] = $param[1];
	$this->param_list_detail[$param[1]]["datatype"] = $param[2];
}
$query = db()->query("SELECT `name`, `opttype`, `optname`, `optvalue` FROM `_template_params_opt` WHERE `template_id`='".$this->id."'");
while($param = $query->fetch_row())
{
	$this->param_list_detail[$param[0]][$param[1]][$param[2]]=$param[3];
}

}

/**
 * Retrieve list of required libraries
 */
private function library_query()
{

$this->library_list = array();
$query = db()->query("SELECT `library_id` FROM `_template_library_ref` WHERE `template_id`='".$this->id."'");
while (list($library_id) = $query->fetch_row())
{
	$this->library_list[] = $library_id;
}

}

/**
 * Load the libraries associated to the template
 */
function library_load()
{

foreach ($this->library_list as $library_id)
{
	if (DEBUG_LIBRARY == true)
		echo "<p>Loading library id#$library_id from template id#$this->id</p>\n";
	library($library_id)->load();
}

}

/**
 * Assignment of params
 */
public function __set($name, $value)
{
	if (in_array($name, $this->param_list))
	{
		//echo "<p>$name : $value</p>\n";
		$this->params[$name] = $value;
		return true;
	}
	else
	{
		return false;
	}
}

/**
 * Affichage
 */
public function disp()
{

if (TEMPLATE_CACHE)
{
	$params_str = "'$this->id'";
	foreach($this->param_list as $name)
	{
		$params_str .= ",";
		if (isset($this->params[$name]))
			$params_str .= "'".addslashes($this->params[$name])."'";
	}
	$this->cache_id = md5($params_str);
	if (!$this->cache_check())
	{
		$this->cache_generate();
	}
	else
	{
		$this->cache_disp();
	}
}
else
{
		foreach ($this->params as $name=>$value)
		{
			${$name} = $value;
		}
		include "template/".$this->name.".tpl.php";
}

}

protected function cache_generate()
{

$filename = "cache/$this->cache_id.tpl.html";

ob_start();
foreach ($this->params as $name=>$value)
	${$name} = $value;
// Faire plutôt du eval() avec remplacement des include et génération de sous-templates.
// Pour les disp() d'object, je ne sais pas trop coment le gérer, pourquoi pas une variable globale qui dit qu'on est en mode cache
//   et qui renvoie seulement <<INCLUDE_DATAMODEL:$datamodel|$id|$view_name>>
//header('Status: 200 OK', false, 200);
include "template/".$this->name.".tpl.php";
$data = ob_get_flush();

//$length = ob_get_length();
fwrite(fopen($filename,"w"), $data);

}

protected function cache_check()
{

$filename = "cache/$this->cache_id.tpl.html";
//echo "<br />".time()." > ".(filemtime($filename)+TEMPLATE_CACHE_TIME)."<br />";
if (!file_exists($filename))
{
	return false;
}
elseif ((($filemtime=filemtime($filename))+TEMPLATE_CACHE_TIME) < time())
{
	return false;
}
else
{
	$return = true;
	foreach($this->param_list_detail as $name=>$param)
	{
		if ($param["datatype"] == "dataobject")
		{
			$query = db()->query("SELECT `datetime` FROM `_databank_update` WHERE databank_id='".$param["structure"]["databank"]."' AND dataobject_id='".$this->params[$name]."' ORDER BY datetime DESC LIMIT 1");
			if ($query->num_rows())
			{
				list($i) = $query->fetch_row();
				if (strtotime($i)>filemtime($filename))
					$return=false;
			}
		}
	}
	return $return;
}

}

protected function cache_disp()
{

$filename = "cache/$this->cache_id.tpl.html";
//header('Status: 304 Not Modified', false, 304);
header('Last-Modified: '.gmdate('D, d M Y H:i:s',filemtime($filename)).' GMT');
//header('Expires: '.gmdate('D, d M Y H:i:s',filemtime($filename)+60).' GMT');
header('Content-Length: '.filesize($filename));
echo fread(fopen($filename, "r"), filesize($filename));

}

}

/**
 * Accès aux templates
 * 
 * @param integer $id
 */
function template($id=0)
{

if (!isset($GLOBALS["template"]))
{
	$_SESSION["template"] = $GLOBALS["template"] = new template_databank();
}

if (is_numeric($id) && $id>0)
	return $GLOBALS["template"]->get($id);
else
	return $GLOBALS["template"];

}

function template_current()
{

return template(TEMPLATE_ID);

}

// Nouvelle classe BIDON !!!
/*
class template_html
{

protected $name="";
protected $params=array();

protected $tpl="";

function __construct($name, $params=array())
{

$filename = "/home/mathieu/workspace/FTN GroupWare/"."template/$name.tpl.html";
$this->tpl = fread(fopen($filename,"r"), filesize($filename));

}

function disp()
{

$tpl = $this->tpl;

// A récupérer dans les paramètres du template
$params = array( "id" );
foreach($params as $i)
	$this->params[$i] = $_GET[$i];

// A obtenir :
$this->params["personne"] = databank("personne")->get($this->params["id"]);
$this->params["personne"]->db_retrieve_all();

while (ereg("{([^}]+)}", $tpl, $req))
{
	$tpl = str_replace($req[0], $this->replace("$req[1]"), $tpl);
}

echo $tpl;

}

protected function replace($string, $object=null)
{

$l = explode(":",$string);

if (count($l)>1)
{
	if (substr($l[0], 0, 1) == "\$")
	{
		$var = substr($l[0], 1);
		if ($object)
			return $this->replace($l[1], $object->{$var});
		else
			return $this->replace($l[1], $this->params[$var]);
	}
}
elseif (count($l) == 1)
{
	if ($pos = strpos($string, "("))
	{
		$var = substr($string, 0, $pos);
		if ($object)
			return $object->$var();
		else
			return $var();
	}
	elseif (substr($string, 0, 1) == "\$")
	{
		$var = substr($string, 1);
		if ($object)
			return $object->{$var};
		else
			return $this->params[$var];
	}
}
else
{
	return $string;
}

}

}

function template_html($name, $params=array())
{

return new template_html($name, $params);

}
*/

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [end]");

?>
