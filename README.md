OAOGMS —— 小程序统计
===============

## 系统环境
> CentOS 7 + nginx 1.12 + php 7 + mysql + ThinkPHP 5.1

V1.0.0
===============
## Admin模块
搭建简易GMS,对小程序行为日志做统计，其主要功能包括：

 + Auth权限
 + 简易菜单
 + 首页
 + 报表（登录，授权，看广告）
 
## API模块
小程序行为采集接口，其主要功能包括：

 + 登录
 + 注册
 + 日志采集

## 数据库结构
~~~
-- ----------------------------
-- oao_auth_group
-- ----------------------------
DROP TABLE IF EXISTS `oao_auth_group`;
CREATE TABLE `oao_auth_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '用户组id,自增主键',
  `module` varchar(20) NOT NULL DEFAULT '' COMMENT '用户组所属模块',
  `type` varchar(10) NOT NULL DEFAULT '' COMMENT '组类型',
  `title` char(20) NOT NULL DEFAULT '' COMMENT '用户组中文名称',
  `description` varchar(80) NOT NULL DEFAULT '' COMMENT '描述信息',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '用户组状态：为1正常，为0禁用,-1为删除',
  `rules` varchar(500) NOT NULL DEFAULT '' COMMENT '用户组拥有的规则id，多个规则 , 隔开',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for oao_auth_group_access
-- ----------------------------
DROP TABLE IF EXISTS `oao_auth_group_access`;
CREATE TABLE `oao_auth_group_access` (
  `uid` int(10) unsigned NOT NULL COMMENT '用户id',
  `group_id` mediumint(8) unsigned NOT NULL COMMENT '用户组id',
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for oao_auth_rule
-- ----------------------------
DROP TABLE IF EXISTS `oao_auth_rule`;
CREATE TABLE `oao_auth_rule` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT '规则id,自增主键',
  `module` varchar(20) NOT NULL COMMENT '规则所属module',
  `type` tinyint(2) NOT NULL DEFAULT '1' COMMENT '1-url;2-主菜单',
  `name` char(80) NOT NULL DEFAULT '' COMMENT '规则唯一英文标识',
  `title` char(20) NOT NULL DEFAULT '' COMMENT '规则中文描述',
  `group` char(20) NOT NULL DEFAULT '' COMMENT '权限节点分组',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否有效(0:无效,1:有效)',
  `condition` varchar(300) NOT NULL DEFAULT '' COMMENT '规则附加条件',
  PRIMARY KEY (`id`),
  KEY `module` (`module`,`name`,`status`,`type`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for oao_calendar
-- 日志辅表，用于生成连续日志
-- ----------------------------
DROP TABLE IF EXISTS `oao_calendar`;
CREATE TABLE `oao_calendar` (
  `date` date NOT NULL,
  UNIQUE KEY `unique_date` (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for oao_channel_active
-- ----------------------------
DROP TABLE IF EXISTS `oao_channel_active`;
CREATE TABLE `oao_channel_active` (
  `aid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` char(30) NOT NULL DEFAULT 'test' COMMENT '活动名称',
  `mid` int(11) NOT NULL DEFAULT '0' COMMENT '小程序id',
  `sid` int(11) NOT NULL DEFAULT '0' COMMENT '渠道id',
  `path` char(80) NOT NULL DEFAULT 'pages/index/index' COMMENT '监控链接 mid + sid',
  `remark` char(200) DEFAULT NULL COMMENT '备注',
  `create_timestamp` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`aid`),
  KEY `sys_admin` (`sid`),
  KEY `mini` (`mid`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='渠道表';

-- ----------------------------
-- Table structure for oao_menu
-- ----------------------------
DROP TABLE IF EXISTS `oao_menu`;
CREATE TABLE `oao_menu` (
  `nid` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
  `type` varchar(10) NOT NULL DEFAULT 'admin' COMMENT '菜单类别（admin后台，user会员中心）',
  `icon` varchar(20) NOT NULL DEFAULT '' COMMENT '分类图标',
  `pid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父级分类ID',
  `sort` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '排序（同级有效）',
  `url` char(255) NOT NULL DEFAULT '' COMMENT '链接地址',
  `hide` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否隐藏',
  `group` varchar(50) DEFAULT '' COMMENT '分组',
  `is_dev` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否仅开发者模式可见',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`nid`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='菜单管理';

