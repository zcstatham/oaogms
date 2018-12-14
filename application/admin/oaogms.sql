/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : oaogms

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2018-12-14 10:38:52
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for oao_auth_group
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for oao_mini
-- ----------------------------
DROP TABLE IF EXISTS `oao_mini`;
CREATE TABLE `oao_mini` (
  `mid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `name` char(30) NOT NULL DEFAULT '' COMMENT '小程序名称',
  `type` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '类型：1.小程序，2.小游戏',
  `appid` char(80) NOT NULL DEFAULT '' COMMENT '小程序appid',
  `sid` int(11) DEFAULT NULL COMMENT '小程序渠道商id',
  `status` char(12) NOT NULL DEFAULT '' COMMENT '渠道小程序id,例：+mid 申请, mid已绑定,-mid:已解绑,null',
  `remark` char(140) NOT NULL DEFAULT '' COMMENT '小程序描述',
  `create_timestamp` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`mid`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='小程序表';

-- ----------------------------
-- Table structure for oao_mini_action_log
-- ----------------------------
DROP TABLE IF EXISTS `oao_mini_action_log`;
CREATE TABLE `oao_mini_action_log` (
  `aid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `action_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '行为id',
  `uid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '执行用户id',
  `action_ip` char(20) NOT NULL COMMENT '执行行为者ip',
  `record_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '触发行为的appid',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '日志备注',
  `create_timestamp` datetime NOT NULL COMMENT '执行行为的时间',
  PRIMARY KEY (`aid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='行为日志表';

-- ----------------------------
-- Table structure for oao_mini_login_log
-- ----------------------------
DROP TABLE IF EXISTS `oao_mini_login_log`;
CREATE TABLE `oao_mini_login_log` (
  `lid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `uid` int(11) unsigned NOT NULL COMMENT '登录用户id',
  `login_ip` char(20) NOT NULL COMMENT '登录ip',
  `login_type` char(10) NOT NULL COMMENT 'wxScene: 微信场景，channel：渠道Id',
  `scene_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '登录场景id',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '日志备注',
  `create_timestamp` datetime NOT NULL COMMENT '登录时间',
  PRIMARY KEY (`lid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='登录日志表';

-- ----------------------------
-- Table structure for oao_sys_admin
-- ----------------------------
DROP TABLE IF EXISTS `oao_sys_admin`;
CREATE TABLE `oao_sys_admin` (
  `sid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `username` varchar(32) NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(64) NOT NULL DEFAULT '' COMMENT '用户密码',
  `nickname` char(16) NOT NULL DEFAULT '' COMMENT '昵称',
  `email` char(100) DEFAULT NULL COMMENT '邮箱地址',
  `mobile` char(20) DEFAULT NULL COMMENT '手机号码',
  `salt` char(8) NOT NULL DEFAULT '' COMMENT '密码盐值',
  `login` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '登录次数',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '账号状态',
  `create_timestamp` datetime NOT NULL COMMENT '注册时间',
  PRIMARY KEY (`sid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='系统用户表';

-- ----------------------------
-- Records of oao_sys_admin
-- ----------------------------
INSERT INTO `oao_sys_admin` VALUES ('1', 'admin', '11910e5e47b10789832cf91c53307ca2', '超级管理员', '578322713', '18535318830', 'oaogms', '1', '1', '2018-12-14 19:04:11');

-- ----------------------------
-- Table structure for oao_user
-- ----------------------------
DROP TABLE IF EXISTS `oao_user`;
CREATE TABLE `oao_user` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `nickname` varchar(32) NOT NULL DEFAULT '' COMMENT '昵称',
  `avator` varchar(64) NOT NULL DEFAULT '' COMMENT '头像',
  `openid` char(16) NOT NULL DEFAULT '' COMMENT 'openid',
  `sex` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '性别',
  `regip` char(20) NOT NULL DEFAULT '0' COMMENT '注册IP',
  `last_login_ip` char(20) NOT NULL DEFAULT '0' COMMENT '最后登录IP',
  `last_login_timestamp` datetime NOT NULL COMMENT '最后登录时间',
  `create_timestamp` datetime NOT NULL COMMENT '注册时间',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='小游戏用户表';

-- ----------------------------
-- Table structure for oao_user_extend
-- ----------------------------
DROP TABLE IF EXISTS `oao_user_extend`;
CREATE TABLE `oao_user_extend` (
  `eid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户UID',
  `mid` int(11) NOT NULL COMMENT '小程序UID',
  `regip` char(20) NOT NULL DEFAULT '0' COMMENT '注册IP',
  `last_login_ip` char(20) NOT NULL DEFAULT '0' COMMENT '最后登录IP',
  `last_login_timestamp` datetime NOT NULL COMMENT '最后登录时间',
  `create_timestamp` datetime NOT NULL COMMENT '注册时间',
  PRIMARY KEY (`eid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户小程序关系';

--
-- 表的结构 `oao_menu`
--
DROP TABLE IF EXISTS `oao_menu`;
CREATE TABLE `oao_menu` (
  `nid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(50) NOT NULL DEFAULT '' COMMENT '标题',
  `type` varchar(10) NOT NULL DEFAULT 'admin' COMMENT '菜单类别（admin后台，user会员中心）',
  `icon` varchar(20) NOT NULL DEFAULT '' COMMENT '分类图标',
  `pid` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '父级分类ID',
  `sort` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '排序（同级有效）',
  `url` char(255) NOT NULL DEFAULT '' COMMENT '链接地址',
  `hide` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否隐藏',
  `tip` varchar(255) NOT NULL DEFAULT '' COMMENT '提示',
  `group` varchar(50) DEFAULT '' COMMENT '分组',
  `is_dev` tinyint(1) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否仅开发者模式可见',
  `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '状态',
  PRIMARY KEY (`nid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='菜单管理';

-- ----------------------------
-- Records of oao_menu
-- ----------------------------
INSERT INTO `oao_menu` VALUES ('1', '首页', 'admin', 'home', '0', '0', 'admin/index/index', '0', '', '导航', '0', '0');
INSERT INTO `oao_menu` VALUES ('2', '报表', 'admin', 'publice', '0', '1', 'admin/publice/group', '0', '', '导航', '0', '0');
INSERT INTO `oao_menu` VALUES ('3', '小程序', 'admin', 'mini', '0', '2', 'admin/mini/index', '0', '', '导航', '0', '0');
INSERT INTO `oao_menu` VALUES ('4', '系统', 'admin', 'system', '0', '3', 'admin/system/index', '0', '', '导航', '0', '0');
INSERT INTO `oao_menu` VALUES ('5', '应用中心', 'admin', 'mini-list', '1', '0', 'admin/index/index', '0', '', '首页', '0', '0');
INSERT INTO `oao_menu` VALUES ('6', '更新缓存', 'admin', 'refresh', '1', '0', 'admin/index/clear', '0', '', '首页', '0', '0');
INSERT INTO `oao_menu` VALUES ('7', '小程序概况', 'admin', 'cog', '2', '0', 'admin/publice/index', '0', '', '报表', '0', '0');
INSERT INTO `oao_menu` VALUES ('8', '小程序渠道', 'admin', 'book', '2', '0', 'admin/publice/channel', '0', '', '报表', '0', '0');
INSERT INTO `oao_menu` VALUES ('9', '自有小程序', 'admin', 'mini', '3', '0', 'admin/mini/index', '0', '', '小程序管理', '0', '0');
INSERT INTO `oao_menu` VALUES ('10', '渠道小程序', 'admin', 'mini', '3', '0', 'admin/mini/channel', '0', '', '小程序管理', '0', '0');
INSERT INTO `oao_menu` VALUES ('11', '用户列表', 'admin', 'user', '4', '0', 'admin/user/index', '0', '', '系统管理', '0', '0');
INSERT INTO `oao_menu` VALUES ('12', '用户组表', 'admin', 'users', '4', '0', 'admin/group/index', '0', '', '系统管理', '0', '0');
INSERT INTO `oao_menu` VALUES ('13', '菜单列表', 'admin', 'th-list', '4', '0', 'admin/menu/index', '0', '', '系统管理', '0', '0');
INSERT INTO `oao_menu` VALUES ('14', '权限列表', 'admin', 'paw', '4', '0', 'admin/group/access', '0', '', '系统管理', '0', '0');