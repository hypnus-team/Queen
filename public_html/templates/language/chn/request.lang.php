<?php

$language += array
(
	  
	  
	  'DronePanel' => '信息面板',

      
	 'input_shortcut_name' => '请输入快捷方式名称',

	  
	  'illegal_mid'        => '非法的模块编号',
	  'wait_clients'       => '指令正在发送中',
	  'already_clients'    => '等待客户端回应',
	  'wait_parse'         => '等待HypnuScript解析输出',
      'request_status_-1'  => '客户端返回目标模块不存在',
      'request_status_-2'  => '请求数据超过客户端容许长度',
      'request_status_-3'  => 'HypnuScript解析错误',
      'request_status_-4'  => '请求数据超过服务器限制长度',
      'request_status_-5'  => '客户端返回数据超过最大限制长度 $runtime[\'max_response_size\']',
      'request_status_-6'  => '客户端返回数据超过最大限制长度 $runtime[\'max_response_unit\']',
      'request_status_-7'  => '客户端返回目标模块执行函数地址为空',
	  'request_status_-100'=> '客户端返回数据写入数据库失败',
	  'request_status_-101'=> '获取请求数据失败',
	  'request_status_-9'  => '请求任务状态转换(准备->待发送)失败',
	  'request_status_-10' => '请求任务IPC传输失败',

	  'ipc_recv_timeout'   => 'IPC通道接收任务返回超时',
	  'ipc_recv_fail'      => 'IPC通道接收任务返回失败',

	  'ipc_namepipe_locked'   => 'IPC(namepipe)数据写入失败,通道忙碌中',
	  'ipc_namepipe_not_exist'=> 'IPC(namepipe)数据写入失败,通道不存在或已关闭',


	  'no_mid_client'      => '客户端未安装目标模块',
	  'fail_get_uniqu_tid' => '获取唯一TaskID失败',
	  'fail_insert_req'    => '指令请求写入数据库失败',
	  'fail_insert_reqdata'=> '指令内容写入数据库失败',
	  'invalid_tid'        => '无效的请求编号',
	  'unkown_status'      => '未知的请求状态编号',

	  'create_shortcut'    => '保存为快捷方式',
	  'close'              => '关闭',
	  'up_panel'           => '面板上移',  
	  'adjust_panel_width' => '调整面板宽度',
	  'switch_group_effects' => '操作关联|断开',
	  'hide_show'          => '隐藏|显示',

	  'illegal_sid'        => '不存在或无权限操作的快捷方式',

	  'fail_stream_path'   => '获取流数据缓存目录失败',
	  'error_upload_file'  => array(
	                            0 => '文件上传失败: #',
	                            1 => '文件上传失败: The uploaded file exceeds the upload_max_filesize directive in php.ini.',	
	                            2 => '文件上传失败: The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',	
	                            3 => '文件上传失败: The uploaded file was only partially uploaded.',	
	                            4 => '文件上传失败: No file was uploaded.',	
	                            6 => '文件上传失败: Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3.',	
	                            7 => '文件上传失败: Failed to write file to disk. Introduced in PHP 5.1.0.',	
	                            8 => '文件上传失败: A PHP extension stopped the file upload. PHP does not provide a way to ascertain which extension caused the file upload to stop; examining the list of loaded extensions with phpinfo() may help. Introduced in PHP 5.2.0.',	
                              ),


      'requestdata_parser_fail'=> '返回数据分析失败',
      
	  'fail_posit_symbol' => '=符号无法定位: ',
	  'fail_param_name'   => '变量名无法定位或非法: ',
	  'fail_match_symbol' => 'array( 与 ) 个数不匹配: ',
	  'fail_iconv'        => '字符集转换失败，可能系统不支持目标字符集: ', 
	  'illegal_charset'   => '非法的字符集编号',
	  'exec_-1'           => 'eval变量失败',
	  'exec_-2'           => 'mod变量非法',
	  'exec_-3'           => 'mod指向的htm模板文件不存在',
	  'exec_-4'           => 'module变量被覆盖或非法',
	  'exec_-5'           => '解析执行失败,未知错误0 ',
	  'fail_use_reserved_name' => '占用了保留变量名',

	  
	  
	  'no_any_legal_drone'   => '无有效目标客户端',

);
?>