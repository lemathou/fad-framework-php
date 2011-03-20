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


class _permission_manager extends __permission_manager
{



};

class _permission extends __permission
{

function datamodel_update($id, $perm)
{

if (!datamodel()->exists($id))
	return false;

db()->query("DELETE FROM `_datamodel_perm_ref` WHERE `datamodel_id`='$id' AND `perm_id` = '$this->id'");
if (is_array($perm))
	$query = db()->query("INSERT INTO `_datamodel_perm_ref` (`perm_id`, `datamodel_id`, `perm`) VALUES('$this->id', '$id', '".implode(",", $perm)."')");

}

};


if (DEBUG_GENTIME == true)
	gentime(__FILE__." [begin]");

?>