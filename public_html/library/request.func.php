<?php

class ReqFunc{

	private static $_cid = false;
	private static $_result = false;
    private static $_instance = false;
	private static $_tid = false;
	private static $_data = false;
	private static $_drones_num = 0; //需要处理的drones个数
	private static $_mid = false;
	private static $_charset = false;
	private static $_mod_name_array = false;
	private static $_moduleName = false;
	private static $_requestStatus = -9;
	private static $_nonBlock = false;
	private static $_db;
	private static $_mysql_ini;


    public static function shower(){
		var_dump (self::$_cid);		
		var_dump (self::$_instance);
		var_dump (self::$_result);
		var_dump (self::$_tid);
		var_dump (self::$_data);
		var_dump (self::$_drones_num);
	}

	public static function is_sys_module_id(){
	    return GlobalFunc::is_sys_module_id(self::$_mid);
	}

	public static function init($db,$mysql_ini){
		self::$_db = $db;
		self::$_mysql_ini = $mysql_ini;
	}

	public static function SetNonBlock($nonBlock){
	    self::$_nonBlock = $nonBlock;
	}
    
    public static function SetTaskID($TaskId){
	    if (!preg_match('/^[a-zA-Z0-9]{32}$/',$TaskId)){
		    return false;
		}
		self::$_tid = $TaskId;
		return true;
	}

	public static function SetModuleID($moduleId){
	    if (!preg_match('/^[A-F0-9]{32}$/',$moduleId)){
		    return false;
		}
		self::$_mid = $moduleId;
		return true;
	}
    
	//初始化 快捷方式 数据
	public static function InitShortcutsData($shortCutId){

		$query = 'select module,data,dummy,token from '.self::$_mysql_ini['prefix'].'shortcuts where sid = '.$shortCutId.' limit 1';
		$result = self::$_db->query($query);
		if (1 == mysqli_num_rows($result)){
			$tmp = $result->fetch_assoc();
			self::$_mid = $tmp['module'];
			self::$_data = $tmp['data']; //use shortcut's data				   
			if ($tmp['dummy']){
				$query = 'select dummy from '.self::$_mysql_ini['prefix'].'dummy where dummy = '.$tmp['dummy'].' limit 1';  
			}else{
				$query = 'select tid from '.self::$_mysql_ini['prefix'].'token where tid = '.$tmp['token'].' limit 1';
			}
			$result = self::$_db->query($query);
			if (1 != mysqli_num_rows($result)){
				return false;
			}		   
		}else{
			return false;
		}
		return true;
	}

	//输出
	public static function OutputResult($needTitleShow){
        
		global $options;
		global $lasterror;
		global $language;

		$ret = false;
		$template = Template::getInstance();
		$template->setOptions($options);
		$broadcast = false;
		if ($lasterror){
			ob_start();
			include($template->getfile('header_warning.htm'));
			$broadcast = ob_get_contents() ;
			ob_end_clean();
		}
		
		if (empty(self::$_result)){
			$ret["tips"] = $broadcast;
		}else{
			foreach (self::$_result as $cid => $a){
				if (isset($a['lasterror'])){
					$lasterror = array ();
					$lasterror[] = $a['lasterror'];
					ob_start();
					include($template->getfile('header_warning.htm'));
					$cc = ob_get_contents();
					ob_end_clean(); 
					foreach (self::$_instance[$cid] as $uniqu){
						$tmp = array();
						$tmp['cid'] = $cid;
						$tmp['uniqu'] = $uniqu;
						$tmp['content'] = $cc;
						$tmp['fail'] = true;
						$ret['drones'][] = $tmp;
					}			
				}else{
					//var_dump (self::$_cid);
					//var_dump (self::$_result);
				   foreach (self::$_instance[$cid] as $uniqu){
					   $tmp = array();
					   $tmp['cid'] = $cid;
					   $tmp['uniqu'] = $uniqu;			
					   if ($needTitleShow){
						   $clientId = $a['cid'];
						   $clientName = $a['name'];
						   $moduleName = self::$_moduleName;
						   $moduleId   = self::$_mid;
						   ob_start();
						   include($template->getfile('app_title.htm'));
						   $tmp['title'] = ob_get_contents();
						   ob_end_clean();
					   }
					   if (false !== $broadcast){
						   $tmp['content'] = $broadcast;
						   $tmp['fail'] = true;
					   }
					   if (isset($a['content'][$uniqu])){
						   $tmp['content'] .= $a['content'][$uniqu];
					   }else{
						   if (false === $broadcast){
							   $tmp['cid'] = $a['cid'];
							   $tmp['mid'] = $a['mid'];
							   $tmp['keepRequest'] = 1; 
							   $tmp['tid'] = self::$_tid;
						   }
					   }
					   $ret['drones'][] = $tmp;
				   }
				}
			}	
		}
		return $ret;
	}

     

