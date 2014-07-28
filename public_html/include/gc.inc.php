<?php

set_time_limit(0);
ignore_user_abort(true); 

$expire_time = time() - $gc_frequency;

$lock_file_timestamp = filemtime($gc_lock_file);

$do_gc = false;

if (($lock_file_timestamp) and ($lock_file_timestamp > $expire_time)){
    
}else{
	if ($fp=@fopen($gc_lock_file,'w')){
		if (@flock($fp,LOCK_NB | LOCK_EX)){
			$do_gc = true;
			fwrite($fp, time());
			flock($fp,LOCK_UN);
		}
		fclose($fp);
	}
}

if (true === $do_gc){

	$query = 'delete a,b from '.$mysql_ini['prefix'].'online_task as a left join '.$mysql_ini['prefix'].'online_task_lock as b on a.tid=b.tid where (a.chunk=0 and a.status=2) or (a.chunk!=0 and a.status!=2)'." and (UNIX_TIMESTAMP(a.lastact) < UNIX_TIMESTAMP() - $gc_frequency)";

	$db->query($query);

	$query = 'delete a from '.$mysql_ini['prefix'].'online_task_lock as a where not exists (select b.tid from '.$mysql_ini['prefix'].'online_task as b where b.tid = a.tid)';

	$db->query($query);

	$query = 'delete a from '.$mysql_ini['prefix'].'data_request as a where not exists (select b.rid from '.$mysql_ini['prefix'].'online_task as b where b.rid = a.rid)';

	$db->query($query);

	$query = 'delete a from '.$mysql_ini['prefix'].'data_response as a where not exists (select b.tid from '.$mysql_ini['prefix'].'online_task as b where b.tid = a.tid)';

	$db->query($query);

	gc_streams($stream_path,$expire_time);
}


function gc_streams($path = '.',$expire_time) {
	$current_dir = opendir($path);    
	while(($file = readdir($current_dir)) !== false) {    
		$sub_dir = $path . DIRECTORY_SEPARATOR . $file;    
		if($file == '.' || $file == '..') {
			continue;
		} else if(is_dir($sub_dir)) {    
			
			gc_streams($sub_dir,$expire_time);
		} else {    
			if (strlen($file) < 32){ 
			    
			}else{
				$c_file_timestamp = filemtime($path.'/'.$file);

				$times = date("y-m-d h:M:s",$c_file_timestamp);
				
				
				if ($c_file_timestamp < $expire_time){
					unlink($path.'/'.$file);
				}
			}
		}
	}
}
?>