<?php

/**
  * $Id: datamodel_display.inc.php 32 2011-01-24 07:13:42Z lemathoufou $
  * 
  * Copyright 2008-2011 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * http://sourceforge.net/projects/phpfadframework/
  * Licence : http://www.gnu.org/copyleft/gpl.html  GNU General Public License
  * 
  */

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [begin]");


/**
 * object display class
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

$return = "<div>\n";
foreach ($this->object->fields() as $name=>$field)
{
	$return .= "<p>$field->label : $field</p>\n";
}
$return .= "</div>\n";

return $return;

}

}

/**
 * Used to display object properly
 *
 */
class datamodel_display_form extends datamodel_display
{

public function disp($type="table")
{

return $this->content_disp();

}

public function content_disp($type="table")
{

$return = "<table cellspacing=\"2\" cellpadding=\"2\" border=\"0\">\n";
$return .= $this->table_disp();
$return .= "</table>\n";

return $return;

}

public function fields_disp($type="table")
{

$return = "";
foreach ($this->object->fields() as $name=>$field)
{
	if (in_array($name, $this->datamodel->fields_calculated()))
		$field_class = "field calculated_field";
	else
		$field_class = "field";
	$return .= "<tr class=\"$field_class\">\n";
	$return .= "	<td class=\"label\"><label for=\"$name\">".$field->label."</label> :</td>\n";
	$return .= "	<td>".$field->form_field_disp(false)."</td>\n";
	$return .= "</tr>\n";
}

return $return;

}

}

/**
 * Used to insert object
 *
 */
class datamodel_insert_form extends datamodel_display_form
{

public function disp()
{

$return = "<form class=\"datamodel_form datamodel_insert_form ".$this->datamodel->name()."_form\" method=\"post\" onsubmit=\"return agregat_verify(this)\">\n";
$return .= $this->content_disp();
$return .= "<p style=\"text-align: right;margin: 0;\"><input type=\"submit\" value=\"Ajouter\" /></p>\n";
$return .= "</form>\n";

return $return;

}

public function content_disp()
{

$return = "<fieldset>\n";
$return .= "<legend><b>Ajouter :</b> ".$this->datamodel->label()."</legend>\n";
$return .= "<table cellspacing=\"2\" cellpadding=\"2\" border=\"0\" width=\"100%\">\n";
$return .= $this->fields_disp();
$return .= "</table>\n";
$return .= "</fieldset>\n";
	
return $return;

}

}

/**
 * Used to update object
 *
 */
class datamodel_update_form extends datamodel_display_form
{

public function disp()
{

$return = "<form class=\"datamodel_form datamodel_update_form ".$this->datamodel->name()."_form\" method=\"post\" onsubmit=\"return agregat_verify(this)\">\n";
$return .= $this->content_disp();
$return .= "<p style=\"text-align: right;margin: 0;\"><input type=\"submit\" value=\"Mettre à jour\" /></p>\n";
$return .= "</form>\n";

return $return;

}

public function content_disp()
{

$return = "<fieldset>\n";
$return .= "<legend><b>Mettre à jour :</b> ".$this->object."</legend>\n";
$return .= "<table cellspacing=\"2\" cellpadding=\"2\" border=\"0\" width=\"100%\">\n";
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
		$return .= "	<td class=\"label\"><label for=\"_update\">Update datetime</label> :</td>\n";
		$return .= "	<td><input size=\"17\" maxlength=\"17\" value=\"".date("d/m/Y H:i:s", $this->object->_update)."\" readonly /></td>\n";
		$return .= "</tr>\n";
}
$return .= $this->fields_disp();
$return .= "</table>\n";
$return .= "</fieldset>\n";
	
return $return;

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
