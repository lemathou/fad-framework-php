<?

if (DEBUG_GENTIME ==  true)
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
protected $fields = array();

public function __construct(datamodel $datamodel, $fields=array())
{

$this->datamodel = $datamodel;

if (is_array($fields))
	foreach($fields as $name=>$field)
		$this->__set($name, $field);

}

public function __get($name)
{

if (isset($this->fields[$name]))
	return $this->fields[$name];

}

public function __set($name, data $field)
{

if (!isset($this->datamodel->{$name}))
	trigger_error("Datamodel Display : undefined field '$name'");
elseif (!is_a($field, ($type=get_class($this->datamodel->{$name}))))
	trigger_error("Datamodel Display : field '$name' must be an instance of class '$type'");
else
	$this->fields[$name] = $field;

}

public function disp()
{

echo "<div>\n";
foreach ($this->datamodel->fields() as $name=>$field)
	if (isset($this->fields[$name]))
	{
		if ($field->type == "dataobject" || $field->type == "dataobject_list")
			if (isset($field->disp_opt["ref_disp_field"]) && $disp_field=$field->disp_opt["ref_disp_field"])
			{
				$return_obj = array();
				foreach($this->fields[$name]->value as $obj)
					$return_obj[] = $obj->{$disp_field}->disp_url();
				echo "<p>".$field->label." : ".implode(",",$return_obj)."</p>\n";
			}
			else
				echo "<p>".$field->label." : ".$this->fields[$name]->disp_url()."</p>\n";
		else
			echo "<p>".$field->label." : ".$this->fields[$name]."</p>\n";
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
foreach ($this->datamodel->fields() as $name=>$field)
	if (isset($this->fields[$name]))
		$return .= "<tr> <td>".$this->fields[$name]->disp_opt("label")." :</td> <td>".$this->fields[$name]->form_field_disp(false)."</td> </tr>\n";
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

$return = "<div class=\"datamodel_form datamodel_insert_form\">\n";
$return .= "<form id=\"".$this->datamodel->name()."\" method=\"post\" onsubmit=\"return agregat_verify(this, new Array ('".implode("','",$this->datamodel->fields_required())."'))\">\n";
$return .= "<table cellspacing=\"5\" cellpadding=\"0\">\n";
foreach ($this->datamodel->fields() as $name=>$field)
	if (isset($this->fields[$name]))
	{
		if (in_array($name, $this->datamodel->fields_key()))
			$field_class = "field key_field";
		elseif (in_array($name, $this->datamodel->fields_required()))
			$field_class = "field required_field";
		elseif (in_array($name, $this->datamodel->fields_calculated()))
			$field_class = "field calculated_field";
		else
			$field_class = "field";
		$return .= "<tr class=\"$field_class\"> <td class=\"label\">".$this->fields[$name]->disp_opt("label")." :</td> <td>".$this->fields[$name]->form_field_disp(false)."</td> </tr>\n";
	}
$return .= "<tr> <td>&nbsp;</td> <td><input type=\"submit\" value=\"Ajouter\" /></td> </tr>\n";
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
class datamodel_update_form extends datamodel_display
{

public function disp($print=true)
{

$return = "<div class=\"datamodel_form datamodel_update_form\">\n";
$return .= "<form id=\"".$this->datamodel->name()."\" method=\"post\" onsubmit=\"return agregat_verify(this, new Array ('".implode("','",$this->datamodel->fields_required())."'))\">\n";
$return .= "<table cellspacing=\"5\" cellpadding=\"0\">\n";
foreach ($this->datamodel->fields() as $name=>$field)
	if (isset($this->fields[$name]))
	{
		if (in_array($name, $this->datamodel->fields_key()))
			$field_class = "field key_field";
		elseif (in_array($name, $this->datamodel->fields_required()))
			$field_class = "field required_field";
		elseif (array_key_exists($name, $this->datamodel->fields_calculated()))
			$field_class = "field calculated_field";
		else
			$field_class = "field";
		$return .= "<tr class=\"$field_class\"> <td class=\"label\">".$this->fields[$name]->label." :</td> <td>".$this->fields[$name]->form_field_disp(false)."</td> </tr>\n";
	}
$return .= "<tr> <td>&nbsp;</td> <td><input type=\"submit\" name=\"_update\" value=\"Mettre à jour\" /></td> </tr>\n";
$return .= "</table>\n";
$return .= "</form>\n";
$return .= "</div>\n";

if ($print)
	echo $return;
else
	return $return;

}

}

class datamodel_display_tpl_html extends datamodel_display
{

protected $tplfile = "";
protected $tpl = "";

function tplfile_set($name="")
{

if (!is_string($name) || !$name)
	$name = $this->datamodel->name();

$filename = PATH_ROOT."/template/datamodel/$name.tpl.html";

if (file_exists($filename))
{
	$this->tplfile = $name;
	$this->tpl = file_get_contents($filename);
}

}

function replace($string)
{

$a1 = explode(".",$string);
if (count($a1) == 1)
{
	$a2 = explode(":",$string);
	if (count($a2) == 1)
	{
		return $this->fields[$string];
	}
	elseif ($a2[1] == "label")
	{
		return $this->fields[$a2[0]]->label;
	}
	elseif ($a2[1] == "link")
	{
		return $this->fields[$a2[0]]->disp_url();
	}
	elseif ($a2[1] == "disp")
	{
		return $this->fields[$a2[0]]->disp();
	}
}
else
	return $string;

}

function disp($print=true)
{

if (!$this->tplfile)
	$this->tplfile_set();

/*
$from = array();
$to = array();
foreach ($this->fields as $name=>$field)
{
	// Value
	$from[] = "{".$name."}";
	$to[] = $field;
	// Label
	$from[] = "{".$name.":label}";
	$to[] = $field->label;
	// Link
	$from[] = "{".$name.":link}";
	$to[] = $field->disp_url();
	// Form
	$from[] = "{".$name.":form}";
	$to[] = $field->form_field_disp(false);
}
$return = str_replace($from, $to, $this->tpl);
*/

$return = $this->tpl;
while (ereg("{([^}]+)}", $return, $req))
{
	$return = str_replace($req[0], $this->replace("$req[1]"), $return);
}

if ($print)
	echo $return;
else
	return $return;

}

function __tostring()
{

return $this->disp(false);

}

}

class datamodel_display_tpl_php extends datamodel_display
{

protected $tplfile = "";

function tplfile_set($name="")
{

if (!is_string($name) || !$name)
	$name = $this->datamodel->name();

if (file_exists(PATH_ROOT."/template/datamodel/$name=".$this->fields["id"].".tpl.php"))
{
	$this->tplfile = $name."=".$this->fields["id"];
}
elseif (file_exists(PATH_ROOT."/template/datamodel/$name.tpl.php"))
{
	$this->tplfile = $name;
}

}

function disp($print=true)
{

foreach ($this->fields as $i=>$j)
{
	${$i} = $j;
}

include PATH_ROOT."/template/datamodel/".$this->tplfile.".tpl.php";

}

}

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [end]");

?>