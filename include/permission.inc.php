<?

/**
  * $Id: permission.inc.php 59 2009-03-03 15:48:26Z mathieu $
  * 
  * « Copyright 2008 Mathieu Moulin - iProspective - lemathou@free.fr »
  * 
  * This file is part of FTNGroupWare.
  * 
  */

/**
 * Gestion des permissions
 */
class permission
{

protected $list = array();

function __construct($id)
{

$query = db()->query(" SELECT databank_id , perm FROM _account_databank_perm WHERE account_id = $id ");
while (list($databank_id , $perm) = $query->fetch_row())
	$this->list[$databank_id] = $perm;

}

}

function permission()
{

return $GLOBALS["permission"];

}

?>