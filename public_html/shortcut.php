<?php
   define('CPANEL', TRUE);
     
   require "./include/common.inc.php";       
   require "$languagedir"."./shortcut.lang.php";
   

   $shortcut_name = $_GET['name'];
   $cid   = intval($_GET['cid']);
   $mid   = $_GET['mid'];   
   
   $sid  = false;
   $data = '';

   $tmp = @file_get_contents('php://input');
   $tmp = explode(':',$tmp);
   if (('sid' === $tmp[0]) and (intval($tmp[1]))){
       $sid = intval($tmp[1]);
   }
   if (false === $sid){
	   if (!empty($_POST)){
		   $data = json_encode($_POST);
	   }
   }

   if (!preg_match('/^[A-F0-9]{32}$/',$mid)){
	   $lasterror[] = $language['illegal_mid'];
   }elseif (!$shortcut_name){
	   $lasterror[] = $language['illegal_shortcut_name'];
   }else{
	   $db = GlobalFunc::connect_db($mysql_ini);
	   if (!$db){
		   $lasterror[] = $language['fail_db'];
	   }else{
           if (false !== $sid){
		       $query = 'select a.data from '.$mysql_ini['prefix'].'shortcuts as a left join '.$mysql_ini['prefix'].'dummy as b on b.dummy = a.dummy where a.sid='.$sid.' limit 1';
			   $result = $db->query($query);
		       if (1 == mysqli_num_rows($result)){
			       $result = $result->fetch_assoc();
				   $data = $result['data'];
               }else{
			       $lasterror[] = $language['invalid_sid'];
		       }
		   }
		   
		   if (!$lasterror){
			   $dummy = GlobalFunc::get_dummy_from_cid($uid,$cid,$db,$mysql_ini);
			   if (false === $dummy){
				   $lasterror[] = $language['client_off_line'];			   
			   }	
		   }

		   if (!$lasterror){				      
			   $query = 'insert into '.$mysql_ini['prefix'].'shortcuts values (NULL,?,0,?,?,?,NULL)';
			   $stmt = $db->prepare($query);		
			   $stmt->bind_param('isss',$dummy,$shortcut_name,$mid,$data);
			   if ($stmt->execute()){
				   $query = 'update '.$mysql_ini['prefix'].'dummy set shortcuts_num = shortcuts_num + 1 where dummy = '.$dummy.' limit 1';
				   $db->query($query);				
			   }else{
				   $lasterror[] = $language['insert_db_fail'];
			   }				
		   }
	   }	   
	   if (!$lasterror){
		   $lastsuccess[] = $language['save_complete'];
	   }
   }   

   include($template->getfile('header_warning.htm'));
   exit;
?>