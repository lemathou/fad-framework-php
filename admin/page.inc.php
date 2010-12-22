<?

/**
  * $Id$
  * 
  * Copyright 2008 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */

if (!defined("ADMIN_OK"))
	die("ACCES NON AUTORISE");

$_type = "page";
$_label = "Page";

if (isset($_POST["_insert"]))
{
	$_type()->add($_POST);
}

if (isset($_POST["_update"]) && isset($_POST["id"]) && $_type()->exists($_POST["id"]))
{
	$_type($_POST["id"])->update($_POST);
}

if (isset($_POST["_delete"]) && $_type()->exists($_POST["_delete"]))
{
	$_type()->del($_POST["_delete"]);
}

?>
<form method="get" class="page_form">
<input type="submit" value="<?php echo $_label; ?>" />
<select name="id" onchange="this.form.submit()">
	<option value=""></option>
<?php
foreach ($_type()->list_detail_get() as $id=>$info)
{
	if (isset($_GET["id"]) && ($id==$_GET["id"]))
		echo "	<option value=\"$id\" selected>[$id] $info[name]</option>\n";
	else
		echo "	<option value=\"$id\">[$id] $info[name]</option>\n";
}
?>
</select>
<a href="?list">Liste</a>
<a href="?add">Ajouter</a>
</form>

<?

//var_dump(data()->list_name_get());

// Permissions
$permission_list = permission()->list_detail_get();
// Permissions
$template_list = template()->list_detail_get();

// ACTION

if (isset($_GET["id"]) && $_type()->exists($id=$_GET["id"]))
{

?>
<div class="admin_menu admin_submenu">
	<a href="javascript:;" name="update_form" onclick="admin_submenu(this.name)" class="selected">Formulaire</a>
	<a href="javascript:;" name="param_list" onclick="admin_submenu(this.name)">Paramètres</a>
</div>
<div id="update_form" class="subcontents">
<?
$_type($id)->update_form();
?>
</div>

<div id="param_list" class="subcontents" style="display:none;">
<?php

$page = $_type()->list_detail_get($id);

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
		<tr class="title header"> <td colspan="5"><?=$template->label()?> : template ID#<?=$template->id()?></td> </tr>
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
		<tr class="tpl_name"> <td colspan="5"><?=$template->label()?> (sub-template ID#<?=$template->id()?>)</td> </tr>
		<tr class="separator"> <td>&nbsp;</td> </tr>
		<tr class="title">
			<td>Name (in template)</td>
			<td>Datatype</td>
			<td>Default value (JSON)</td>
			<td>Name (in page)</td>
			<td>Surcharged value (by page)</td>
		</tr>
		<?php
		foreach ($template->param_list_detail() as $nb=>$param)
		{
			$name = $param["name"];
		?>
		<tr>
			<td class="label"><?=$name?></td>
			<td><?=data()->get_name($param["datatype"])->label?></td>
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
			<td><p>Parameter not passed in parent template</p></td>
			<?	
			}
			?>
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
?>
</div>
<?php

}

// INSERTION
elseif (isset($_GET["add"]))
{

$_type()->insert_form();

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

<?

page()->table_list();

}

?>
<script type="text/javascript">
function param_update(name)
{
	var element = document.getElementById('param['+name+'][value]');
	element.name = element.id;
	element = document.getElementById('param['+name+'][update_pos]');
	element.name = element.id;
}
</script>