	//阻塞 返回
	public static function BlockConnDrone($hasTid=false){

		global $language;
        
		$requestData = false;
		$dealed = true;

		if (!$hasTid){
			if (self::$_nonBlock){
			    return;
			}
			if ((0 == self::$_requestStatus) or (1 == self::$_requestStatus)){
			   if (HYP_IPC_MODE === "mysql"){
				   self::$_db->close();
				   $a = self::mysql_recv_block($requestData);
			   }else{ //if (HYP_IPC_MODE !== "namepipe"){
				   $a = HYP_IPC::task_recv(self::$_tid,$requestData,self::$_drones_num);
				   $dealed = false;
			   }
			}else{
			    return;
			}			
		}else{
		    $a = self::mysql_recv_block($requestData);
		}

	   if (1 == $a){	     //success					       
		   foreach ($requestData as $c_cid => $c){
			   $uniqu  = self::$_instance[$c_cid];//$c_cid.'_'.$moduleId;
			   $status = $c['status'];
			   if (2 == $c['status']){
				   
				   self::SetDataCharset($c['data']);

				   self::$_result[$c_cid]['content'] = HYP_SCRIPT::parser($c['data'],$c_cid,self::$_mid,$uniqu,self::$_result[$c_cid]['lasterror'],$language);

			   }else{
				   if (isset($language['request_status_'.$status])){
					   self::$_result[$c_cid]['lasterror'] =  $language['request_status_'.$status];
				   }else{
					   self::$_result[$c_cid]['lasterror'] =  $language['unkown_status'].$status;
				   }
			   }
			   if (true !== $dealed)
				   $dealed[$c_cid] = $c_cid;
		   }
		   if (is_array($dealed)){
			   $queryHead = 'update '.self::$_mysql_ini['prefix'].'online_task set dealed=1 where tid=\''.self::$_tid.'\' and (';
			   self::batch_mysql_query($queryHead,'cid',$dealed,self::$_db);
		   }
	   }elseif (0 == $a){    //timeout
		   $lasterror[] = $language['ipc_recv_timeout'];
	   }else{                //fail
		   $lasterror[] = $language['ipc_recv_fail'];
	   }
	
	}


	//发送指令(IPC != mysql) 并 获取返回(if Block)
	public static function ConnectDrone(){
        
	    self::$_requestStatus = HYP_IPC::task_send(self::$_tid,self::$_result,self::$_mid,self::$_data);

		$query = 'update '.self::$_mysql_ini['prefix'].'online_task set status = '.self::$_requestStatus.' where tid=\''.self::$_tid.'\' and status=-9 limit '.self::$_drones_num;

		if (!self::$_db->query($query)){
			return false;
		}
		return true;
	 
	}

	//获取 模块相关 名称，字符集，...
	public static function GetModAttribute($language_choosed){

       global $module_charset;

	   $query = 'select charset from '.self::$_mysql_ini['prefix'].'modules where module=\''.self::$_mid.'\' limit 1';

	   $result = self::$_db->query($query);
	   if (1 == mysqli_num_rows($result)){
		   $tmp = $result->fetch_assoc();

		   if ($tmp['charset'] > 0){ // 0: utf-8 ignore it
		       if (isset($module_charset[$tmp['charset']])){
				   self::$_charset = $module_charset[$tmp['charset']];
			   }
		   }
		   
		   self::$_mod_name_array = GlobalFunc::get_mod_name(self::$_mid,$language_choosed);
		   self::$_moduleName = GlobalFunc::clip_str_width(self::$_mod_name_array['name']);		   
						   
	   }else{
		   return false;
	   }
	   return true;
	    
	}

	//检测data长度是否超标
	private static function TooLongData(){
	    global $runtime;
		if ((self::$_data) and (strlen(self::$_data) > $runtime['max_request_size'])){
		    return true;
		}
		return false;
	}

