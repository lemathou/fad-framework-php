<h1>Gestion des langues</h1>
<?php

$query = db()->query("SELECT id, name, code FROM _lang");
while ($lang = $query->fetch_assoc())
{
	echo "<p>[$lang[id]] $lang[code] $lang[name]</p>\n";
}

?>
