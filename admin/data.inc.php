<?php

/**
  * $Id: data.inc.php 76 2010-10-15 09:24:20Z mathieu $
  * 
  * Copyright 2010 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */

if (!defined("ADMIN_OK"))
{
	die("ACCES NON AUTORISE");
}

if (isset($_GET["datamodel_id"]) && $_GET["datamodel_id"] && isset($_GET["add"]) && count($_POST))
{

$object = datamodel($_GET["datamodel_id"])->create();
$object->update_from_form($_POST);
$object->db_insert();

}

if (isset($_GET["datamodel_id"]) && $_GET["datamodel_id"] && isset($_GET["object_id"]) && $_GET["object_id"] && count($_POST))
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
if (isset($_GET["datamodel_id"]) && $_GET["datamodel_id"])
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
<?php } ?>
</form>

<div style="padding-top: 30px">
<?php

if (isset($_GET["datamodel_id"]) && $_GET["datamodel_id"] && isset($_GET["add"]))
{

datamodel($_GET["datamodel_id"])->create()->insert_form()->disp();

}

elseif (isset($_GET["datamodel_id"]) && isset($_GET["object_id"]) && $_GET["object_id"])
{

datamodel($_GET["datamodel_id"])->get($_GET["object_id"])->form()->disp();

}

elseif (isset($_GET["datamodel_id"]) && $_GET["datamodel_id"])
{

if (!isset($_POST["params"]))
	$params = array();
else
{
	$params = array();
	foreach($_POST["params"] as $n=>$v) if ($n)
		$params[] = array("name"=>$n, "value"=>$v);
	//print_r($params);
}
if (!isset($_POST["fields"]))
	$fields = array();
else
{
	$fields = $_POST["fields"];
	//print_r($fields);
}
if (!isset($_POST["sort"]))
	$sort = array();
else
{
	$sort = array ($_POST["sort"][0]=>$_POST["sort"][1]);
	//print_r($sort);
}
datamodel($_GET["datamodel_id"])->table_list($params, $fields, $sort);

}

?>
</div>
