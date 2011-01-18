<?php

/**
  * $Id$
  * 
  * Copyright 2008-2011 Mathieu Moulin - lemathou@free.fr
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
foreach (datamodel()->list_detail_get() as $id=>$info)
{
	if (isset($_GET["datamodel_id"]) && ($id==$_GET["datamodel_id"]))
		echo "	<option value=\"$id\" selected>[$id] $info[label]</option>\n";
	else
		echo "	<option value=\"$id\">[$id] $info[label]</option>\n";
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
?>
<div style="display: inline;position: absolute;">
Query:
<select id="q_type"><?php
$opt_list = array("like"=>"LIKE", "fulltext"=>"Fulltext");
foreach($opt_list as $i=>$j)
	if (isset($_GET["q_type"]) && $_GET["q_type"] == $i)
		echo "<option value=\"$i\" selected>$j</option>";
	else
		echo "<option value=\"$i\">$j</option>";
?></select>
<input class="q_str" onkeyup="admin_data_query(this)" onblur="object_list_hide($(this).parent().eq(0))" onfocus="this.select();admin_data_query(this)" />
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

if (!isset($_GET["_fields"]))
	$fields = array();
else
	$fields = $_GET["_fields"];

if (!isset($_GET["_sort"]) || !is_array($_GET["_sort"]))
	$sort = array();
else
	$sort = array ($_GET["_sort"][0]=>$_GET["_sort"][1]);

$params = array();
if (isset($_GET["_params"]) && is_array($_GET["_params"])) foreach($_GET["_params"] as $name=>$value)
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

if (!isset($_GET["page"]))
	$_GET["page"] = 1;
if (!isset($_GET["page_nb"]))
	$_GET["page_nb"] = 10;
	
datamodel($_GET["datamodel_id"])->table_list($params, $fields, $sort, $_GET["page"], $_GET["page_nb"]);

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

$(document.zeform).submit(function(){
	$("[name^='_params']", this).each(function(){
		if (!$(this).val())
			$(this).removeAttr("name");
	});
	$(".asmSelect", this).removeAttr("name");
	return true;
});

$(document).ready(function(){
	$("#databank_params input, #databank_params select", document.zeform).each(function(){
		if (this.name)
		{
			$(this).attr("id", '_params['+$(this).attr("name")+']');
			$(this).removeAttr("name");
			$(this).change(function(){
				$(this).attr("name", $(this).attr("id"));
			});
			if ($(this).val())
				$(this).change();
		}
	});
});
</script>
