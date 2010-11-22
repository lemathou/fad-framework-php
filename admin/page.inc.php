<?

/**
  * $Id: page.inc.php 58 2009-03-03 15:47:37Z mathieu $
  * 
  * Copyright 2008 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */

if (!defined("ADMIN_OK"))
{
	die("ACCES NON AUTORISE");
}

?>
<style type="text/css">
td
{
	vertical-align: top;
	font-size: 0.9em;
}
tr.header td
{
	font-size: 1em;
	padding: 10px;
}
td.label
{
	font-weight: bold;
}
table.tpl_params td
{
	padding: 0px 1px;
	border: 1px #ccc solid;
}
table.tpl_params tr.separator td
{
	border: 0px;
}
table.tpl_params tr.tpl_name td
{
	font-weight: bold;
	border: 0px;
	font-size: 1em;
} 
table.tpl_params tr.title td
{
	font-weight: bold;
	background-color: #ffa;
}
input, textarea
{
	width: 100%;
}
</style>

<script type="text/javascript">
function param_update(name)
{
	var element = document.getElementById('param['+name+'][value]');
	element.name = element.id;
	element = document.getElementById('param['+name+'][update_pos]');
	element.name = element.id;
}
// initialisation
editAreaLoader.init({
	id: "update[script]"	// id of the textarea to transform		
	,start_highlight: true	// if start with highlight
	,allow_resize: "both"
	,allow_toggle: true
	,word_wrap: false
	,language: "fr"
	,syntax: "php"	
});
</script>
<?
// Types
$type_list = array
(
	"static_html" => "Page HTML statique",
	"template" => "Utilisation d'un template (valeur par défaut)",
	"redirect" => "Redirection vers une page extérieure",
	"alias" => "Alias d'une autre page du site",
	"static_html" => "Page HTML statique",
	"php" => "Script PHP"
);

// Templates
$template_list = array();
$query = db()->query("SELECT t1.id , t1.name , t2.title FROM _template as t1 LEFT JOIN _template_lang as t2 ON t1.id=t2.id AND t2.lang_id=".SITE_LANG_ID." WHERE t1.type='container'");
while ($template = $query->fetch_assoc())
{
	if (!$template["title"])
		$template["title"] = $template["name"];
	$template_list[$template["id"]] = $template["title"];
}

// Permissions
$perm_list = array();
$query = db()->query("SELECT `id`, `name` FROM `_perm`");
while ($perm = $query->fetch_assoc())
{
	$perm_list[$perm["id"]] = $perm["name"];
}

// ACTIONS

// Insert
if (isset($_POST["insert"]) && is_array($_POST["insert"]))
{

page()->add($_POST["insert"]["name"], $_POST["insert"]);

}

// Update
if (isset($_POST["update"]) && is_array($_POST["update"]) && isset($_POST["update"]["id"]) && page()->exists($id=$_POST["update"]["id"]))
{

page($id)->update($_POST["update"]);
echo "<p>Page mise à jour</p>";

}

// EDITION

