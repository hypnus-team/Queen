<?php
   define('CPANEL', TRUE);
     
   require "./include/common.inc.php";       
   require "$languagedir"."./cpanel.lang.php";
   
   $clientId = intval($_POST['cid']);
   $isMulti  = intval($_POST['multi']);

   if (!$clientId){
       $lasterror[] = $language['client_off_line'];
   }else{
	   $db = connect_db($mysql_ini);
	   if (!$db){
		   $lasterror[] = $language['fail_db'];
	   }
   }
   
   $dummy = false;
   $shortcuts_num = 0;

   if (!$lasterror){	  
	   $query = 'select a.name,b.alias,a.mod_num,a.dummy,b.shortcuts_num from '.$mysql_ini['prefix'].'online_clients as a Left JOIN '.$mysql_ini['prefix'].'dummy as b ON b.dummy = a.dummy where a.cid='.$clientId.' and a.status=1 limit 1';
       $result = $db->query($query);
	   if (1 === mysqli_num_rows($result)){
		   $result =  $result->fetch_assoc();
		   $mod_num = $result['mod_num'];
		   $dummy   = $result['dummy'];
           
		   
		   if (strlen($result['alias'])){
		       $drone_name = htmlspecialchars(trim($result['alias']));
		   }else{
			   $drone_name = htmlspecialchars(trim($result['name']));
		   }
		   $drone_name = clip_str_width($drone_name);

		   $shortcuts_num = $result['shortcuts_num'];
		   $same_mod_filter = array(); 
		   $auto_hide_warning = false; 
		   $app_array_id = 0;
		   if ($mod_num){
			   $query = 'select a.module,a.version,a.latest_version,a.root from '.$mysql_ini['prefix'].'modules as a,'.$mysql_ini['prefix'].'online_module as b where b.cid='.$clientId.' and b.module = a.module limit '.$mod_num;
			   $result = $db->query($query);
			   
			   $i = mysqli_num_rows($result);

			   if ($mod_num > $i){ 
				   $tmp = $mod_num - $i;
			       $lastwarning[] = $tmp.$language['some_unsupport_mod'];
			   }
			   for (;$i > 0 ;$i --){	
				   $tmp = $result->fetch_assoc();
                   $mod_name_array = get_mod_name($tmp['module'],$language_choosed);
				   $tmp['title'] = clip_str_width($mod_name_array['name']);
				   $tmp['name']  = $mod_name_array['name'];	
				   
				   if (isset ($same_mod_filter[$tmp['root']])){
                       if ($same_mod_filter[$tmp['root']]['ver'] > $tmp['version']){
						   $auto_hide_warning .= $tmp['name'].' ver.'.$tmp['version'].',';
                           continue;
					   }else{
						   $auto_hide_warning .= $same_mod_filter[$tmp['root']]['name'].' ver.'.$same_mod_filter[$tmp['root']]['ver'].',';
					       unset ($app_array[$same_mod_filter[$tmp['root']]['id']]);
					   }			      
				   }
				   $same_mod_filter[$tmp['root']]['id']  = $app_array_id;
				   $same_mod_filter[$tmp['root']]['ver'] = $tmp['version'];
				   $same_mod_filter[$tmp['root']]['name'] = $tmp['name'];
				   
				   $app_array[$app_array_id] = $tmp;
				   $app_array_id ++;
			   }
			   $app_array[$app_array_id] = false;
		   }
	   }else{
	       $lasterror[] = $language['client_off_line'];
	   }	  
   }

   if (false !== $auto_hide_warning){
	   $auto_hide_warning = substr($auto_hide_warning,0,strlen($auto_hide_warning) - 1);
	   $lastwarning[] = $language['low_mod_auto_hide'].': '.$auto_hide_warning;
	   $tmp = $app_array;
	   $app_array = array();
	   foreach ($tmp as $a => $b){ 
	       $app_array[] = $b;
	   }
   
   }
              

   $shortcuts_array = array();
   if (($isMulti) and (!$lasterror) and ($shortcuts_num)){ 
       
	   $query = 'select name,sid,module from '.$mysql_ini['prefix'].'shortcuts where dummy = '.$dummy.' limit '.$shortcuts_num;
	   $r = $db->query($query);
	   $b = mysqli_num_rows($r);
	   for (;$b > 0 ;$b --){
		   $a = $r->fetch_assoc();
		   $a['name'] =  htmlspecialchars($a['name']);
		   $shortcuts_array[$tmp['mid']][] = $a;
	   }
   }


   if ((!$lasterror) and (!$app_array)){
       $lastwarning[] = $language['no_valid_mod'];
   }
   
   include($template->getfile('app_panel.htm'));
   exit;

?>