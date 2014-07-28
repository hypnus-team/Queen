<?php


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


function connect_db($dbase,$describe=""){
	@$db = new mysqli($dbase['address'],$dbase['username'],$dbase['password'],$dbase['basename']);
	if (mysqli_connect_errno()){	
		errorlog(mysqli_connect_error());
		return FALSE;
	}else{			
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



function clip_str_width($src,$reserve){
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
?>