<?

/**
  * $Id$
  * 
  * Copyright 2008 Mathieu Moulin - iProspective - lemathou@free.fr
  * 
  * This file is part of FTNGroupWare.
  * 
  */

// Durées en ms
define("GENTIME_S",0.25);
define("GENTIME_M",1);
define("GENTIME_L",5);

class gentime
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
foreach ($aff as $i)
{
	$t += $i["time"];
	if ($i["time"] < 1)
		$time = ($i["time"]*1000)." us";
	else
		$time = ($i["time"])." ms";
	//$color = ($i["time"] >= GENTIME_S) ? ($i["time"] >= GENTIME_M) ? ($i["time"] >= GENTIME_L) ? "red" : "blue" : "green" : "black";
	if ($i["time"] >= GENTIME_L)
	{
		$colornum = round(255-255*($i["time"])/($time_max), -1);
		$color = "rgb(255,$colornum,$colornum)";
	}
	elseif ($i["time"] >= GENTIME_M)
	{
		$colornum = round(255-255*($i["time"])/GENTIME_L, -1);
		$color = "rgb($colornum,$colornum,255)";
	}
	elseif ($i["time"] >= GENTIME_S)
	{
		$colornum = round(255-255*($i["time"])/GENTIME_M, -1);
		$color = "rgb($colornum,255,$colornum)";
	}
	else
	{
		$colornum = round(255-255*($i["time"])/GENTIME_S, -1);
		$color = "rgb($colornum,$colornum,$colornum)";
	}
	$width = round(log($i["time"]*1000)*50)-round(log($time_min*1000)*50);
	echo "<tr>";
	echo "<td align=\"right\">$i[name]</td>";
	echo "<td><div style=\"float:left;background-color:$color;width:${width}px;margin-right:10px;\">&nbsp;</div><div style=\"color:black;\"> $time</div></td>";
	echo "<td align=\"right\">".round($t, 4)." ms</td>";
	echo "</tr>\n";
}
echo "</table\n>";

}

}

/**
 * Quick access function
 * 
 * @param $name
 */
function gentime($name="")
{

if (!isset($GLOBALS["gentime"]))
	$GLOBALS["gentime"] = new gentime();

if ($name)
	$GLOBALS["gentime"]->add($name);
else
	return $GLOBALS["gentime"];
}

// Instancié de suite pour être le plus précis possible !
$gentime = new gentime();
	
gentime("BEGIN");

?>