-- ----------------------------
-- Table structure for oao_mini
-- ----------------------------
DROP TABLE IF EXISTS `oao_mini`;
CREATE TABLE `oao_mini` (
  `mid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` char(30) NOT NULL DEFAULT '' COMMENT '小程序名称',
  `type` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '类型：1.小程序，2.小游戏',
  `appid` char(80) NOT NULL DEFAULT '' COMMENT '小程序appid',
  `appsecret` varchar(40) NOT NULL COMMENT 'secret',
  `remark` char(140) NOT NULL DEFAULT '' COMMENT '小程序描述',
  `create_timestamp` datetime NOT NULL COMMENT '创建时间',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '启用， 删除',
  PRIMARY KEY (`mid`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='小程序表';

-- ----------------------------
-- Table structure for oao_mini_extend
-- ----------------------------
DROP TABLE IF EXISTS `oao_mini_extend`;
CREATE TABLE `oao_mini_extend` (
  `meid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `mid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '小程序ID',
  `bindid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '绑定小程序ID',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态',
  `updata_timestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '更新时间',
  `create_timestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '创建时间',
  PRIMARY KEY (`meid`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for oao_mini_log
-- 日志大表，后期需做优化
-- ----------------------------
DROP TABLE IF EXISTS `oao_mini_log`;
CREATE TABLE `oao_mini_log` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `type` char(12) NOT NULL DEFAULT '' COMMENT '行为 login,toMini,browseAd,auth',
  `uid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '执行用户id',
  `action_ip` char(20) NOT NULL COMMENT '执行行为者ip',
  `mid` int(11) NOT NULL DEFAULT '1',
  `aid` int(11) unsigned DEFAULT '0' COMMENT '推广活动',
  `remark` varchar(255) DEFAULT '' COMMENT '日志备注',
  `create_timestamp` datetime NOT NULL COMMENT '执行行为的时间',
  PRIMARY KEY (`id`),
  KEY `type` (`type`),
  KEY `create_timestamp` (`create_timestamp`),
  KEY `uid` (`uid`),
  KEY `aid` (`aid`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='行为日志表';

-- ----------------------------
-- Table structure for oao_sys_admin
-- ----------------------------
DROP TABLE IF EXISTS `oao_sys_admin`;
CREATE TABLE `oao_sys_admin` (
  `sid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `username` varchar(32) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(64) NOT NULL DEFAULT '' COMMENT '用户密码',
  `nickname` char(16) NOT NULL DEFAULT '' COMMENT '昵称',
  `avator` varchar(150) NOT NULL DEFAULT '' COMMENT '头像',
  `email` char(100) DEFAULT NULL COMMENT '邮箱地址',
  `mobile` char(20) DEFAULT NULL COMMENT '手机号码',
  `salt` char(8) NOT NULL DEFAULT '' COMMENT '密码盐值',
  `login` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '登录次数',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '账号状态',
  `create_timestamp` datetime NOT NULL COMMENT '注册时间',
  PRIMARY KEY (`sid`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='系统用户表';

-- ----------------------------
-- Table structure for oao_user
-- 小程序用户表
-- ----------------------------
DROP TABLE IF EXISTS `oao_user`;
CREATE TABLE `oao_user` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `nickname` varchar(32) DEFAULT '' COMMENT '昵称',
  `avator` varchar(64) DEFAULT '' COMMENT '头像',
  `openid` char(64) NOT NULL DEFAULT '' COMMENT 'openid',
  `sex` tinyint(3) unsigned DEFAULT '0' COMMENT '性别',
  `reg_ip` char(20) NOT NULL DEFAULT '0' COMMENT '注册IP',
  `last_login_ip` char(20) NOT NULL DEFAULT '0' COMMENT '最后登录IP',
  `last_login_timestamp` datetime NOT NULL COMMENT '最后登录时间',
  `create_timestamp` datetime NOT NULL COMMENT '注册时间',
  PRIMARY KEY (`uid`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='小游戏用户表';

-- ----------------------------
-- Table structure for oao_user_extend
-- 小程序用户扩展
-- ----------------------------
DROP TABLE IF EXISTS `oao_user_extend`;
CREATE TABLE `oao_user_extend` (
  `eid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户UID',
  `mid` int(11) NOT NULL COMMENT '小程序UID',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '状态 0 未授权 1授权',
  `reg_ip` char(20) NOT NULL DEFAULT '0' COMMENT '注册IP',
  `last_login_ip` char(20) NOT NULL DEFAULT '0' COMMENT '最后登录IP',
  `last_login_timestamp` datetime NOT NULL COMMENT '最后登录时间',
  `create_timestamp` datetime NOT NULL COMMENT '注册时间',
  PRIMARY KEY (`eid`)
) ENGINE=InnoDB AUTO_INCREMENT=34 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='用户小程序关系';

~~~
V1.0.1
===============

+ 修复渠道分析模块图标显示问题