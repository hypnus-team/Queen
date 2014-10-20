<?php
   define('ACCOUNT', TRUE);

   require "./include/common.inc.php";       
   require "$languagedir"."./myaccount.lang.php";

   $actType = intval($_POST['type']);  
   $actAid  = intval($_POST['id']);    
   $actAct  = intval($_POST['act']);   
   $actSid  = $_POST['sid'];   
   $actDiv  = intval($_POST['divId']); 
   $actSrcId    = intval($_POST['srcId']);  
   $actSrcType  = intval($_POST['srcType']);
   $newmid      = $_POST['newmid'];   

   $db = GlobalFunc::connect_db($mysql_ini);		
   if ($db===FALSE){
	   $lasterror[] = $language['fail_db'];
   }
   $total_modules = get_all_module_array($db);

   if (!$lasterror){
	   if ($newmid){ 
           $query = "select dummy,timestamp from ".$mysql_ini['prefix']."shortcuts where sid=".$actSid." limit 1";
		   $result = $db->query($query);
		   if (1 == mysqli_num_rows($result)){
			   $result = $result->fetch_assoc();
               $dummy = intval($result['dummy']);
			   $timestamp = $result['timestamp'];
			   $query = "select dummy from ".$mysql_ini['prefix']."dummy where dummy=".$dummy." limit 1";
			   $result = $db->query($query);
			   if (1 == mysqli_num_rows($result)){
			       if (isset($total_modules['content'][$newmid])){
					   $query = "update ".$mysql_ini['prefix']."shortcuts set module='".$newmid."',timestamp='".$timestamp."' where sid=".$actSid." limit 1";
					   $db->query($query);
					   $v['sid'] = $actSid;
					   $v['module'] = $newmid;
					   include($template->getfile('myshortcuts_mid.htm'));
					   exit;
				   }
			   }			     
		   }
		   exit ("fail");
	   }
	   if ($actType){
		   $post_src_check = false;
		   
           if ((($actSrcId) and ($actSrcType))){		   
			   if (2 == $actSrcType){
				   $query = "select dummy from ".$mysql_ini['prefix']."dummy where dummy=$actSrcId limit 1";
			   }else{
				   $query = "select tid from ".$mysql_ini['prefix']."token where tid=$actSrcId limit 1";
			   }
			   $result = $db->query($query);
			   if (1 == mysqli_num_rows($result)){
				   $post_src_check = true;
			   }		   
		   }

		   $query_dummy = "";
		   $query_token = "";

		   if (3 == $actType){ 
			   $dummy_id = GlobalFunc::get_dummy_from_cid($uid,$actAid,$db,$mysql_ini);
			   if ($dummy_id){
				   $actType = 2;
				   $actAid  = $dummy_id;
			   }	   
		   }

		   if (2 == $actType){ 
			   $query = "select dummy from ".$mysql_ini['prefix']."dummy where dummy=$actAid limit 1";		   
		   }else{
			   $query = "select tid from ".$mysql_ini['prefix']."token where tid=$actAid limit 1";		   
		   }
		   $result = $db->query($query);
		   if (1 == mysqli_num_rows($result)){
			   if (2 == $actType){ 
				   $query_dummy = ' where dummy='.$actAid.' limit 1';
			   }else{
				   $query_token = ' where tid='.$actAid.' limit 1';   
			   }			   
			   if (!shortcuts_manage_do()){
				   $lasterror[] = $language['opt_fail'];
			   }
		   }else{
			   $lasterror[] = $language['no_access'];
		   }	   
	   }
   }
   
   if (!$lasterror){
	   $ret_token = array();
	   $ret_dummy = array();
	   $ret_drone = array();      
	  
	   if ((2 != $actType) and (3 != $actType)){
		   $query = "select tid,token,comment,shortcuts_num from ".$mysql_ini['prefix']."token".$query_token;
		   $result = $db->query($query);
		   $i = mysqli_num_rows($result);
		   for (;$i >0 ;$i--){
			   $r = $result->fetch_assoc();
			   $ret_token[$r['tid']] = $r;
			   if ($r['shortcuts_num']){
				   $query = "select sid,name,module from ".$mysql_ini['prefix']."shortcuts where token=".$r['tid']." order by timestamp DESC"." limit ".$r['shortcuts_num'];
				   $result_sc = $db->query($query);
				   $ii = mysqli_num_rows($result_sc);
				   for (;$ii >0 ;$ii--){
					   $r_sc = $result_sc->fetch_assoc();   
					   $ret_token[$r['tid']]['shortcuts'][] = $r_sc;
				   }
			   }
		   }
       }
	   if ((1 != $actType) and (3 != $actType)){
		   $query = "select dummy,alias,shortcuts_num from ".$mysql_ini['prefix']."dummy".$query_dummy;
		   $result = $db->query($query);
		   $i = mysqli_num_rows($result);
		   for (;$i >0 ;$i--){
			   $r = $result->fetch_assoc();
			   $ret_dummy[$r['dummy']] = $r;
			   if ($r['shortcuts_num']){
				   $query = "select sid,name,module from ".$mysql_ini['prefix']."shortcuts where dummy=".$r['dummy']." order by timestamp DESC"." limit ".$r['shortcuts_num'];
				   $result_sc = $db->query($query);
				   $ii = mysqli_num_rows($result_sc);
				   for (;$ii >0 ;$ii--){
					   $r_sc = $result_sc->fetch_assoc();   
					   $ret_dummy[$r['dummy']]['shortcuts'][] = $r_sc;
				   }
			   }
		   }
	   }

       if ((1 != $actType) and (2 != $actType)){
		   $query = "select cid,name,dummy from ".$mysql_ini['prefix']."online_clients";
		   $result = $db->query($query);
		   $i = mysqli_num_rows($result);
		   for (;$i >0 ;$i--){
			   $r = $result->fetch_assoc();
			   if ($r['dummy']){
			       if (is_array($ret_dummy[$r['dummy']])){
				       if (!$ret_dummy[$r['dummy']]['alias']){
					       $ret_dummy[$r['dummy']]['alias'] = $r['name'];
					   }
				   }
			   }else{
				   $r['alias'] = $r['name'];
				   $ret_drone[$r['cid']] = $r;
				   $ret_drone[$r['cid']]['shortcuts_num'] = 0;
			   }
		   }
	   }
       
	   $ret = array();
	   $index = 1;
	   foreach ($ret_token as $a => $b){
	       $ret[$index] = $b;
		   $c = explode(',',$b['comment']);
           $ret[$index]['alias'] = '';
		   foreach ($c as $d => $e){
		       $ret[$index]['alias'] .= '<span class="label label-info">'.$e.'</span> ';
		   }
           $ret[$index]['type'] = 1;
           $ret[$index]['id']   = $a;
		   $index ++;
	   }
	   foreach ($ret_dummy as $a => $b){
	       $ret[$index] = $b;
           $ret[$index]['type'] = 2;
           $ret[$index]['id']   = $a;
		   $index ++;
	   }
	   foreach ($ret_drone as $a => $b){
	       $ret[$index] = $b;
           $ret[$index]['type'] = 3;
           $ret[$index]['id']   = $a;
		   $index ++;
	   }


	  
	
   }

   include($template->getfile('myshortcuts.htm'));
   exit;

