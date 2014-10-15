<?php
   define('ACCOUNT', TRUE);
    
   require "./include/common.inc.php";       
   require "$languagedir"."./myaccount.lang.php";
  
  
 

   session_start();

	$account_act = intval($_GET['a']);
	
	$db = GlobalFunc::connect_db($mysql_ini);		
	if ($db===FALSE){
		$lasterror[] =  $language['fail_db'];
	}else{
	   if ($account_act == 2){ 
		   $baseDir = 'http://'.$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		   $p = strrpos($baseDir,"/");
           $baseDir = substr($baseDir,0,$p);
           $query = 'select token,comment from '.$mysql_ini['prefix']."token";
           $result = $db->query($query);
		   $i = mysqli_num_rows($result);
		   $my_token = false;
		   for (;$i>0;$i--){
			   $r = $result->fetch_assoc();
			   $my_token[$r['token']] = $r['comment'];
		   }
		   if (empty($my_token)){
		       $lasterror[] = $language['gen_no_token'];
		   }
	   }elseif ($account_act == 1){ 
		   if ((strlen($_POST['oldpsw']))&&(strlen($_POST['newpsw']))&&(strlen($_POST['newpsw_02']))){
			   if ($_POST['newpsw']!== $_POST['newpsw_02']){
				   $lasterror[] = $language['different_newpsw'];
			   }
			   if (!$lasterror){
				   if ($_POST['oldpsw'] == $_POST['newpsw']){
					   $lasterror[] = $language['same_two_psw'];
				   }
			   }

			   if (!$lasterror){
				   $query = "select reg_time,password from ".$mysql_ini['prefix']."members limit 1";
				   $result = $db->query("$query");
				   if (mysqli_num_rows($result) === 1){ 
					   $result = $result->fetch_assoc();			   
					   $reg_time = $result['reg_time'];
					   $md5_psw  = $result['password'];

					   $old_md5_psw = md5($login_username."hypnus".$_POST['oldpsw']."$reg_time");
					   if ($md5_psw === $old_md5_psw){
						   $new_md5_psw = md5($login_username."hypnus".$_POST['newpsw']."$reg_time");
						   $query = "update ".$mysql_ini['prefix']."members set password = \"$new_md5_psw\" limit 1"; 
						   if ($db->query("$query")){
							   $lastsuccess[] = $language['changpsw_success'];
						   }else{
						       $lasterror[] = $language['changpsw_fail'];
						   }
					   }else{
					       $lasterror[] = $language['invalid_oldpsw'];			   
					   }			
				   }
			   }
		   }
	   }elseif ($account_act == 6){ 
		   $setup_type = $_POST['setup_type'];
		   if ($setup_type == 'charactor'){  
			   
		   }elseif ($setup_type == 'secret'){  
			   $secret_dynamic_proxy    = 0;
			   if (intval($_POST['secret_dynamic_proxy'])){
				   $secret_dynamic_proxy    = 1;
			   }
			   $secret_ssl_setup_opt           = intval($_POST['secret_ssl_opt']);
			   $secret_vaild_logon             = intval($_POST['secret_vaild_logon']);
			   $secret_auto_logout_without_opt = intval($_POST['secret_auto_logout_without_opt']);
			   if (!isset($language['secret_ssl_setup_opt_type'][$secret_ssl_setup_opt])){
				   $secret_ssl_setup_opt = 0;                                 
			   }
			   if (!isset($language['secret_vaild_logon_type'][$secret_vaild_logon])){
				   $secret_vaild_logon = 0;                                 
			   }
			   if (!isset($language['secret_auto_logout_without_opt_type'][$secret_auto_logout_without_opt])){
				   $secret_auto_logout_without_opt = 0;                                 
			   }
			   
			   if ($_SESSION["$cookiepre"."ssl"] != $secret_ssl_setup_opt){				  
				  $need_2_refresh = true;
			   }
               
			   if (($_SESSION["$cookiepre"."ssl"] != $secret_ssl_setup_opt) or ($_SESSION["$cookiepre"."dynamic_proxy"] != $secret_dynamic_proxy) or ($_SESSION["vaild_logon"] != $secret_vaild_logon) or ($_SESSION["auto_logout_without_opt"] != $secret_auto_logout_without_opt)){
				   $query = "update ".$mysql_ini['prefix']."members set sec_dynamic_proxy = $secret_dynamic_proxy, sec_vaild_logon = $secret_vaild_logon, sec_logout_without_opt = $secret_auto_logout_without_opt,sec_ssl = $secret_ssl_setup_opt limit 1";			
				   $db->query($query);
				   if (1 == $db->affected_rows){
					   $_SESSION["$cookiepre"."dynamic_proxy"]           = $secret_dynamic_proxy;
					   $_SESSION["$cookiepre"."vaild_logon"]             = $secret_vaild_logon;
					   $_SESSION["$cookiepre"."auto_logout_without_opt"] = $secret_auto_logout_without_opt;	
					   $_SESSION["$cookiepre"."ssl"]                     = $secret_ssl_setup_opt;
					   $lastsuccess[] = $language['set_option_success'];
				   }else{
				       $lasterror[] = $language['set_option_fail'];
				   }			
			   }
		   }

		   if (($need_2_refresh) and (!$lasterror)){
			   header("Location: ./myaccount.php?a=6");
			   exit();
		   }
		   
		   
		   
		   
		   
		   
		   
		   
		   
		
		
		  
		   $query = "select alias from ".$mysql_ini['prefix']."members_character limit 1";
		   $result = $db->query("$query");
		   if (1 === mysqli_num_rows($result)){
			   $result = $result->fetch_assoc();		   
			   $account_alias = $result['alias'];	  
			   if ($result['empty_alias']){
				   $account_alias = "";
			   }
			   $charactor_reject_mail = $result['reject_mail'];
		   }	   
		   $charactor_language = $_SESSION["$cookiepre"."language"];
		   $secret_dynamic_proxy = $_SESSION["$cookiepre"."dynamic_proxy"];
		   $secret_vaild_logon = $_SESSION["$cookiepre"."vaild_logon"];
		   $secret_auto_logout_without_opt = $_SESSION["$cookiepre"."auto_logout_without_opt"];
		   $secret_ssl_setup_opt = $_SESSION["$cookiepre"."ssl"];
		   

	   }elseif ($account_act == 7){ 
	       $responseonly = false;
		   $max_token    = 100;
		   $tid     = $_POST['tid'];
		   $comment = $_POST['comment'];
           $c_token = array();
		   
		   if ($tid){
			   $responseonly = true;
			   $query = "update ".$mysql_ini['prefix']."token set comment = ? where token='$tid' limit 1";
			   $stmt = $db->prepare($query);			
			   $stmt->bind_param("s",$comment);
			   $stmt->execute();
			   $stmt->close();
		       
			   $query = "select token,comment from ".$mysql_ini['prefix']."token where token='$tid' limit 1";
		   }else{
			   $new_comment = $_POST['new_comment'];
			   $query = "select token,comment from ".$mysql_ini['prefix']."token";
		   }
		   $result = $db->query("$query");
		   $i = mysqli_num_rows($result);
		   $max_token -= $i;
		   for (;$i >0 ;$i --){
			   $r = $result->fetch_assoc();
			   $r['comment'] = htmlspecialchars($r['comment']);
			   $r['remark'] = explode(',',$r['comment']);
			   $c_token[] = $r;
		   }
		   if ($new_comment){
		       if ($max_token > 0){
			       $new_token = GlobalFunc::random(32);
				   $query = "insert into ".$mysql_ini['prefix']."token values (NULL,?,?,0)";
				   $stmt = $db->prepare($query);			
				   $stmt->bind_param("ss",$new_token,$new_comment);
				   if (!$stmt->execute()){
				       $lasterror[] = $language['create_token_fail'];
				   }else{
					   $max_token --;
					   $r['token']   = $new_token;
					   $r['comment'] = htmlspecialchars($new_comment);
			           $r['remark'] = explode(',',$r['comment']);
			           $c_token[] = $r;					   
				   }
				   $stmt->close();
			   }else{
			       $lasterror[] = $language['no_more_token'];
			   }    
		   }
	   }
	}
	include($template->getfile('myaccount.htm'));
	exit;
?>