<?php

/**
  * $Id$
  * 
  * Copyright 2008-2011 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  */

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [begin]");


/**
 * datamodel display class
 * 
 * Donne un affichage suivant un datamodel.
 * 
 */
class datamodel_display
{

protected $datamodel = NULL;
protected $object = NULL;

public function __construct(dataobject $object)
{

$this->object = $object;
$this->datamodel = $object->datamodel();

}

public function __get($name)
{

if (isset($this->object->$name))
	return $this->object->$name;

}

public function disp()
{

echo "<div>\n";
foreach ($this->object->field_list() as $name=>$field)
{
	echo "<p>$field->label : $field</p>\n";
}
echo "</div>\n";

}

}

/**
 * Used to update fields
 *
 */
class datamodel_display_form extends datamodel_display
{

public function disp($print=true)
{

$return = "<div>\n";
$return .= "<form id=\"".$this->datamodel->name()."\" method=\"post\" onsubmit=\"return agregat_verify(this, new Array ('".implode("','",$this->datamodel->fields_required())."'))\">\n";
$return .= "<table cellspacing=\"5\" cellpadding=\"0\">\n";
foreach ($this->object->field_list() as $name=>$field)
	$return .= "<tr> <td>".$field->label." :</td> <td>".$field->form_field_disp(false)."</td> </tr>\n";
$return .= "<tr style=\"border:1px gray dotted;\"> <td>&nbsp;</td> <td><input type=\"submit\" value=\"Mettre à jour\" /></td> </tr>\n";
$return .= "</table>\n";
$return .= "</form>\n";
$return .= "</div>\n";

if ($print)
	echo $return;
else
	return $return;

}

}

/**
 * Used to update fields
 *
 */
class datamodel_insert_form extends datamodel_display
{

public function disp($print=true)
{

$return = "<form class=\"datamodel_form datamodel_insert_form ".$this->datamodel->name()."_form\" id=\"".$this->datamodel->name()."_form\" method=\"post\" onsubmit=\"return agregat_verify(this, new Array ('".implode("','",$this->datamodel->fields_required())."'))\">\n";
$return .= "<table cellspacing=\"5\" cellpadding=\"0\">\n";
foreach ($this->object->field_list() as $name=>$field)
{
	if (in_array($name, $this->datamodel->fields_required()))
		$field_class = "field required_field";
	elseif (in_array($name, $this->datamodel->fields_calculated()))
		$field_class = "field calculated_field";
	else
		$field_class = "field";
	$return .= "<tr class=\"$field_class\">\n";
	$return .= "	<td class=\"label\"><label for=\"$name\">".$field->label."</label> :</td>\n";
	$return .= "	<td>".$field->form_field_disp(false)."</td>\n";
	$return .= "</tr>\n";
}
$return .= "<tr>\n";
$return .= "	<td>&nbsp;</td>\n";
$return .= "	<td><input type=\"submit\" value=\"Ajouter\" /></td>\n";
$return .= "</tr>\n";
$return .= "</table>\n";
$return .= "</form>\n";

if ($print)
	echo $return;
else
	return $return;

}

}

/**
 * Used to update fields
 *
 */
class datamodel_update_form extends datamodel_display
{

public function disp($print=true)
{

$return = "<form id=\"".$this->datamodel->name()."_form\" class=\"datamodel_form datamodel_update_form ".$this->datamodel->name()."_form\" method=\"post\" onsubmit=\"return agregat_verify(this, new Array ('".implode("','",$this->datamodel->fields_required())."'))\">\n";
$return .= "<table cellspacing=\"5\" cellpadding=\"0\">\n";
{
		$return .= "<tr class=\"data_id\">\n";
		$return .= "	<td class=\"label\"><label for=\"id\">ID</label> :</td>\n";
		$return .= "	<td><input name=\"id\" size=\"6\" maxlength=\"6\" value=\"".$this->object->id."\" readonly /></td>\n";
		//<input type=\"button\" value=\"update\" onclick=\"admin_data_id_update_toggle(this)\" />
		$return .= "</tr>\n";
}
if ($this->datamodel->info("dynamic"))
{
		$return .= "<tr class=\"data_string\">\n";
		$return .= "	<td class=\"label\"><label>Update datetime</label> :</td>\n";
		$return .= "	<td><input size=\"17\" maxlength=\"17\" value=\"".date("d/m/Y H:i:s", $this->object->_update)."\" readonly /></td>\n";
		$return .= "</tr>\n";
}
foreach ($this->object->field_list() as $name=>$field)
{
	if (in_array($name, $this->datamodel->fields_required()))
		$field_class = "field required_field";
	elseif (in_array($name, $this->datamodel->fields_calculated()))
		$field_class = "field calculated_field";
	else
		$field_class = "field";
	$return .= "<tr class=\"$field_class\">\n";
	$return .= "	<td class=\"label\"><label for=\"$name\">".$field->label."</label> :</td>\n";
	$return .= "	<td>".$field->form_field_disp(false)."</td>\n";
	$return .= "</tr>\n";
}
$return .= "<tr>\n";
$return .= "	<td>&nbsp;</td>\n";
$return .= "	<td><input type=\"submit\" value=\"Mettre à jour\" /></td>\n";
$return .= "</tr>\n";
$return .= "</table>\n";
$return .= "</form>\n";

if ($print)
	echo $return;
else
	return $return;

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
