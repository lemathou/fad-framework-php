<?

include "config/config.inc.php";
define("SITE_LANG",SITE_LANG_DEFAULT);
define("SITE_LANG_ID",SITE_LANG_DEFAULT_ID);
define("REDIRECT",false);

include PATH_INCLUDE."/header.inc.php";

// Démarrage de la session
include "include/session_start.inc.php";

databank();

function action()
{

foreach ($_GET as $i=>$j)
	$_POST[$i] = $j;

// Databank
if (!isset($_POST["databank"]) || !($databank=databank($_POST["databank"])))
	die("[]\n");

if (!isset($_POST["params"]))
	$_POST["params"] = array();

if (isset($_POST["q"]))
	$_POST["params"][] = array("value"=>$_POST["q"]);

if (!isset($_POST["fields"]))
	$_POST["fields"] = array();

$query = $databank->query($_POST["params"], $_POST["fields"], array(), 10);

echo "[\n";
if (count($query))
{
	foreach ($query as $object)
	{
		$field_list = array();
		foreach ($_POST["fields"] as $i)
		{
			if (isset($object->{$i}))
			{
				if ($object->{$i}->value === null)
					$field_list[] = "$i:null";
				elseif ($object->{$i}->value === true)
					$field_list[] = "$i:true";
				elseif ($object->{$i}->value === false)
					$field_list[] = "$i:false";
				elseif (is_numeric($object->{$i}->value))
					$field_list[] = "$i:".$object->{$i}->value;
				else
					$field_list[] = "$i:".json_encode($object->{$i}->value)."";
			}
		}
		echo "	{id:$object->id, value:'".addslashes("$object")."', fields:{".implode(", ", $field_list)."}},\n";
	}
}
echo "]\n";

}

header("Content-type: text/html; charset=".SITE_CHARSET);
action();

?>
