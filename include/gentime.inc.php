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

// Durées en ms
define("GENTIME_S",0.25);
define("GENTIME_M",1);
define("GENTIME_L",5);

class _gentime
{

protected $list = array();
protected $timestamp = 0;

function __construct()
{

$this->timestamp = microtime(true);

}

/**
 * Add a point 
 * @param string
 */
public function add($name="")
{

//echo "<p>$name</p>\n";

$this->list[] = array ("$name", microtime(true));

}

public function t()
{

echo ($this->list[(count($this->list)-1)][1]-$this->timestamp);

}

public function total()
{

$lasttime = $this->timestamp;
echo "<p>begin at : $lasttime</p>\n";

$aff = array();
$time_min = 0;
$time_max = 0;
foreach ($this->list as $time)
{
	$time_ms = ($time[1]-$lasttime)*1000;
	$aff[]=array( "name"=>$time[0], "time"=>$time_ms);
	$lasttime = $time[1];
	if (!$time_min || $time_min>$time_ms)
		$time_min = $time_ms;
	if ($time_ms>$time_max)
	{
		$time_max = $time_ms;
		//echo "<p>New time max : $time[0] : $time_ms</p>\n";
	}
}

echo "<table style=\"font-size:8pt;\" width=\"100%\">\n";
echo "<p>Time MAX : $time_max</p>\n";
$t = 0;
$sep = 0;
foreach ($aff as $i)
{
	$t += $i["time"];
	if ($i["time"] >= GENTIME_L)
	{
		$colornum = round(255-255*($i["time"])/($time_max), -1);
		$color = "rgb(255,$colornum,$colornum)";
		$time = ($i["time"])." ms";
	}
	elseif ($i["time"] >= GENTIME_M)
	{
		$colornum = round(255-255*($i["time"])/GENTIME_L, -1);
		$color = "rgb($colornum,$colornum,255)";
		$time = ($i["time"])." ms";
	}
	elseif ($i["time"] >= GENTIME_S)
	{
		$colornum = round(255-255*($i["time"])/GENTIME_M, -1);
		$color = "rgb($colornum,255,$colornum)";
		$time = ($i["time"])." ms";
	}
	else
	{
		$colornum = round(255-255*($i["time"])/GENTIME_S, -1);
		$color = "rgb($colornum,$colornum,$colornum)";
		$time = ($i["time"]*1000)." us";
	}
	$width = round(log($i["time"]*1000)*50)-round(log($time_min*1000)*50);
	if (substr($i["name"], -5) == "[end]")
	{
		$sep--;
	}
	if ($sep > 3)
	{
		$txtcolor = " style=\"color: red;\"";
	}
	elseif ($sep > 2)
	{
		$txtcolor = " style=\"color: orange;\"";
	}
	elseif ($sep > 1)
	{
		$txtcolor = " style=\"color: blue;\"";
	}
	elseif ($sep > 0)
	{
		$txtcolor = " style=\"color: green;\"";
	}
	else
	{
		$txtcolor = "";
	}
	echo "<tr>";
	echo "<td align=\"right\"$txtcolor>$i[name]".str_repeat("&nbsp; &nbsp; &nbsp; ", $sep)."</td>";
	echo "<td><div style=\"float:left;background-color:${color};width:${width}px;margin-right:10px;\">&nbsp;</div><div style=\"color:black;\"> ${time}</div></td>";
	echo "<td align=\"right\">".round($t, 4)." ms</td>";
	echo "</tr>\n";
	if (substr($i["name"], -7) == "[begin]")
		$sep++;
}
echo "</table\n>";

}

}

/**
 * Quick access function
 * 
 * @param $name
 */
function gentime($name=null)
{

if (!isset($GLOBALS["_gentime"]))
	$GLOBALS["_gentime"] = new _gentime();

if (is_string($name))
	$GLOBALS["_gentime"]->add($name);
else
	return $GLOBALS["_gentime"];

}

// Instancié de suite pour être le plus précis possible !
gentime();
	
gentime("BEGIN");

?>
