<?php

$HypnusInstall = TRUE;

ini_set('display_errors',0);

$lasterror   = false;
$lastsuccess = false;
$lastwarning = false;

$last_page   = false; 

$config_ini_file = './include/config.inc.php';

   define('Hypnus', TRUE);
   define('HYPNUS_ROOT', dirname(__FILE__).'/');  
   define('TPLDIR',HYPNUS_ROOT."./templates/");   

   require './library/global.func.php';

   $language_choosed = 'chn';
   $template_choosed = 'bootstrap';

   $tpldir = TPLDIR.'./'."$template_choosed".'/html/';
   $cachedir = './cache/'."$template_choosed".'/';
   $tplpath = './templates/'."$template_choosed";

   
   if (!is_writable ($cachedir)){
	   exit ("directory fail to write: ".$cachedir);   
   }
   

   $languagedir = TPLDIR.'./language/'."$language_choosed".'/';

   require "$languagedir"."./common.lang.php";

   require "./include/template.class.php";   

   require "$languagedir"."./install.lang.php";

   $sub_title = $language['sub_title_license'];

   if (isset($_POST['submit'])){
	   if ('我同意' === $_POST['submit']){
		   $act = "check";   
		   $sub_title = $language['sub_title_check'];
	   }elseif ('下一步' === $_POST['submit']){
	       $act = "input";		   
		   $sub_title = $language['sub_title_input'];
	   }elseif ('完成' === $_POST['submit']){
	       $act = "finish";		   
		   $sub_title = $language['sub_title_finish'];
	   }elseif ('保存用户信息' === $_POST['submit']){
	       $act = "save";
		   $sub_title = $language['sub_title_finish'];
	   }
   }

   

   if ('check' === $act){
       
	   
	   $curr_os = PHP_OS;
	   $curr_php_version = PHP_VERSION;

	   if (!check_eval_available()){ 
	       $lasterror[] = $language['error_eval_invailable'];
	   }

       if (!function_exists('json_encode')){
		   $lasterror[] = $language['error_json_encode_invailable'];
       }

	   if (!function_exists(mb_strlen)){
	      $lasterror[] = $language['error_mbstring_invailable'];
	   }
	   
	   if (!function_exists(iconv)){
	      $lasterror[] = $language['error_iconv_invailable'];
	   }

	   if(@ini_get(file_uploads)) {
		   $max_size = @ini_get(upload_max_filesize);
		   $curr_upload_status = $language['attach_enabled'].$max_size;
	   } else {
		   $curr_upload_status = $language['attach_disabled'];
	   }
	   $curr_disk_space = intval(diskfreespace('.') / (1024 * 1024)).'M';

	   if(!function_exists('mysqli_connect')) {
		  $lasterror[] = $language['error_mysqli_invailable'];
	   }
       
	   $checkdirarray = array(
				'stream' => './stream',
				'cache' => './cache/bootstrap',

	   );

	   foreach($checkdirarray as $key => $dir) {
		   if(!dir_writeable($dir)) {
			   $lasterror[] = $dir.' '.$language['error_dir_unwritable'];			
		   }
	   }

	   if (!is_writeable($config_ini_file)){
	       $lasterror[] = $config_ini_file.' '.$language['error_file_unwritable'];	
	   }

       if (!$lasterror){
	       $curr_ipc_type = false;
		   if (function_exists('posix_mkfifo')){
			   if (check_namepipe("./stream/")){
				   $curr_ipc_type = "namepipe";
			   }
		   }else{
		       $lastwarning[] = $language['warning_not_support_posix'];
		   }  
		   if (!$curr_ipc_type){
			  $curr_ipc_type = "mysql";
			  $lastwarning[] = $language['warning_not_support_namedpipe'];
		   }
	   }

   } 

   if ('input' === $act){ 
       $srv_path = $_SERVER['REQUEST_URI'];
	   $srv_path = str_replace(strrchr($srv_path,'/'),'',$srv_path);
       $srv_path .= '/srv/';
	   $lastwarning[] = $language['input_tips'];
	   $ipc_type = $_POST['ipc_type'];
   }


   if ('finish' === $act){
	   
	   $ssl       = intval($_POST['ssl']);
	   $salt      = $_POST['salt'];
	   $max_drone = intval($_POST['max_drone']);
	   $dbhost = $_POST['db_addr'];
	   $dbuser = $_POST['db_user'];
	   $dbpw   = $_POST['db_pass'];
	   $dbname = $_POST['db_name'];
	   $dbprefix = $_POST['db_prefix'];
       $ipc_type = $_POST['ipc_type'];
	   $queen_addr = $_POST['queen_addr'];	   
	   $queen_host = $_POST['queen_host']; 
	   $queen_path = $_POST['queen_path'];

	   if(!@mysql_connect($dbhost,$dbuser,$dbpw)) {
	       $lasterror[] = $language['fail_conn_db'];
	   }else{
		   if(mysql_get_server_info() > '4.1') {
				mysql_query("CREATE DATABASE IF NOT EXISTS `$dbname` DEFAULT CHARACTER SET utf8");
			} else {
				mysql_query("CREATE DATABASE IF NOT EXISTS `$dbname`");
			}
			if(mysql_errno()) {
				$lasterror[] = 'database_errno_'.mysql_errno();
			}			
			mysql_close();
	   }
	   
	   $db = false;

	   if (!$lasterror){
		   $sql_result = array();
		   if (sql_parser($dbprefix,"./borg.sql",$sql_result)){
				@$db = new mysqli($dbhost,$dbuser,$dbpw,$dbname);
				if (mysqli_connect_errno()){	
					$lasterror[] = mysqli_connect_error();
				}else{
					$result = $db->query("SHOW TABLES");
					if (0 < mysqli_num_rows($result)){
					    $lasterror[] = $language['not_emtpy_table'];
					}else{
						@$db->query("SET character_set_connection=utf8, character_set_results=utf8, character_set_client=binary");
						$j = 0;
						foreach ($sql_result as $a){
							@$db->query($a);
							$check = substr($a,0,6);
							if ('CREATE' == $check){
								$j ++;
							}elseif ('REPLAC' == $check){
								if (0 >= $db->affected_rows){
									$lasterror[] = $language['error_sql_insert'].' '.$a;
								}
							}
						}
						$result = $db->query("SHOW TABLES");
						$i = mysqli_num_rows($result);
						if ($i != $j){
							$i = $j - $i;
							$lasterror[] = $i.$language['error_some_sql_import'];
						}
					}
				}
		   }else{
		       $lasterror[] = $language['error_parse_sql'];
		   }		   
	   }

	  
	   if (!$lasterror){ 
           
		   $configfile  = '<?php'."\n";
           $configfile .= 'define (\'HYP_IPC_MODE\',"'.$ipc_type.'");'."\n";
		   $configfile .= '$IPC_mod_path       =  dirname(__FILE__).\'/../IPC/\';'."\n";
		   $configfile .= '$mysql_ini = array ( '."\n";
		   $configfile .= '       \'address\'  => \''.$dbhost.'\','."\n";
           $configfile .= '	      \'username\' => \''.$dbuser.'\','."\n";
           $configfile .= '	      \'password\' => \''.$dbpw.'\','."\n";
           $configfile .= '	      \'basename\' => \''.$dbname.'\','."\n";
           $configfile .= '	      \'prefix\'   => \''.$dbprefix.'\','."\n";
           $configfile .= ');'."\n";

		   $configfile .= '$srv_block_ini = array('."\n";
		   $configfile .= '    \'sleep\'         => 2,'."\n";
		   $configfile .= '    \'breath_delay\'  => 15,'."\n";
		   $configfile .= '    \'db_living\'     => 3 * 60,'."\n";
           $configfile .= ');'."\n";

		   $configfile .= '$queen_srv_ini = array('."\n";
		   $configfile .= '    \'buff\'     => 160,'."\n";
		   $configfile .= '    \'addr\'     => \''.$queen_addr.'\','."\n";
		   $configfile .= '    \'host\'     => \''.$queen_host.'\','."\n";
		   $configfile .= '    \'path\'     => \''.$queen_path.'\','."\n";		   
           $configfile .= ');'."\n";


           $configfile .= '$runtime = array('."\n";
		   $configfile .= '    \'max_response_unit\' => 1024 * 1024,'."\n";
           $configfile .= '    \'max_request_size\'  => 1024 * 1024,'."\n";
	       $configfile .= '    \'max_response_size\' => 0,'."\n";
           $configfile .= ');'."\n";

           $configfile .= '$repo_path = dirname(__FILE__).\'/../repo/\';'."\n";
           $configfile .= '$log_file_path  = false;'."\n";
           $configfile .= '$tplrefresh = 0;'."\n";
           $configfile .= '$max_clients = '.$max_drone.';'."\n";           
		   $configfile .= '$ssl_valid = ';		   
		   if ($ssl){
		       $configfile .= 'true;'."\n";   
		   }else{
		       $configfile .= 'false;'."\n";   
		   }
           $configfile .= '$repo_closed = true;'."\n";
           $configfile .= '$hash_salt = \''.$salt.'\';'."\n";
           $configfile .= '$stream_path       =  dirname(__FILE__).\'/../stream/\';'."\n";
		   $configfile .= '$stream_path_level =  2;'."\n";

           $configfile .= '$gc_lock_file      = $stream_path.\'gc.lock\';'."\n";
           $configfile .= '$gc_frequency      = 7 * 24 * 60 * 60;'."\n";

	       $configfile .= '$cookiepre = \'Borg_\';'."\n";
	       $configfile .= '$cookiedomain = \'\';'."\n";
		   $configfile .= '$cookiepath = \'/\';'."\n";
           $configfile .= '?>';


		   if (!file_put_contents($config_ini_file,$configfile)){
		       $lasterror[] = $language['error_write_config_ini'];
		   }
		   
	   }
       
	   if (!$lasterror){
	       $lastsuccess[] = $language['success_complete'];		   

		   
		   $no_any_account = true;
           if ($db){
		       $query = "select username from ".$dbprefix."members limit 1";
			   $result = $db->query($query);
               if (mysqli_num_rows($result)){
			       $no_any_account = false;
				   $lastwarning[] = $language['warning_not_set_account'];
				   $last_page = true;
			   }else{
			       $lastwarning[] = $language['warning_set_account'];
			   }
		   } 
	   }	   

   }

   if ('save' === $act){
	   include "$config_ini_file";
	   $no_any_account = true;
       if ($_POST['password'] === $_POST['re-password']){
	       
           $md5_username = md5($hash_salt.$_POST['username']);
		   $current_time = time();
		   $password = md5($_POST['username']."$hash_salt".$_POST['password']."$current_time");		
		   
		   @$db = new mysqli($mysql_ini['address'],$mysql_ini['username'],$mysql_ini['password'],$mysql_ini['basename']);
		   if (mysqli_connect_errno()){	
			   $lasterror[] = mysqli_connect_error();
		   }else{
			   $query = 'insert into '.$mysql_ini['prefix'].'members values (\'admin\',\'\',\''.$md5_username.'\',\''.$password.'\',0,'.$current_time.',100,0,0,1,0,0,0,0,0)';			   
			   $db->query($query);
		       if (1 == $db->affected_rows){				   
				   $lastsuccess[] = $language['success_insert_account'];
			       $no_any_account = false;
				   $last_page = true;
			   }else{
				   $lasterror[] = $language['error_fail_insert_account'].$query;   
			   }     	       
		   }
	   }else{
	       $lasterror[] = $language['error_unsame_password'];
	   }
	   $act = 'finish';
   }

   if ($last_page){
      if (unlink("./install.php")){
		  $lastsuccess[] = $language['success_del_install_file'];
	  }else{
		  $lastwarning[] = $language['warning_del_install_file'];
	  }
   }


