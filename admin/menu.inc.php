<?

/**
  * $Id: menu.inc.php 58 2009-03-03 15:47:37Z mathieu $
  * 
  * Copyright 2008 Mathieu Moulin - iProspective - lemathou@free.fr
  * 
  * This file is part of FTNGroupWare.
  * 
  */

if (!defined("ADMIN_OK"))
{
	die("ACCES NON AUTORISE");
}

// Types
$type_list = array
(
	"template" => "Utilisation d'un template (valeur par défaut)",
	"redirect" => "Redirection vers une page extérieure",
	"alias" => "Alias d'une autre page du site",
);

// Templates
$template_list = array();
$query = db()->query(" SELECT t1.id , t1.name , t2.title FROM _template as t1 LEFT JOIN _template_lang as t2 ON t1.id=t2.id AND t2.lang_id=".SITE_LANG_ID." WHERE t1.name NOT LIKE '%/%'");
while ($template = $query->fetch_assoc())
{
	if (!$template["title"])
		$template["title"] = $template["name"];
	$template_list[$template["id"]] = $template["title"];
	
}

// Permissions
$perm_list = array();
$query = db()->query(" SELECT id , name FROM _perm ");
while ($perm = $query->fetch_assoc())
{
	$perm_list[$perm["id"]] = $perm["name"];
}

// ACTIONS

// Insert
if (isset($_POST["insert"]) && is_array($page=$_POST["insert"]))
{

$query_string = " INSERT INTO `_page` ( `name` , `template_id` ) VALUES ( '".db()->string_escape($page["name"])."' , '".db()->string_escape($page["template_id"])."' ) ";
$query = db()->query($query_string);

if ($page["id"]=$query->last_id())
{
	$query_string = " INSERT INTO `_page_lang` ( `id` , `lang_id` , `url` , `titre` , `titre_court` ) VALUES ( '".$page["id"]."' , '".SITE_LANG_ID."' , '".$page["url"]."' , '".db()->string_escape($page["titre"])."' , '".db()->string_escape($page["titre_court"])."' ) ";
	$query = db()->query($query_string);
	
	if (isset($_POST["insert"]["perm"]) && is_array($_POST["insert"]["perm"]) && (count($_POST["insert"]["perm"]) > 0))
	{
		$query_perm_list = array();
		foreach($_POST["insert"]["perm"] as $perm_id)
		{
			if (isset($perm_list[$perm_id]))
			{
				$query_perm_list[] = "( '$page[id]' , '$perm_id' )";
			}
		}
		if (count($query_perm_list)>0)
		{
			$query_string = " INSERT INTO `_page_perm_ref` ( `page_id` , `perm_id` ) VALUES ".implode(" , ",$query_perm_list);
			db()->query($query_string);
		}
	}
}

}

// Update
if (isset($_POST["update"]) && is_array($page=$_POST["update"]))
{

db()->query(" UPDATE `_page` SET `name` = '".db()->string_escape($page["name"])."' , `template_id`='$page[template_id]' WHERE `id`='$page[id]' ");

db()->query(" UPDATE `_page_lang` SET `url` = '".db()->string_escape($page["url"])."' , `titre` = '".db()->string_escape($page["titre"])."' , `titre_court` = '".db()->string_escape($page["titre_court"])."' WHERE `id`='$page[id]' AND `lang_id`='".SITE_LANG_ID."'");

db()->query(" DELETE FROM `_page_perm_ref` WHERE `page_id` = '$page[id]' ");
if (isset($page["perm"]) && is_array($page["perm"]) && (count($page["perm"]) > 0))
{
	$query_perm_list = array();
	foreach($page["perm"] as $perm_id)
	{
		if (isset($perm_list[$perm_id]))
		{
			$query_perm_list[] = "( '$page[id]' , '$perm_id' )";
		}
	}
	if (count($query_perm_list)>0)
	{
		$query_string = " INSERT INTO `_page_perm_ref` ( `page_id` , `perm_id` ) VALUES ".implode(" , ",$query_perm_list);
		db()->query($query_string);
	}
}

db()->query(" DELETE FROM `_page_params` WHERE `page_id` = '$page[id]' ");
if (isset($page["param"]) && is_array($page["param"]) && (count($page["param"]) > 0))
{
	$query_param_list = array();
	foreach($page["param"] as $name=>$value)
	{
		$query_param_list[] = "( '$page[id]' , '$name' , '".db()->string_escape($value)."' )";
	}
	if (count($query_param_list)>0)
	{
		$query_string = " INSERT INTO `_page_params` ( `page_id` , `name` , `value` ) VALUES ".implode(" , ",$query_param_list);
		db()->query($query_string);
	}
}

}

