<?php

   define('CPANEL', TRUE);
     
   require "./include/common.inc.php";       
   require "$languagedir"."./request.lang.php";
   require "./include/hypnuscript.parser.php";

if (!$lasterror){      
   $module_charset = array( 
	    0 => 'UTF-8',
        1 => 'GB2312',		
   );   


   
   $moduleId   = strtoupper($_GET['mid']);
   $shortCutId   = intval($_GET['sid']);
   $TaskId     = $_GET['tid'];
   $isMulti    = intval($_GET['multi']);
   $cid        = false;
   $remain_drones_num = 0; 

   if (isset($_GET['cid'])){
	   $tmp = explode(';',$_GET['cid']);
	   foreach ($tmp as $a){
		   if (is_numeric($a)){
			   $a = intval($a);
			   $cid[$a] = intval($a);
		   }
	   }     
   }		   		   

   if (isset($_GET['nb'])){
	   $nonBlock = true;
   }else{
	   $nonBlock = false;
   }

   if (isset($_GET['reqtitle'])){
	   $needTitleShow = true; 
   }else{
       $needTitleShow = false; 
   }  

   $requestCharset = 0; 

   $requestData = '';

   $db = connect_db($mysql_ini);
   if (!$db){
       $lasterror[] = $language['fail_db'];
   }else{
	   if (isset($_GET['tid'])){
	       if (!preg_match('/^[a-zA-Z0-9]{32}$/',$TaskId)){
			   $lasterror[] = $language['invalid_tid'];
		   }
		   if (!preg_match('/^[A-F0-9]{32}$/',$moduleId)){
			   $lasterror[] = $language['illegal_mid'];
		   }
		   
		   if ($lasterror){ 
		       $cid = false;
		   }

           if (!$lasterror){ 
			   if (false === ($cid = check_owner_sub_units($cid,$uid,$db,$mysql_ini,$moduleId))){
			       $lasterror[] = $language['no_any_legal_drone'];    
			   }else{
				   $requestData = '';
				   $a = HYP_mysql_recv_block($TaskId,$requestData,$cid,$language,$nonBlock);
				   if (1 == $a){	     
						foreach ($requestData as $c_cid => $c){
						   $uniqu  = $c_cid.'_'.$moduleId;
						   $status = $c['status'];
						   if (2 == $c['status']){
							   $cid[$uniqu]['content'] = hypnuscript_parser($c['data'],$cid[$uniqu]['cid'],$moduleId,$uniqu,$cid[$uniqu]['lasterror'],$language,$module_charset,$requestCharset);
						   }else{
							   if (isset($language['request_status_'.$status])){
								   $cid[$uniqu]['lasterror'][] =  $language['request_status_'.$status];
							   }else{
								   $cid[$uniqu]['lasterror'][] =  $language['unkown_status'].$status;
							   }
						   }
					   }					   
				   }elseif (0 == $a){    
				       if (!$nonBlock){
						   $lasterror[] = $language['ipc_recv_timeout'];
					   }
				   }else{                
					   $lasterror[] = $language['ipc_recv_fail'];
				   }
			   }
		   }		    
	  }else{
	        
		   $data = $_POST;		  

		   if(!empty($_FILES)){ 
		       $upload_files_lazy_deal = array();
		       $upload_files_index = 0;
		       foreach ($_FILES as $n=>$a){
				   if ($a['error']){
					   if (isset($language['error_upload_file'][$a['error']])){
					       $lasterror[] = $language['error_upload_file'][$a['error']];
					   }else{
					       $lasterror[] = $language['error_upload_file'][0].$a['error'];
					   }					   
				       break;
				   }
				   $upload_files_lazy_deal[$upload_files_index] = $a;
				   unset($a['tmp_name']);
				   $data['_FILES'][$n] = $a;
				   $data['_FILES'][$n]['index'] = $upload_files_index;
				   $upload_files_index ++;				   
				}				
			}			

		   if (empty($data)){
			   $data = false;
		   }else{
		       $data = json_encode($data);
		   }
		   
		   if ($shortCutId){
               $query = 'select module,data,dummy,token from '.$mysql_ini['prefix'].'shortcuts where sid = '.$shortCutId.' limit 1';			   
               $result = $db->query($query);
               if (1 == mysqli_num_rows($result)){
			       $tmp = $result->fetch_assoc();
				   $moduleId = $tmp['module'];
				   $data = $tmp['data']; 
				   if ($tmp['dummy']){
					   $query = 'select dummy from '.$mysql_ini['prefix'].'dummy where dummy = '.$tmp['dummy'].' limit 1';  
				   }else{
					   $query = 'select tid from '.$mysql_ini['prefix'].'token where tid = '.$tmp['token'].' limit 1';
				   }
				   $result = $db->query($query);
				   if (1 != mysqli_num_rows($result)){
				       $lasterror[] = $language['illegal_sid'];    
				   }			   
			   }else{
			       $lasterror[] = $language['illegal_sid'];    
			   }
		   }

		   if (!$lasterror){
			   if (!preg_match('/^[A-F0-9]{32}$/',$moduleId)){
				   $lasterror[] = $language['illegal_mid'];
			   }else{				   
				   if (!is_sys_module_id($moduleId)){
					   $query = 'select charset from '.$mysql_ini['prefix'].'modules where module=\''.$moduleId.'\' limit 1';
					   $result = $db->query($query);
					   if (1 == mysqli_num_rows($result)){
						   $tmp = $result->fetch_assoc();
						   $requestCharset = $tmp['charset'];
						   
						   $mod_name_array = get_mod_name($moduleId,$language_choosed);
						   $moduleName = clip_str_width($mod_name_array['name']);

						   if ($data){
							   if (0 != $tmp['charset']){
								   $data = iconv($module_charset[$tmp['charset']],"utf-8//IGNORE",$data);
								   if (false === $data){
									   $lasterror[] = $language['fail_iconv'].$module_charset[$tmp['charset']];
								   }
							   }
						   }
					   }else{
						   $lasterror[] = $language['illegal_mid'];
					   }
				   }
			   }
		   }

		   if ($lasterror){ 
		       $cid = false;
		   }
           
		   if (!$lasterror){
			   if (false !== $cid){
				   if (false === ($cid = check_owner_sub_units($cid,$uid,$db,$mysql_ini,$moduleId))){
				       $lasterror[] = $language['no_any_legal_drone'];    
				   }
			   }
		   }
		   

		   if (!$lasterror){			 
              
			  $query = 'insert into '.$mysql_ini['prefix'].'online_task_lock values(?)';
			  $stmt = $db->prepare($query);
              for ($retry = 10;$retry;$retry -- ){
				  $TaskId = random(32);
				  $stmt->bind_param('s',$TaskId);
				  if (!$stmt->execute()){
					  if ((false !== strstr($stmt->error,'Duplicate')) and ($retry > 1)){
						  continue;
					  }else{
						  $lasterror[] = $language['fail_get_uniqu_tid'];
					  }
				  }else{
				      break;
				  }
			  }
			  $stmt->close();
		   }

		   if (!$lasterror){
			   if(!empty($upload_files_lazy_deal)){ 
			       $upload_path = get_stream_path($TaskId);
		           if ($upload_path){
					   foreach ($upload_files_lazy_deal as $i => $a){						   
						   if (!move_uploaded_file($a['tmp_name'],"$upload_path"."$TaskId".'.UP.'."$i")){
						       $lasterror[] = $a['tmp_name'].' move to: '."$upload_path"."$TaskId".'.UP.'."$i";
						   }
					   }
				   }else{
					   $lasterror[] = $language['fail_stream_path'];
				   }
			   }
		   }

           if (!$lasterror){
              $requestStatus = -9;
			  if (($data) and (strlen($data) > $runtime['max_request_size'])){
			      $requestStatus = -4;  
			  }
			  $query = 'insert into '.$mysql_ini['prefix'].'online_task values (?,?,0,?,?,0,0,'.$requestStatus.',NULL)';
			  $stmt = $db->prepare($query);
              
			  $requestId = false; 
			  $requestInserted = false;

              foreach ($cid as $uniqu => $value){
				  if ($value['lasterror']){
				      continue;
				  }

				  if (false === $requestId){
					  if ($shortCutId){
						  $requestId = $shortCutId;
					  }elseif ($data){
						  $requestId = $TaskId;
					  }else{
						  $requestId = '';
					  }
				  }
				  $stmt->bind_param('siss',$TaskId,$value['cid'],$requestId,$moduleId);
				  if (!$stmt->execute()){
				      $cid[$uniqu]['lasterror'][] = $language['fail_insert_req'];
				  }elseif ((false === $requestInserted) and (!$shortCutId) and ($data) and (-9 == $requestStatus)){
					  $requestInserted = true;
					  if (HYP_IPC_MODE === "mysql"){
						  $query = 'insert into '.$mysql_ini['prefix'].'data_request values (?,?)';	          
						  $stmt02 = $db->prepare($query);
						  $stmt02->bind_param('ss',$TaskId,$data);
						  if (!$stmt02->execute()){
							  $lasterror[] = $language['fail_insert_reqdata'];
						  }
						  $stmt02->close();
					  }						  
				  }
			      $remain_drones_num ++;
				  if ($lasterror){
				      break;
				  }
			  }
			  $stmt->close();			  
		   }

           if (!$lasterror){
			   

			   

		       include "$IPC_mod_path".'IPC_'.HYP_IPC_MODE.'.php';
			   $requestStatus = HYP_IPC::task_send();
		       $query = 'update '.$mysql_ini['prefix'].'online_task set status = '.$requestStatus.' where tid=\''.$TaskId.'\' and status=-9 limit '.$remain_drones_num;

			   if (!$db->query($query)){
				   $lasterror[] = $language['request_status_-9'];
			   }elseif (!$nonBlock){
				   if ((0 == $requestStatus) or (1 == $requestStatus)){
                       if (HYP_IPC_MODE === "mysql"){
						   $db->close();
					       $a = HYP_mysql_recv_block($TaskId,$requestData,$cid,$language);
						   $dealed = true;
					   }else{ 
						   $a = HYP_IPC::task_recv($TaskId,$requestData,$remain_drones_num);
						   $dealed = false;
					   }
					   if (1 == $a){	     
					       foreach ($requestData as $c_cid => $c){
						       $uniqu  = $c_cid.'_'.$moduleId;
							   $status = $c['status'];
							   if (2 == $c['status']){
							       $cid[$uniqu]['content'] = hypnuscript_parser($c['data'],$cid[$uniqu]['cid'],$moduleId,$uniqu,$cid[$uniqu]['lasterror'],$language,$module_charset,$requestCharset);
							   }else{
							       if (isset($language['request_status_'.$status])){
									   $cid[$uniqu]['lasterror'][] =  $language['request_status_'.$status];
								   }else{
									   $cid[$uniqu]['lasterror'][] =  $language['unkown_status'].$status;
								   }
							   }
							   if (true !== $dealed)
								   $dealed[$c_cid] = $c_cid;
						   }
						   if (is_array($dealed)){
						       $queryHead = 'update '.$mysql_ini['prefix'].'online_task set dealed=1 where tid=\''.$TaskId.'\' and (';
							   batch_mysql_query($queryHead,'cid',$dealed,$db);
						   }
					   }elseif (0 == $a){    
						   $lasterror[] = $language['ipc_recv_timeout'];
					   }else{                
						   $lasterror[] = $language['ipc_recv_fail'];
					   }
				   }
			   }
		   }
	   }   
   }
}


