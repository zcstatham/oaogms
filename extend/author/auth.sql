-- ----------------------------
-- oao_auth_rule，规则表，
-- ----------------------------
DROP TABLE IF EXISTS `oao_auth_rule`;
CREATE TABLE `oao_auth_rule` (
  `id` mediumint(8) UNSIGNED NOT NULL COMMENT '规则id,自增主键',
  `module` varchar(20) NOT NULL COMMENT '规则所属module',
  `type` tinyint(2) NOT NULL DEFAULT '1' COMMENT '1-url;2-主菜单',
  `name` char(80) NOT NULL DEFAULT '' COMMENT '规则唯一英文标识',
  `title` char(20) NOT NULL DEFAULT '' COMMENT '规则中文描述',
  `group` char(20) NOT NULL DEFAULT '' COMMENT '权限节点分组',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否有效(0:无效,1:有效)',
  `condition` varchar(300) NOT NULL DEFAULT '' COMMENT '规则附加条件'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- oao_auth_group 用户组表，
-- ----------------------------
DROP TABLE IF EXISTS `oao_auth_group`;
CREATE TABLE `oao_auth_group` (
  `id` mediumint(8) UNSIGNED NOT NULL COMMENT '用户组id,自增主键',
  `module` varchar(20) NOT NULL DEFAULT '' COMMENT '用户组所属模块',
  `type` varchar(10) NOT NULL DEFAULT '' COMMENT '组类型',
  `title` char(20) NOT NULL DEFAULT '' COMMENT '用户组中文名称',
  `description` varchar(80) NOT NULL DEFAULT '' COMMENT '描述信息',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '用户组状态：为1正常，为0禁用,-1为删除',
  `rules` varchar(500) NOT NULL DEFAULT '' COMMENT '用户组拥有的规则id，多个规则 , 隔开'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- ----------------------------
-- oao_auth_group_access 用户组明细表
-- ----------------------------

DROP TABLE IF EXISTS `oao_auth_group_access`;
CREATE TABLE `oao_auth_group_access` (
  `uid` int(10) UNSIGNED NOT NULL COMMENT '用户id',
  `group_id` mediumint(8) UNSIGNED NOT NULL COMMENT '用户组id'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


--
-- Indexes for table `oao_auth_group`
--
ALTER TABLE `oao_auth_group`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `oao_auth_group_access`
--
ALTER TABLE `oao_auth_group_access`
  ADD UNIQUE KEY `uid_group_id` (`uid`,`group_id`),
  ADD KEY `uid` (`uid`),
  ADD KEY `group_id` (`group_id`);

--
-- Indexes for table `oao_auth_rule`
--
ALTER TABLE `oao_auth_rule`
  ADD PRIMARY KEY (`id`),
  ADD KEY `module` (`module`,`name`,`status`,`type`);


--
-- 使用表AUTO_INCREMENT `oao_auth_group`
--
ALTER TABLE `oao_auth_group`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '用户组id,自增主键', AUTO_INCREMENT=1;
--
-- 使用表AUTO_INCREMENT `oao_auth_rule`
--
ALTER TABLE `oao_auth_rule`
  MODIFY `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '规则id,自增主键', AUTO_INCREMENT=1;