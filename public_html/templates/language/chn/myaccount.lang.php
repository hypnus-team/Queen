<?php

$language += array
(
     'title'              => 'Hypnus 控制面板',
	 'logout'             => '退出',

     'myacount_01'        => '我的帐户',
	 'changpsw'           => '修改密码',
	 'clientgen'          => '生成客户端',
	 'module_favorites'   => '模块收藏夹',

	  'oldpsw'             => '原密码',
	  'newpsw'             => '新密码',
	  'newpsw_02'          => '重复密码',
	  'submit'             => '提交',

	  'fail_password'      => '帐号不存在或密码错误',
  	  'fail_db'            => '连接数据库服务器失败，请与我们联系',

	  'invalid_oldpsw'     => '原密码不符',
	  'different_newpsw'   => '两次输入新密码不同',
	  'same_two_psw'       => '原密码和新密码不能相同',
	  'changpsw_success'   => '密码修改完成',


	  'account_manage'             => '帐户管理',  
	  'input_opt_code'             => '输入操作码 <a href="./readme.php?article=35"><i class="icon-question-sign"></i></a>',
	  'apply'                      => '应用',
	  'refer_url'                  => '推介链接 <a href="./readme.php?article=36"><i class="icon-question-sign"></i></a>',
	  'i_want_join'                => '我要参加',
	  'not_now_yet'                => '未启用',

	  'charactor_setup'            => '个性化设置',
	  'secret_setup'               => '安全选项',
	  'account_alias'              => '帐户别名 <a href="./readme.php?article=37"><i class="icon-question-sign"></i></a>',
	  'account_language'           => '语言',
      'account_style_setup'        => '界面风格',
      'style_type'                 => array(
	                                   
									   
									   '2' => 'Bootstrap',									  
									  ),
	  'language_type'              => array(
	                                   
									   '1' => '简体中文',
									   
									  ),

      'account_mail_setup'         => '接收邮件通知 <a href="./readme.php?article=38"><i class="icon-question-sign"></i></a>',
	  'accept'                     => '同意',
	  'reject'                     => '拒绝',

      'secret_dynamic_proxy'       => '通过动态代理访问 <a href="./readme.php?article=39"><i class="icon-question-sign"></i></a>',
      'secret_vaild_logon'         => '登录有效期 <a href="./readme.php?article=40"><i class="icon-question-sign"></i></a>',
	  'secret_vaild_logon_type'    => array(
	                                   '0' => '浏览器进程',
	                                   '1' => '一小时',
	                                   '2' => '一天',
	                                   '3' => '一个月',
	                                   '4' => '永久',	                                  
									  ),

      'secret_auto_logout_without_opt'         => '无操作自动退出 <a href="./readme.php?article=41"><i class="icon-question-sign"></i></a>',
      'secret_auto_logout_without_opt_type'    => array(
	                                               '0' => '15分钟',
												   '1' => '1小时',
	                                               '2' => '永不',
	                                               ),
	  'secret_ssl_setup'           => 'SSL加密设置 <a href="./readme.php?article=46"><i class="icon-question-sign"></i></a>',
      'secret_ssl_setup_opt_type'  => array(
	                                      '0' => '登陆时使用',
										  '1' => '全程使用',
	                                  ),

      'set_option_fail'            => '设置保存失败，请稍候再试',
	  'set_option_success'         => '设置保存成功',

	  'account_alias_too_long'     => '帐户别名过长，请修改',
	  'account_alias_too_short'    => '帐户别名过短，请修改',
	  'account_alias_special_char' => '别名有英文，数字以外的字符',
	  'account_alias_already_have' => '别名已被占用，请修改',
	  'fail'                       => '错误',

	  'others_setup'               => '其他选项',

	  


	  'token_setup'                => 'Token管理',
	  'token_num'                  => 'Token编号',
	  'token_remark'               => '注释',
	  'operation'                  => '操作',
	  'token_new'                  => '新建Token',
	  'token_remark_exa'           => '注释1,注释2,注释3,...',
	  'token_edit'                 => '编辑',
	  'token_save'                 => '保存',
	  'insert_token_fail'          => 'Token数据库写入失败', 
	  'no_more_token'              => '已达到当前帐号的最大Token个数',
	  'create_token_fail'          => 'Token创建失败，请稍候再试',


	  'set_drone_token'  => '选择Token',
	  'set_drone_os'     => '选择OS',
	  'drone_os'         => array(
	      '1' => 'Linux 32',
	      '2' => 'Linux 64',
	  ),
	  'set_drone_type'  => '选择类型',
	  'drone_type'      => array(
	      '0' => '无加密 (不推荐)',
	      '1' => 'CENTOS.openssl.10',
		  '2' => 'DEBIAN.openssl.1.0.0',
	  ),
	  'drone_gen' => '生成下载链接',
	  'notice_open_source' => '如没有对应的可用发行版，用户可以在讨论区下载Drone的完整源码，自行编译。',
	  'gen_no_token' => '生成客户端需前先设置Token,[<a href="./myaccount.php?a=7">立刻生成</a>]',

	  

	  'shortcuts_manage' => '快捷方式管理',
	  'shortcuts_manage_opt' => '管理操作',
	  'Copy'             => '复制',
	  'Del'              => '删除',
	  'Paste'            => '粘贴',
	  'MoveUp'           => '提升',  
	  'sc_name'          => '名称',
	  'sc_number'        => '数量',
	  'sc_opt'           => '操作',
	  'no_access'        => '无操作权限',
	  'opt_fail'         => '操作失败',
	  'shortcuts_name'   => '快捷方式名',
	  'shortcuts_version'=> '版本号',	  
	  'shortcuts_mode'   => '模块',
	  'upgradable'       => '可升级',

	  'gen_drone_notice_ssl_valide' => '服务器端配置文件设置为不支持SSL协议,生成配置使用SSL协议的客户端可能无法与服务器实现通讯.',


);

?>