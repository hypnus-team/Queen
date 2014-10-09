<?php

ini_set('display_errors',0);

set_time_limit(0);
ignore_user_abort(true); 

include "../include/config.inc.php";
include "./include/global.func.php";






include "$IPC_mod_path".'IPC_'.HYP_IPC_MODE.'.php';

$ret = 0;

$status = intval($_GET['status']);
$tid    = $_GET['tid'];
$stream_size = intval($_GET['size']);
$cid    = intval($_GET['cid']);

if (!preg_match('/^[a-zA-Z0-9]{32}$/',$tid)){
    
}else{
	$db = connect_db($mysql_ini);
	if ($db){
		if ($status){
			$status = -$status;
			$query = 'update '.$mysql_ini['prefix'].'online_task set status = '.$status.' where tid=\''.$tid.'\' and status=1 and cid='.$cid.' limit 1';
			$db->query($query);
            HYP_IPC::task_response($tid,$cid.':'.$status,':',"");
			
		}else{
			$query = 'select chunk,status,size,module from '.$mysql_ini['prefix'].'online_task where tid=\''.$tid.'\' and cid='.$cid.' limit 1' ;

			$result = $db->query($query);
			if (1 === mysqli_num_rows($result)){
				$result = $result->fetch_assoc();
				if ($result['chunk']){ 
					if ((1 == $result['status']) or (2 == $result['status'])){
						$data = @file_get_contents('php://input');
						$c_size = strlen($data);
						if ($c_size){
							$c_stream_file = get_stream_path($tid);
                            if ($c_size === file_put_contents($c_stream_file.$tid.'.DOWN.'.$result['chunk'],$data)){
								$query = 'update '.$mysql_ini['prefix'].'online_task set status = 2,chunk=chunk+1';
								if (($stream_size) and (0 == $result['size'])){ 
									$query .= ',size= '.$stream_size;
								}
								$query .=  ' where tid=\''.$tid.'\' and cid='.$cid.' limit 1' ;
								$db->query($query);

								$ret = 1;
							}
						}
					}
				}else{                 
					if (1 == $result['status']){
						$data = @file_get_contents('php://input');

						$status = 2;
                                            
						$pipeOk = HYP_IPC_task_response($tid,$cid.':'.$status.':',$data);

						if (!$pipeOk){
							if (strlen($data)){
								$query = 'replace into '.$mysql_ini['prefix'].'data_response values (?,?,?) ';
								$stmt = $db->prepare($query);
								$stmt->bind_param("sis",$tid,$cid,$data);
								if (!$stmt->execute()){
									$status = -100;
								}
								$stmt->close();	
							}
						}
						$query = 'update '.$mysql_ini['prefix'].'online_task set status = '.$status.' where tid=\''.$tid.'\' and status=1 and cid='.$cid.' limit 1' ;
						$db->query($query);

						mylog($query);

						if ('0001'.'0000000000000000000000000000' === $result['module']){ 
							if ($i = strpos($data,"$m=")){
								$i += 4 + 2;
							    $new_mid = strtoupper(substr($data,$i,32));
								if (preg_match('/^[A-F0-9]{32}$/',$new_mid)){
									$insert_mid = false;
                                    if ('$r=0' === substr($data,0,4)){ 
										$insert_mid = true;
									}elseif ('$r=5' === substr($data,0,4)){ 
									    $query = 'select cid from '.$mysql_ini['prefix'].'online_module where cid='.$cid.' and module=\''.$new_mid.'\' limit 1';
										$result = $db->query($query);
										if (0 === mysqli_num_rows($result)){
										    $insert_mid = true;
										}
									}
									if (true === $insert_mid){
										$query = 'update '.$mysql_ini['prefix'].'online_clients set mod_num=mod_num+1 where cid='.$cid.' limit 1';
										$db->query($query);
										$query = 'insert into '.$mysql_ini['prefix'].'online_module values ('.$cid.',\''.$new_mid.'\')';
                                        $db->query($query);
									}								
								}
							}   
						}
					}
				}	
			}
		}
	}
}
exit("$ret");


 function mylog($str){
		 $fp = fopen('./abort.txt', 'a');
		 fwrite($fp, "\r\n".$str);
		 fclose($fp);
	 }
?>