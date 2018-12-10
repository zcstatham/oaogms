# noinspection SqlNoDataSourceInspectionForFile

-- --------------------------------------------------------

--
-- 表的结构 `oao_mini`
--

DROP TABLE IF EXISTS `oao_mini`;
CREATE TABLE `oao_mini` (
  `m_id` int(11) UNSIGNED NOT NULL PRIMARY KEY COMMENT '主键',
  `m_name` char(30) NOT NULL DEFAULT '' COMMENT '小程序名称',
  `m_type` tinyint(2) UNSIGNED NOT NULL DEFAULT '1' COMMENT '类型：1.小程序，2.小游戏',
  `m_appid` char(80) NOT NULL DEFAULT '' COMMENT '小程序appid',
  `m_sid` int(11) COMMENT '小程序渠道商id',
  `m_status` char(12) NOT NULL DEFAULT '' COMMENT '渠道小程序id,例：+mid 申请, mid已绑定,-mid:已解绑,null',
  `m_remark` char(140) NOT NULL DEFAULT '' COMMENT '小程序描述',
  `m_create_timestamp` DATETIME NOT NULL COMMENT '创建时间'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='小程序表' ROW_FORMAT=DYNAMIC;

--
-- 表的结构 `oao_sys_account`
--

DROP TABLE IF EXISTS `oao_sys_account`;
CREATE TABLE `oao_sys_account` (
  `s_id` int(11) UNSIGNED NOT NULL PRIMARY KEY COMMENT '用户ID',
  `s_username` varchar(32) NOT NULL DEFAULT '' COMMENT '用户名',
  `s_password` varchar(64) NOT NULL DEFAULT '' COMMENT '用户密码',
  `s_nickname` char(16) NOT NULL DEFAULT '' COMMENT '昵称',
  `s_email` char(100) DEFAULT NULL COMMENT '邮箱地址',
  `s_mobile` char(20) DEFAULT NULL COMMENT '手机号码',
  `s_salt` char(8) NOT NULL DEFAULT '' COMMENT '密码盐值',
  `s_login` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '登录次数',
  `s_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '账号状态',
  `s_create_timestamp` DATETIME NOT NULL COMMENT '注册时间'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='系统用户表';

--
-- 表的结构 `oao_mini_login_log`
--

DROP TABLE IF EXISTS `oao_mini_login_log`;
CREATE TABLE `oao_mini_login_log` (
  `l_id` int(11) UNSIGNED NOT NULL PRIMARY KEY COMMENT '主键',
  `l_user_id` int(11) UNSIGNED NOT NULL COMMENT '登录用户id',
  `l_action_ip` char(20) NOT NULL COMMENT '登录ip',
  `l_scene_id` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '登录场景id',
  `l_remark` varchar(255) NOT NULL DEFAULT '' COMMENT '日志备注',
  `l_create_timestamp` DATETIME NOT NULL COMMENT '登录时间'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='登录日志表' ROW_FORMAT=FIXED;

--
-- 表的结构 `oao_mini_action_log`
--

DROP TABLE IF EXISTS `oao_mini_action_log`;
CREATE TABLE `oao_mini_action_log` (
  `a_id` int(11) UNSIGNED NOT NULL PRIMARY KEY COMMENT '主键',
  `a_action_id` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '行为id',
  `a_user_id` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '执行用户id',
  `a_action_ip` char(20) NOT NULL COMMENT '执行行为者ip',
  `a_record_id` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '触发行为的appid',
  `a_remark` varchar(255) NOT NULL DEFAULT '' COMMENT '日志备注',
  `a_create_timestamp` DATETIME NOT NULL COMMENT '执行行为的时间'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='行为日志表' ROW_FORMAT=FIXED;

-- --------------------------------------------------------

--
-- 表的结构 `oao_user`
--

DROP TABLE IF EXISTS `oao_user`;
CREATE TABLE `oao_user` (
  `u_id` int(11) UNSIGNED NOT NULL PRIMARY KEY COMMENT '用户ID',
  `u_nickname` varchar(32) NOT NULL DEFAULT '' COMMENT '昵称',
  `u_avator` varchar(64) NOT NULL DEFAULT '' COMMENT '头像',
  `u_openid` char(16) NOT NULL DEFAULT '' COMMENT 'openid',
  `u_sex` tinyint(3) UNSIGNED NOT NULL DEFAULT '0' COMMENT '性别',
  `u_birthday` date NOT NULL DEFAULT '1917-01-01' COMMENT '生日',
  `u_login` int(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '登录次数',
  `u_reg_ip` char(20) NOT NULL DEFAULT '0' COMMENT '注册IP',
  `u_last_login_ip` char(20) NOT NULL DEFAULT '0' COMMENT '最后登录IP',
  `u_last_login_timestamp` DATETIME NOT NULL COMMENT '最后登录时间',
  `u_create_timestamp` DATETIME NOT NULL COMMENT '注册时间'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='小游戏用户表';

-- --------------------------------------------------------

--
-- 表的结构 `oao_user_extend`
--

DROP TABLE IF EXISTS `oao_user_extend`;
CREATE TABLE `oao_user_extend` (
  `ue_id` int(11) UNSIGNED NOT NULL PRIMARY KEY COMMENT '主键',
  `u_id` int(11) NOT NULL COMMENT '用户UID',
  `m_id` int(11) NOT NULL COMMENT '小程序UID',
  `ue_create_timestamp` DATETIME NOT NULL COMMENT '注册时间'
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='用户小程序关系';

-- --------------------------------------------------------

--
-- 使用表AUTO_INCREMENT `oao_mini`
--
ALTER TABLE `oao_mini`
  MODIFY `m_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键', AUTO_INCREMENT=2;
--
-- 使用表AUTO_INCREMENT `sent_action_log`
--
ALTER TABLE `oao_sys_account`
  MODIFY `s_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键', AUTO_INCREMENT=1;
--
-- 使用表AUTO_INCREMENT `sent_ad`
--
ALTER TABLE `oao_mini_login_log`
  MODIFY `l_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键', AUTO_INCREMENT=1;
--
-- 使用表AUTO_INCREMENT `sent_addons`
--
ALTER TABLE `oao_mini_action_log`
  MODIFY `a_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键', AUTO_INCREMENT=1;
--
-- 使用表AUTO_INCREMENT `sent_ad_place`
--
ALTER TABLE `oao_user`
  MODIFY `u_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '主键', AUTO_INCREMENT=1;
--
-- 使用表AUTO_INCREMENT `sent_attachment`
--
ALTER TABLE `oao_user_extend`
  MODIFY `ue_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT;