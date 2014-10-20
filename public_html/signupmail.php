<?php

   define('C_AUTHENTICATION_PAGE',TRUE); 

   require "./include/common.inc.php";       
   require "$languagedir"."./signup.lang.php";

   require './include/recaptchalib.php';

   $language['panel'] = $language['panel_signupmail'];

   $active_key = $_GET['r'];
   
   if (!preg_match('/^[a-zA-Z0-9]{32}$/',$active_key)){
	   $lasterror[] = $language['fail_signup_key'];
   }

   if (!$lasterror){
	   $db = GlobalFunc::connect_db($mysql_ini);
	   if ($db===FALSE){
		   $lasterror[] =  $language['fail_db'];
	   }else{			
		   $current = time() - 48*60*60; 
		   $del_expired_record_query = 'delete from '.$mysql_ini['prefix'].'members_inactive where reg_time < '.$current;
		   $ret = $db->query("$del_expired_record_query");

		   $result = $db->query('select * from '.$mysql_ini['prefix'].'members_inactive where `key` = \''.$active_key.'\' limit 1');
           
		   if (mysqli_num_rows($result)===1){ 
		       $result = $result->fetch_assoc();
			   $del_registed_record_query = 'delete from '.$mysql_ini['prefix'].'members_inactive where `key` = \''.$active_key.'\' limit 1';
 		       if ($ret = $db->query("$del_registed_record_query")){
				   $insert_query = 'insert into '.$mysql_ini['prefix'].'members values (null,\'\',\'\',\'\',\''.$result['email'].'\',\''.$result['username'].'\',\''.$result['password'].'\',0,'.$result['reg_time'].',100,0,0,1,0,0,0,0,0)';
			       if ($ret = $db->query("$insert_query")){ 
				       $lastsuccess[] = $language['success_active'];					   
				   }else{
				       $lasterror[] = $language['fail_insert_member'];
				   }
			   }else{
			       $lasterror[] = $language['fail_del_key'];
			   }
		   }else{
			   $lasterror[] =  $language['fail_signup_key'];
		   }
	   }

   }   
   
   if ($lasterror){
	   include($template->getfile('signup.htm'));
   }else{
   	   include($template->getfile('login.htm'));
   }
   exit;


?>