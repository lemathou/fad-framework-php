<?php
header("HTTP/1.0 401 Unavailable");
header("Content-type: text/html; charset=".SITE_CHARSET);
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">

<head>

<meta http-equiv="Content-Type" content="text/html; charset=<?=SITE_CHARSET?>" />
<meta http-equiv="content-Language" content="<?=SITE_LANG?>" />

<meta name="robots" content="noindex,nofollow" />

<title>ADMINISTRATION</title>

<link rel="stylesheet" type="text/css" href="/css/common.css" />
<link rel="stylesheet" type="text/css" href="/css/admin.css" />

<script type="text/javascript" language="Javascript" src="/js/jquery-1.4.2.min.js"></script>

<script type="text/javascript" language="javascript" src="/js/common.js"></script>
<script type="text/javascript" language="javascript" src="/js/admin.js"></script>

</head>

<body>
<form method="post" onsubmit="login_connect(this)">
<input name="_login[password_crypt]" type="hidden" />
<table cellspacing="0" cellpadding="0"><tr>
	<td width="170"><input name="_login[username]" class="username_field" value="" style="width:100%;" /></td>
	<td width="80"><input name="_login[password]" class="password_field" type="password" value="" /></td>
	<td class="tinytext">Mémoriser</td>
	<td><input name="_login[permanent]" type="checkbox" /></td>
	<td><input align="absmiddle" src="/img/bouton/valider.gif" alt="Connexion" type="image" onclick="this.form.submit();" title="Connexion" /></td>
</tr></table>
</form>
</body>

</html>