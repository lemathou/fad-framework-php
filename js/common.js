/**
  * $Id$
  * 
  * Copyright 2008-2010 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * 
  */

// DOM Access elements
function getID(element_id)
{
	return document.getElementById(element_id);
}

// Lookup in arrays
function in_array(a, val)
{
    for(var i = 0, l = a.length; i < l; i++) {
        if(a[i] == val) {
            return true;
        }
    }
    return false;
}

// Addslashes and Stripslashes
function addslashes(str) {
	str=str.replace(/\\/g,'\\\\');
	str=str.replace(/\'/g,'\\\'');
	str=str.replace(/\"/g,'\\"');
	str=str.replace(/\0/g,'\\0');
	return str;
}
function stripslashes(str) {
	str=str.replace(/\\'/g,'\'');
	str=str.replace(/\\"/g,'"');
	str=str.replace(/\\0/g,'\0');
	str=str.replace(/\\\\/g,'\\');
	return str;
}

// Retrieve Cookies
function getCookieVal(offset)
{
	var endstr=document.cookie.indexOf (";", offset);
	if (endstr==-1) endstr=document.cookie.length;
	return unescape(document.cookie.substring(offset, endstr));
}
function LireCookie(nom)
{
	var arg=nom+"=";
	var alen=arg.length;
	var clen=document.cookie.length;
	var i=0;
	while (i<clen) {
		var j=i+alen;
		if (document.cookie.substring(i, j)==arg) return getCookieVal(j);
		i=document.cookie.indexOf(" ",i)+1;
		if (i==0) break;
	}
	return null;
}


// Retrieve GET vars
function getUrlVars()
{
	var vars = [], hash;
	var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
	for(var i = 0; i < hashes.length; i++)
	{
		hash = hashes[i].split('=');
		if ((n=hash[0].indexOf('['))>0 || (n=hash[0].indexOf('%5B'))>0)
		{
			hash[0] = hash[0].slice(0, n);
			if (!vars[hash[0]])
			{
				vars.push(hash[0]);
				vars[hash[0]] = new Array();
			}
			vars[hash[0]].push(hash[1]);
		}
		else
		{
			vars.push(hash[0]);
			vars[hash[0]] = hash[1];
		}
		//alert(hash[0]);
	}
	return vars;
}
function getUrlVar(name)
{
	return getUrlVars()[name];
}


// Add to Bookmark
function bookmark_add()
{

var title = "TOP GONES :: Le site Lyonnais des bonnes affaires";
var url = "http://www.top-gones.com/";

if (window.sidebar)
{ // Mozilla Firefox Bookmark
	window.sidebar.addPanel(title, url,"");
}
else if( window.external )
{ // IE Favorite
	window.external.AddFavorite( url, title);
}
else if(window.opera && window.print)
{ // Opera Hotlist
	return true;
}

}


/* LOGIN */

// Cryptage MD5
var sHex = "0123456789abcdef";
function hex(i)
{
	h = "";
	for(j = 0; j <= 3; j++)
		h += sHex.charAt((i>>(j*8+4))&0x0F)+sHex.charAt((i>>(j*8))&0x0F);
	return h;
}
function add(x, y)
{
	return ((x&0x7FFFFFFF) + (y&0x7FFFFFFF)) ^ (x&0x80000000) ^ (y&0x80000000);
}
function R1(A, B, C, D, X, S, T)
{
	q = add(add(A, (B & C) | ((~B) & D)), add(X, T));
	return add((q << S) | (q >>> (32 - S)), B);
}
function R2(A, B, C, D, X, S, T)
{
	q = add(add(A, (B & D) | (C & (~D))), add(X, T));
	return add((q << S) | (q >>> (32 - S)), B);
}
function R3(A, B, C, D, X, S, T)
{
	q = add(add(A, B ^ C ^ D), add(X, T));
	return add((q << S) | (q >>> (32 - S)), B);
}
function R4(A, B, C, D, X, S, T)
{
	q = add(add(A, C ^ (B | (~D))), add(X, T));
	return add((q << S) | (q >>> (32 - S)), B);
}
function calcMD5(sInp)
{
	wLen = (((sInp.length + 8) >> 6) + 1) << 4;
	var X = new Array(wLen);
	j = 4;
	for (i = 0; (i * 4) < sInp.length; i++)
	{
		X[i] = 0;
		for (j = 0; (j < 4) && ((j + i * 4) < sInp.length); j++)
		{
			X[i] += sInp.charCodeAt(j + i * 4) << (j * 8);
		}
	}
	if (j == 4) X[i++] = 0x80;
	else X[i - 1] += 0x80 << (j * 8);
	for(; i < wLen; i++) { X[i] = 0; }
	X[wLen - 2] = sInp.length * 8;
	a = 0x67452301; b = 0xefcdab89; c = 0x98badcfe; d = 0x10325476;
	for (i=0; i<wLen; i+=16)
	{
		aO=a; bO=b; cO=c; dO=d;
		a=R1(a,b,c,d,X[i+ 0],7 ,0xd76aa478);
		d=R1(d,a,b,c,X[i+ 1],12,0xe8c7b756);
		c=R1(c,d,a,b,X[i+ 2],17,0x242070db);
		b=R1(b,c,d,a,X[i+ 3],22,0xc1bdceee);
		a=R1(a,b,c,d,X[i+ 4],7 ,0xf57c0faf);
		d=R1(d,a,b,c,X[i+ 5],12,0x4787c62a);
		c=R1(c,d,a,b,X[i+ 6],17,0xa8304613);
		b=R1(b,c,d,a,X[i+ 7],22,0xfd469501);
		a=R1(a,b,c,d,X[i+ 8],7 ,0x698098d8);
		d=R1(d,a,b,c,X[i+ 9],12,0x8b44f7af);
		c=R1(c,d,a,b,X[i+10],17,0xffff5bb1);
		b=R1(b,c,d,a,X[i+11],22,0x895cd7be);
		a=R1(a,b,c,d,X[i+12],7 ,0x6b901122);
		d=R1(d,a,b,c,X[i+13],12,0xfd987193);
		c=R1(c,d,a,b,X[i+14],17,0xa679438e);
		b=R1(b,c,d,a,X[i+15],22,0x49b40821);
		a=R2(a,b,c,d,X[i+ 1],5 ,0xf61e2562);
		d=R2(d,a,b,c,X[i+ 6],9 ,0xc040b340);
		c=R2(c,d,a,b,X[i+11],14,0x265e5a51);
		b=R2(b,c,d,a,X[i+ 0],20,0xe9b6c7aa);
		a=R2(a,b,c,d,X[i+ 5],5 ,0xd62f105d);
		d=R2(d,a,b,c,X[i+10],9 , 0x2441453);
		c=R2(c,d,a,b,X[i+15],14,0xd8a1e681);
		b=R2(b,c,d,a,X[i+ 4],20,0xe7d3fbc8);
		a=R2(a,b,c,d,X[i+ 9],5 ,0x21e1cde6);
		d=R2(d,a,b,c,X[i+14],9 ,0xc33707d6);
		c=R2(c,d,a,b,X[i+ 3],14,0xf4d50d87);
		b=R2(b,c,d,a,X[i+ 8],20,0x455a14ed);
		a=R2(a,b,c,d,X[i+13],5 ,0xa9e3e905);
		d=R2(d,a,b,c,X[i+ 2],9 ,0xfcefa3f8);
		c=R2(c,d,a,b,X[i+ 7],14,0x676f02d9);
		b=R2(b,c,d,a,X[i+12],20,0x8d2a4c8a);
		a=R3(a,b,c,d,X[i+ 5],4 ,0xfffa3942);
		d=R3(d,a,b,c,X[i+ 8],11,0x8771f681);
		c=R3(c,d,a,b,X[i+11],16,0x6d9d6122);
		b=R3(b,c,d,a,X[i+14],23,0xfde5380c);
		a=R3(a,b,c,d,X[i+ 1],4 ,0xa4beea44);
		d=R3(d,a,b,c,X[i+ 4],11,0x4bdecfa9);
		c=R3(c,d,a,b,X[i+ 7],16,0xf6bb4b60);
		b=R3(b,c,d,a,X[i+10],23,0xbebfbc70);
		a=R3(a,b,c,d,X[i+13],4 ,0x289b7ec6);
		d=R3(d,a,b,c,X[i+ 0],11,0xeaa127fa);
		c=R3(c,d,a,b,X[i+ 3],16,0xd4ef3085);
		b=R3(b,c,d,a,X[i+ 6],23, 0x4881d05);
		a=R3(a,b,c,d,X[i+ 9],4 ,0xd9d4d039);
		d=R3(d,a,b,c,X[i+12],11,0xe6db99e5);
		c=R3(c,d,a,b,X[i+15],16,0x1fa27cf8);
		b=R3(b,c,d,a,X[i+ 2],23,0xc4ac5665);
		a=R4(a,b,c,d,X[i+ 0],6 ,0xf4292244);
		d=R4(d,a,b,c,X[i+ 7],10,0x432aff97);
		c=R4(c,d,a,b,X[i+14],15,0xab9423a7);
		b=R4(b,c,d,a,X[i+ 5],21,0xfc93a039);
		a=R4(a,b,c,d,X[i+12],6 ,0x655b59c3);
		d=R4(d,a,b,c,X[i+ 3],10,0x8f0ccc92);
		c=R4(c,d,a,b,X[i+10],15,0xffeff47d);
		b=R4(b,c,d,a,X[i+ 1],21,0x85845dd1);
		a=R4(a,b,c,d,X[i+ 8],6 ,0x6fa87e4f);
		d=R4(d,a,b,c,X[i+15],10,0xfe2ce6e0);
		c=R4(c,d,a,b,X[i+ 6],15,0xa3014314);
		b=R4(b,c,d,a,X[i+13],21,0x4e0811a1);
		a=R4(a,b,c,d,X[i+ 4],6 ,0xf7537e82);
		d=R4(d,a,b,c,X[i+11],10,0xbd3af235);
		c=R4(c,d,a,b,X[i+ 2],15,0x2ad7d2bb);
		b=R4(b,c,d,a,X[i+ 9],21,0xeb86d391);
		a=add(a,aO); b=add(b,bO); c=add(c,cO); d=add(d,dO);
	}
	return hex(a)+hex(b)+hex(c)+hex(d);
}
// Cryptage mot de passe login avec sid
function encrypt(sid, widgetclear, widgetcrypted)
{
	if( widgetclear.value) {
		widgetcrypted.value = calcMD5(sid + widgetclear.value);
		widgetclear.value = "";
	}
}
function login_connect(login_form)
{
	if (login_form['_login[username]'].value && login_form['_login[password]'].value)
	{
		login_form['_login[password_crypt]'].value = calcMD5(LireCookie('PHPSESSID') + login_form['_login[password]'].value);
		login_form['_login[password]'].value = "";
		login_form.submit();
	}
	else
		return false;
}

/* Bidouille Email */

function email_replace(id, domain, nom)
{
	document.getElementById('id_'+id).innerHTML = '<a href="mailto:'+nom+'@'+domain+'">'+nom+'@'+domain+'</a>';
}

/* Changer l'URL de la page appelante */

function opener_url(url)
{
	if (window.opener)
	{
		window.opener.document.location.href = url;
		window.opener.focus();
	}
	else
	{
		var w = window.open(url);
		w.focus();
	}
	return false;
};

/* Gestion des banques de donnée (formulaires, etc.) */

var datamodel_autoadd_count = new Object();

function datamodel_insert_form(datamodel, template, element, name)
{
	$.post("/view.php", {datamodel: datamodel, template: template}, function(data){
		if (data.length > 0 && element)
		{
			$(element).append("<div>"+data+"<p style=\"text-align: right;margin-top: 0;\"><input type=\"button\" value=\"CANCEL\" onclick=\"$(this.parentNode.parentNode).remove()\" /></p></div>");
			if (name)
			{
				if (name.substr(-2,2) == "[]")
				{
					if (!datamodel_autoadd_count[datamodel])
						datamodel_autoadd_count[datamodel] = 0;
					else
						datamodel_autoadd_count[datamodel]++;
					var nb = datamodel_autoadd_count[datamodel];
					name = name.substr(0,name.length-2)+'['+nb+']';
				}
				$("[name]", element).each(function(){
						this.name = name+'['+this.name+']';
				});
			}
			datamodel_fields_clean(element);
		}
	});
}

function databank_list_create(name)
{
	if (fields[name]['value'])
	{
		for (var nb in fields[name]['value'])
		{
			databank_list_add(name, fields[name]['value'][nb]['id'], fields[name]['value'][nb]['name']);
		}
	}
}
function databank_list_add(name, id, text)
{
	fields[name]['nb']++;
	var nb = fields[name]['nb'];
	var node = document.getElementById(name+'_list');
	var childnode = document.createElement('div');
	childnode.setAttribute('id', name+'_'+nb);
	var s = document.createElement('input');
		s.setAttribute('id', name+'['+nb+']');
		s.setAttribute('type', 'hidden');
		s.setAttribute('value', id);
		childnode.appendChild(s);
	var s = document.createElement('span');
		s.innerHTML = text;
		childnode.appendChild(s);
	var s = document.createElement('span');
		s.innerHTML = ' X';
		s.setAttribute('style', 'color: red;cursor: pointer;font-weight: bold;');
		s.setAttribute('onclick', 'databank_list_del(\''+name+'\', \''+nb+'\')');
		childnode.appendChild(s);
	node.appendChild(childnode);
	for (var i=0;i<=nb;i++)
	{
		if (document.getElementById(name+'['+i+']'))
			document.getElementById(name+'['+i+']').name = name+'['+i+']';
	}
	$('#'+name+'_suggestions').hide();
	$('#'+name+'_autoSuggestionsList').html('');
}
function databank_list_del(name, nb)
{
	if (fields[name] && fields[name]['type'] == 'link')
	{
		//if (window.confirm('About to remove the link between objects. Are you sure ?'))
		{
			childname = name+'_'+nb;
			var node = document.getElementById(childname)
			var parent = node.parentNode;
			parent.removeChild(node);
			change(name);
		}
	}
	else
	{
		//if (window.confirm('About to remove the linked object and all its attached resources ! Are you sure ?'))
		{
			childname = name+'_'+nb;
			var node = document.getElementById(childname)
			var parent = node.parentNode;
			parent.removeChild(node);
			// AJAX !
		}
	}
}
function databank_lookup(name)
{
	var field = document.getElementById(name+'_input');
	$.post("/rpc.php", {queryString: field.value, method: "databank_list_add"}, function(data){
		if (data.length >0)
		{
			$('#'+name+'_suggestions').show();
			$('#'+name+'_autoSuggestionsList').html(data);
		}
		else
		{
			$('#'+name+'_suggestions').hide();
			$('#'+name+'_autoSuggestionsList').html('');
		}
	});
}

function rpc_query(datamodel, params, callback_func, fields, time)
{
	var d = new Date();
	var t = ""+d.getTime();
	if (!time)
	{
		query_lasttime = t;
		setTimeout(function(){rpc_query(datamodel, params, callback_func, fields, t);}, '250');
	}
	else if (query_lasttime==time)
	{
		query_lasttime = 0;
		$.post("/_rpc.php", {"datamodel":datamodel, "params":params, "fields":fields}, function(data){
			//alert(data);
			callback_func(data);
		}, "json");
	}
}

var query_lasttime = 0;

// Requête sur une databank et renvoi des résultats vers une fonction
function object_list_query(datamodel, params, field)
{
	rpc_query(datamodel, params, function(data){object_list_aff(data, field)}, 1);
}
// Affiche la liste des objects à partir de la liste
function object_list_aff(list, field)
{
	if (list.length)
	{
		$(".q_select", field).hide();
		$(".q_select", field).html("");
		$.each(list, function(key, object){
			var element = document.createElement("p");
			$(element).html(object.value);
			$(element).click(function(){
				object_select(object, field);
			});
			$(".q_select", field).append(element);
		});
		$(".q_select", field).show();
	}
	else
	{
		$(".q_select", field).hide();
		$(".q_select", field).html('<p>Aucun résultat...</p>');
		$(".q_select", field).show();
	}
}
// Modifie l'objet choisi
function object_select(object, field)
{
	if ($(".q_id", field).get(0).tagName == "SELECT")
		$(".q_id", field).append($("<option selected></option>").attr("value",object.id).text(object.value));
	else
		$(".q_id", field).val(object.id);
	$(".q_str", field).val(object.value);
	object_list_hide(field);
	$(".q_id", field).change();
}
// Cache la liste de résultats RPC
function object_list_hide(field)
{
	setTimeout(function(){$(".q_select", field).hide();}, '250');
}

/* Gestion champs de formulaires (datamodel) avec controles */

function field_control(field, type)
{
	switch(type)
	{
	case "data_email":
		break;
	case "data_url":
		break;
	case "data_text":
		break;
	case "data_richtext":
		break;
	case "data_measure":
		break;
	case "data_float":
		break;
	case "data_id":
		break;
	case "data_number":
		break;
	case "data_integer":
		break;
	case "data_string":
		break;
	default:
		break;
	}
}

/* Gestion formulaires avec data fields */

function datamodel_fields_clean(element)
{
	$("select.data_dataobject_list", element).each(function(){
		$(this).asmSelect({
			sortable: true,
			animate: true,
			addItemTarget: 'bottom'
		});
		$("select.asmSelect", this.parentNode).hide();
	});
	$("select.data_fromlist", element).asmSelect({
		sortable: true,
		animate: true,
		addItemTarget: 'bottom'
	});
	$("textarea.data_script", element).each(function(){
		// initialisation
		editAreaLoader.init({
			"id": this.id	// id of the textarea to transform		
			,"start_highlight": true	// if start with highlight
			,"allow_resize": "both"
			,"allow_toggle": true
			,"word_wrap": true
			,"language": "fr"
			,"syntax": "php"	
		});
	});
	$("textarea.data_text", element).autoGrow();
	$("textarea.data_richtext", element).ckeditor();
	$("input.data_datetime", element).datetimepicker();
	$("input.data_time", element).timepicker();
	$("input.data_date", element).datepicker();
}

function datamodel_form_colorinit(element_id)
{
	var element = document.getElementById(element_id);
	if (element)
	{
		$("input[type='text'], textarea", element).change(function(){
			if ($(this).val())
			{
				$(this).css("background-color","white");
				$(this).css("border-color","black");
			}
			else if($(this).hasClass("required"))
			{
				$(this).css("background-color","#fefebd");
				$(this).css("border-color","red");
			}
			else
			{
				$(this).css("background-color","#fefebd");
				$(this).css("border-color","black");
			}
		});
		$("input[type='text'], textarea", element).change();
		$("select", element).change(function(){
			if ($(this).val())
			{
				$("option", this).css("background-color","white"); // TODO : A améliorer, logiquement seulement besoin pour [value='']
				$(this).css("border-color","black");
			}
			else if($(this).hasClass("required"))
			{
				$("option:selected", this).css("background-color","#fefebd");
				$(this).css("border-color","red");
			}
			else
			{
				$("option:selected", this).css("background-color","#fefebd");
				$(this).css("border-color","black");
			}
		});
		$("select", element).change();
	}
}

/* Gestion formulaires texte avec valeur par défaut grisée */

function field_autotext(field, text, color)
{
	field.text = text;
	if (!color)
		color = 'gray';
	if (field.value==text || field.value=='')
	{
		field.value=text;
		field.style.color=color;
	}
	$(field).focus(function(){
		if (this.value==this.text)
		{
			this.value='';
			this.style.color='black';
		}
		else
			this.select();
	})
	$(field).blur(function(){
		if (this.value==this.text || this.value=='')
		{
			this.value = this.text;
			this.style.color=color;
		}
	})
}

/* Date Heure */

function date_maj(element_id)
{
	if (element=document.getElementById(element_id))
	{
		var date = new Date();
		var D = date.getDate();
		if (D < 10) D = "0"+D;
		var M = date.getMonth();
		M++;
		if (M < 10) M = "0"+M;
		var Y = date.getFullYear();
		var h = date.getHours();
		if (h < 10) h = "0"+h;
		var m = date.getMinutes();
		if (m < 10) m = "0"+m;
		//element.innerHTML = D+'/'+M+'/'+Y+', '+h+':'+m+':'+s;
		//var s = date.getSeconds();
		//if (s < 10) s = "0"+s;
		//setTimeout('date_maj()', 1000)
		element.innerHTML = D+'/'+M+'/'+Y+', '+h+':'+m;
		setTimeout('date_maj(\'+element_id+\')', 60000)
	}
}

/* Captcha */

function field_captcha(field)
{
	if (field.value=='')
	{
		field.style.backgroundColor='#ddd';
		field.style.border='1px red solid';
	}
	$(field).focus(function(){
		this.style.backgroundColor='white';
		this.style.border='';
	})
	$(field).blur(function(){
		this.style.backgroundColor='#ddd';
		this.style.border='1px red solid';
	})
}

/* Modal */

// pseudo-POPUP de notification

function info_show(src, type, height)
{
	if (type == "930")
	{
		if (!height)
			height = ($(window).height() - 100);
		var text = '<div style="background: url(\'/img/fond/popup_fond_haut.png\') no-repeat;height:26px;"><p class="pseudopopup_close"><a href="javascript:;" onclick="$.modal.close();">Fermer</a></p></div><div style="padding:0px 10px;background: url(\'/img/fond/popup_fond_milieu.png\') repeat-y;"><iframe src="'+src+'" width="910" height="'+height+'" frameborder="0" style="margin: 0px;border: 0px;padding: 0px;height:'+height+'px;" allowTransparency="true"></iframe></div><div style="background: url(\'/img/fond/popup_fond_bas.png\') no-repeat;height:6px;"></div>';
		//alert(text);
		$.modal(text, {
			closeHTML: "",
			overlayClose: false,
			containerCss: { background: 'none' },
			opacity: 75,
			zIndex: 10001
		});
	}
	else
	{
		if (!height)
			height = 400;
		$.modal('<div style="background: url(\'/img/fond/popup_visuel_fond_haut.png\') no-repeat;height:27px;"><p class="pseudopopup_close"><a href="javascript:;" onclick="$.modal.close();">Fermer</a></p></div><div style="padding:0px 10px;background: url(\'/img/fond/popup_visuel_fond_milieu.png\') repeat-y;"><iframe src="'+src+'" width="690" height="'+height+'" frameborder="0" style="margin: 0px;border: 0px;padding: 0px;height:'+height+'px;" allowTransparency="true"></iframe></div><div style="background: url(\'/img/fond/popup_visuel_fond_bas.png\') no-repeat;height:6px;"></div>', {
			closeHTML: "",
			overlayClose: false,
			opacity: 75,
			containerCss: { background: 'none' },
			zIndex: 10001
		});
	}
}
function pseudopopup(src)
{
	info_show(src);
}

function modal_size(newwidth, newheight)
{
	//javascript:modal_size(100,100);
	$("#simplemodal-container").css("overflow","hidden");
	var w = $("#simplemodal-container").css("width");
	var h = $("#simplemodal-container").css("height");
	w = w.substring(0, (w.length-2));
	h = h.substring(0, (h.length-2));
	//alert(w+','+h);
	var a = false;
	if (w > newwidth)
	{
		w2 = (w-1)+'px';
		$("#simplemodal-container").css("width", w2)
		a = true;
	}
	if (w < newwidth)
	{
		w2 = (w+1)+'px';
		$("#simplemodal-container").css("width", w2)
		a = true;
	}
	if (h > newheight)
	{
		h2 = (h-1)+'px';
		$("#simplemodal-container").css("height", h2)
		a = true;
	}
	if (h < newheight)
	{
		h2 = (h+1)+'px';
		$("#simplemodal-container").css("height", h2)
		a = true;
	}
	if (a)
		setTimeout('modal_size('+newwidth+','+newheight+')', 5);
	else
		$.modal().close();
}

/* Notification */

function notify(text, type)
{
	if (type == "miaou")
	{
		var permanent = true;
	}
	else
	{
		var permanent = false;
	}
	//alert(text.length);
	var size = "1";
	if (text.length < 25)
		size = "1.5";
	else if (text.length < 50)
		size = "1.2";
	else if (text.length > 100)
		size = "0.8";
	$('#page_notification').jnotifyAddMessage({
		text: '<table width="100%" height="100%" cellspacing="0" cellpadding="0"><tr><td align="center" valign="middle" style="font-size:'+size+'em;">'+text+'</td></tr></table>',
		permanent: permanent
	});
}

/* Module Article : Edition */

function article_edit(id)
{
	window.open('article_edit,65,'+id+'.html?mode=edit', '_blank', 'width=990, height=600, toolbar=no, menubar=no, scrollbars=yes, resizable=no, location=no, directories=no, status=no');
	return false; // TODO : le n° de la page/popup d'edition des articles n'est pas censé être connu à l'avance ! ou bien utiliser une variable dans les pages editrices
}

/* MODIFICATIONS GÉNÉRIQUES */

$(document).ready( function(){
	datamodel_fields_clean(this);
});
