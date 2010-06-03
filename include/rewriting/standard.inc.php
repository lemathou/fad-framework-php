<?

function url($page_id=0, $options=array())
{

if (!menu()->exists($page_id))
	return "?page_id=0"; // accueil
elseif (!count($options))
	return "?page_id=$page_id";
else
	return "?page_id=$page_id".implode("&amp;",$options);

}

?>
