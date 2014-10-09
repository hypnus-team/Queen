<?php

class HYP_IPC{

    private static $_task_pipe_prefix   = 'pipe.task.';
    private static $_client_pipe_prefix = 'pipe.';


	public static function task_response($tid,$prefix,$data){
	   global $stream_path;
	   //global $task_pipe_prefix;

	   $a = strlen($data);
	   $prefix .= $a.':';

	   $pipe = @fopen($stream_path.self::$_task_pipe_prefix.$tid, 'r+b');
	   if ($pipe){	   
		   if (flock($pipe,LOCK_EX)){		   
			   fwrite($pipe,$prefix.$data);
		   }
		   fclose($pipe);
	   }else{    // fail
		   return false;
	   }
	   return true;
	}


	public static function task_recv($TaskId,&$requestData,$num){
		global $stream_path;
		//global $task_pipe_prefix;

		$timeout = 15; //超时

		$ret = -1;

		$fifo_file = $stream_path.self::$_task_pipe_prefix.$TaskId;

		if (file_exists($fifo_file))
			unlink($fifo_file);

		$tmp_contents = '';

		$success = posix_mkfifo($fifo_file, 0700);
		if ($success){		
			$last_char = '';
			$pipe = @fopen($fifo_file, 'r+b');
			if ($pipe){
				stream_set_blocking($pipe, false);
				while ($num > 0){
					$non_recv = true; //not recv anything.
					$read = array($pipe);
					$write = NULL;
					$except = NULL;				
					if (false === ($num_changed_streams = stream_select($read, $write, $except,$timeout))) {
						$ret = -1;
						break;
					}elseif ($num_changed_streams > 0) {
						$ret = 1;
						while (false !== ($a = fread ($pipe,1))){
							if (0 === strlen($a)){
								break;
							}else{					
								$tmp_contents .= $a;
								$non_recv = false;
							}
						}					
					}else{
						if (1 !== $ret){
							$ret = 0;
						}
						break;				
					}	
					
					if (1 === $ret){
						$num -= self::namepipe_data_parser($tmp_contents,$requestData);
						$timeout = 1;
					}
				}	
				
				unlink($fifo_file);
				if ($num > 0){
					while (false === $non_recv){
						$non_recv = true;
						while (false !== ($a = fread ($pipe,1))){
							if (0 === strlen($a)){
								break;
							}else{					
								$tmp_contents .= $a;
								$non_recv = false;
							}
						}
					}
					self::namepipe_data_parser($tmp_contents,$requestData);			
				}
				if (strlen($tmp_contents)){
					errorlog('HYP_IPC_task_recv not complete : '.$tmp_contents);	
				}
				
				fclose($pipe);
			}
		}
		return $ret;
	}

	public static function task_send(){

		global $TaskId;
		global $cid;
		global $moduleId;
		global $data;

		global $language;

		global $stream_path;
		//global $client_pipe_prefix;
		
		$ret = -10;
		
		foreach ($cid as $uniqu => $a){
			if (!$a['lasterror']){
			   $fifo_file = $stream_path.self::$_client_pipe_prefix.$a['cid'];
			   
			   $pipe = @fopen($fifo_file, 'r+b');
			   if ($pipe){
				   if (flock($pipe,LOCK_EX|LOCK_NB)){			
					   if ($data){
						   $len = strlen($data);
					   }else{
						   $len = 0;
						   $data = '';
					   }
					   $out = pack("H32CA32CV",$moduleId,255,$TaskId,255,$len);      
					   fwrite ($pipe,"\x00"."$out".$data."\x00");
					   $ret = 1;
				   }else{
					   $cid[$uniqu]['lasterror'][] = $language['ipc_namepipe_locked'];
				   }
				   fclose($pipe);
				}else{
					$cid[$uniqu]['lasterror'][] = $language['ipc_namepipe_not_exist'];
				}
			}	
		}
		
		return $ret;
	}

