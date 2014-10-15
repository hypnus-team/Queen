<?php

ini_set('display_errors',0);
error_reporting(E_ERROR); 

set_time_limit(0);
ignore_user_abort(true); 

include "../include/config.inc.php";
include "../library/global.func.php";






$c_time = time();

$db = GlobalFunc::connect_db($mysql_ini);
if (!$db){
    repond_breath (3);
}

@$db->query("SET character_set_connection=utf8, character_set_results=utf8, character_set_client=binary");

$token  = $_REQUEST['token'];
$mac    = $_REQUEST['mac'];
$name   = $_REQUEST['name'];
$flag   = $_REQUEST['flag'];
$exists = $_REQUEST['exists'];
$module = array();
if (is_array($_REQUEST['mod'])){
    foreach ($_REQUEST['mod'] as $a){
	    $module[$a] = $a;
	}
}

$name = ''.$name;


if (false !== ($tmp = array_search ('000000000000',$mac))){
    unset ($mac[$tmp]);
}

$mac_num = count($mac);

if ((!preg_match('/^[a-zA-Z0-9]{32}$/',$token)) or (!$mac_num) or (!is_numeric($flag) or ($flag <=0) or ($flag > 2147483647))){
    repond_breath (1);
}
foreach ($mac as $a){
    if (!preg_match('/^[a-fA-F0-9]{12}$/',$a)){
	    repond_breath (1);
	}
}
$module_num = 0;
$tmp = $module;
foreach ($tmp as $a){
    if (!preg_match('/^[a-fA-F0-9]{32}$/',$a)){
	    repond_breath (1);
	}else{
	    if (GlobalFunc::is_sys_module_id($a)){ 
			unset ($module[$a]);
		    continue;
		}
	}
	$module_num ++;
}

if (!$module_num){
    repond_breath (1);
}



$query = 'select tid from '.$mysql_ini['prefix'].'token where token = \''.$token.'\' limit 1';
$result = $db->query($query);
if (1 === mysqli_num_rows($result)){
    $result = $result->fetch_assoc();
	$tid   = $result['tid'];
}else{
    repond_breath (5);
}



$exists_mac = array();

foreach ($mac as $c_mac){
	$query = 'select flag from `'.$mysql_ini['prefix'].'online_mac` where mac = \''.$c_mac.'\'';	
	
    $result = $db->query($query);
    if (!$result){
	    repond_breath (4);
	}
	$i = mysqli_num_rows($result);
    for (;$i > 0 ;$i --){
	    $a = $result->fetch_assoc();
		if ($flag != $a['flag']){
			$exists_mac[$a['flag']] = true;
		}
	}	
}

$tmp = $exists_mac;

if (is_array($exists)){
	foreach ($exists as $a){
        if (!is_numeric($a)){
	        repond_breath (1);
		}
		if (true === $exists_mac[$a]){
		    unset ($tmp[$a]);
		}
	}
}

if (count($tmp)){ 
    $tmp = '';
	foreach ($exists_mac as $a => $b){
	    $tmp .= pack("i",$a);
	}
	repond_breath(2,$tmp);
}



$members_max_clients = 0;
$query = 'select maxclient from '.$mysql_ini['prefix']."members limit 1";
$result = $db->query($query);
if (1 === mysqli_num_rows($result)){
    $result = $result->fetch_assoc();
	$members_max_clients = $result['maxclient'];
}


sort($mac);
$mid = md5(strtoupper(implode(',',$mac)));
$unique = mt_rand(0,2147483647);


$dummy = 0;
$query = 'select dummy from '.$mysql_ini['prefix'].'dummy_clients where mid=\''.$mid.'\' limit 1';
$result = $db->query($query);
if (1 === mysqli_num_rows($result)){
    $result = $result->fetch_assoc();
	$dummy = $result['dummy'];
}

$clientId = false;


$query = 'insert into '.$mysql_ini['prefix']."online_clients values (NULL,1,?,?,?,?,?,?,?,?,?,?)";

$stmt = $db->prepare($query);
if ($stmt){	
	$stmt->bind_param("sissiiiiii",$mid,$unique,$name,$_SERVER["REMOTE_ADDR"],$tid,$dummy,$mac_num,$module_num,$c_time,$c_time);
	$result = $stmt->execute();
	$clientId = $stmt->insert_id;
	
	$stmt->close();
	if (!$result){
		repond_breath (7);
	}
}else{
    repond_breath (6);
}

if (!$clientId){
    repond_breath (7);
}



$query = 'insert into '.$mysql_ini['prefix'].'online_mac values ';
foreach ($mac as $a){
	$query .= "($clientId,'$a','$flag'),";
}
$query = substr($query,0,-1);
$db->query($query);




$query = 'insert into '.$mysql_ini['prefix'].'online_module values ';
foreach ($module as $a){
	$a = strtoupper($a);
	$query .= "($clientId,'$a'),";
}
$query = substr($query,0,-1);
$db->query($query);


$db->close();


$hexCID = pack("V",$clientId);   
repond_breath (0,$hexCID,false);

include "$IPC_mod_path".'IPC_'.HYP_IPC_MODE.'.php';
HYP_IPC::KeepAlive();

exit;

 function mylog($str){
     $fp = fopen('./abort.txt', 'a');
     $str = date('Y-m-d H:i:s')." { $str } "."\r\n";
     fwrite($fp, $str);
     fclose($fp);
 }

 function repond_breath($cmd,$src="",$die=true){
	
	 
	 
	 
     $len = strlen($src);
	 $out  = pack("Cx15CCx31CV",1,255,$cmd,255,$len); 
	 echo "\x00"."$out"."$src";
     ob_flush(); 
	 flush(); 
	 if ($die){
		 die();
	 }
 }

?>