	//字符转换，如果需要的话
    public static function SetDataCharset_Req(){
        return self::SetDataCharset(self::$_data);
	}

	public static function SetDataCharset(&$dst){
	    if ($dst){
		    if (false !== self::$_charset){
			    if (false === ($dst = iconv(self::$_charset,"utf-8//IGNORE",$dst))){
				    return self::$_charset;
				}
			}
		}
		return true;
	}
    
    //设置_data,提交数据
	public static function SetData($src,$json=false){
		if (!empty($src)){
			if ($json){
				$src = json_encode($src);      
			}
			self::$_data = $src;
		}
	}

    //任务插入task表
	public static function TaskInsertDB($shortCutId){
          global $lasterror;
		  global $language;

		  if (self::TooLongData()){
			  self::$_requestStatus = -4;  
		  }

		  $query = 'insert into '.self::$_mysql_ini['prefix'].'online_task values (?,?,0,?,?,0,0,'.self::$_requestStatus.',NULL)';
		  $stmt = self::$_db->prepare($query);
		  
		  $requestId = false; 
		  $requestInserted = false;

		  foreach (self::$_result as $cid => $value){
			  if ($value['lasterror']){
				  continue;
			  }

			  if (false === $requestId){
				  if ($shortCutId){
					  $requestId = $shortCutId;
				  }elseif (self::$_data){
					  $requestId = self::$_tid;
				  }else{
					  $requestId = '';
				  }
			  }
			  $stmt->bind_param('siss',self::$_tid,$value['cid'],$requestId,self::$_mid);
			  if (!$stmt->execute()){
				  self::$_result[$cid]['lasterror'] = $language['fail_insert_req'];
			  }elseif ((false === $requestInserted) and (!$shortCutId) and (self::$_data) and (-9 == self::$_requestStatus)){
				  $requestInserted = true;
				  if (HYP_IPC_MODE === "mysql"){
					  $query = 'insert into '.self::$_mysql_ini['prefix'].'data_request values (?,?)';	          
					  $stmt02 = self::$_db->prepare($query);
					  $stmt02->bind_param('ss',self::$_tid,self::$_data);
					  if (!$stmt02->execute()){
						  $lasterror[] = $language['fail_insert_reqdata'];
					  }
					  $stmt02->close();
				  }						  
			  }
			  self::$_drones_num ++;
			  if ($lasterror){
				  break;
			  }
		  }
		  $stmt->close();	
	}

	//处理上传文件
	public static function mv_uploaded_file($file_array){
		global $lasterror;
		global $language;
		$upload_path = GlobalFunc::get_stream_path(self::$_tid);
		if ($upload_path){
			foreach ($file_array as $i => $a){
				if (!move_uploaded_file($a['tmp_name'],"$upload_path".self::$_tid.'.UP.'."$i")){
					$lasterror[] = $a['tmp_name'].' move to: '."$upload_path".self::$_tid.'.UP.'."$i";
				}
			}
		}else{
			$lasterror[] = $language['fail_stream_path'];
		}
	
	}

    //获取 并 保存 唯一的 task ID
	public static function GetTaskID(){
		$ret = false;
        $query = 'insert into '.self::$_mysql_ini['prefix'].'online_task_lock values(?)';
		$stmt = self::$_db->prepare($query);
		for ($retry = 10;$retry;$retry -- ){
			$TaskId = GlobalFunc::random(32);
			$stmt->bind_param('s',$TaskId);
			if (!$stmt->execute()){
				if ((false !== strstr($stmt->error,'Duplicate')) and ($retry > 1)){
					continue;
				}
			}else{
				self::$_tid = $TaskId;
				$ret = true;
			    break;
			}
		}
		$stmt->close();
		return $ret;
	}

	//根据 instance@cid 返回 _cid[cid] = cid
	//                       _instance[cid][] = instance
	//
    public static function CidParser($src){
	   $ret = false;
	   $tmp = explode(';',$src);
	   foreach ($tmp as $a){
           $b = explode ('@',$a,2);
		   if (2 == count($b)){
		       $instance = intval($b[0]);
			   $cid      = intval($b[1]);
			   self::$_cid[$cid]        = $cid;
			   self::$_instance[$cid][$instance] = $instance;
			   $ret = true;
		   }
	   }
	   return $ret;
	}
    
