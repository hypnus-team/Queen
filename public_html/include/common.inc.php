<?php   

ini_set('display_errors',0);

$lasterror   = false;
$lastsuccess = false;
$lastwarning = false;

   define('Hypnus', TRUE);
   define('HYPNUS_ROOT', substr(dirname(__FILE__), 0, -7));  
   define('TPLDIR',HYPNUS_ROOT."./templates/");   

   require "./include/config.inc.php";
   require './include/global.func.php';

   $language_choosed = 'chn';
   $template_choosed = 'bootstrap';

   $tpldir = TPLDIR.'./'."$template_choosed".'/html/';
   $cachedir = './cache/'."$template_choosed".'/';
   $tplpath = './templates/'."$template_choosed";

   $languagedir = TPLDIR.'./language/'."$language_choosed".'/';

   require "$languagedir"."./common.lang.php";

   session_name ("$cookiepre".session_name());
   session_start();

   if ($ssl_valid){
	   if ((C_AUTHENTICATION_PAGE === TRUE) or (1 == $_SESSION["$cookiepre".'ssl'])){
		   if (!isset($_SERVER["HTTPS"])){
			   $Header = "Location:https://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
			   header("$Header");
			   exit();
		   }
	   }else{
	       if ((isset($_SESSION["$cookiepre".'ssl'])) and (isset($_SERVER["HTTPS"]))){
			   $Header = "Location:http://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
			   header("$Header");
			   exit();			   
		   }
	   }
   }

   

   if (C_AUTHENTICATION_PAGE !== TRUE){
       if (!isset($_SESSION["$cookiepre".'uid'])){
		   $invalid_session = true;
		   $lasterror[] = $language['off_line'];
	   }else{
		   if (isset($_SESSION["$cookiepre".'usrname'])){
			   $inline_username = $_SESSION["$cookiepre".'usrname'];
		   }else{
			   $inline_username = $language['set_alias_plz'];
		   }	   
           
		   $my = $_SESSION;
		   $uid = $my["$cookiepre".'uid'];
		   $alias_show = $my["$cookiepre".'alias_show'];
		   $login_username = $my["$cookiepre".'login_username'];
		   $dynamic_proxy  = $my["$cookiepre".'dynamic_proxy'];		   
		   
		   
		 
		   if ($kicked){ 
			   session_unset();
			   session_destroy();
			   $lasterror[] = $language['been_kicked'];
		   }

		   if ($dynamic_proxy == 0){ 
			   if ($_SERVER['REMOTE_ADDR'] != $my["$cookiepre".'REMOTE_ADDR']){
			       session_unset();
				   session_destroy();
				   $lasterror[] = $language['been_kicked_02'];		   
			   }
		   }   
		   
		   if ($my["$cookiepre".'auto_logout_without_opt'] == 2){     
														   
		   }else{
			   if ($my["$cookiepre".'auto_logout_without_opt'] == 1){ 
				   $session_expire = time() - 60 * 60;
			   }else{                                            
				   $session_expire = time() - 15 * 60;
			   }
			   
			   if ($my["$cookiepre".'time'] < $session_expire){ 
			       session_unset();
				   session_destroy();
				   $lasterror[] = $language['been_kicked_03'];
			   }
		   }
	   }
	   if (!$lasterror){
		   $_SESSION["$cookiepre".'time'] = time();
	   }
   }

   session_write_close();

   require "./include/template.class.php";   

?>