?>

<style type="text/css">
table td
{
	vertical-align: top;
}
</style>

<?php

// EDITION
if (isset($_GET["id"]) && ($id=$_GET["id"]) && ($query=db()->query("SELECT t1.id , t1.name , t1.template_id , t2.url , t2.titre_court , t2.titre FROM _page as t1 LEFT JOIN _page_lang as t2 ON t1.id=t2.id AND t2.lang_id=".SITE_LANG_ID." WHERE t1.id='$id'")) && $query->num_rows())
{

$page = $query->fetch_assoc();

$page["perm"] = array();
$query_perm = db()->query("SELECT perm_id FROM _page_perm_ref WHERE page_id='$page[id]'");
while (list($id) = $query_perm->fetch_row())
{
	$page["perm"][] = $id;
}

?>

<p><a href="?list">Retour à la liste</a></p>

<h2>Edition d'une page</h2>

<form action="" method="POST">
<table>
<tr>
	<td class="label">ID :</td>
	<td><input name="update[id]" value="<?php echo $page["id"]; ?>" readonly /></td>
</tr>
<tr>
	<td class="label">Type :</td>
	<td><input name="update[name]" value="<?php echo $page["name"]; ?>" size="32" /></td>
</tr>
<tr>
	<td class="label">Name :</td>
	<td><input name="update[name]" value="<?php echo $page["name"]; ?>" size="32" /></td>
</tr>
<tr>
	<td class="label">Template associé :</td>
	<td><select name="update[template_id]"><?php
	foreach ($template_list as $id=>$name)
	{
		if ($id == $page["template_id"])
			echo "<option value=\"$id\" selected>$name</option>";
		else
			echo "<option value=\"$id\">$name</option>";
	}
	?></select></td>
</tr>
<tr>
	<td class="label">URL (rewriting) :</td>
	<td><input name="update[url]" value="<?php echo $page["url"]; ?>" size="64" /></td>
</tr>
<tr>
	<td class="label">Titre court (lien) :</td>
	<td><input name="update[titre_court]" value="<?php echo $page["titre_court"]; ?>" size="32" /></td>
</tr>
<tr>
	<td class="label">Titre (header de page) :</td>
	<td><input name="update[titre]" value="<?php echo $page["titre"]; ?>" size="64" /></td>
</tr>
<tr>
	<td class="label">Permissions :</td>
	<td><select name="update[perm][]" size="4" multiple>
	<?
	foreach($perm_list as $i => $j)
	{
		if (in_array($i, $page["perm"]))
			print "<option value=\"$i\" selected>$j</option>";
		else
			print "<option value=\"$i\">$j</option>";
	}
	?>
	</select></td>
</tr>
<tr>
	<td class="label">Paramètres :</td>
	<td><?php
	if (isset($template_list[$page["template_id"]]))
	{
		// Chargement infos template
		$template["params"] = array();
		$query_params = db()->query(" SELECT t1.name , t1.datatype , t1.defaultvalue , t2.description FROM _template_params as t1 LEFT JOIN _template_params_lang as t2 ON t1.template_id=t2.template_id AND t1.name=t2.name AND t2.lang_id='".SITE_LANG_ID."' WHERE t1.template_id = $page[template_id] ");
		while ($param = $query_params->fetch_assoc())
		{
			$template["params"][$param["name"]] = $param;
			$template["params"][$param["name"]]["opt"] = array();
			$query_opt = db()->query("SELECT opttype , optname , optvalue FROM _template_params_opt WHERE template_id='$page[template_id]' AND name='$param[name]'");
			while ($opt=$query_opt->fetch_assoc())
			{
				$template["params"][$param["name"]]["opt"][$opt["opttype"]][$opt["optname"]] = $opt["optvalue"];
			}
		}
		// Chargement params page
		$page["params"] = array();
		$query_params = db()->query("SELECT name , value FROM _page_params WHERE page_id='$page[id]'");
		while ($param=$query_params->fetch_assoc())
		{
			$page["params"][$param["name"]] = $param["value"];
		}
		// Affichage
		foreach ($template["params"] as $name=>$params)
		{
			echo "<p style=\"margin: 0px;\"><b>$name :</b> $params[datatype]</p>\n";
			if ($params["datatype"] == "dataobject")
			{
				echo $databank = $params["opt"]["structure"]["databank"];
				echo "<p style=\"margin: 0px;\">Databank $databank : ";
				echo "<select name=\"update[param][$name]\"><option value=\"0\">-- Sélectionner --</option>\n";
				$query = databank($databank)->query();
				if (!isset($page["params"][$name]))
					$page["params"][$name] = 0;
				foreach ($query as $result)
				{
					if ($page["params"][$name] == "$result->id")
					{
						echo "<option value=\"$result->id\" selected>ID $result->id : $result</option>\n";
					}
					else
					{
						echo "<option value=\"$result->id\">ID $result->id : $result</option>\n";
					}
				}
				echo "</select></p>\n";
			}	
		}
	}
	?></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><input type="submit" value="Mettre à jour" /></td>
</tr>
</table>
</form>

<?php

}

