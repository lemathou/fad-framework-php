<?php

/**
  * $Id$
  * 
  * Copyright 2008-2011 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * http://sourceforge.net/projects/phpfadframework/
  * Licence : http://www.gnu.org/copyleft/gpl.html  GNU General Public License
  * 
  */

if (!defined("ADMIN_OK"))
	die("ACCES NON AUTORISE");

$_type = "pagemodel";
$_label = "Modèle de page";

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

$_type()->retrieve_objects();

?>
<form method="get" class="page_form">
<input type="submit" value="<?php echo $_label; ?>" />
<select name="id" onchange="this.form.submit()">
	<option value=""></option>
<?php
foreach ($_type()->list_get() as $id=>$object)
{
	if (isset($_GET["id"]) && ($id==$_GET["id"]))
		echo "	<option value=\"$id\" selected>[$id] ".$object->label()."</option>\n";
	else
		echo "	<option value=\"$id\">[$id] ".$object->label()."</option>\n";
}
?>
</select>
<a href="?list">Liste</a>
<a href="?add">Ajouter</a>
</form>

<?php

//var_dump(data()->list_name_get());

// Permissions
$permission_list = permission()->list_detail_get();
// Permissions
$template_list = template()->list_detail_get();
template()->retrieve_objects();

// ACTION

