<?php
$language += array
(
     'title'                          => 'Hypnus 安装面板',
     'error_eval_invailable'          => 'eval()不可调用,请开启该选项后重试',
	 'error_mbstring_invailable'      => 'mbstring库不存在,请安装或开启后重试',
	 'error_iconv_invailable'         => 'iconv函数无法调用,请开启后重试',
	 'error_mysqli_invailable'        => 'mysqli无法调用,请开启后重试',
	 'attach_enabled'                 => '允许/最大尺寸 ',
	 'attach_disabled'                => '不允许上传附件',
	 'warning_not_support_namedpipe'  => '安装系统不支持named pipe,将会降低系统与客户端响应速度.(#提示:如果系统为Linux,可能是由于SeLinux导致posix_mkfifo无法访问./stream/路径造成,请做相应设置或禁用SeLinux)',
	 'error_dir_unwritable'           => '目录无写入权限,请正确设置后重试',
	 'input_tips'                     => '请完成下面的表单,通常情况下不需要修改红色选项内容。',
	 'fail_conn_db'                   => '数据库连接失败,请检查相关设置',
	 'error_file_unwritable'          => '文件无写入权限,请正确设置后重试',
	 'error_parse_sql'                => '导入sql文件失败',
	 'error_import_sql'               => '导入sql语句失败',
	 'error_some_sql_import'          => ' 个sql表导入失败',
	 'success_complete'               => '安装成功完成',
	 'error_read_config_inc'          => '读取配置文件失败',
	 'error_write_config_ini'         => '写入配置文件失败',
	 'warning_del_install_file'       => '自动删除./install.php文件失败,请在安装完成后手动删除',
	 'success_del_install_file'       => '自动删除./install.php文件已完成',
	 'warning_set_account'            => '用户数据库为空,请设置用户帐号密码',
	 'warning_not_set_account'        => '用户数据库不为空,请使用已有帐号密码登录',
	 'error_unsame_password'          => '二次输入的密码不同',
	 'success_insert_account'         => '用户帐号密码写入完成',
	 'error_fail_insert_account'      => '帐号密码写入数据库失败',
	 'error_sql_insert'               => 'sql插入语句失败',

	 'sub_title_check'  => '检查配置文件状态及目录权限',
	 'sub_title_input'  => '浏览/编辑当前配置',
	 'sub_title_finish' => '完成配置文件及数据库写入',
	 'sub_title_license'=> 'Hypnus 用户许可协议',

	 'warning_not_support_posix' => '不支持posix扩展库,建议安装php-posix后再安装,以启动对named pipe的支持,会极大提高系统与客户端响应速度.',

);
?>