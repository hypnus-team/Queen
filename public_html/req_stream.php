<?php

   define('CPANEL', TRUE);
   require "./include/common.inc.php";       
   require "$languagedir"."./request.lang.php";	

   $module_charset = array( //模块字符集定义 iconv函数使用 (by db_modules.charset)
	    0 => 'UTF-8',
        1 => 'GB2312',		
   );  

   $clientId   = intval($_GET['cid']);
   $moduleId   = strtoupper($_GET['mid']);
   $header     = $_GET['header'];
   $streamSize = intval($_GET['size']);
   
   if (!isset($header['content-type'])){
       $header['content-type'] = 'application/binary';
   }

   $db = GlobalFunc::connect_db($mysql_ini);
   if (!$db){
       $lasterror[] = $language['fail_db'];
   }else{  

	  $my_GET = $_GET;
	  unset ($my_GET['cid']);
	  unset ($my_GET['mid']);  
	  unset ($my_GET['header']);	
	  unset ($my_GET['size']);		  
	  $data = $_POST;
	  if (!empty($my_GET)){
		  foreach ($my_GET as $a => $b){
			  if (!isset($data[$a])){
				  $data[$a] = $b;
			  }
		  }
	  }
		
	   if (empty($data)){
		   $data = false;
	   }else{
		   $data = json_encode($data);
	   }		 
	   
	   if (!preg_match('/^[A-F0-9]{32}$/',$moduleId)){
		   $lasterror[] = $language['illegal_mid'];
	   }

	   if (!$clientId){
		   $lasterror[] = $language['client_off_line'];
	   }

	   if (!$lasterror){
		   $query = 'select a.name,b.alias from '.$mysql_ini['prefix'].'online_clients as a left join '.$mysql_ini['prefix'].'dummy as b on b.dummy=a.dummy where a.cid='.$clientId.' limit 1';
		   $result = $db->query($query);
		   if (1 == mysqli_num_rows($result)){
			   $tmp = $result->fetch_assoc();
			   $clientName = GlobalFunc::clip_str_width($tmp['name']);

			   $query = 'select b.module,b.charset from '.$mysql_ini['prefix'].'online_module as a left join '.$mysql_ini['prefix'].'modules as b on b.module=a.module where a.cid='.$clientId.' and a.module=\''.$moduleId.'\' limit 1';
			   $result = $db->query($query);
			   if (1 != mysqli_num_rows($result)){
				   $lasterror[] =  $language['no_mid_client'];
			   }else{				   				
				   $tmp = $result->fetch_assoc();
				   $mod_name_array = GlobalFunc::get_mod_name($tmp['module'],$language_choosed);
				   $moduleName = GlobalFunc::clip_str_width($mod_name_array['name']);					   
				   if ($data){
					   if (0 != $tmp['charset']){
						   $data = iconv($module_charset[$tmp['charset']],"utf-8//IGNORE",$data);
						   if (false === $data){
							   $lasterror[] = $language['fail_iconv'].$module_charset[$tmp['charset']];
						   }
					   }
				   }   
			   }
		   }else{
			   $lasterror[] = $language['client_off_line'];
		   }
	   }

       if (!$lasterror){			 
		  
		  $query = 'insert into '.$mysql_ini['prefix'].'online_task_lock values(?)';
		  $stmt = $db->prepare($query);
		  for ($retry = 10;$retry;$retry -- ){
			  $TaskId = GlobalFunc::random(32);
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
		  $requestStatus = -9;
		  if (strlen($data) > $runtime['max_request_size']){
			  $requestStatus = -4;  
		  }
		  $query = 'insert into '.$mysql_ini['prefix'].'online_task values (?,?,0,?,?,1,'.$streamSize.','.$requestStatus.',NULL)';
		  $stmt = $db->prepare($query);		  
		  
		  if ($data){
			  $requestId = $TaskId;
		  }
		  
		  $stmt->bind_param('siss',$TaskId,$clientId,$requestId,$moduleId);
		  if (!$stmt->execute()){	
			  $lasterror[] = $language['fail_insert_req'];		 
		  }elseif (($data) and (-9 == $requestStatus)){
			  if (HYP_IPC_MODE === "mysql"){
				  $query = 'insert into '.$mysql_ini['prefix'].'data_request values (?,?)';	          
				  $stmt = $db->prepare($query);
				  $stmt->bind_param('ss',$TaskId,$data);
				  if (!$stmt->execute()){
					  $lasterror[] = $language['fail_insert_reqdata'];
				  }
			  }
		  }
		  $stmt->close();
	   }

	   if (!$lasterror){
		   include "$IPC_mod_path".'IPC_'.HYP_IPC_MODE.'.php';
		   $cid["$clientId".'_'."$moduleId"]['cid'] = $clientId;
		   $requestStatus = HYP_IPC::task_send($TaskId,$cid,$moduleId,$data);
		   $query = 'update '.$mysql_ini['prefix'].'online_task set status = '.$requestStatus.' where tid=\''.$TaskId.'\' and status=-9 limit 1';
		   if ($db->query($query)){
			   $requestStatus = 0;			       
		   }else{
			   $lasterror[] = $language['request_status_-9'];
		   }
	   }
   }

   

   set_time_limit(0);
   ignore_user_abort(true);

   define("STREAM_TASK_END_OVERTIME",51);
   define("STREAM_TASK_END_BROWSERBROKE",52);
   define("STREAM_TASK_END_COMPLISHED",53);
   define("STREAM_TASK_END_UNKNOWN",54);
   define("STREAM_TASK_END_READFILE",55);
   define("STREAM_TASK_END_CONNMYSQL",56);

   $header_show = false;

   $end_status = STREAM_TASK_END_UNKNOWN;

   $streamWaitExpire = 60; 
   $waitSecPer       = 2;  
   if (!$lasterror){ 
	   $chunked_size = 0;
	   $chunk_output = 1;
	   $waitSeconds  = 0;

	   $stream_file  = GlobalFunc::get_stream_path($TaskId,false);
	   $stream_file .= "$TaskId".'.DOWN.';
       $query = 'select chunk,size,status from '.$mysql_ini['prefix'].'online_task where tid=\''.$TaskId.'\' limit 1';
       while (!$lasterror){
		   $result = $db->query($query);
		   if (1 != mysqli_num_rows($result)){
			   $lasterror[] =  'select Task fail StreamLoop';
		   }else{
			   $tmp = $result->fetch_assoc();
			   $chunk_input = $tmp['chunk'];
			   $totalSize   = $tmp['size'];
			   if ((1 != $tmp['status']) and (2 != $tmp['status']) and (0 != $tmp['status'])){
			       $lasterror[] = 'status = '.$tmp['status'];
				   break;
			   }
			   while ($chunk_output < $chunk_input){ 
				   if (1 == $chunk_output){ 
				       $header_show = true;
					   foreach ($header as $a => $b){
					       header("$a".':'."$b");
					   }
					   if ($totalSize){
					       header ("Content-Length: ".$totalSize);
					   } 
				   }				   
				   $fCont = file_get_contents("$stream_file".$chunk_output);
				   if (false === $fCont){
					   $lasterror[] = 'file get contents fail: '."$stream_file".$chunk_output;
					   $end_status = STREAM_TASK_END_READFILE;
					   break;
				   }
				   if ($totalSize > 0){
					   $a = strlen($fCont);
					   $chunked_size += $a;
					   if ($chunked_size > $totalSize){
					       $a = $a - ($chunked_size - $totalSize);
						   $fCont = substr($fCont,0,$a);
					   }

				   }				   
				   echo "$fCont";
				   ob_flush(); 
				   flush(); 
				   $waitSeconds  = 0;
				   $chunk_output ++;   
			   }
			   if (($totalSize > 0) and ($chunked_size >= $totalSize)){
				   $end_status = STREAM_TASK_END_COMPLISHED;
				   break;
			   }			   
			   if (0 !== connection_status()){
				   $end_status = STREAM_TASK_END_BROWSERBROKE;
				   break;
			   }
			   
			   
			  
			   $db->close();
			   sleep ($waitSecPer);
			   $db = GlobalFunc::connect_db($mysql_ini);
			   if (!$db){
				   $lasterror[] = $language['fail_db'].' StreamLoop';
				   $end_status = STREAM_TASK_END_CONNMYSQL;
			   }
			   $waitSeconds += $waitSecPer;
			   if ($waitSeconds > $streamWaitExpire){
				   $lasterror[] = 'connect over time';
				   $end_status = STREAM_TASK_END_OVERTIME;
				   break;
			   }
		   }
	   }
   }
   if (($lasterror) and (!$header_show)){
	   include($template->getfile('tips.htm'));
       
   }
   if (($db) and ($TaskId)){ 
	   $query = 'update '.$mysql_ini['prefix'].'online_task set status = '.$end_status.' where tid=\''.$TaskId.'\' limit 1' ;
	   $db->query($query);	
   }
   exit();
?>