	public static function KeepAlive(){

		global $srv_block_ini;
		global $mysql_ini;
		global $clientId;
		global $mac_num;

		global $stream_path;
		//global $client_pipe_prefix;

		$fifo_file = $stream_path.self::$_client_pipe_prefix."$clientId";

		$mac_record_cleared = false;

		$active_offline = false; // Drone 主动下线

		if (file_exists($fifo_file))
			unlink($fifo_file);
	   
		$success = posix_mkfifo($fifo_file, 0700);

		if (!$success){
			errorlog('Error: Could not create a named pipe: '.posix_strerror(posix_errno()));		
		}else{
			$pipe = @fopen($fifo_file, 'r+b');
			if ($pipe){
				stream_set_blocking($pipe, false);
				$off_line = false;
				while (1){
					$read = array($pipe);
					$write = NULL;
					$except = NULL;				
					if (false === ($num_changed_streams = stream_select($read, $write, $except,$srv_block_ini['breath_delay']))) {
						
						break;
					} elseif ($num_changed_streams > 0){
						while (1){
							$a = fread($pipe,1);
							if (0 == strlen($a)){
								break;
							}else{
								echo "$a";
							}
						}	
						ob_flush();
						flush();
					}else{
						if ($off_line){
							break;
						}else{
							echo "\x00";
							ob_flush();
							flush();
						}
					}	
					if (($active_offline) or ((!$off_line) and (0 != connection_status()))){
						$off_line = true;
						$db = connect_db($mysql_ini);
						if ($db){				
							$query = 'delete from '.$mysql_ini['prefix']."online_mac where cid=$clientId limit $mac_num";
							$db->query($query);			
							$mac_record_cleared = true;
							$query = 'update '.$mysql_ini['prefix']."online_clients set status=0 where cid=$clientId limit 1";
							$db->query($query);
							$db->close();
						}
					}else{
						//db.living
						$c_time = time();
						if (($c_time - $db_timer) > $srv_block_ini['db_living']){
							$db_timer = $c_time;
							$db = connect_db($mysql_ini);
							if ($db){
								$query = "update ".$mysql_ini['prefix']."online_clients set lastliving = $db_timer where cid=$clientId and status = 1 limit 1";
								$db->query($query);
								if (1 != $db->affected_rows){
									errorlog('db.living update 失败 (namepipe),疑似Drone主动下线...cid:'."$clientId",2);
									$active_offline = true;
								}
								$db->close();
							}												
						}
					}
				}
				fclose($pipe);
			}else{
				errorlog('Error: fopen fifo_file : '.$fifo_file);
			}
						
			$db = connect_db($mysql_ini);
			if ($db){		
				if (!$mac_record_cleared){
					$query = 'delete from '.$mysql_ini['prefix']."online_mac where cid=$clientId limit $mac_num";
					$db->query($query);
				}
				
				$module_num = 0;
				$query = 'select mod_num from '.$mysql_ini['prefix']."online_clients where cid=$clientId limit 1";
				$result = $db->query($query);
				if (1 === mysqli_num_rows($result)){
					$result = $result->fetch_assoc();
					$module_num = $result['mod_num'];
					$module_num ++; // add sys remain mid Number
					if ($module_num){
						$query = 'delete from '.$mysql_ini['prefix']."online_module where cid=$clientId limit $module_num";
						$db->query($query);
					}		
				}	
				$query = 'delete from '.$mysql_ini['prefix']."online_clients where cid=$clientId limit 1";
				$db->query($query);	
				$db->close();
			}
			unlink($fifo_file);						   		
		}
		
		errorlog('namepipe.keeplive 结束返回...cid:'."$clientId",2);
	}

	//ret 返回个数
	//
	private static function namepipe_data_parser(&$source_data,&$requestData){

		$tmp = $source_data;
		
		$totalLen = strlen($tmp);
		$ret = 0;

		while ($totalLen > 0){
			if (false !== ($n = strpos($tmp,':'))){
				$c_cid = substr($tmp,0,$n);
				$tmp = substr($tmp,$n + 1);
				$totalLen -= $n+1;
				if (false !== ($n = strpos($tmp,':'))){
					$c_status = substr($tmp,0,$n);
					$tmp = substr($tmp,$n + 1);
					$totalLen -= $n+1;
					if (false !== ($n = strpos($tmp,':'))){
						$c_dataSize = substr($tmp,0,$n);
						$totalLen -= $n+1;
						if ($totalLen >= $c_dataSize){
							$totalLen -= $c_dataSize;

							$requestData[$c_cid]['cid'] = $c_cid;
							$requestData[$c_cid]['status'] = $c_status;
							$requestData[$c_cid]['data'] = substr($tmp,$n + 1,$c_dataSize);

							$tmp = substr($tmp,$n + 1 + $c_dataSize);						
							$source_data = $tmp;
							$ret ++;

							//errorlog("totalLen remain:".$totalLen);
						}
					}
				}
			}		
		}
		return $ret;
	}
}
?>