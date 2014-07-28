<?php

if(!defined('Hypnus')) {
	exit('Access Denied');
}


function set_member_session($result,$cookiepre,$username){
    session_write_close();
	session_name ("$cookiepre".session_name());
	session_start();

	foreach ($result as $key => $value){
		$_SESSION["$cookiepre".$key] = $value;
	}				   

   $_SESSION["$cookiepre".'alias_show'] = clip_str_width(htmlspecialchars($result['alias']));
   
   $_SESSION["$cookiepre"."time"] = time();
   $_SESSION["$cookiepre"."REMOTE_ADDR"] = $_SERVER["REMOTE_ADDR"];
   $_SESSION["$cookiepre".'login_username'] = $username;
   
   $_SESSION["$cookiepre".'ssl']           = intval($result['sec_ssl']);
   $_SESSION["$cookiepre".'dynamic_proxy'] = intval($result['sec_dynamic_proxy']);
   $_SESSION["$cookiepre".'vaild_logon']   = intval($result['sec_vaild_logon']);
   $_SESSION["$cookiepre".'auto_logout_without_opt'] = intval($result['sec_logout_without_opt']);

   
   if ($result['sec_vaild_logon'] == 4){
	   setcookie(session_name(), session_id(), time()+9999999, "/");    
   }elseif ($result['sec_vaild_logon']== 3){
	   setcookie(session_name(), session_id(), time()+30*24*60*60, "/");
   }elseif ($result['sec_vaild_logon'] == 2){
	   setcookie(session_name(), session_id(), time()+24*60*60, "/");  
   }elseif ($result['sec_vaild_logon'] == 1){
	   setcookie(session_name(), session_id(), time()+60*60, "/");     
   }else {
	   setcookie(session_name(), session_id(), 0, "/");                
   }
}



function get_dummy_from_cid($uid,$cid,$db,$mysql_ini){
    $dummy = false;
	$query = 'select dummy,mid from '.$mysql_ini['prefix'].'online_clients where cid='.$cid.' limit 1';
	$result = $db->query($query);
	if (1 === mysqli_num_rows($result)){
		$tmp = $result->fetch_assoc();
		$dummy = $tmp['dummy'];
		if (0 == $dummy){ 
            $query = 'insert into '.$mysql_ini['prefix'].'dummy values (NULL,1,\'\',0)';
			$db->query($query);
			$dummy = $db->insert_id;
			if ($dummy){
				$query = 'insert into '.$mysql_ini['prefix'].'dummy_clients values (\''.$tmp['mid'].'\','.$dummy.')';
				$db->query($query);
				$query = 'update '.$mysql_ini['prefix'].'online_clients set dummy = '.$dummy.' where cid='.$cid.' limit 1';
				$db->query($query);
			}
		}
	}	 
	return $dummy;
}

function is_sys_module_id($mid){
    if ('0000000000000000000000000000' === substr($mid,4)){
	    return true;
	}
	return false;
}

function get_stream_path($tid,$create=true){
    global $stream_path_level;
	global $stream_path;
	$ret = $stream_path;
	if ($stream_path_level > 0){
		$a = substr($tid,0,1);
		$ret .= $a.'/';
		if ((false === is_dir($ret)) and (true === $create) and (false === mkdir($ret, 0755))){
		    $ret = false;
		}else if ($stream_path_level > 1){
			$a = substr($tid,1,2);
			$ret .= $a.'/';
			if ((false === is_dir($ret)) and (true === $create) and (false === mkdir($ret, 0755))){
		        $ret = false;
			}elseif ($stream_path_level > 2){
				$a = substr($tid,3,3);
				$ret .= $a.'/';
				if ((false === is_dir($ret)) and (true === $create) and (false === mkdir($ret, 0755))){
					$ret = false;
				}
			}
		}	
	}
	return $ret;
}

function checksession($jump_to_logout,&$my_session){
   session_start();
   $my_session = $_SESSION;
  if ((isset($my_session["$cookiepre".'uid']))&&(isset($my_session["$cookiepre".'time']))){
		   return $my_session["$cookiepre".'uid'];
   }
   session_destroy();
   return false;

}

function strexists($haystack, $needle) {
		return !(strpos($haystack, $needle) === FALSE);
}

function connect_db($dbase,$describe=""){
	@$db = new mysqli($dbase['address'],$dbase['username'],$dbase['password'],$dbase['basename']);
	if (mysqli_connect_errno()){	
		errorlog(mysqli_connect_error());
		return FALSE;
	}else{
		@$db->query("SET character_set_connection=utf8, character_set_results=utf8, character_set_client=binary");			
		return $db;
	}
}



function errorlog($str,$type=1){
	global $log_file_path;
	if ($log_file_path){
		$log_file = false;
		if (2 == $type){
			$log_file = $log_file_path.'hyp_notice.log';
		}else{ 
			$log_file = $log_file_path.'hyp_error.log';
		}
		if ($log_file){
			if ($fp=@fopen($log_file,'a+')){
				if (@flock($fp,LOCK_NB | LOCK_EX)){
					$str = date('Y-m-d H:i:s')." { $str } "."\r\n";
					fwrite($fp, $str);
					flock($fp,LOCK_UN);
				}
				fclose($fp);			
			}	
		}
	}
 }

function random($length) {
	$hash = '';
	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
	$max = strlen($chars) - 1;	
	for($i = 0; $i < $length; $i++) {
		$hash .= $chars[mt_rand(0, $max)];
	}
	return $hash;
}


function clip_str_width($src,$reserve=10){
	$ret = '';
	$len = mb_strlen($src,"UTF-8");
	$i = 0;
	for (; $len > 0; $len --) {
		if ($reserve > 0){
			$tmp = mb_substr($src, $i, 1, "UTF-8");
			if (strlen($tmp) > 1){
				$reserve --;
			}
			$reserve --;
			$ret .= $tmp;
		}else{
			if ($len > 2){
				$ret .= '..';
				$ret .= mb_substr($src, -1, 1, "UTF-8");
			}else{
				$ret = $src;
			}
			break;
		}			
		$i ++;
	}
	return $ret;
}


function get_mod_name($mid,$language){
    ob_start();
	$ret = false;
	if (file_exists('./repo/modules/'.$mid.'/mod.name.php')){
		require './repo/modules/'.$mid.'/mod.name.php';
		if (is_array($mod_name[$language])){
		    $ret = $mod_name[$language];
		}else{
			$ret = current($mod_name);
		}
	}
	if (false === $ret){
	    $ret['name']        = '???';
		$ret['author']      = '???';
		$ret['description'] = '???';
	}
    ob_end_clean();
    return $ret;
}

?>