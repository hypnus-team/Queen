<?php

function hypnuscript_parser($src,$cid,$module,$UNIQU,&$lasterror,$language,$module_charset,$charsetId=0){

	$cid = intval($cid);
	
    if (!isset($module_charset[$charsetId])){
	    $lasterror[] = $language['illegal_charset'];
		return false;
	}
	if (0 != $charsetId){
		$src = iconv($module_charset[$charsetId],"utf-8//IGNORE",$src);
		if (false === $src){
			$lasterror[] = $language['fail_iconv'].$module_charset[$charsetId];
			return false;
		}		
	}	
    $step_1 = explode("\x04",$src);
    $result = '';
	foreach ($step_1 as $a){
		$a = trim($a,"\x00");
		if ($a){
			$tmp = explode('=',$a,2);
			
			if (2 !== count($tmp)){
				$lasterror[] = $language['fail_posit_symbol'].$a;
			    return false;    
			}else{               
				$name = trim($tmp[0]);
				$isArray = false;
				if ('[]' === substr($name,-2,2)){
				    $isArray = true;
					$name = substr($name,0,-2);
				}				
				if (('$template' == $name) or ('$MID' == $name) or ('$CID' == $name) or ('$UNIQU' == $name) or ('$RESULT' == $name)){
				    $lasterror[] = $language['fail_use_reserved_name'].': '.$name;
				    return false;
				}
			    if (!preg_match('/^\$[a-zA-Z]([A-Za-z0-9])*$/',$name)){
					$lasterror[] = $language['fail_param_name'].$a;
					return false;
				}else{
                    $value = $tmp[1]; 
					
                    
					$value = htmlspecialchars($value);
					
					if (false !== strpos($value,"\x01")){ 
						$tmp = str_split($value);
						
						$value = '';
						$balance = 0;
						foreach ($tmp as $a => $b){
						    if ("\x01" === $b){
								$balance ++;
							    $value .= 'array(';
								if ("\x01" !== $tmp[$a + 1]){
								    $value .= '"';
								}
							}elseif ("\x02" === $b){
								$balance --;
							    if ("\x02" !== $tmp[$a - 1]){
								    $value .= '"';
								}
								$value .= ')';								
							}elseif ("\x03" === $b){
								if ("\x02" !== $tmp[$a - 1]){
								    $value .=  '"';
								}
								$value .= ',';
								if ("\x01" !== $tmp[$a + 1]){
								    $value .=  '"';
								}
							}else{
								$tmp[$a] = string_to_hex($tmp[$a]);
							    $value .= $tmp[$a];
							}
						}
						if (0 !== $balance){
						    $lasterror[] = $language['fail_match_symbol'].$value;
							return false;
						}
					}else{
						$value = string_to_hex($value);
					    $value = '"'.$value.'"';
					}
				}
				if ($isArray){
				    $name .= '[]';
				}
				$result .= $name.'='.$value.';';
			}
		}
	}
    
	$res = '';
    $ret = hypnuscript_exec($result,$cid,$module,$UNIQU,$res);

	if (true !== $ret){
		$res = false;
		switch ($ret){
		    case -1:
				$lasterror[] = $language['exec_-1'];
			    @header("http/1.1 200 ok"); 
			    break;
            case -2:
				$lasterror[] = $language['exec_-2'];
			    break;
            case -3:
				$lasterror[] = $language['exec_-3'];
			    break;
            case -4:
				$lasterror[] = $language['exec_-4'];
			    break;
            default:
                $lasterror[] = $language['exec_-5']; 
			    break;
		}		
	}

    return $res;
}


function string_to_hex($src){
	$ret = '';
	if ('' !== $src){
		$tmp = unpack("H*",$src);
		$tmp = str_split($tmp[1],2);	
		foreach ($tmp as $a){
			$ret .= '\x'.$a;
		}
	}
	return $ret;
}






function hypnuscript_exec($variable,$CID,$MID,$UNIQU,&$RESULT){

    if (!preg_match('/^[a-zA-Z0-9]{32}$/',$MID)){
	    $ret = -4;
	}else{
        $mod_path     = dirname(__FILE__).'/../repo/modules/'.$MID.'/';
		$options['template_dir'] = $mod_path;
	    $options['cache_dir']    = $mod_path;
	    $options['auto_update']  = false;
        $template = Template::getInstance();
		$template->setOptions($options);

        unset($options);

		ob_start();    
		
		if (false === eval($variable)){
			$ret = -1;
		}else{
			global $language_choosed;
			if (file_exists($mod_path.'./inc.php')){
				include "$mod_path".'./inc.php';
			}
			if (!isset($mod)){
			    $mod = 'index';
			}
			if (preg_match('/^[a-zA-Z0-9]{1,}$/',$mod)){
				$mod = $mod.'.htm';
				if (file_exists($mod_path.$mod)){					
					
					include($template->getfile($mod));
					$RESULT = ob_get_contents() ;    
					$ret = true;
				}else{
					$ret = -3;
				}		        
			}else{
				$ret = -2;		
			}
		}
		ob_end_clean();   

	}		
	return $ret;

}
?>