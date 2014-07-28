<?php

   define('CPANEL', TRUE);
     
   require "./include/common.inc.php";       
   require "$languagedir"."./mod_manage.lang.php";




if (!$lasterror){   
   $cid = intval($_POST['cid']);

   $total_modules_array  = array();
   $c_modules_array      = array();
   $result_modules_array = array();
   $tmp_array            = array();

   $db = connect_db($mysql_ini);
   if (!$db){
       $lasterror[] = $language['fail_db'];
   }else{
	   $query = 'select mod_num from '.$mysql_ini['prefix'].'online_clients where cid='.$cid.' limit 1' ;
       $result = $db->query($query);
	   if (1 == mysqli_num_rows($result)){
		   $tmp = $result->fetch_assoc();
		   if ($tmp['mod_num'] > 0){
			   $query = 'select module from '.$mysql_ini['prefix'].'online_module where cid='.$cid.' limit '.$tmp['mod_num'] ;
               $result = $db->query($query);
			   $i = mysqli_num_rows($result);
			   for (;$i;$i--){
				   $tmp = $result->fetch_assoc();				   
				   $c_modules_array[] = $tmp['module'];
			   }
		   }
	   }else{
	       $lasterror[] = $language['client_off_line'];
	   }

       if (!$lasterror){
		   $query = 'select a.*,b.description from '.$mysql_ini['prefix'].'modules as a left join '.$mysql_ini['prefix'].'module_repo as b on a.repo = b.repo_id';
		   $result = $db->query($query);
		   $i = mysqli_num_rows($result);
		   for (;$i;$i--){
			   $tmp = $result->fetch_assoc();
			   $tmp['os'] = explode(',',$tmp['os']);
			   $total_modules_array[$tmp['module']] = $tmp;
		   }
           
           foreach ($c_modules_array as $a){
			   if (isset($total_modules_array[$a])){
				   $root_installed[$total_modules_array[$a]['root']] = true;
				   $total_modules_array[$a]['installed'] = true;
			   }
		   }
		   
           foreach ($total_modules_array as $a => $b){
		       if ($b['version'] === $b['latest_version']){
			       $tmp_array[$a] = $b;
				   if (true === $root_installed[$b['root']]){
				        $tmp_array[$a]['root_installed'] = true;
				   }
			   }
		   }

           
		   foreach ($tmp_array as $a => $b){
		       $tmp_array[$a] += get_mod_name($b['module'],$language_choosed);
		   }

           
		   foreach ($tmp_array as $b){
		       foreach ($b['os'] as $c){
			       $result_modules_array[$c][] = $b;
			   }
		   }

          
		  
		  
		  
	   }
   }
}
   include($template->getfile('mod_manage.htm'));
   exit;

?>