if (isset($_GET["id"]) && is_numeric($id=$_GET["id"]) && ($query=db()->query("SELECT t1.id , t1.name , t1.template_id , t2.url , t2.titre_court , t2.titre FROM _page as t1 LEFT JOIN _page_lang as t2 ON t1.id=t2.id AND t2.lang_id=".SITE_LANG_ID." WHERE t1.id='$id'")) && $query->num_rows())
{

$page = $query->fetch_assoc();

$page["perm"] = array();
$query_perm = db()->query("SELECT perm_id FROM _page_perm_ref WHERE page_id='$page[id]'");
while (list($perm_id) = $query_perm->fetch_row())
{
	$page["perm"][] = $perm_id;
}

?>
<p><a href="?list">Retour à la liste</a></p>

<h2>Edition d'une page</h2>

<form action="" method="POST">
<table width="100%" cellspacing="0" cellpadding="0">
<tr>
	<td width="200 class="label">ID :</td>
	<td width="300"><input name="update[id]" value="<?php echo $page["id"]; ?>" readonly /></td>
	<td rowspan="8">
	<h3 style="margin-bottom: 0px;">SCRIPT de contrôle (optionnel)</h3>
	<textarea id="update[script]" name="update[script]" style="background-color:#eee;" rows="20"><?php
	$filename = "page/scripts/$page[name].inc.php";
	if (file_exists($filename) && filesize($filename))
	{
		echo $content = htmlspecialchars(fread(fopen($filename,"r"),filesize($filename)));
	}
	else
	{
		$content="";
	}
	?></textarea>
	</td>
</tr>
<tr>
	<td class="label">Name :</td>
	<td><input name="update[name]" value="<?php echo $page["name"]; ?>" /></td>
</tr>
<tr>
	<td class="label">Template associé :</td>
	<td><select name="update[template_id]"><?php
	foreach ($template_list as $tpl_id=>$name)
	{
		if ($tpl_id == $page["template_id"])
			echo "<option value=\"$tpl_id\" selected>$name</option>";
		else
			echo "<option value=\"$tpl_id\">$name</option>";
	}
	?></select></td>
</tr>
<tr>
	<td class="label">URL (rewriting) :</td>
	<td><input name="update[url]" value="<?php echo $page["url"]; ?>" /></td>
</tr>
<tr>
	<td class="label">Titre court (lien) :</td>
	<td><input name="update[titre_court]" value="<?php echo $page["titre_court"]; ?>" /></td>
</tr>
<tr>
	<td class="label">Titre (header de page) :</td>
	<td><input name="update[titre]" value="<?php echo $page["titre"]; ?>" /></td>
</tr>
<tr>
	<td class="label">Permissions :</td>
	<td><select name="update[perm_list][]" size="4" multiple>
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
	<td><input type="submit" value="Mettre à jour" /></td>
</tr>
</table>
</form>

<?php

// ADD/Update a param
if (isset($_POST["param"]) && is_array($_POST["param"]))
{
	foreach ($_POST["param"] as $name=>$param)
	{
		//echo "<p>Updating param $name : $value</p>\n";
		if (is_numeric($param["update_pos"]))
			db()->query("REPLACE INTO _page_params (page_id, name, value, update_pos) VALUES ('$id', '$name', '".db()->string_escape($param["value"])."', '".db()->string_escape($param["update_pos"])."')");
		else
			db()->query("REPLACE INTO _page_params (page_id, name, value, update_pos) VALUES ('$id', '$name', '".db()->string_escape($param["value"])."', null)");
	}
}

// Delete a param
if (isset($_POST["param_del"]) && ($name=$_POST["param_del"]))
{
	//echo "<p>Param $name DELETED</p>\n";
	db()->query("DELETE FROM _page_params WHERE page_id='$id' AND name='$name'");
}

// Retrieve param list
$params = array();
$params_ok = array();
$query_params = db()->query("SELECT name, value, update_pos FROM _page_params WHERE page_id='$id'");
while (list($name, $value, $update_pos) = $query_params->fetch_row())
{
	$params[$name] = array("value"=>$value, "update_pos"=>$update_pos);
}

if (isset($page["template_id"]) && (is_a($template=template($page["template_id"]), "template")))
{
	?>
	<h3>Liste des paramètres des templates associés :</h3>
	<form method="post">
	<table cellspacing="0" cellpadding="0" border="0" class="tpl_params">
	<?php
	if (count($template->param_list()))
	{
		?>
		<tr class="title header"> <td colspan="5"><?=$template->title()?> : template ID#<?=$template->id()?></td> </tr>
		<?php
		foreach ($template->param_list() as $name=>$param)
		{
		?>
		<tr>
			<td class="label"><?=$name?></td>
			<td></td>
		</tr>
		<?php
		}
	}
	$tpl_filename = "template/".$template->name().".tpl.php";
	$subtemplates = template::subtemplates(fread(fopen($tpl_filename, "r"), filesize($tpl_filename))); 
	foreach($subtemplates as $tpl)
	{
		$template = template($tpl["id"]);
		?>
		<tr class="separator"> <td>&nbsp;</td> </tr>
		<tr class="tpl_name"> <td colspan="5"><?=$template->title()?> (sub-template ID#<?=$template->id()?>)</td> </tr>
		<tr class="separator"> <td>&nbsp;</td> </tr>
		<tr class="title">
			<td>Name (in template)</td>
			<td>Datatype</td>
			<td>Default value (JSON)</td>
			<td>Name (in page)</td>
			<td>Surcharged value (by page)</td>
		</tr>
		<?php
		foreach ($template->param_list() as $name=>$param)
		{
		?>
		<tr>
			<td class="label"><?=$name?></td>
			<td><?=data()->title($param["datatype"])?></td>
			<td><? if ($param["value"] === null) echo "<i>NULL</i>"; else echo json_encode($param["value"]); ?></td>
			<?php
			if (isset($tpl["params"]) && $tpl["params"] === true || (isset($tpl["params"][$name]) && $tpl["params"][$name] == $name))
			{
			?>
			<td class="label"><?=$name?></td>
			<td>
			<textarea id="param[<?=$name?>][value]" cols="40" rows="4"><? if (isset($params[$name])) echo $params[$name]["value"]; ?></textarea>
			<input id="param[<?=$name?>][update_pos]" value="<? if (isset($params[$name])) echo $params[$name]["update_pos"]; ?>" size="1" />
			<input type="submit" value="<?php if (isset($params[$name])) echo "Update"; else echo "Add" ?>" onclick="param_update('<?=$name?>')" />
			<?
			if (isset($params[$name]))
			{
				$params_ok[] = $name;
				echo "<input type=\"submit\" value=\"DEL\" style=\"color:red;\" onclick=\"this.name='param_del';this.value='$name';\" />";
			}
			?>
			</td>
			<?
			}
			elseif (isset($tpl["params"][$name]))
			{
			$name = $tpl["params"][$name];
			?>
			<td class="label"><?=$name?></td>
			<td>
			<textarea id="param[<?=$name?>][value]" cols="40" rows="4"><? if (isset($params[$name])) echo $params[$name]["value"]; ?></textarea>
			<input id="param[<?=$name?>][update_pos]" value="<? if (isset($params[$name])) echo $params[$name]["update_pos"]; ?>" size="1" />
			<input type="submit" value="<?php if (isset($params[$name])) echo "Update"; else echo "Add" ?>" onclick="param_update('<?=$name?>')" />
			<?
			if (isset($params[$name]))
			{
				$params_ok[] = $name;
				echo "<input type=\"submit\" value=\"DEL\" style=\"color:red;\" onclick=\"this.name='param_del';this.value='$name';\" />";
			}
			?>
			</td>
			<?
			}
			else
			{
			?>
			Parameter not passed in parent template
			<?	
			}
			?></td>
		</tr>
		<?php
		}
	}
		?>
		<tr class="separator"> <td>&nbsp;</td> </tr>
		<tr class="tpl_name"> <td colspan="5">Paramètres supplémentaires</td> </tr>
		<tr class="separator"> <td>&nbsp;</td> </tr>
		<tr class="title">
			<td colspan="3">&nbsp;</td>
			<td>Name</td>
			<td>Value</td>
		</tr>
		<?
	foreach ($params as $name=>$param)
	{
		if (!in_array($name, $params_ok))
		{
		?>
		<tr>
			<td colspan="3">&nbsp;</td>
			<td class="label"><?=$name?></td>
			<td>
			<textarea id="param[<?=$name?>][value]" cols="40" rows="4"><?=$params[$name]["value"]?></textarea>
			<input id="param[<?=$name?>][update_pos]" value="<?=$param["update_pos"]?>" size="1" />
			<input type="submit" value="Update" onclick="param_update('<?=$name?>')" />
			<input type="submit" value="DEL" style="color:red;" onclick="this.name='param_del';this.value='<?=$name?>';" />
			</td>
		</tr>
		<?
		}
	}
	?>
	</table>
	</form>
	<?php
}

}

// INSERTION
elseif (isset($_GET["add"]))
{

$page = array
(
	"name" => "",
	"type" => "template",
	"template_id" => "0",
	"url" => "",
	"titre_court" => "",
	"titre" => "",
	"description" => "",
	"perm" => array(),
);

if (isset($_POST["insert"]))
{
	foreach ($page as $i=>$j)
		if (isset($_POST["insert"][$i]))
			$page[$i] = $_POST["insert"][$i];
}

?>
<p><a href="?list">Retour à la liste</a></p>

<h2>Ajout d'une page</h2>

<form action="" method="POST">
<table>
<tr>
	<td class="label">Name</td>
	<td><input name="insert[name]" value="<?=$page["name"]?>" size="32" /></td>
</tr>
<tr>
	<td class="label">Type</td>
	<td><select name="insert[type]">
	<?php
	foreach ($type_list as $type=>$label)
		if ($page["type"] == $type)
			echo "<option value=\"$type\" selected>$label</option>";
		else
			echo "<option value=\"$type\">$label</option>";
	?></select></td>
</tr>
<tr>
	<td class="label">Template :</td>
	<td><select name="insert[template_id]"><?php
	foreach ($template_list as $id=>$name)
	{
		if ($page["template_id"] == $id)
			echo "<option value=\"$id\" selected>$name</option>";
		else
			echo "<option value=\"$id\">$name</option>";
	}
	?></select></td>
</tr>
<tr>
	<td class="label">URL (rewriting)</td>
	<td><input name="insert[url]" value="<?=$page["url"]?>" size="64" /></td>
</tr>
<tr>
	<td class="label">Titre court (lien)</td>
	<td><input name="insert[titre_court]" value="<?=$page["titre_court"]?>" size="64" /></td>
</tr>
<tr>
	<td class="label">Titre (header de page)</td>
	<td><input name="insert[titre]" value="<?=$page["titre"]?>" size="64" /></td>
</tr>
<tr>
	<td class="label">Description</td>
	<td><textarea name="insert[description]" cols="64" rows="4"><?=$page["description"]?></textarea></td>
</tr>
<tr>
	<td class="label">Permissions</td>
	<td><select name="insert[perm][]" size="4" multiple><?
	foreach($perm_list as $i=>$j)
	{
		if (in_array($i, $page["perm"]))
			print "<option value=\"$i\" selected>$j</option>";
		else
			print "<option value=\"$i\">$j</option>";
	}
	?></select></td>
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

<p>Une page est accessible par une url (à l'aide de rewriting).</p>
<p>Une page est de type : "template", "alias" (d'une autre page) ou encore "redirection" (vers une page extérieure au site).</p>
<p>Une page associée à un template se paréamètre en fonction de ce dernier.</p>
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
