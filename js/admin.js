function admin_submenu(id)
{
	$(".admin_submenu a").removeClass("selected");
	$(".admin_submenu a[name='"+id+"']").addClass("selected");
	$(".subcontents").hide();
	$("#"+id).show();
}
