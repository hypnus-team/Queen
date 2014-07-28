<?php

   define('C_AUTHENTICATION_PAGE',TRUE); 
    
   require "./include/common.inc.php";       
   require "$languagedir"."./signup.lang.php";
  
   session_start();
   
   $fail_login_time = 0;
   $currenTime = time();
   

   
   $userIP = $_SERVER["REMOTE_ADDR"]; 

   $db = connect_db($mysql_ini);			   
   if ($db===FALSE){	
	   $lasterror[] =  $language['fail_db'];
   }else{		
	   $expired_time = $currenTime - 60 * 60;
	   $query = 'delete from '.$mysql_ini['prefix'].'spam_members where lastact < '.$expired_time;
       $db->query("$query");

       $query = 'select * from '.$mysql_ini['prefix'].'spam_members where ip = \''.$userIP.'\' limit 1';
	   $result = $db->query("$query");
	   if (mysqli_num_rows($result) === 1){ 
	       $result = $result->fetch_assoc();
		   $fail_login_time = $result['times'];
		   if ($fail_login_time >= 3){      
			   $is_need_seccode = true;
		   }
	   }
   }   
   
   
   if (file_exists('./install.php')){
       $lastwarning[] = $language['install_exists'];
   }
 
   

if ($_POST['submit']){

  if(!$lasterror){
   
   $username = $_POST['user'];
   $password = $_POST['pass'];
   
   if ($is_need_seccode){
      $lasterror[] = $language['fail_spam'];	 
   }   

   if (!$lasterror){
	   unset ($_SESSION["$cookiepre"."seccode"]);

	   if ((strlen($username)>19) || (strlen($username)<2) || (strlen($password)>19) || (strlen($password)<6)){
		   $lasterror[] = $language['login_fail_password'];
	   }
			 
	   if (!$lasterror){
		   $md5_username = md5($hash_salt."$username");
		   $lasterror[] =  $language['login_fail_password']; 
		   $query = 'select * from '.$mysql_ini['prefix'].'members where username = \''.$md5_username.'\' limit 1';;
		   $result = $db->query("$query");
		   if (mysqli_num_rows($result) === 1){ 
			   $result = $result->fetch_assoc();
			   $md5_password = md5("$username"."$hash_salt"."$password".$result['reg_time']);
			   if ($md5_password === $result['password']){ 
				   
				   $query = 'update '.$mysql_ini['prefix'].'members set lastact_time = null where uid = \''.$result['uid'].'\'limit 1';
				   $db->query("$query");                           
				   unset ($lasterror);
                   $result['uid'] = 999; 
				   set_member_session($result,$cookiepre,$username);
				   
  			       
				   
				   
				   include "./include/gc.inc.php"; 

				   Header("Location:"."./cpanel.php");
				   exit;

			   }
		   }
	   }

	   if ($lasterror){ 
		   $fail_login_time ++;
		   if ($fail_login_time == 1 ){ 
			   $query = 'insert into '.$mysql_ini['prefix'].'spam_members values (\''.$userIP.'\',1,'.$currenTime.')';
		   }else{
			   if ($fail_login_time >= 3){
				   $fail_login_time = 3;		
				   $is_need_seccode = true;
			   }
			   $query = 'update '.$mysql_ini['prefix'].'spam_members set lastact = '.$currenTime.',times = '.$fail_login_time.' where ip = \''.$userIP.'\' limit 1';
		   }					 
		   $db->query("$query"); 
	   }
   }
  }
}

include($template->getfile('login.htm'));
exit;
?>