	//return Online Clients List
	// $_result[cid][...]
	//
	public static function check_owner_sub_units(){
		global $language;

		$clients = self::$_cid;

		$cid = self::$_cid;

		$max_request_len = 1024; //最大每次请求长度(受mysql单指令长度限制)
								 //注：可能会超过，需要保守使用

		$query_head = 'select a.cid,a.name,b.alias,c.module from '.self::$_mysql_ini['prefix'].'online_clients as a left join '.self::$_mysql_ini['prefix'].'dummy as b on b.dummy=a.dummy left join '.self::$_mysql_ini['prefix'].'online_module as c on c.cid=a.cid and c.module=\''.self::$_mid.'\''.' where a.status=1'.' and (';
		$query_tail = ') limit ';

		$max_request_len -= strlen($query_head) + strlen($query_tail);
		
		while (!empty($cid)){
			$units = 0;
			$remain = $cid;
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
					unset ($cid[$a]);
					$units ++;
				}else{
					break;
				}
			}
			$query  = substr($query,0,strlen($query) - 4);
			$query .= $query_tail.$units;

			$result = self::$_db->query($query);		
			$i = mysqli_num_rows($result);
			if ($i){			
				for (;$i>0;$i--){
					$tmp = $result->fetch_assoc();
					unset ($clients[$tmp['cid']]);
					self::$_result[$tmp['cid']]['cid'] = $tmp['cid'];
					self::$_result[$tmp['cid']]['mid'] = self::$_mid;
					if (!empty($tmp['alias'])){
						self::$_result[$tmp['cid']]['name'] = GlobalFunc::clip_str_width($tmp['alias']);
					}else{
						self::$_result[$tmp['cid']]['name'] = GlobalFunc::clip_str_width($tmp['name']);
					}	
					if ((!GlobalFunc::is_sys_module_id(self::$_mid)) and (!$tmp['module'])){
						self::$_result[$tmp['cid']]['lasterror'] = $language['no_mid_client'].self::$_mid;//$language['no_mid_client'];
					}
				}
			}
		}

		foreach ($clients as $a) {
			//var_dump ($query);
			self::$_result[$a]['lasterror'] = $language['client_off_line'];		
		}

		if (false === self::$_result){
		    return false;
		}else{
			return true;
		}
	}


	private static function mysql_recv_block(&$requestData){
		global $srv_block_ini;

		$total = $srv_block_ini['breath_delay'];
        
		$requestData = array();
		$ret = 0;

		$cid_2_drone = array();
		$num = 0;
		foreach (self::$_result as $uniqu => $a){
			if (!$a['lasterror']){
				$cid_2_drone[$a['cid']] = $uniqu;		    
				$num ++;
			}
		}

		$c_complete_array = array();
//$num = 1;
		while ($num){
			$c_new_gets = false;
			$db = GlobalFunc::connect_db(self::$_mysql_ini);
			if (!$db){
				$ret = -1;
			}else{
				$query = 'select cid,status,module from '.self::$_mysql_ini['prefix'].'online_task where tid=\''.self::$_tid.'\' and dealed=0 limit '.$num;

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
									$query = 'select data from '.self::$_mysql_ini['prefix'].'data_response where tid=\''.self::$_tid.'\' and cid='.$ccid.' limit 1';
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
						$queryHead = 'update '.self::$_mysql_ini['prefix'].'online_task set dealed=1 where tid=\''.self::$_tid.'\' and (';
					    if (self::batch_mysql_query($queryHead,'cid',$c_complete_array,$db)){
							$num -= count($c_complete_array);
							//unset ($cid_2_drone[$tmp['cid']]);
						}
					}
				}

				$db->close();


				if (self::$_nonBlock)
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
					//break;
					$total = 0;
					sleep ($srv_block_ini['sleep']);
					GlobalFunc::errorlog("[sleep]".$srv_block_ini['sleep']." tid:".self::$_tid." num:".$num);
					continue;				
				}else{
				    break;
				}
			}
		}
		return $ret;
	}



	//$array 内容必须为数字，不支持字符串
	//$qHead = " select .... where xxx=1 and (";
	//$maxSize 仅统计生成部分
	private static function batch_mysql_query($qHead,$key,$array,$db,$maxSize=1024){
		
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
				GlobalFunc::errorlog("[batch_mysql_query fail]".$query);
			}
		}

		return $ret;
	}
}

?>