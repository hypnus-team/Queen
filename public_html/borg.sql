-- --------------------------------------------------------

--
-- 表的结构 `db_data_request`
--

CREATE TABLE IF NOT EXISTS `db_data_request` (
  `rid` char(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL COMMENT 'Request ID',
  `data` mediumblob NOT NULL,
  PRIMARY KEY (`rid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `db_data_response`
--

CREATE TABLE IF NOT EXISTS `db_data_response` (
  `tid` char(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL COMMENT 'Task ID',
  `cid` int(10) unsigned NOT NULL,
  `data` mediumblob NOT NULL,
  PRIMARY KEY (`tid`,`cid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `db_dummy`
--

CREATE TABLE IF NOT EXISTS `db_dummy` (
  `dummy` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `clients_num` int(10) unsigned NOT NULL,
  `alias` char(30) NOT NULL,
  `shortcuts_num` int(10) unsigned NOT NULL,
  PRIMARY KEY (`dummy`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `db_dummy_clients`
--

CREATE TABLE IF NOT EXISTS `db_dummy_clients` (
  `mid` char(32) NOT NULL,
  `dummy` int(10) unsigned NOT NULL,
  PRIMARY KEY (`mid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `db_members`
--

CREATE TABLE IF NOT EXISTS `db_members` (
  `alias` char(30) NOT NULL,
  `email` char(40) NOT NULL,
  `username` char(32) NOT NULL,
  `password` char(32) NOT NULL,
  `lastact_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reg_time` int(11) unsigned NOT NULL,
  `maxclient` int(10) unsigned NOT NULL,
  `dummy_num` int(10) unsigned NOT NULL,
  `groups` int(10) unsigned NOT NULL COMMENT 'current groups number',
  `reject_mail` tinyint(3) unsigned NOT NULL,
  `language` tinyint(3) unsigned NOT NULL,
  `sec_dynamic_proxy` tinyint(3) unsigned NOT NULL,
  `sec_vaild_logon` tinyint(3) unsigned NOT NULL,
  `sec_logout_without_opt` tinyint(3) unsigned NOT NULL,
  `sec_ssl` tinyint(3) unsigned NOT NULL,
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `db_modules`
--

CREATE TABLE IF NOT EXISTS `db_modules` (
  `module` char(32) NOT NULL,
  `charset` tinyint(3) unsigned NOT NULL,
  `repo` int(10) unsigned NOT NULL,
  `root` char(32) NOT NULL,
  `version` int(10) unsigned NOT NULL,
  `os` char(16) NOT NULL,
  `latest_version` int(10) unsigned NOT NULL,
  PRIMARY KEY (`module`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


--
-- 转存表中的数据 `db_modules`
--

REPLACE INTO `db_modules` (`module`, `charset`, `repo`, `root`, `version`, `os`, `latest_version`) VALUES
('0F0102030405060708090A0B0C0D0E0F', 0, 1, '0F0102030405060708090A0B0C0D0E0F', 1, '1,2', 2),
('010102030405060708090A0B0C0D0E0F', 0, 1, '010102030405060708090A0B0C0D0E0F', 1, '1,2', 2),
('020102030405060708090A0B0C0D0E0F', 0, 1, '020102030405060708090A0B0C0D0E0F', 1, '1,2', 1),
('010102030405060708090A0B0C0D0E10', 0, 1, '010102030405060708090A0B0C0D0E0F', 2, '1,2', 2),
('0F0102030405060708090A0B0C0D0E10', 0, 1, '0F0102030405060708090A0B0C0D0E0F', 2, '1,2', 2),
('030102030405060708090A0B0C0D0E00', 0, 1, '030102030405060708090A0B0C0D0E00', 1, '1,2', 1);

-- --------------------------------------------------------


--
-- 表的结构 `db_module_repo`
--

CREATE TABLE IF NOT EXISTS `db_module_repo` (
  `repo_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `url` tinytext CHARACTER SET utf8 NOT NULL,
  `priority` tinyint(3) unsigned NOT NULL COMMENT '0:invalid  9:highest',
  `description` tinytext CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`repo_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- 转存表中的数据 `db_module_repo`
--

REPLACE INTO `db_module_repo` (`repo_id`, `url`, `priority`, `description`) VALUES
(1, 'https://hypnusoft.com/cpanel/repo/', 9, 'hypnus官方模块仓库，所有模块人工源码审核。');


-- --------------------------------------------------------

--
-- 表的结构 `db_online_clients`
--

CREATE TABLE IF NOT EXISTS `db_online_clients` (
  `cid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `status` tinyint(3) unsigned NOT NULL,
  `mid` char(32) NOT NULL,
  `uniqu` int(11) unsigned NOT NULL,
  `name` char(30) NOT NULL,
  `ip` char(40) NOT NULL COMMENT 'IP åœ°å€ (å¤–ç½‘)ï¼Œè€ƒè™‘äº†ipv6',
  `token` int(10) unsigned NOT NULL,
  `dummy` int(10) unsigned NOT NULL,
  `mac_num` int(10) unsigned NOT NULL,
  `mod_num` int(10) unsigned NOT NULL,
  `lastliving` int(10) unsigned NOT NULL COMMENT 'dbå‘¼å¸åŒ…',
  `online_time` int(11) unsigned NOT NULL,
  PRIMARY KEY (`cid`),
  UNIQUE KEY `mid` (`mid`,`uniqu`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `db_online_mac`
--

CREATE TABLE IF NOT EXISTS `db_online_mac` (
  `cid` int(10) unsigned NOT NULL,
  `mac` char(12) NOT NULL,
  `flag` int(10) unsigned NOT NULL,
  KEY `cid` (`cid`),
  KEY `mac` (`mac`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `db_online_module`
--

CREATE TABLE IF NOT EXISTS `db_online_module` (
  `cid` int(10) unsigned NOT NULL,
  `module` char(32) NOT NULL,
  KEY `cid` (`cid`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `db_online_task`
--

CREATE TABLE IF NOT EXISTS `db_online_task` (
  `tid` char(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL COMMENT 'Task ID',
  `cid` int(10) unsigned NOT NULL,
  `dealed` tinyint(3) unsigned NOT NULL,
  `rid` char(32) NOT NULL COMMENT 'Request ID(NULL:no request data)',
  `module` char(32) NOT NULL,
  `chunk` int(11) NOT NULL COMMENT 'for stream only',
  `size` int(11) NOT NULL COMMENT 'for stream only',
  `status` int(3) NOT NULL,
  `lastact` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`tid`,`cid`),
  KEY `cid` (`cid`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `db_online_task_lock`
--

CREATE TABLE IF NOT EXISTS `db_online_task_lock` (
  `tid` char(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL COMMENT 'Task ID',
  PRIMARY KEY (`tid`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `db_shortcuts`
--

CREATE TABLE IF NOT EXISTS `db_shortcuts` (
  `sid` int(11) NOT NULL AUTO_INCREMENT COMMENT 'shortcut Id',
  `dummy` int(10) unsigned NOT NULL COMMENT 'dummy Id',
  `token` int(10) unsigned NOT NULL COMMENT 'token ID',
  `name` char(30) NOT NULL,
  `module` char(32) CHARACTER SET latin1 NOT NULL COMMENT 'module Id',
  `data` mediumblob NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`sid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- 表的结构 `db_spam_members`
--

CREATE TABLE IF NOT EXISTS `db_spam_members` (
  `ip` char(40) NOT NULL,
  `times` smallint(3) unsigned NOT NULL,
  `lastact` int(11) unsigned NOT NULL,
  PRIMARY KEY (`ip`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `db_token`
--

CREATE TABLE IF NOT EXISTS `db_token` (
  `tid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `token` char(32) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `comment` char(30) NOT NULL,
  `shortcuts_num` int(10) unsigned NOT NULL,
  PRIMARY KEY (`tid`),
  UNIQUE KEY `token` (`token`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
