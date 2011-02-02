<?

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [begin]");

/**
 * This file contains form classes, to display data and agregats.
 * Put all the javascript code you need.
 *
 */
class form
{

public $name="";

public $method="POST";
public $action="";
public $enctype="";
public $accept="";

public $js_onsubmit="";
public $js_onreset="";

public $css_id="";
public $css_class="";
public $css_style="";

public $field_list = array();

public static $field_type_list = array( "hidden" , "password" , "text" , "textarea" , "enum" , "set" );

public function __construct($name, $options=array())
{

$this->name = $name;
while (list($name, $value) = each($options))
	if (isset($this->{$name}))
		$this->{$name} = $value;

}

public function field_add(form_field $form_field, $options=array())
{

$this->field_list[$form_field->name] = $form_field;


/*
if (isset(self::$field_type_list[$type]))
{
	$this->field_list[$name] = new "form_field_$type"($name, $options);
}
*/

}

public function field_get($name)
{

if (isset($this->field_list[$name]))
	return $this->field_list[$name];
else
	return NULL;

}

public function validate()
{



}

public function value_set($field_list=array())
{

while (list($name, $value) = each($field_list))
	if (isset($this->field_list[$name]) && $this->field_list[$name]->type != "submit")
		$this->field_list[$name]->value_set($value);

}

public function disp($disp="table")
{

$this->header_disp();
foreach ($this->field_list as $field)
	if ($field->type == "hidden")
		print "<tr>".$field->disp(false)."</tr>";
if ($disp=="table")
{
	print "<table>\n";
	reset($this->field_list);
	foreach ($this->field_list as $field)
		if ($field->type != "hidden")
		{
			print "<tr>\n";
			if ($field->label)
				print "<td>".$field->label."&nbsp;:</td>\n";
			else
				print "<td>&nbsp;</td>\n";
			print "<td>".$field->disp(false)."</td>\n";
			print "</tr>\n";
		}
	print "</table>";
}
$this->footer_disp();

}

public function header_disp($print=true)
{

$return = "<form name=\"$this->name\" action=\"$this->action\" method=\"$this->method\">\n";

if ($print)
	print $return;
else
	return $return;

}

public function footer_disp($print=true)
{

$return = "</form>\n";

if ($print)
	print $return;
else
	return $return;

}

protected function css_disp()
{

$return = "";
if ($this->css_id)
	$$return .=" id=\"$this->css_id\"";
if ($this->css_class)
	$$return .=" class=\"$this->css_class\"";
if ($this->css_style)
	$$return .=" style=\"$this->css_style\"";

return $return;

}

}

// Classes des champs

// Classe abstraite de base
abstract class form_field
{

public $name="";
public $type="text";

public $label="";
public $alt="";
public $tabindex=NULL;
public $accesskey="";

public $css_id="";
public $css_class="";
public $css_style="";

public $js_onchange="";
public $js_onselect="";
public $js_onfocus="";
public $js_onblur="";

public $user_change = true;

public function __construct($name, $type, $value, $options=array())
{

$this->name=$name;
$this->type=$type;
$this->value=$value;
$this->option_set($options);

}

function option_set($options=array())
{

while (list($name,$value)=each($options))
	if (isset($this->{$name}))
		$this->{$name} = $value;

}

function disp($print=true)
{


}

function css_disp()
{

$return = "";
if ($this->css_id)
	$return .=" id=\"$this->css_id\"";
if ($this->css_class)
	$return .=" class=\"$this->css_class\"";
if ($this->css_style)
	$return .=" style=\"$this->css_style\"";

return $return;

}

function js_disp()
{

$return = "";
if ($this->js_onchange)
	$return .=" onchange=\"$this->js_onchange\"";
if ($this->js_onblur)
	$return .=" onblur=\"$this->js_onblur\"";
if ($this->js_onfocus)
	$return .=" onfocus=\"$this->js_onfocus\"";
	
return $return;

}

}

// Champ texte
class form_field_text extends form_field
{

public $value="";
public $label="";
public $size=20;
public $maxlength=255;

function __construct($name, $value, $options=array())
{

form_field::__construct($name, "text", $value, $options);

}

function value_set($value)
{

$this->value = $value;

}

function disp($print=true)
{

$return = "<input type=\"text\" name=\"$this->name\" value=\"$this->value\" size=\"$this->size\" maxlength=\"$this->maxlength\"".$this->css_disp()."/>";

if ($print)
	print $return;
else
	return $return;

}

}