function shortcuts_manage_do(){
	$ret = true;
	
	global $db;
    global $uid;

    global $post_src_check;

	global $actAct;	
	global $actType;
	global $actAid;

	global $actSid;
    global $actSrcId;
	global $actSrcType;

	global $mysql_ini;

    if (1 == $actAct){      
	   if ($post_src_check){
		   $post_success_num = 0;
		   if (2 == $actType){
			   $query = 'insert into '.$mysql_ini['prefix'].'shortcuts values (NULL,'.$actAid.',0,?,?,?,NULL)';
		   }else{
			   $query = 'insert into '.$mysql_ini['prefix'].'shortcuts values (NULL,0,'.$actAid.',?,?,?,NULL)';
		   }
		   $stmt = $db->prepare($query);
		   if ($stmt){
			   foreach ($actSid as $a => $b){
				   $b = intval($b);
				   if (2 == $actSrcType){
					   $query = "select * from ".$mysql_ini['prefix']."shortcuts where sid=$b and dummy=$actSrcId limit 1";
				   }else{
					   $query = "select * from ".$mysql_ini['prefix']."shortcuts where sid=$b and token=$actSrcId limit 1";
				   }
				   $result = $db->query($query);
				   if (1 == mysqli_num_rows($result)){
					   $result = $result->fetch_assoc();
					   $stmt->bind_param('sss',$result['name'],$result['module'],$result['data']);
					   if ($stmt->execute()){
						   $post_success_num ++;
					   }
				   }							   
			   }	
			   $stmt->close();
		   }
		   if ($post_success_num){
			   if (2 == $actType){
				   $query = "update ".$mysql_ini['prefix']."dummy set shortcuts_num=shortcuts_num+$post_success_num where dummy=$actAid limit 1";
			   }else{
			       $query = "update ".$mysql_ini['prefix']."token set shortcuts_num=shortcuts_num+$post_success_num where tid=$actAid limit 1";
			   }
			   $db->query($query);
		   }
	   }			            	   
   }elseif (2 == $actAct){ 
	   if (2 == $actType){
		   $query = "delete from ".$mysql_ini['prefix']."shortcuts where sid=? and dummy=$actAid limit 1";
	   }else{
		   $query = "delete from ".$mysql_ini['prefix']."shortcuts where sid=? and token=$actAid limit 1";
	   }
	   $stmt = $db->prepare($query);
	   $m = 0;
	   if ($stmt){
		   foreach ($actSid as $a => $b){
			   $b = intval($b);
			   $stmt->bind_param("i",$b);
			   if ($stmt->execute()){
				   $m ++;
			   }
		   }				   
		   $stmt->close();
	   }
	   if ($m){
		   if (2 == $actType){
			   $query = "select dummy from ".$mysql_ini['prefix']."shortcuts where dummy=$actAid";
		   }else{
		       $query = "select token from ".$mysql_ini['prefix']."shortcuts where token=$actAid";
		   }
		   $result = $db->query($query);
		   $shortcuts_num = mysqli_num_rows($result);
		   if (2 == $actType){
			   $query = "update ".$mysql_ini['prefix']."dummy set shortcuts_num=$shortcuts_num where dummy=$actAid limit 1";
		   }else{
		       $query = "update ".$mysql_ini['prefix']."token set shortcuts_num=$shortcuts_num where tid=$actAid limit 1";
		   }
		   $db->query($query);
	   }
   }elseif (3 == $actAct){ 
	   foreach ($actSid as $a => $b){
		   $b = intval($b);
		   if (2 == $actType){
			   $query = "update ".$mysql_ini['prefix']."shortcuts set timestamp=NULL where sid=$b and dummy=$actAid limit 1";
		   }else{
		   	   $query = "update ".$mysql_ini['prefix']."shortcuts set timestamp=NULL where sid=$b and token=$actAid limit 1";
		   }
		   $db->query($query);
	   }				   
   }
    return $ret;
}



function get_all_module_array($db){ 
	                                
                                    
								    
								    
								    

    global $mysql_ini;
	global $repo_path;
	global $language_choosed;

	$ret = array();

    $query = "select module,root,version,latest_version from ".$mysql_ini['prefix']."modules";

	$result = $db->query($query);
	$i = mysqli_num_rows($result);
	for (;$i >0 ;$i--){
		$tmp = $result->fetch_assoc();
	    $root = $tmp['root'];
		$mid  = $tmp['module'];
		$ver  = $tmp['version'];

        $ret['index'][$root][$ver] = $mid; 
		$ret['latest_version'][$root] = $tmp['latest_version'];
        $ret['content'][$mid]['root']    = $root;
        $ret['content'][$mid]['version'] = $ver;
		
		$a = GlobalFunc::get_mod_name($mid,$language_choosed);
		
		$ret['content'][$mid]['name'] = $a['name'];
	}
	return $ret;
}

?>