include($template->getfile('install.htm'));
exit;


function sql_parser($prefix,$sqlfile,&$result){
	$file = fopen($sqlfile, "r");
	if (!$file){
		return false;
	}
	$write_in = false;
	$buf = "";
    while(!feof($file)){
		$tmp = fgets($file);
		$tmp = trim($tmp);
		if ($write_in){
		    $buf .= $tmp;
			if (strpos($tmp,';')){
			   $result[] = $buf;
			   $buf = "";
			   $write_in = false;
			}
		}else{
		    if ((stristr($tmp,'CREATE TABLE IF NOT EXISTS `db_')) or (stristr($tmp,'REPLACE INTO `db_'))){
			   $tmp = str_replace('`db_','`'.$prefix,$tmp);
			   $buf = $tmp;
			   $write_in = true;
			}
		}
	}
	fclose($file);
	return true;
}

function check_eval_available(){
	$isevalfunctionavailable = false;

    $evalcheck = "\$isevalfunctionavailable = true;";

    eval($evalcheck);

	if ($isevalfunctionavailable === true) {
		return true;
	}
	return false;

}

function check_namepipe($path){

	$ret = false;

    
    $fifo_file = $path.'/testaaa';

	if (file_exists($fifo_file))
		unlink($fifo_file);

    $success = posix_mkfifo($fifo_file, 0700);
	if ($success){		
        $pipe = @fopen($fifo_file, 'r+b');
        if ($pipe){
			$ret = true;
			fclose($pipe);
		}
		unlink($fifo_file);
	}
    return $ret;
}

function dir_writeable($dir) {
	if(!is_dir($dir)) {
		@mkdir($dir, 0777);
	}
	if(is_dir($dir)) {
		if($fp = @fopen("$dir/test.txt", 'w')) {
			@fclose($fp);
			@unlink("$dir/test.txt");
			$writeable = 1;
		} else {
			$writeable = 0;
		}
	}
	return $writeable;
}

?>