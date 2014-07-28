<?php

ini_set('display_errors',0);

$obj    = $_REQUEST['obj'];
$module = strtoupper($_REQUEST['mid']);


if (!preg_match('/^[A-F0-9]{32}$/',$module)){
    exit;
}

$module_dir = './repo/modules/';

if ('logo' === $obj){
	if ($module == '00000000000000000000000000000000'){
		show_jpeg($module_dir.'../logos/add.jpg');	    
	}elseif (is_dir($module_dir.$module)){
		if (file_exists($module_dir.$module.'/logo.jpg')){
		    show_jpeg($module_dir.$module.'/logo.jpg');
		}else{
		    show_jpeg($module_dir.'logo.jpg');
		}
	}else{
	    show_jpeg($module_dir.'error.jpg');
	}
}elseif ('js' === $obj){
	$jsName = $_REQUEST['JsName'];
	if (!preg_match('/^[a-zA-Z0-9]{1,}$/',$jsName)){        
		exit;
	}
	if (is_dir($module_dir.$module)){
		if (file_exists($module_dir.$module.'/'.$jsName.'.js')){
            $tmp = file_get_contents($module_dir.$module.'/'.$jsName.'.js');
			$tmp = str_replace('{$MID}',$module,$tmp);
			header("Content-Type: text/javascript");
			echo "$tmp";
		}
	}
}

exit;

function show_jpeg($filename){
	 header("Content-Type: image/jpeg");
	 $result = file_get_contents($filename);
	 echo "$result";

}

?>