// Champ password
class form_field_password extends form_field_text
{

function __construct($name, $value, $options=array())
{

form_field::__construct($name, "password", $value, $options);

}

function disp($print=true)
{

$return = "<input type=\"password\" name=\"$this->name\" value=\"$this->value\" size=\"$this->size\" maxlength=\"$this->maxlength\"".$this->css_disp()."/>";

if ($print)
	print $return;
else
	return $return;

}

}

// Champ hidden
class form_field_hidden extends form_field
{

public $value="";

function __construct($name, $value, $options=array())
{

form_field::__construct($name, "hidden", $value, $options);

}

function value_set($value)
{

$this->value = $value;

}

function disp($print=true)
{

$return = "<input type=\"hidden\" name=\"$this->name\" value=\"$this->value\"".$this->css_disp()."/>";

if ($print)
	print $return;
else
	return $return;

}

}

// Champ textarea
class form_field_textarea extends form_field
{

public $value="";
public $cols=15;
public $rows=4;
public $maxlength=255;
public $readonly = false;

function __construct($name, $value, $options=array())
{

form_field::__construct($name, "textarea", $value, $options);

}

function value_set($value)
{

$this->value = $value;

}

function disp($print=true)
{

$return = "<textarea name=\"$this->name\" cols=\"$this->cols\" rows=\"$this->rows\" maxlength=\"$this->maxlength\"".$this->css_disp().">".$this->value."</textarea>";

if ($print)
	print $return;
else
	return $return;

}

}

// Liste select
// A finir
// penser � g�rer les optgroup !
class form_field_select extends form_field
{

public $value_list=array();
public $value_selected=NULL;
public $disabled=false;
public $size=1;
public $multiple=false;

function __construct($name, $value, $options=array())
{

form_field::__construct($name, "select", $value, $options);

$this->value_selected = $value;

}

function value_set($value)
{

$this->value_selected = $value;

}

function disp($print=true)
{

$return = "<select name=\"$this->name\"".$this->css_disp().$this->js_disp().">".$this->options_disp()."</select>";
if ($print)
	echo $return;
else
	return $return;

}

function options_disp($print=false)
{

$return = "";
foreach ($this->value_list as $value => $text)
	if ($value == $this->value_selected)
		$return .= "<option value=\"$value\" selected=\"selected\">$text</option>";
	else
		$return .= "<option value=\"$value\">$text</option>";

if ($print)
	print $return;
else
	return $return;

}

}

// Checkbox
// reste � traiter le cas de plusieurs valeurs
class form_field_checkbox extends form_field
{

public $value_list=array();

function __construct($name, $value, $options=array())
{

form_field::__construct($name, "checkbox", $value, $options);

}

function disp($print=true)
{

$return = "<input type=\"checkbox\" name=\"$this->name\"".$this->css_disp()." value=\"$this->value\" checked=\"$this->selected/>";

if ($print)
	print $return;
else
	return $return;

}

}

// Bouton radio
class form_field_radio extends form_field
{

public $value_list=array("");
public $value_selected=NULL;

function __construct($name, $value, $options=array())
{

form_field::__construct($name, "radio", $value, $options);

}

function disp($print=true)
{

$return = "<input type=\"radio\" name=\"$this->name\"".$this->css_disp()." value=\"$this->value\" selected=\"$this->selected\"/>";

if ($print)
	print $return;
else
	return $return;

}

}

// Bouton Submit
class form_field_submit extends form_field
{

public $value="";

function __construct($name, $value, $options=array())
{

form_field::__construct($name, "submit", $value, $options);

}

function disp($print=true)
{

$return = "<input type=\"submit\" name=\"$this->name\"".$this->css_disp()." value=\"$this->value\"/>";

if ($print)
	print $return;
else
	return $return;

}

}

// Bouton reset
class form_field_reset extends form_field
{

public $value="";

function __construct($name, $options=array())
{

form_field::__construct($name, "reset", $value, $options);

}

function disp($print=true)
{

$return = "<input type=\"submit\" name=\"$this->name\"".$this->css_disp()." value=\"$this->value\"/>";

if ($print)
	print $return;
else
	return $return;

}

}

if (DEBUG_GENTIME ==  true)
	gentime(__FILE__." [end]");

?>