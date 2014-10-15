<?php

   define('CPANEL', TRUE);
     
   require "./include/common.inc.php";       //定义基本变量以及语言   
   require "$languagedir"."./request.lang.php";
   require "./library/hypnuscript.parser.php";
   require "./library/request.func.php";

if (!$lasterror){      
   $module_charset = array( //模块字符集定义 iconv函数使用 (by db_modules.charset)
	    0 => 'UTF-8',
        1 => 'GB2312',		
   );   
//ini_set('display_errors',1);
//error_reporting(E_ALL);
   //$clientId   = intval($_GET['cid']);
   $moduleId   = strtoupper($_GET['mid']);
   $shortCutId = intval($_GET['sid']);
   $TaskId     = $_GET['tid'];
   $isMulti    = intval($_GET['multi']);
   $cid        = false;


   if (isset($_GET['cid'])){
	   if (false === ReqFunc::CidParser($_GET['cid'])){
	       $lasterror[] = $language['no_any_legal_drone'];
	   }
   }

   if (isset($_GET['nb'])){
	   ReqFunc::SetNonBlock(true);
   }else{
	   ReqFunc::SetNonBlock(false);
   }

   if (isset($_GET['reqtitle'])){
	   $needTitleShow = true; //回显Title
   }else{
       $needTitleShow = false; //回显Title
   }  

   $requestCharset = 0; //utf-8 default

   $requestData = '';

   $db = GlobalFunc::connect_db($mysql_ini);
   if (!$db){
       $lasterror[] = $language['fail_db'];
   }else{
       ReqFunc::init($db,$mysql_ini);

	   if (isset($_GET['tid'])){
	       if (!ReqFunc::SetTaskID($TaskId)){
			   $lasterror[] = $language['invalid_tid'];
		   }
		   if (!ReqFunc::SetModuleID($moduleId)){
			   $lasterror[] = $language['illegal_mid'];
		   }

           if (!$lasterror){ 
			   if (false === ReqFunc::check_owner_sub_units()){
			       $lasterror[] = $language['no_any_legal_drone'];    
			   }else{				   
				   ReqFunc::BlockConnDrone(true);
			   }
		   }		    
	  }else{
	        
		   $data = $_POST;		  

		   if(!empty($_FILES)){ //upload files
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

	   
		   if ($shortCutId){
			   if (!ReqFunc::InitShortcutsData($shortCutId)){
			       $lasterror[] = $language['illegal_sid'];    
			   }
		   }else{
			   ReqFunc::SetData($data,true);	
			   if (!ReqFunc::SetModuleID($moduleId)){
			       $lasterror[] = $language['illegal_mid'];
			   }
		   }

		   if (!$lasterror){		   
			   if (!ReqFunc::is_sys_module_id()){
				   if (false === ReqFunc::GetModAttribute($language_choosed)){
					   $lasterror[] = $language['illegal_mid'];
				   }else{
					   if (true !==($tmp = ReqFunc::SetDataCharset_Req())){
						   $lasterror[] = $language['fail_iconv'].$tmp;
					   }						   
				   }
			   }else{
				   
			   }
		   }
           
		   if (!$lasterror){
			   if (false === ReqFunc::check_owner_sub_units()){
			       $lasterror[] = $language['no_any_legal_drone'];    
			   }		   
		   }

		   if (!$lasterror){			 
              if (false === ReqFunc::GetTaskID()){
			      $lasterror[] = $language['fail_get_uniqu_tid'];
			  }
		   }

		   if (!$lasterror){
			   if(!empty($upload_files_lazy_deal)){ //upload files
			       ReqFunc::mv_uploaded_file($upload_files_lazy_deal);
			   }
		   }

           if (!$lasterror){              
			  ReqFunc::TaskInsertDB($shortCutId);
		   }

           if (!$lasterror){
			   //select_drones($cid,$fail_drones,$remain_drones,$fin_drones);

			   //$remain_drone_num = count($remain_drones);

		       include "$IPC_mod_path".'IPC_'.HYP_IPC_MODE.'.php';
               if (false === ReqFunc::ConnectDrone()){
                   $lasterror[] = $language['request_status_-9'];//$requestStatus = -9;			       			   
			   }else{				   
					ReqFunc::BlockConnDrone();
			   }
		   }
	   }   
   }
}


$ret = ReqFunc::OutputResult($needTitleShow);
//var_dump ($ret);
$ret =  rawurlencode(json_encode($ret));
exit ($ret);
var_dump ($ret);
exit;




?>




