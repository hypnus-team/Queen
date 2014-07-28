<?php

function HYP_IPC_task_response($tid,$prefix,$data){
   return false;
}

function HYP_IPC_task_recv($TaskId,&$requestData,$num){
    return -1;
}

function HYP_IPC_task_send(){
    return 0;
}

function HYP_IPC_KeepAlive(){

	global $srv_block_ini;
	global $mysql_ini;
	global $clientId;
	global $mac_num;

	$breath_delay = 0;

	$db_timer = time();

	$active_offline = false; 

	while (true){
		usleep($srv_block_ini['sleep'] * 1000000);    
		
		$db = connect_db($mysql_ini);
		if ($db){
			$c_status = connection_status();
			if (($active_offline) or ($c_status!=0)){ 
				$query = 'delete from '.$mysql_ini['prefix']."online_mac where cid=$clientId limit $mac_num";
				$db->query($query);
				
				$module_num = 0;
				$query = 'select mod_num from '.$mysql_ini['prefix']."online_clients where cid=$clientId limit 1";
				$result = $db->query($query);
				if (1 === mysqli_num_rows($result)){
					$result = $result->fetch_assoc();
					$module_num = $result['mod_num'];
					$module_num ++; 
					if ($module_num){
						$query = 'delete from '.$mysql_ini['prefix']."online_module where cid=$clientId limit $module_num";
						$db->query($query);
					}
					$query = 'delete from '.$mysql_ini['prefix']."online_clients where cid=$clientId limit 1";
					$db->query($query);
				}			
				break;
			}else{
				$query = 'select rid,tid,module from '.$mysql_ini['prefix'].'online_task  where cid = '.$clientId.' and status = 0';
				$result = $db->query($query);	
				$i = mysqli_num_rows($result);	
				
				for (;$i > 0;$i--){
					$request_data = '';
					$error = false;
					$tmp = $result->fetch_assoc();
					if (!empty($tmp['rid'])){
						if (32 === strlen($tmp['rid'])){
							$query = 'select data from '.$mysql_ini['prefix'].'data_request where rid = \''.$tmp['rid'].'\' limit 1';
						}else{
							$query = 'select data from '.$mysql_ini['prefix'].'shortcuts where sid = \''.$tmp['rid'].'\' limit 1';
						}					
						$res = $db->query($query);
						if (1 == mysqli_num_rows($res)){
							$a = $res->fetch_assoc();
							$request_data = $a['data'];	
						}else{ 
							$error = true;
							$query = 'update '.$mysql_ini['prefix'].'online_task set status = -101 where tid = \''.$tmp['tid'].'\' and cid='.$clientId.' limit 1';
							$db->query($query);
						}
					}
					if (false === $error){	
						$len = strlen($request_data);
						$out = pack("H32CA32CV",$tmp['module'],255,$tmp['tid'],255,$len);      
						echo "\x00"."$out".$request_data;
						echo "\x00";
						ob_flush(); 
						flush(); 
						$query = 'update '.$mysql_ini['prefix'].'online_task set status = 1 where tid = \''.$tmp['tid'].'\' and cid='.$clientId.' limit 1';
						$db->query($query);				
						
						
						$breath_delay = $srv_block_ini['breath_delay'];			
					}
				}

				if ($breath_delay < 0){
					echo "\x00";
					ob_flush(); 
					flush();
					
					$breath_delay  = $srv_block_ini['breath_delay'];
				}else{
					$breath_delay -= $srv_block_ini['sleep'];
				}

				
				$c_time = time();
				if (($c_time - $db_timer) > $srv_block_ini['db_living']){
					 $db_timer = $c_time;
					 $query = "update ".$mysql_ini['prefix']."online_clients set lastliving = $db_timer where cid=$clientId and status = 1 limit 1";
					 $db->query($query);
					 if (1 != $db->affected_rows){
						 errorlog('db.living update 失败,疑似Drone主动下线...',2);
						 $active_offline = true;
					 }
				}
			}
			$db->close();
		}else{

		}    

	 }
}

?>