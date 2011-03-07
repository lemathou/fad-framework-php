<?

include "config/config.inc.php";

header("Content-type: text/html; charset=".SITE_CHARSET);

$c1 = mysql_connect("localhost", "root", "rtf0rezd");
mysql_select_db("maths_groupes", $c1);


$def_list = array();
$query = mysql_query("SELECT * FROM 1_definition", $c1);
while ($def = mysql_fetch_assoc($query))
{
	$def_list[] = $def;
}
$preuve_list = array();
$query = mysql_query("SELECT * FROM 1_preuve", $c1);
while ($preuve = mysql_fetch_assoc($query))
{
	$preuve_list[] = $preuve;
}
$result_list = array();
$query = mysql_query("SELECT * FROM 1_resultat", $c1);
while ($result = mysql_fetch_assoc($query))
{
	$result_list[] = $result;
}
$rq_list = array();
$query = mysql_query("SELECT * FROM 1_remarque", $c1);
while ($rq = mysql_fetch_assoc($query))
{
	$rq_list[] = $rq;
}
$ref_list = array();
$query = mysql_query("SELECT * FROM 1_definition_liens", $c1);
while ($ref = mysql_fetch_assoc($query))
{
	$ref_list[] = $ref;
}

echo mysql_error($c1);

$c2 = mysql_connect("localhost", "root", "rtf0rezd");
mysql_select_db("mathematiques", $c2);
/*
foreach($def_list as $def)
{
	mysql_query("INSERT INTO entity (`insert_datetime`, `type`, `label`, `keywords`, `id2`, `ref2`) VALUES (NOW(), 'definition', '".addslashes($def["nom"])."', '".addslashes($def["keywords"])."', '$def[id]', '$def[ref]')", $c2);
}
foreach($preuve_list as $preuve)
{
	mysql_query("INSERT INTO entity (`insert_datetime`, `type`, `label`, `id2`, `ref2`) VALUES (NOW(), 'proof', '".addslashes($preuve["nom"])."', '$preuve[id]', '$preuve[ref]')", $c2);
}
foreach($rq_list as $rq)
{
	mysql_query("INSERT INTO entity (`insert_datetime`, `type`, `label`, `keywords`, `id2`, `ref2`) VALUES (NOW(), 'remark', '".addslashes($rq["nom"])."', '".addslashes($rq["keywords"])."', '$rq[id]', '$rq[ref]')", $c2);
}
foreach($result_list as $result)
{
	mysql_query("INSERT INTO entity (`insert_datetime`, `type`, `label`, `keywords`, `id2`, `ref2`) VALUES (NOW(), 'result', '".addslashes($result["nom"])."', '".addslashes($result["keywords"])."', '$result[id]', '$result[ref]')", $c2);
}
foreach($ref_list as $ref)
{
	echo "<p>$ref[type_1] $ref[ref_1] : $ref[type_1] $ref[ref_2]</p>\n";
	if ($ref["type_2"] == "resultat")
		$ref["type_2"] = "result";
	if ($ref["type_1"] == "remarque")
		$ref["type_2"] = "remark";
	$query = mysql_query("SELECT id FROM entity WHERE ref2 = '$ref[ref_1]' AND type='$ref[type_1]'", $c2);
	list($id_1) = mysql_fetch_row($query);
	echo mysql_error($c2);
	$query = mysql_query("SELECT id FROM entity WHERE ref2 = '$ref[ref_2]' AND type='$ref[type_2]'", $c2);
	list($id_2) = mysql_fetch_row($query);
	echo mysql_error($c2);
	mysql_query("INSERT INTO entity_ref (`entity_id_2`, `entity_id_1`) VALUES ('$id_2', '$id_1')", $c2);
	if (mysql_error($c2))
		echo "<p>".mysql_error($c2)."</p>";
}

foreach ($def_list as $o)
{
	$filename = "/home/mathieu/Travaux personnels/Mathématiques/Mathématiques supérieures - Site/math/definition/$o[ref].htm";
	echo "<p>$filename</p>\n";
	if (file_exists($filename))
	{
		$content = fread(fopen($filename, "r"), filesize($filename));
		echo $query_str = "UPDATE `entity` SET `content_html`='".addslashes($content)."' WHERE `type`='definition' && `ref2`='$o[ref]'";
		mysql_query($query_str, $c2);
	}
}
*/

foreach ($preuve_list as $o)
{
	$filename = "/home/mathieu/Travaux personnels/Mathématiques/Mathématiques supérieures - Site/math/preuve/$o[ref].htm";
	echo "<p>$filename</p>\n";
	if (file_exists($filename))
	{
		$content = fread(fopen($filename, "r"), filesize($filename));
		$query_str = "UPDATE `entity` SET `content_html`='".addslashes($content)."' WHERE `type`='proof' && `ref2`='$o[ref]'";
		mysql_query($query_str, $c2);
	}
}

foreach ($rq_list as $o)
{
	$filename = "/home/mathieu/Travaux personnels/Mathématiques/Mathématiques supérieures - Site/math/remarque/$o[ref].htm";
	echo "<p>$filename</p>\n";
	if (file_exists($filename))
	{
		$content = fread(fopen($filename, "r"), filesize($filename));
		$query_str = "UPDATE `entity` SET `content_html`='".addslashes($content)."' WHERE `type`='remarq' && `ref2`='$o[ref]'";
		mysql_query($query_str, $c2);
	}
}

foreach ($result_list as $o)
{
	$filename = "/home/mathieu/Travaux personnels/Mathématiques/Mathématiques supérieures - Site/math/resultat/$o[ref].htm";
	echo "<p>$filename</p>\n";
	if (file_exists($filename))
	{
		$content = fread(fopen($filename, "r"), filesize($filename));
		$query_str = "UPDATE `entity` SET `content_html`='".addslashes($content)."' WHERE `type`='result' && `ref2`='$o[ref]'";
		mysql_query($query_str, $c2);
	}
}

if (mysql_error($c2))
	echo "<p>".mysql_error($c2)."</p>";



?>

