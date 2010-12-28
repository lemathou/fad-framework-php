<?php

/**
  * $Id$
  * 
  * Copyright 2010 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */

if (!defined("ADMIN_OK"))
	die("ACCES NON AUTORISE");

if (isset($_GET["datamodel_id"]) && datamodel()->exists($_GET["datamodel_id"]) && isset($_GET["add"]) && count($_POST))
{

$object = datamodel($_GET["datamodel_id"])->create();
$object->update_from_form($_POST);
$object->db_insert();

}

if (isset($_GET["datamodel_id"]) && datamodel()->exists($_GET["datamodel_id"]) && isset($_GET["object_id"]) && datamodel($_GET["datamodel_id"])->exists($_GET["object_id"]) && count($_POST))
{

$object = datamodel($_GET["datamodel_id"])->get($_GET["object_id"]);
$object->update_from_form($_POST);
$object->db_update();

}

?>
<form method="get" class="page_form">
<input type="submit" value="Donnée" />
<select id="datamodel_id" name="datamodel_id" onchange="$('#object_id').val('');this.form.submit()">
	<option value=""></option>
<?php
foreach (datamodel()->list_name_get() as $name=>$id)
{
	if (isset($_GET["datamodel_id"]) && ($id==$_GET["datamodel_id"]))
		echo "	<option value=\"$id\" selected>[$id] $name</option>\n";
	else
		echo "	<option value=\"$id\">[$id] $name</option>\n";
}
?>
</select>
<?php
if (isset($_GET["datamodel_id"]) && datamodel()->exists($_GET["datamodel_id"]))
{
	$databank = datamodel($_GET["datamodel_id"]);
	if ($databank->count() <= 20)
	{
		?>
		<select id="object_id" name="object_id" onchange="this.form.submit()" class="q_id" >
		<option value=""></option>
		<?php
		$query = $databank->query();
		foreach ($query as $object)
		{
			if (isset($_GET["object_id"]) && ("$object->id"==$_GET["object_id"]))
				echo "	<option value=\"$object->id\" selected>[$object->id] $object</option>\n";
			else
				echo "	<option value=\"$object->id\">[$object->id] $object</option>\n";
		}
		?>
		</select>
		<?php
	}
	else
	{
		?>
		ID#<input id="object_id" name="object_id" class="q_id" value="<?php if (isset($_GET["object_id"])) echo $_GET["object_id"]; ?>" onchange="this.form.submit()" size="6" />
		<?
	}
}
if (isset($_GET["datamodel_id"]) && datamodel()->exists($_GET["datamodel_id"]))
{
?>
<div style="display: inline;position: absolute;">
Query:
<select id="q_type" name="q_type"><?php
$opt_list = array("fulltext"=>"Fulltext", "like"=>"LIKE");
foreach($opt_list as $i=>$j)
	if (isset($_GET["q_type"]) && $_GET["q_type"] == $i)
		echo "<option value=\"$i\" selected>$j</option>";
	else
		echo "<option value=\"$i\">$j</option>";
?></select>
<input class="q_str" onkeyup="object_list_query($('#datamodel_id').val(), [{'type':$('#q_type').val(), 'value':this.value}], $(this).parent().parent().eq(0));" onblur="object_list_hide($(this).parent().eq(0))" onfocus="this.select();if(this.value) object_list_query($('#datamodel_id').val(), [{'type':$('#q_type').val(), 'value':this.value}], $(this).parent().parent().eq(0));" />
<a href="?datamodel_id=<?php echo $_GET["datamodel_id"]; ?>&list">Liste</a>
<a href="?datamodel_id=<?php echo $_GET["datamodel_id"]; ?>&add">Ajouter</a>
<div id="q_select" class="q_select"></div>
</div>
<?php
}
?>
</form>

<?php

// Insert form
if (isset($_GET["datamodel_id"]) && $_GET["datamodel_id"] && isset($_GET["add"]))
{

datamodel($_GET["datamodel_id"])->create(true)->insert_form()->disp();

}

// Update form
elseif (isset($_GET["datamodel_id"]) && isset($_GET["object_id"]) && $_GET["object_id"])
{

datamodel($_GET["datamodel_id"])->get($_GET["object_id"])->form()->disp();

}

// List
elseif (isset($_GET["datamodel_id"]) && $_GET["datamodel_id"])
{

if (!isset($_POST["fields"]))
	$fields = array();
else
	$fields = $_POST["fields"];

if (!isset($_POST["sort"]) || !is_array($_POST["sort"]))
	$sort = array();
else
	$sort = array ($_POST["sort"][0]=>$_POST["sort"][1]);

$params = array();
if (isset($_POST["params"]) && is_array($_POST["params"])) foreach($_POST["params"] as $name=>$value)
{
	if (isset(datamodel($_GET["datamodel_id"])->{$name}))
	{
		$field = datamodel($_GET["datamodel_id"])->{$name};
		if (is_a($field, "data_integer"))
			$params[$name] = array("name"=>$name, "value"=>$value);
		elseif (is_a($field, "data_string"))
			$params[$name] = array("name"=>$name, "value"=>"%$value%", "type"=>"LIKE");
		else
			$params[$name] = array("name"=>$name, "value"=>$value);
	}
}

datamodel($_GET["datamodel_id"])->table_list($params, $fields, $sort);

}

?>
<script type="text/javascript">
function databank_list_sort(form, field)
{
	document.zeform['sort[0]'].value = field;
	databank_form_submit();
}
function databank_params_aff()
{
	element = document.getElementById('databank_select_form');
	if (element.style.display == 'block')
		element.style.display = 'none';
	else
		element.style.display = 'block';
}
function databank_form_submit(form)
{
	$("[name^='params']").each(function(){
		if (!$(this).val())
			$(this).attr("name", "");
	});
	document.zeform.submit();
}
$(document).ready(function(){
	$("#databank_params [name]").each(function(){
		$(this).attr("id", 'params['+$(this).attr("name")+']');
		$(this).removeAttr("name");
		$(this).change(function(){
			$(this).attr("name", $(this).attr("id"));
		});
		if ($(this).val())
			$(this).change();
	});
});
</script>
