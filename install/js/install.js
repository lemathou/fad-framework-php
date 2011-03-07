/**
  * $Id: install.js 50 2011-03-05 18:30:47Z lemathoufou $
  * 
  * Copyright 2008-2011 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * http://sourceforge.net/projects/phpfadframework/
  * Licence : http://www.gnu.org/copyleft/gpl.html  GNU General Public License
  * 
  */

function folder_lookup(name)
{
	var popup = window.open("/install/folder.php?file_choose_name="+name, "folders", "menubar=no, status=no, scrollbars=yes, width=800, height=400");
	popup.focus();
}

function file_delete(name)
{
	var element = $("[name='action']").get(0);
	element.name = "file_delete";
	element.value = name;
	element.form.submit();
}

function file_rename(field, name)
{
	field.name = "file_rename_new";
	var element = $("[name='action']").get(0);
	element.name = 'file_rename';
	element.value = name;
	element.form.submit();
}

function file_view(name)
{
	var popup = window.open("/install/view.php?file="+name, "view", "menubar=no, status=no, scrollbars=yes, width=600, height=400");
	popup.focus();
}

function path_choose(tag_name, name)
{
	$("[name='"+tag_name+"']", window.opener.document).val(name);
	window.opener.focus();
	window.close();
}

