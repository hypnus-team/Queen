<?php

class HYP_SCRIPT{

	public static function parser($src,$cid,$module,$instance_Array,&$lasterror,$language){

		$cid = intval($cid);
		
		$step_1 = explode("\x04",$src);
		$result = '';
		foreach ($step_1 as $a){
			$a = trim($a,"\x00");
			if ($a){
				$tmp = explode('=',$a,2);
				//var_dump ($tmp);
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
						//var_dump ($value);					
						//$value = htmlspecialchars($value,ENT_QUOTES);
						$value = htmlspecialchars($value);
						
						if (false !== strpos($value,"\x01")){ //array
							$tmp = str_split($value);
							//var_dump ($tmp);
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
									$tmp[$a] = self::_string_to_hex($tmp[$a]);
									$value .= $tmp[$a];
								}
							}
							if (0 !== $balance){
								$lasterror[] = $language['fail_match_symbol'].$value;
								return false;
							}
						}else{
							$value = self::_string_to_hex($value);
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
		
		$res = false;
		foreach ($instance_Array as $b){
			if (true !== ($ret = self::_exec($result,$cid,$module,$b,$res[$b]))){
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
				break;
			}
		}

		return $res;
	}


	private static function _string_to_hex($src){
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

	//ret: -1 eval变量失败
	//     -2 mod变量非法
	//     -3 mod指向的htm模板文件不存在
	//     -4 module变量被覆盖或非法
	//   true: 成功
	private static function _exec($variable,$CID,$MID,$UNIQU,&$RESULT){

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
			//var_dump ($variable);
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
						//var_dump ($mod);
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
}
?>