$template = Template::getInstance();
$template->setOptions($options);
$broadcast = false;
if ($lasterror){
	ob_start();
	include($template->getfile('header_warning.htm'));
	$broadcast = ob_get_contents() ;
	ob_end_clean();
}
if (!$cid){
	$ret["tips"] = $broadcast;
}else{
	
	foreach ($cid as $uniqu => $a){
		$tmp = array();
		$tmp['uniqu'] = $uniqu;
		$lasterror    = $a['lasterror'];

		if ($lasterror){
			ob_start();
			include($template->getfile('header_warning.htm'));
			$tmp['content'] = ob_get_contents();
			ob_end_clean(); 
			$tmp['fail'] = true;
		}else{
		   if ($needTitleShow){
			   $clientId = $a['cid'];
			   $clientName = $a['name'];
			   ob_start();
			   include($template->getfile('app_title.htm'));
			   $tmp['title'] = ob_get_contents();
			   ob_end_clean();   
		   }      
		   if (false !== $broadcast){
			   $tmp['content'] = $broadcast;
			   $tmp['fail'] = true;
		   }
		   if (isset($a['content'])){
			   $tmp['content'] .= $a['content'];
		   }else{
			   if (false === $broadcast){
				   $tmp['cid'] = $a['cid'];
				   $tmp['mid'] = $a['mid'];
				   $tmp['keepRequest'] = 1; 
				   $tmp['tid'] = $TaskId;
			   }
		   }
		}
		$ret['drones'][] = $tmp;
	}
}
$ret =  rawurlencode(json_encode($ret));
exit ($ret);
var_dump ($ret);
exit;




	function HYP_mysql_recv_block($tid,&$requestData,&$cid,$language,$nonblock=false){
		global $mysql_ini;
		global $srv_block_ini;

		$total = $srv_block_ini['breath_delay'];
        
		$requestData = array();
		$ret = 0;

		$cid_2_drone = array();
		$num = 0;
		foreach ($cid as $uniqu => $a){
			if (!$a['lasterror']){
				$cid_2_drone[$a['cid']] = $uniqu;		    
				$num ++;
			}
		}

		$c_complete_array = array();

		while ($num){
			$c_new_gets = false;
			$db = connect_db($mysql_ini);
			if (!$db){
				$ret = -1;
			}else{
				$query = 'select cid,status,module from '.$mysql_ini['prefix'].'online_task where tid=\''.$tid.'\' and dealed=0 limit '.$num;

				$result = $db->query($query);
				$i = mysqli_num_rows($result);
				if ($i > 0){
				    for (;$i;$i--){
						$tmp = $result->fetch_assoc();
						if (isset($cid_2_drone[$tmp['cid']])){
							if ((1 == $tmp['status']) or (0 == $tmp['status'])){							
							    continue;
							}else{

								$ccid  = $tmp['cid'];

								$c_complete_array[$tmp['cid']] = $ccid;
								
								$requestData[$tmp['cid']]['cid']    = $ccid;
								$requestData[$tmp['cid']]['status'] = $tmp['status'];
								$requestData[$tmp['cid']]['data']   = '';

								

								$requestLen = false;
					            
                                if (2 == $tmp['status']){     									
									$query = 'select data from '.$mysql_ini['prefix'].'data_response where tid=\''.$tid.'\' and cid='.$ccid.' limit 1';
									$response_result = $db->query($query);
									if (1 == mysqli_num_rows($response_result)){
										$tmp = $response_result->fetch_assoc();
										$requestData[$ccid]['data'] = $tmp['data'];
									}                                    
								}

								$ret = 1;
								$c_new_gets = true;								
							}						
						}					
					}
                    if (!empty($c_complete_array)){
						$queryHead = 'update '.$mysql_ini['prefix'].'online_task set dealed=1 where tid=\''.$tid.'\' and (';
					    if (batch_mysql_query($queryHead,'cid',$c_complete_array,$db)){
							$num -= count($c_complete_array);
							
						}
					}
				}

				$db->close();


				if ($nonblock)
					break;

				if (0 === $ret){
					if ($total >= $srv_block_ini['sleep']){
						$total -= $srv_block_ini['sleep'];
						sleep ($srv_block_ini['sleep']);
						continue;
					}else{
					    break;
					}
				}
                

				if ((1 === $ret) and ($num) and (true === $c_new_gets)){
					
					$total = 0;
					sleep ($srv_block_ini['sleep']);
					errorlog("[sleep]".$srv_block_ini['sleep']." tid:".$tid." num:".$num);
					continue;				
				}else{
				    break;
				}
			}
		}
		return $ret;
	}