// INSERTION
elseif (isset($_GET["add"]))
{

$page = array
(
	"name" => "",
	"template_id" => "0",
	"url" => "",
	"titre_court" => "",
	"titre" => "",
	"perm" => array(),
);

?>

<p><a href="?list">Retour à la liste</a></p>

<h2>Ajout d'une page</h2>

<form action="" method="POST">
<table>
<tr>
	<td class="label">Name</td>
	<td><input name="insert[name]" value="<?php echo $page["name"]; ?>" size="32" /></td>
</tr>
<tr>
	<td class="label">Template :</td>
	<td><select name="insert[template_id]"><?php
	foreach ($template_list as $id=>$name)
	{
		echo "<option value=\"$id\">$name</option>";
	}
	?></select></td>
</tr>
<tr>
	<td class="label">URL (rewriting)</td>
	<td><input name="insert[url]" value="<?php echo $page["url"]; ?>" size="64" /></td>
</tr>
<tr>
	<td class="label">Titre court (lien)</td>
	<td><input name="insert[titre_court]" value="<?php echo $page["titre_court"]; ?>" size="64" /></td>
</tr>
<tr>
	<td class="label">Titre (header de page)</td>
	<td><input name="insert[titre]" value="<?php echo $page["titre"]; ?>" size="64" /></td>
</tr>
<tr>
	<td class="label">Permissions</td>
	<td><select name="insert[perm][]" size="4" multiple>
	<?
	foreach($perm_list as $i => $j)
	{
		if (in_array($i, $page["perm"]))
			print "<option value=\"$i\" selected>$j</option>";
		else
			print "<option value=\"$i\">$j</option>";
	}
	?>
	</select></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><input type="submit" value="Ajouter" /></td>
</tr>
</table>
</form>

<?php

}

// LISTE
else
{

?>

<h2>Liste et paramétrage des pages disponibles</h2>

<p>Une page est accessible par une url.</p>
<p>Lorsque vous créez une page, vous devez lui associer un template et paramétrer ce template au besoin.</p>
<p>Une page peut être associée à un ou plusieurs menus.</p>

<p><a href="?add">Ajouter une page</a></p>

<table cellspacing="1" border="1" cellpadding="1">
<tr style="font-weight:bold;">
	<td>ID</td>
	<td>Name</td>
	<td>Template</td>
	<td>Title (head)</td>
	<td>URL</td>
	<td>Permissions</td>
</tr>
<?
$query = db()->query(" SELECT t1.id , t1.name , t1.template_id , t2.url , t2.titre , t3.title as template FROM _page as t1 LEFT JOIN _page_lang as t2 ON t1.id=t2.id AND t2.lang_id=".SITE_LANG_ID." LEFT JOIN _template_lang as t3 ON t3.id=t1.template_id AND t3.lang_id=".SITE_LANG_ID." ORDER BY t1.id ");
while ($page = $query->fetch_assoc())
{

print "<tr>\n";
print "<td><a href=\"?id=$page[id]\">$page[id]</a></td>\n";
print "<td><a href=\"?id=$page[id]\">$page[name]</a></td>\n";
print "<td>$page[template]</td>\n";
print "<td>$page[titre]</td>\n";
print "<td>$page[url]</td>\n";
print "<td>";
$query_perm = db()->query("SELECT t1.name FROM _perm as t1 , _page_perm_ref as t2 WHERE t2.page_id = $page[id] AND t1.id=t2.perm_id");
while (list($perm)=$query_perm->fetch_row())
	echo "<p>$perm</p>\n";
echo "</td>\n";
print "</tr>\n";

}

?>
</table>

<?php
}
?>
