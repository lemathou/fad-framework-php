<?php
header("HTTP/1.0 401 Unavailable");
header("Content-type: text/html; charset=".SITE_CHARSET);
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">

<head>

<meta http-equiv="Content-Type" content="text/html; charset=<?php echo SITE_CHARSET; ?>" />
<meta http-equiv="content-Language" content="<?php echo SITE_LANG; ?>" />

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
<table width="100%" cellspacing="0" cellpadding="0"><tr><td align="center">
<h1>Administration</h1>
<h2><?php echo SITE_DOMAIN; ?></h2>
<fieldset style="width: 300px;background: #eef;">
<legend>Connexion</legend>
<table cellspacing="0" cellpadding="2">
<tr>
	<td class="label"><label for="_login[username]">Username</label></td>
	<td width="170"><input id="_login[username]" name="_login[username]" class="username_field" value="" style="width:100%;" /></td>
</tr>
<tr>
	<td class="label"><label for="_login[password]">Password</label></td>
	<td><input id="_login[password]" name="_login[password]" class="password_field" type="password" value="" /></td>
</tr>
<tr>
	<td class="tinytext"><label for="_login[permanent]">Mémoriser</label></td>
	<td><input id="_login[permanent]" name="_login[permanent]" type="checkbox" /></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td><input type="submit" value="Connexion" /></td>
</tr>
</table>
</fieldset>
</td></tr></table>
</form>
</body>

</html>
