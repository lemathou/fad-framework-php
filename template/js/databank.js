function lookup(field, inputString)
{
	$.get("/databank_jquery.php", {databank: ""+fields[field]['databank']+"", field: ""+field+"", queryString: ""+inputString+"", params: ""+JSON.stringify(fields[field]['query_params'])+"" }, function(data)
	{
		if(data.length >0)
		{
			$('#'+field+'_suggestions').show();
			$('#'+field+'_autoSuggestionsList').html(data);
		}
	});
}

function fill(field, id, name)
{
	if (fields[field]['type'] == 'dataobject')
	{
		document.getElementById(field).name = document.getElementById(field).id;
		$('#'+field).val(id);
	}
	else if (fields[field]['type'] == 'dataobject_select')
	{
		document.getElementById(field+'[0]').name = document.getElementById(field+'[0]').id;
		document.getElementById(field+'[1]').name = document.getElementById(field+'[1]').id;
		document.getElementById(field+'[1]').value = id;
	}
	else if (fields[field]['type'] == 'dataobject_list')
	{
		$('#'+field).val(id);
	}
	if (id)
	{
		$('#'+field+'_input').val(name).removeClass('undefined');
		fields[field]['name_current'] = name;
		fields[field]['id_current'] = id;
	}
	else
	{
		$('#'+field+'_input').val('Undefined').addClass('undefined');
		fields[field]['name_current'] = 'Undefined';
		fields[field]['id_current'] = '';
	}
	fields[field]['filled'] = true;
	setTimeout("$('#"+field+"_suggestions').hide();", 200);
}

function fill_empty(field)
{
	fields[field]['filled'] = false;
	$('#'+field+'_input').val('').removeClass('undefined').addClass('filling');
	lookup(field, fields[field]['databank'], '');
}

function fill_old(field)
{
	if (!fields[field]['filled'])
	{
		fields[field]['filled'] = true;
		if (!fields[field]['id_current'])
		{
			$('#'+field+'_input').val('Undefined').addClass('undefined');
		}
		else
			$('#'+field+'_input').val(fields[field]['name_current']);
		setTimeout("$('#"+field+"_suggestions').hide();", 200);
	}
}

function remove(name,nb)
{
	if (fields[name] && fields[name]['type'] == 'link')
	{
		if (window.confirm('About to remove the link between objects. Are you sure ?'))
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
		if (window.confirm('About to remove the linked object and all its attached resources ! Are you sure ?'))
		{
			childname = name+'_'+nb;
			var node = document.getElementById(childname)
			var parent = node.parentNode;
			parent.removeChild(node);
			// AJAX !
		}
	}
}
function add(name)
{
	if (fields[name] && fields[name]['type'] == 'link')
	{
		fields[name]['nb_max']++;
		nb = fields[name]['nb_max'];
		var node = document.getElementById(name);
		var childnode = document.createElement('div');
		childnode.setAttribute('id',name+'_'+nb);
		var s = document.createElement('input');
			s.setAttribute('id',name+'['+nb+']');
			s.setAttribute('type','text');
			s.setAttribute('size','6');
			s.setAttribute('value','0');
			s.setAttribute('onchange','change(\'$this->name\')');
		childnode.appendChild(s);
		var s = document.createElement('input');
			s.setAttribute('type','text');
			s.setAttribute('readonly','');
			s.setAttribute('value','');
		childnode.appendChild(s);
		var s = document.createElement('input');
			s.setAttribute('type','button');
			s.setAttribute('value','UPDATE');
			s.setAttribute('onclick','update(\''+name+'\',\''+nb+'\')');
		childnode.appendChild(s);
		var s = document.createElement('input');
			s.setAttribute('type','button');
			s.setAttribute('value','DEL');
			s.setAttribute('onclick','remove(\''+name+'\',\''+nb+'\')');
			childnode.appendChild(s);
		node.appendChild(childnode);
	}
}
function dataobject_list_add(name, id, text)
{
	fields[name]['nb']++;
	nb = fields[name]['nb'];
	var node = document.getElementById(name+'_list');
	var childnode = document.createElement('div');
	childnode.setAttribute('id',name+'_'+nb);
	var s = document.createElement('input');
		s.setAttribute('id',name+'['+nb+']');
		s.setAttribute('type','hidden');
		s.setAttribute('value',id);
	childnode.appendChild(s);
	var s = document.createElement('input');
		s.setAttribute('type','text');
		s.setAttribute('readonly','');
		s.setAttribute('value',text);
	childnode.appendChild(s);
	var s = document.createElement('input');
		s.setAttribute('type','button');
		s.setAttribute('value','DEL');
		s.setAttribute('onclick','dataobject_list_remove(\''+name+'\',\''+nb+'\')');
		childnode.appendChild(s);
	node.appendChild(childnode);
	for (i=0;i<=nb;i++)
	{
		if (document.getElementById(name+'['+i+']'))
			document.getElementById(name+'['+i+']').name = name+'['+i+']';
	}
}

function dataobject_add(name)
{
	document.location.href = "/"+fields[name]['databank']+"/add";
}

function dataobject_list_remove(name, id)
{
	var node = document.getElementById(name+'_'+id);
	var parent = node.parentNode;
	parent.removeChild(node);
	nb = fields[name]['nb'];
	for (i=0;i<=nb;i++)
	{
		if (document.getElementById(name+'['+i+']'))
			document.getElementById(name+'['+i+']').name = name+'['+i+']';
	}
}
function change(name)
{
	inputElements = document.getElementById(name).getElementsByTagName('input');
	for (var i = 0; i < inputElements.length ; i++)
	{
		if (inputElements[i].id && !inputElements[i].name)
			inputElements[i].name = inputElements[i].id;
	}
}
function update(name,nb)
{
	if (nb)
	{
		var node = document.getElementById(name+'['+nb+']');
		document.location.href = '/'+fields[name]['databank']+'/'+node.value+'/update';
	}
	else if (fields[name]['type'] == 'databank_select')
	{
		var node_0 = document.getElementById(name+'[0]');
		var node_1 = document.getElementById(name+'[1]');
		document.location.href = '/'+node_0.value+'/'+node_1.value+'/update';
	}
	else
	{
		var node = document.getElementById(name);
		document.location.href = '/'+fields[name]['databank']+'/'+node.value+'/update';
	}
}
function databank_change(field, databank)
{
	//alert(field+' : '+databank);
	document.getElementById(field+'[0]').name = document.getElementById(field+'[0]').id;
	document.getElementById(field+'[1]').name = document.getElementById(field+'[1]').id;
	fields[field]['databank'] = databank;
	lookup(field, '');
}

function databank_action(databank, id, action)
{
	document.location.href = '/'+databank+'/'+id+'/'+action;
}

function agregat_verify(form, required_fields)
{

message = '';

for (i in required_fields)
{
	if (required_fields[i]!= 'id' && (!form[required_fields[i]] || !form[required_fields[i]].value))
		message += 'Field '+required_fields[i]+' required\n';
}

if (message)
{
	alert(message);
	return false;
}
else
{
	return true;
}

}
