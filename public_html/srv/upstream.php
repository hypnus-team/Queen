<?php

ini_set('display_errors',0);

include "../include/config.inc.php";
include "../library/global.func.php";

$tid = $_REQUEST['tid'];
$tNo = intval($_REQUEST['tno']);

$mid = $_REQUEST['mid'];
$os  = intval($_REQUEST['os']);

$c_stream_file = false;

if (preg_match('/^[a-fA-F0-9]{32}$/',$mid)){
    $c_stream_file = $repo_path.'bin/'.$mid.'.'.$os;

}elseif (preg_match('/^[a-zA-Z0-9]{32}$/',$tid)){
	
	$c_stream_file = GlobalFunc::get_stream_path($tid,false);

	$c_stream_file .= "$tid".'.UP.'."$tNo";
}

if (($c_stream_file) and (file_exists($c_stream_file))){
	$a = file_get_contents($c_stream_file);
	$totalSize = strlen($a);
	header ("Content-Length: ".$totalSize);
	exit($a);    
}
exit;

?>