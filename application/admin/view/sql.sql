/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : oaogms

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2018-12-11 21:32:30
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for oao_mini
-- ----------------------------
DROP TABLE IF EXISTS `oao_mini`;
CREATE TABLE `oao_mini` (
  `m_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `m_name` char(30) NOT NULL DEFAULT '' COMMENT '小程序名称',
  `m_type` tinyint(2) unsigned NOT NULL DEFAULT '1' COMMENT '类型：1.小程序，2.小游戏',
  `m_appid` char(80) NOT NULL DEFAULT '' COMMENT '小程序appid',
  `m_sid` int(11) DEFAULT NULL COMMENT '小程序渠道商id',
  `m_status` char(12) NOT NULL DEFAULT '' COMMENT '渠道小程序id,例：+mid 申请, mid已绑定,-mid:已解绑,null',
  `m_remark` char(140) NOT NULL DEFAULT '' COMMENT '小程序描述',
  `m_create_timestamp` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`m_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='小程序表';

-- ----------------------------
-- Records of oao_mini
-- ----------------------------

-- ----------------------------
-- Table structure for oao_mini_action_log
-- ----------------------------
DROP TABLE IF EXISTS `oao_mini_action_log`;
CREATE TABLE `oao_mini_action_log` (
  `a_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `a_action_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '行为id',
  `a_user_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '执行用户id',
  `a_action_ip` char(20) NOT NULL COMMENT '执行行为者ip',
  `a_record_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '触发行为的appid',
  `a_remark` varchar(255) NOT NULL DEFAULT '' COMMENT '日志备注',
  `a_create_timestamp` datetime NOT NULL COMMENT '执行行为的时间',
  PRIMARY KEY (`a_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='行为日志表';

-- ----------------------------
-- Records of oao_mini_action_log
-- ----------------------------

-- ----------------------------
-- Table structure for oao_mini_login_log
-- ----------------------------
DROP TABLE IF EXISTS `oao_mini_login_log`;
CREATE TABLE `oao_mini_login_log` (
  `l_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `l_user_id` int(11) unsigned NOT NULL COMMENT '登录用户id',
  `l_login_ip` char(20) NOT NULL COMMENT '登录ip',
  `l_login_type` char(10) NOT NULL COMMENT 'wxScene: 微信场景，channel：渠道Id',
  `l_scene_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '登录场景id',
  `l_remark` varchar(255) NOT NULL DEFAULT '' COMMENT '日志备注',
  `l_create_timestamp` datetime NOT NULL COMMENT '登录时间',
  PRIMARY KEY (`l_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='登录日志表';

-- ----------------------------
-- Records of oao_mini_login_log
-- ----------------------------

-- ----------------------------
-- Table structure for oao_sys_account
-- ----------------------------
DROP TABLE IF EXISTS `oao_sys_account`;
CREATE TABLE `oao_sys_account` (
  `s_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `s_username` varchar(32) NOT NULL DEFAULT '' COMMENT '用户名',
  `s_password` varchar(64) NOT NULL DEFAULT '' COMMENT '用户密码',
  `s_nickname` char(16) NOT NULL DEFAULT '' COMMENT '昵称',
  `s_email` char(100) DEFAULT NULL COMMENT '邮箱地址',
  `s_mobile` char(20) DEFAULT NULL COMMENT '手机号码',
  `s_salt` char(8) NOT NULL DEFAULT '' COMMENT '密码盐值',
  `s_login` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '登录次数',
  `s_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '账号状态',
  `s_create_timestamp` datetime NOT NULL COMMENT '注册时间',
  PRIMARY KEY (`s_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='系统用户表';

-- ----------------------------
-- Records of oao_sys_account
-- ----------------------------

-- ----------------------------
-- Table structure for oao_user
-- ----------------------------
DROP TABLE IF EXISTS `oao_user`;
CREATE TABLE `oao_user` (
  `u_id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键',
  `u_nickname` varchar(32) NOT NULL DEFAULT '' COMMENT '昵称',
  `u_avator` varchar(64) NOT NULL DEFAULT '' COMMENT '头像',
  `u_openid` char(16) NOT NULL DEFAULT '' COMMENT 'openid',
  `u_sex` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '性别',
  `u_reg_ip` char(20) NOT NULL DEFAULT '0' COMMENT '注册IP',
  `u_last_login_ip` char(20) NOT NULL DEFAULT '0' COMMENT '最后登录IP',
  `u_last_login_timestamp` datetime NOT NULL COMMENT '最后登录时间',
  `u_create_timestamp` datetime NOT NULL COMMENT '注册时间',
  PRIMARY KEY (`u_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COMMENT='小游戏用户表';

-- ----------------------------
-- Records of oao_user
-- ----------------------------
INSERT INTO `oao_user` VALUES ('1', 'test', 'test', '123456', '0', '0', '1', '0000-00-00 00:00:00', '2018-12-12 16:12:27');

-- ----------------------------
-- Table structure for oao_user_extend
-- ----------------------------
DROP TABLE IF EXISTS `oao_user_extend`;
CREATE TABLE `oao_user_extend` (
  `ue_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `u_id` int(11) NOT NULL COMMENT '用户UID',
  `m_id` int(11) NOT NULL COMMENT '小程序UID',
  `ue_reg_ip` char(20) NOT NULL DEFAULT '0' COMMENT '注册IP',
  `ue_last_login_ip` char(20) NOT NULL DEFAULT '0' COMMENT '最后登录IP',
  `ue_last_login_timestamp` datetime NOT NULL COMMENT '最后登录时间',
  `ue_create_timestamp` datetime NOT NULL COMMENT '注册时间',
  PRIMARY KEY (`ue_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='用户小程序关系';

-- ----------------------------
-- Records of oao_user_extend
-- ----------------------------
INSERT INTO `oao_user_extend` VALUES ('1', '1', '1', '0', '1', '0000-00-00 00:00:00', '2018-12-11 16:13:00');
INSERT INTO `oao_user_extend` VALUES ('2', '1', '3', '0', '1', '0000-00-00 00:00:00', '2018-12-13 16:13:14');
