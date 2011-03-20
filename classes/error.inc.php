<?php

/**
  * $Id$
  * 
  * Copyright 2008-2011 Mathieu Moulin - lemathou@free.fr
  * 
  * This file is part of PHP FAD Framework.
  * http://sourceforge.net/projects/phpfadframework/
  * Licence : http://www.gnu.org/copyleft/gpl.html  GNU General Public License
  * 
  */

if (DEBUG_GENTIME == true)
	gentime(__FILE__." [begin]");


class _error
{

protected $list = array();

function add($cat, $description)
{

$this->list[$cat][] = $description;

}

function disp()
{

while (list($cat, $list)=each($this->list))
{
	print "<p>$cat</p>";
	print "<ul>";
	while (list(,$description)=each($list))
		print "<li>$description</li>";
	print "</ul>";
}

}

}


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [end]");

?>
