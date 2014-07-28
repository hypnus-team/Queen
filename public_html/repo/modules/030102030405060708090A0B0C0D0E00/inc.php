<?php

$hour = intval($i/(60/5));
$min  = 5 * (ceil($i%(60/5)));

$time_array = "";

$org_hour = $hour;
$org_min = $min;

for ($i=0;$i<288;$i++){    
	$min = $min + 5;
	if (60 == $min){
	    $hour ++;
		$min = 0;

		if ($hour > 23){
	        $hour = 0;
		}
	}
	$show_hour = sprintf("%02d", $hour);
	$show_min  = sprintf("%02d", $min);
	$time_array .= $show_hour.":".$show_min.',';   
}

$error[1] = 'alloc memory fail';
$warning[1] = 'malloced memory not enough';

?>