if (isset($_GET["id"]) && $_type()->exists($id=$_GET["id"]))
{

$pagemodel = $_type($id);

$submenu_list = array
(
	"update_form"=>"Formulaire",
	"param_list"=>"Paramètres",
	"view_list"=>"Templates associés",
	"action_list"=>"Actions",
);

$submenu = "update_form";

if (isset($_GET["view_name"]))
	$submenu = "view_list";

?>
<div class="admin_menu admin_submenu">
<?php
foreach($submenu_list as $i=>$j)
	if ($submenu == $i)
		echo "<a href=\"javascript:;\" name=\"$i\" onclick=\"admin_submenu(this.name)\" class=\"selected\">$j</a>\n";
	else
		echo "<a href=\"javascript:;\" name=\"$i\" onclick=\"admin_submenu(this.name)\">$j</a>\n";
?>
</div>

<div id="update_form" class="subcontents"<?php if ($submenu != "update_form") echo " style=\"display:none;\""; ?>>
<?php
$pagemodel->update_form();
?>
</div>

<div id="view_list" class="subcontents"<?php if ($submenu != "view_list") echo " style=\"display:none;\""; ?>>
<form method="get">
<input type="hidden" name="id" value="<?php echo $id; ?>" />
<h3>Vue associée : <select name="view_name" onchange="this.form.submit()"><option></option><?php
if (!isset($_GET["view_name"]) || !is_string($_GET["view_name"]) || !$pagemodel->view_exists($_GET["view_name"]))
	$_GET["view_name"] = "";
foreach($pagemodel->view_list() as $view_name=>$vue)
{
	if ($_GET["view_name"] == $view_name)
		echo "<option selected>$view_name</option>";
	else
		echo "<option>$view_name</option>";
}
?></select></h3>
</form>

<?php
if ($view_name=$_GET["view_name"])
{
	if (count($_POST))
		$pagemodel->view_update($view_name, $_POST);
	$vue = $pagemodel->view_get($view_name);
	//var_dump($vue);
	?>
	<div>
	<form method="post">
	<p>Template : <select name="template_id"><option></option>
	<?php
	foreach (template()->list_get() as $tpl_name=>$tpl)
	{
		if ($vue[0] == $tpl->id() || $vue[0] == $tpl->name())
			echo "<option value=\"$tpl_name\" selected>[".$tpl->id()."] ".$tpl->info("type")." : ".$tpl->label()."</option>";
		else
			echo "<option value=\"$tpl_name\">[".$tpl->id()."] ".$tpl->info("type")." : ".$tpl->label()."</option>";
	}
	?>
	</select></p>
	<div>
	<p>Paramètres passés :</p>
	<input type="hidden" name="params" value="" />
	<?php
	if (is_array($vue[1])) foreach($vue[1] as $param_name=>$param)
	{
		echo "<p>$param_name : <input name=\"params[$param_name][0]\" value=\"$param[0]\"</p>";
	}
	?>
	</div>
	<div>
	<p>Sous-templates :</p>
	<input type="hidden" name="params" value="" />
	<?php
	if (is_array($vue[2])) foreach($vue[2] as $subtemplate_name=>$param)
	{
		echo "<p>$subtemplate_name : <input name=\"params[$param_name][0]\" value=\"$param[0]\"</p>";
	}
	?>
	</div>
	<p><input type="submit" value="Mettre à jour" /></p>
	</form>
	</div>
	<?php
}
?>

</div>

<div id="param_list" class="subcontents"<?php if ($submenu != "param_list") echo " style=\"display:none;\""; ?>>
<?php

// Update/add a param
if (isset($_POST["param"]) && is_array($_POST["param"])) foreach ($_POST["param"] as $name=>$param)
{
	//echo "<p>$name</p>\n";
	//var_dump($param);
	if (isset($pagemodel->{$name}))
		$pagemodel->param_update($name, $param);
	else
		$pagemodel->param_add($name, $param);
}

// Delete a param
if (isset($_POST["param_del"]))
{
	$page->param_del($_POST["param_del"]);
}

// Add a param
if (isset($_POST["param_add"]) && is_array($_POST["param_add"]) && isset($_POST["param_add"]["name"]))
{
	$pagemodel->param_add($_POST["param_add"]["name"], $_POST["param_add"]);
}

if ($template=$pagemodel->template())
{
	$page_param_list = $page->param_list_detail();
	$posmax = 0;
	foreach ($page_param_list as $name=>$param) if (isset($param["update_pos"]) && is_numeric($param["update_pos"]))
		$posmax++;
	$params_ok = array();
	?>
	<h3>Paramètres des templates (vues) associé(e)s :</h3>
	<form method="post">
	<table cellspacing="0" cellpadding="0" border="0" class="tpl_params">
	<?php
	$tpl_filename = PATH_TEMPLATE."/".$template->info("type")."/".$template->name().".tpl.php";
	$subtemplates[] = array("id"=>$template->id(), "params"=>true, "type"=>"main");
	foreach(_template::subtemplates($tpl_file=fread(fopen($tpl_filename, "r"), filesize($tpl_filename))) as $tpl)
		$subtemplates[] = array("id"=>$tpl["id"], "params"=>(isset($tpl["params"])?$tpl["params"]:null), "type"=>"sub");
	$tpl_page = "<!--INCLUDE:page/<?=page_current()->name()?>,true-->";
	if (strpos($tpl_file, $tpl_page) !== false && template()->exists_name("page/".$page->name()))
		$subtemplates[] = array("id"=>(int)template()->get_name("page/".$page->name())->id(), "params"=>true, "type"=>"sub");
	foreach($subtemplates as $tpl) if ($template = template($tpl["id"]))
	{
	?>
		<tr class="separator"> <td>&nbsp;</td> </tr>
		<tr class="tpl_name"> <td colspan="7"><?php echo $template->info("type")." : <a href=\"template?id=".$template->id()."\">".$template->label()."</a>"; ?> (<?php if ($tpl["type"]=="sub") echo "Sub-"; elseif ($tpl["type"] == "main") echo "Main "; ?>template ID#<?=$template->id()?>)</td> </tr>
		<?php
		if (count($template->param_list_detail()))
		{
		?>
			<tr class="title">
				<td colspan="3">Template</td>
				<td colspan="4">Page</td>
			</tr>
			<tr class="title">
				<td>Name</td>
				<td>Datatype</td>
				<td>Default value (JSON)</td>
				<td>Name</td>
				<td>Datatype</td>
				<td>Surcharged value (JSON)</td>
				<td>Position</td>
			</tr>
		<?php
		}
		foreach ($template->param_list_detail() as $name=>$param)
		{
		?>
		<tr>
			<td class="label"><?=$name?></td>
			<td style="white-space: nowrap;"><?php echo data()->get_name($param["datatype"])->label; ?></td>
			<td><?php echo json_encode($param["value"]); ?></td>
			<?php
			if (isset($tpl["params"]) && ($tpl["params"] === true || (isset($tpl["params"][$name]))))
			{
				if (isset($tpl["params"][$name]) && $tpl["params"][$name] != $name)
					$name = $tpl["params"][$name];
			?>
			<td class="label"><?=$name?></td>
			<?php
			if ($page->param_exists($name))
			{
				$params_ok[] = $name;
			?>
			<td><p><select id="param[<?=$name?>][datatype]"><option value="">-- Choisir --</option><?php
			foreach (data()->list_detail_get() as $info)
				if ($info["name"] == $page_param_list[$name]["datatype"])
					echo "<option value=\"$info[name]\" selected>$info[label]</option>";
				else
					echo "<option value=\"$info[name]\">$info[label]</option>";
			?></select></p>
			<p>Options :</p>
			<div id="param_opt_list_<?=$name?>"><input type="hidden" id="param[<?=$name?>][opt]" /><?php
			foreach($page_param_list[$name]["opt"] as $i=>$j)
			{
				echo "<p>$i : <textarea id=\"param[$name][opt][$i]\">".json_encode($j)."</textarea> <input type=\"button\" value=\"-\" onclick=\"this.parentNode.parentNode.removeChild(this.parentNode)\" /></p>\n";
			}
			?></div>
			<p><input size="8" /> : <input size="16" /> <input type="button" value="+" onclick="page_param_opt_add('<?=$name?>', this.parentNode.childNodes[0].value, this.parentNode.childNodes[2].value)" /></p></td>
			<td><textarea id="param[<?=$name?>][value]" rows="4" style="width: 100%"><?php echo json_encode($page_param_list[$name]["value"]); ?></textarea></td>
			<td><p><select id="param[<?=$name?>][update_pos]"><option value="">Aucune</option><?php
				if ($page_param_list[$name]["update_pos"] == $posmax-1)
					$pos_max = $posmax-1;
				else
					$pos_max = $posmax;
				for ($i=0;$i<=$pos_max;$i++)
					if (is_numeric($page_param_list[$name]["update_pos"]) && $i == $page_param_list[$name]["update_pos"])
						echo "<option value=\"$i\" selected>$i</option>";
					else
						echo "<option value=\"$i\">$i</option>";
			?></select></p></td>
			<td><input type="submit" value="DEL" style="color:red;" onclick="this.name='param_del';this.value='<?php echo $name; ?>';" /> <input type="submit" value="Update" onclick="page_param_update('<?=$name?>')" /></td>
			<?php
			}
			else
			{
			?>
			<td><p><select id="param[<?=$name?>][datatype]"><option value="">-- Choisir --</option><?php
			foreach (data()->list_detail_get() as $info)
				if ($info["name"] == $param["datatype"])
					echo "<option value=\"$info[name]\" selected>$info[label]</option>";
				else
					echo "<option value=\"$info[name]\">$info[label]</option>";

			?></select></p>
			<p>Options :</p>
			<div id="param_opt_list_<?=$name?>"><input type="hidden" id="param[<?=$name?>][opt]" /><?php
			if (isset($page_param_list[$name]["opt"])) foreach($page_param_list[$name]["opt"] as $i=>$j)
			{
				echo "<p>$i : <textarea id=\"param[$name][opt][$i]\">".json_encode($j)."</textarea> <input type=\"button\" value=\"-\" onclick=\"this.parentNode.parentNode.removeChild(this.parentNode)\" /></p>\n";
			}
			?></div>
			<p><input size="8" /> : <input size="16" /> <input type="button" value="+" onclick="page_param_opt_add('<?=$name?>', this.parentNode.childNodes[0].value, this.parentNode.childNodes[2].value)" /></p></td>
			<td><textarea id="param[<?=$name?>][value]" rows="4" style="width: 100%"></textarea></td>
			<td><p><select id="param[<?=$name?>][update_pos]"><option value="">Aucune</option><?php
				for ($i=0;$i<=$posmax;$i++)
					echo "<option value=\"$i\">$i</option>";
			?></select></p></td>
			<td><input type="hidden" id="param[<?php echo $name; ?>][name]" value="<?php echo $name; ?>" /><input type="submit" value="ADD" onclick="return page_param_add('<?=$name?>')" /></td>
			<?php
			}
			}
			else
			{
			?>
			<td><p>NOT passed in parent template</p></td>
			<?php	
			}
			?>
		</tr>
		<?php
		}
	}
	?>
	<tr class="separator"> <td>&nbsp;</td> </tr>
	<tr class="tpl_name"> <td colspan="7">Paramètres supplémentaires</td> </tr>
	<tr class="separator"> <td>&nbsp;</td> </tr>
	<tr class="title">
		<td colspan="3">&nbsp;</td>
		<td>Name</td>
		<td>Datatype</td>
		<td>Value (JSON)</td>
		<td>Position</td>
	</tr>
	<?php
	foreach ($page_param_list as $name=>$param)
	{
		if (!in_array($name, $params_ok))
		{
		?>
		<tr>
			<td colspan="3">&nbsp;</td>
			<td class="label"><?php echo $name; ?></td>
			<td><select id="param[<?=$name?>][datatype]"><option value="">-- Choisir --</option><?php
			foreach (data()->list_detail_get() as $info)
				if ($info["name"] == $param["datatype"])
					echo "<option value=\"$info[name]\" selected>$info[label]</option>";
				else
					echo "<option value=\"$info[name]\">$info[label]</option>";

			?></select>
			<p>Options :</p>
			<div id="param_opt_list_<?=$name?>"><input type="hidden" id="param[<?=$name?>][opt]" /><?php
			if (isset($page_param_list[$name]["opt"])) foreach($page_param_list[$name]["opt"] as $i=>$j)
			{
				echo "<p>$i : <textarea id=\"param[$name][opt][$i]\">".json_encode($j)."</textarea> <input type=\"button\" value=\"-\" onclick=\"this.parentNode.parentNode.removeChild(this.parentNode)\" /></p>\n";
			}
			?></div>
			<p><input size="8" /> : <input size="16" /> <input type="button" value="+" onclick="page_param_opt_add('<?=$name?>', this.parentNode.childNodes[0].value, this.parentNode.childNodes[2].value)" /></p></td>
			<td><p><textarea id="param[<?php echo $name; ?>][value]" rows="4" style="width: 100%"><?php echo json_encode($param["value"]); ?></textarea></p></td>
			<td><p><select id="param_add[update_pos]"><option value="">Aucune</option><?php
				for ($i=0;$i<=$posmax;$i++)
					echo "<option value=\"$i\">$i</option>";
			?></select></p></td>
			<td><input type="submit" value="Update" onclick="return page_param_update('<?php echo $name; ?>')" /> <input type="submit" value="DEL" style="color:red;" onclick="this.name='param_del';this.value='<?php echo $name; ?>';" /></td>
		</tr>
		<?php
		}
	}
	?>
	<tr class="param_add">
		<td colspan="3"><b>Ajouter un paramètre défini dans aucun template mais dans le script de page :</b></td>
		<td class="label"><input id="param_add[name]" value="" style="width:100%;" /></td>
		<td><p><select id="param_add[datatype]"><option value="">-- Choisir --</option><?php
		foreach (data()->list_detail_get() as $info)
			echo "<option value=\"$info[name]\">$info[label]</option>";
		?></select></p></td>
		<td><p><textarea id="param_add[value]" rows="4" style="width: 100%"></textarea></p></td>
		<td><p><select id="param_add[update_pos]"><option value="">Aucune</option><?php
			for ($i=0;$i<=$posmax;$i++)
				echo "<option value=\"$i\">$i</option>";
		?></select></p></td>
		<td><input type="submit" value="ADD" onclick="return page_param_add()" /> <input type="submit" value="Cancel" style="color:red;" onclick="page_param_add_cancel();" /></td>
	</tr>
	</table>
	</form>
	<?php
}
?>
</div>

<div id="action_list" class="subcontents"<?php if ($submenu != "action_list") echo " style=\"display:none;\""; ?>>
<h1>Actions du controlleur</h1>
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

<h2>Liste et paramétrage des modèles de page disponibles</h2>

<p>Un modèle de page est de type : "template", "page html statique", "script php".</p>
<p>Un modèle de page doté d'une vue se paramètre en fonction du template associé à cette dernière.</p>
<p>Un modèle de page sera généralement associé à une ou plusieurs pages.</p>

<?

$_type()->table_list();

}

?>