function check_owner_sub_units($sub,$owner,$db,$mysql_ini,$mid){
	global $language;

    $ret     =  false;

	$clients =  $sub;

    $max_request_len = 1024; 
	                         

    $query_head = 'select a.cid,a.name,b.alias,c.module from '.$mysql_ini['prefix'].'online_clients as a left join '.$mysql_ini['prefix'].'dummy as b on b.dummy=a.dummy left join '.$mysql_ini['prefix'].'online_module as c on c.cid=a.cid and c.module=\''.$mid.'\''.' where a.status=1 '.' and (';
    $query_tail = ') limit ';

	$max_request_len -= strlen($query_head) + strlen($query_tail);
    
    while (!empty($sub)){
		$units = 0;
		$remain = $sub;
		$c_len = $max_request_len;
		$query = $query_head;
		$c_units = array();
	    foreach ($remain as $a){
			$q = 'a.cid='.$a.' or ';
			$b = strlen($q);
	        if ($c_len > $b){
				$c_len -= $b;
				$query .= $q;
				$c_units[$a] = $a;
				unset ($sub[$a]);
				$units ++;
			}else{
			    break;
			}
		}
		$query  = substr($query,0,strlen($query) - 4);
		$query .= $query_tail.$units;

		$result = $db->query($query);		
		$i = mysqli_num_rows($result);
		if ($i){			
		    for (;$i>0;$i--){
			    $tmp = $result->fetch_assoc();
				$uniqu = $tmp['cid'].'_'.$mid;
				unset ($clients[$tmp['cid']]);
				$ret[$uniqu]['cid'] = $tmp['cid'];
				$ret[$uniqu]['mid'] = $mid;
				if (!empty($tmp['alias'])){
					$ret[$uniqu]['name'] = clip_str_width($tmp['alias']);
				}else{
					$ret[$uniqu]['name'] = clip_str_width($tmp['name']);
				}	
				if ((!is_sys_module_id($mid)) and (!$tmp['module'])){
				    $ret[$uniqu]['lasterror'][] = $language['no_mid_client'];
				}
			}
		}
	}

    foreach ($clients as $a) {
		
		$ret[$a.'_'.$mid]['lasterror'][] = $language['client_off_line'];		
	}
	return $ret;
}






function batch_mysql_query($qHead,$key,$array,$db,$maxSize=1024){
    
	$ret = true;

    $sub = $array;
	while (!empty($sub)){
		$units = 0;
		$remain = $sub;
		$c_len = $maxSize;
		$query = '';
	    foreach ($array as $a){
			$q = "$key".'='.$a.' or ';
			$b = strlen($q);
	        if ($c_len > $b){
				$c_len -= $b;
				$query .= $q;
				unset ($sub[$a]);
				$units ++;
			}else{
			    break;
			}
		}
		$query  = substr($query,0,strlen($query) - 4);
		$query  = $qHead.$query.') limit '.$units;

		if (!$db->query($query)){
            $ret = false;  
			errorlog("[batch_mysql_query fail]".$query);
		}
	}

	return $ret;
}

?>




