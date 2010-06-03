<?

function url($page_id=0, $options=array())
{

if (!menu()->exists($page_id))
	return SITE_BASEPATH."/"; // accueil
elseif (!is_array($options) || count($options) == 0)
	return SITE_BASEPATH."/".menu($page_id)->get("name").".html";
else
	return SITE_BASEPATH."/".menu($page_id)->get("name").".html?".implode("&amp;",$options);

}

?>
