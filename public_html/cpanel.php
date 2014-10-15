<?php

   define('CPANEL', TRUE);
     
   require "./include/common.inc.php";       
   require "$languagedir"."./cpanel.lang.php";
  
   $isMulti = intval($_POST['multi']);

if ('group_panel' === $_POST['act']){
	unset ($_POST['act']);
	$selected_clients = $_POST;
	$num = count($_POST);
	if ($num){
		include($template->getfile('group_panel.htm'));
	}else{
	}   
    exit;
}

if (('clients_list' === $_POST['act']) or ('client_panel' === $_POST['act']) or ('group_list' === $_POST['act'])){
   $db = GlobalFunc::connect_db($mysql_ini);
   if (!$db){
	   $lasterror[] = $language['fail_db'];
   }
   if (!$lasterror){
	   if ('group_list' === $_POST['act']){
		   $group_list[23]['name']   = '我的群,啦啦啦';
		   $group_list[23]['online'] = '12/100';
           include($template->getfile('group_list.htm'));
           exit;
	   }elseif ('client_panel' === $_POST['act']){	
		   $clientId   = intval($_POST['cid']);
           $new_alias  = $_POST['new_alias'];
		   if ($clientId){
			   if ($new_alias){
				   $dummy_id = GlobalFunc::get_dummy_from_cid($uid,$clientId,$db,$mysql_ini);
				   if ($dummy_id){
				       $query = 'update '.$mysql_ini['prefix'].'dummy set alias=? where dummy='.$dummy_id.' limit 1';
                       $stmt = $db->prepare($query);			
					   $stmt->bind_param("s",$new_alias);
					   $stmt->execute();
					   if ($stmt->affected_rows){
						   $lastsuccess[] = $language['success_set_alias'];
					   }else{
					       $lasterror[] = $language['fail_set_alias'];
					   }
					   $stmt->close();
				   }else{
				       $lasterror[] = $language['fail_set_alias'];
				   }
			   }
			   
			   $query = 'select a.ip,a.name,a.token,a.mac_num,a.online_time,a.dummy,b.comment,c.alias from '.$mysql_ini['prefix'].'token as b,'.$mysql_ini['prefix'].'online_clients as a left join '.$mysql_ini['prefix'].'dummy as c on c.dummy = a.dummy where a.cid='.$clientId.' and a.status=1 and b.tid = a.token limit 1';

			   

			   $result = $db->query($query);
			   if (1 === mysqli_num_rows($result)){
				   $current_client = $result->fetch_assoc();
				   
				   if ($current_client['alias']){
					   $current_client['alias'] = htmlspecialchars(trim($current_client['alias']));
				   }
				   $c_time = time() - $current_client['online_time'];
				   $sec = $c_time%60;
				   $c_time -= $sec;
				   $min = $c_time%(60*60);
				   $c_time -= $min;
				   $min = $min/60;
				   $hour = $c_time%(60*60*24);
				   $c_time -= $hour;
				   $hour = $hour/(60*60);
				   $day = $c_time/(60*60*24);   	
				   $current_client['online_time'] = $day.$language['day'].$hour.$language['hour'].$min.$language['min'].$sec.$language['sec'];
					 $query = 'select mac from '.$mysql_ini['prefix'].'online_mac where cid = '.$clientId.' limit '.$current_client['mac_num'];
					 $mac_result = $db->query($query);
					 $i = mysqli_num_rows($mac_result);
					 for (;$i > 0 ;$i --){	
						 $tmp = $mac_result->fetch_assoc();
						 $tmp = str_split($tmp['mac'],2);
						 $current_client['mac'][] = implode($tmp,'-');
					 }			   
				   unset ($current_client['token']);
				   $tmp = htmlspecialchars(trim($current_client['comment'])); 
				   $current_client['token'] = explode(',',$tmp);
				   
			   }else{
				   $lasterror[] = $language['client_off_line'];
			   }
		   }else{
		       $lasterror[] = $language['client_off_line'];
		   }

	   }else{ 
		   $c_time = time();
		   $token_shortcuts = array();
		   $query = 'select comment,tid,shortcuts_num from '.$mysql_ini['prefix'].'token';
		   $result = $db->query($query);
		   $i = mysqli_num_rows($result);
		   for (;$i > 0 ;$i --){	
			   $tmp = $result->fetch_assoc();
			   $tmp['comment'] = htmlspecialchars(trim($tmp['comment'])); 
			   $token[$tmp['tid']] = explode(',',$tmp['comment']);
			   $token_shortcuts[$tmp['tid']] = $tmp['shortcuts_num'];
		   }

           $shortcuts_array = array();
		   $token_shortcuts_array = false;

           $query = 'select a.cid,a.name,a.token,a.mid,a.dummy,a.lastliving,a.mac_num,a.mod_num,b.alias,b.shortcuts_num from '.$mysql_ini['prefix'].'online_clients as a Left JOIN '.$mysql_ini['prefix'].'dummy as b ON b.dummy = a.dummy where a.status=1 order by a.online_time DESC';

		   $result = $db->query($query);
		   $i = mysqli_num_rows($result);
		   for (;$i > 0 ;$i --){
			   $tmp = $result->fetch_assoc();	
			   
			   if ($tmp['lastliving'] < ($c_time - (2 * $srv_block_ini['db_living']))){ 
			       $query = 'delete from '.$mysql_ini['prefix'].'online_clients where cid = '.$tmp['cid'].' limit 1';
				   $db->query($query);
				   $query = 'delete from '.$mysql_ini['prefix'].'online_mac where cid = '.$tmp['cid'].' limit '.$tmp['mac_num'];
				   $db->query($query);
				   $query = 'delete from '.$mysql_ini['prefix'].'online_module where cid = '.$tmp['cid'].' limit '.$tmp['mod_num'];
				   $db->query($query);
			   }else{
				   if (strlen($tmp['alias'])){
					   $tmp['machine_name'] = htmlspecialchars(trim($tmp['alias']));
				   }else{
					   $tmp['machine_name'] = htmlspecialchars(trim($tmp['name']));
				   }
				   if (!strlen($tmp['machine_name'])){
					   $tmp['machine_name'] = 'Nil Name';
				   }

				   if ($token_shortcuts[$tmp['token']] > 0){
				       $query = 'select name,sid,module from '.$mysql_ini['prefix'].'shortcuts where token = '.$tmp['token'].' order by timestamp DESC'.' limit '.$token_shortcuts[$tmp['token']];
					   $r = $db->query($query);
                       $b = mysqli_num_rows($r);
					   for (;$b > 0 ;$b --){
						   $a = $r->fetch_assoc();
						   $a['name'] =  htmlspecialchars($a['name']);
						   $token_shortcuts_array[$tmp['mid']][] = $a;
					   }
				   }

				   if ($token[$tmp['token']]){
					   $tmp['token'] = $token[$tmp['token']];
				   }else{
					   unset ($tmp['token']);
				   }			   
				   $list_machine[$i] = $tmp;

				   if ($tmp['shortcuts_num'] > 0){
					   if (!isset($shortcuts_array[$tmp['mid']])){
						   $query = 'select name,sid,module from '.$mysql_ini['prefix'].'shortcuts where dummy = '.$tmp['dummy'].' order by timestamp DESC'.' limit '.$tmp['shortcuts_num'];
						  
						   $r = $db->query($query);
						   $b = mysqli_num_rows($r);
						   for (;$b > 0 ;$b --){
							   $a = $r->fetch_assoc();
							   $a['name'] =  htmlspecialchars($a['name']);
							   $shortcuts_array[$tmp['mid']][] = $a;
						   }
					   }
				   }
			   }
		   }
	   }
   }

   if ('clients_list' === $_POST['act']){
       include($template->getfile('clients_list.htm'));
   }elseif ('client_panel' === $_POST['act']){
       include($template->getfile('client_panel.htm'));
   }
}else{
   if (true === $invalid_session){
       Header("Location:./login.php");
   }

   if ($_GET['act'] == 'logout'){
	   if (!$lasterror){
		   session_start();
		   session_unset();
		   session_destroy();
		   $lastsuccess[] = $language['logout_ok'];
		   $disToolkit = true;
	   }
   }

   include($template->getfile('cpanel.htm'));
}
exit;
?>