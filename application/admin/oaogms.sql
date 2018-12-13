/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : oaogms

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2018-12-12 19:06:39
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for oao_auth_group
-- ----------------------------
DROP TABLE IF EXISTS `oao_auth_group`;
CREATE TABLE `oao_auth_group` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `title` char(100) NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `rules` char(80) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for oao_auth_group_access
-- ----------------------------
DROP TABLE IF EXISTS `oao_auth_group_access`;
CREATE TABLE `oao_auth_group_access` (
  `uid` mediumint(8) unsigned NOT NULL,
  `group_id` mediumint(8) unsigned NOT NULL,
  UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  KEY `uid` (`uid`),
  KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for oao_auth_rule
-- ----------------------------
DROP TABLE IF EXISTS `oao_auth_rule`;
CREATE TABLE `oao_auth_rule` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` char(80) NOT NULL DEFAULT '',
  `title` char(20) NOT NULL DEFAULT '',
  `type` tinyint(1) NOT NULL DEFAULT '1',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `condition` char(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

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
  `actionid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '行为id',
  `userid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '执行用户id',
  `actionip` char(20) NOT NULL COMMENT '执行行为者ip',
  `recordid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '触发行为的appid',
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
  `sceneid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '登录场景id',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '日志备注',
  `login_type` char(10) NOT NULL COMMENT 'wxScene: 微信场景，channel：渠道Id',
  `login_ip` char(20) NOT NULL COMMENT '登录ip',
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
  `login_times` int(11) NOT NULL DEFAULT 0 COMMENT '登录次数',
  `type` tinyint(4) NOT NULL DEFAULT 0 COMMENT '用户类型：0 普通，1 渠道，2 运营，3 管理',
  `status` tinyint(4) NOT NULL DEFAULT 0 COMMENT '账号状态',
  `last_login_ip` datetime NOT NULL COMMENT '最后登录ip',
  `last_login_timestamp` datetime NOT NULL COMMENT '最后登录时间',
  `create_timestamp` datetime NOT NULL COMMENT '注册时间',
  PRIMARY KEY (`sid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='系统用户表';

-- ----------------------------
-- Table structure for oao_user
-- ----------------------------
DROP TABLE IF EXISTS `oao_user`;
CREATE TABLE `oao_user` (
  `uid` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `nickname` varchar(32) NOT NULL DEFAULT '' COMMENT '昵称',
  `avator` varchar(64) NOT NULL DEFAULT '' COMMENT '头像',
  `oppenid` char(16) NOT NULL DEFAULT '' COMMENT 'oppen_id',
  `sex` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '性别',
  `reg_ip` char(20) NOT NULL DEFAULT '0' COMMENT '注册IP',
  `last_login_ip` char(20) NOT NULL DEFAULT '0' COMMENT '最后登录IP',
  `last_login_timestamp` datetime NOT NULL COMMENT '最后登录时间',
  `create_timestamp` datetime NOT NULL COMMENT '注册时间',
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='小游戏用户表';

-- ----------------------------
-- Table structure for oao_user_extend
-- ----------------------------
DROP TABLE IF EXISTS `oao_user_extend`;
CREATE TABLE `oao_user_extend` (
  `eid` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户UID',
  `mid` int(11) NOT NULL COMMENT '小程序UID',
  `reg_ip` char(20) NOT NULL DEFAULT '0' COMMENT '注册IP',
  `last_login_ip` char(20) NOT NULL DEFAULT '0' COMMENT '最后登录IP',
  `last_login_timestamp` datetime NOT NULL COMMENT '最后登录时间',
  `create_timestamp` datetime NOT NULL COMMENT '注册时间',
  PRIMARY KEY (`eid`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COMMENT='用户小程序关系';
