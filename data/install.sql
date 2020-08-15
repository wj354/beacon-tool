DROP TABLE IF EXISTS `@pf_manage`;
CREATE TABLE `@pf_manage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '用户名',
  `pwd` varchar(255) DEFAULT NULL COMMENT '用户密码',
  `realName` varchar(255) DEFAULT NULL,
  `type` int(11) DEFAULT '0' COMMENT '管理员类型',
  `errTice` int(11) DEFAULT '0' COMMENT '错误次数',
  `errTime` date DEFAULT NULL COMMENT '错误时间',
  `thisTime` datetime DEFAULT NULL COMMENT '本次登录时间',
  `lastTime` datetime DEFAULT NULL COMMENT '最后登录时间',
  `thisIp` varchar(255) DEFAULT NULL COMMENT '本次登录IP',
  `lastIp` varchar(255) DEFAULT NULL COMMENT '最后一次登录IP',
  `isLock` int(1) DEFAULT '0' COMMENT '是否锁定账号',
  `email` varchar(255) DEFAULT NULL COMMENT '管理员邮箱',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of @pf_manage
-- ----------------------------
INSERT INTO `@pf_manage` VALUES ('1', 'admin', 'e10adc3949ba59abbe56e057f20f883e', 'wj008', '1', '0', '1999-01-01', '2018-12-06 21:30:31', '2018-12-06 21:30:19', '127.0.0.1', '127.0.0.1', '0', 'admin');

-- ----------------------------
-- Table structure for @pf_sys_menu
-- ----------------------------
DROP TABLE IF EXISTS `@pf_sys_menu`;
CREATE TABLE `@pf_sys_menu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '菜单标题',
  `allow` int(1) DEFAULT '0' COMMENT '是否启用',
  `pid` varchar(255) DEFAULT NULL COMMENT '所属上级菜单',
  `show` int(1) DEFAULT '0' COMMENT '是否展开',
  `url` varchar(255) DEFAULT NULL COMMENT '栏目路径',
  `sort` int(11) DEFAULT '0' COMMENT '排序',
  `remark` text COMMENT '备注',
  `icon` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of @pf_sys_menu
-- ----------------------------
INSERT INTO `@pf_sys_menu` VALUES ('1', '首页', '1', '0', '1', '', '10', '', '');
INSERT INTO `@pf_sys_menu` VALUES ('2', '系统账号管理', '1', '1', '1', '', '0', '', 'icofont-teacher');
INSERT INTO `@pf_sys_menu` VALUES ('3', '管理员管理', '1', '2', '1', '~/Manage', '12', null, null);
INSERT INTO `@pf_sys_menu` VALUES ('4', '修改管理密码', '1', '2', '1', '~/Manage/password', '0', '', '');
INSERT INTO `@pf_sys_menu` VALUES ('5', '网站信息管理', '1', '1', '1', '', '20', '', 'icofont-navigation-menu');
INSERT INTO `@pf_sys_menu` VALUES ('6', '系统菜单', '1', '0', '1', null, '400', null, null);
INSERT INTO `@pf_sys_menu` VALUES ('7', '工具箱', '1', '6', '1', '', '0', '', 'icofont-tools-alt-2');
INSERT INTO `@pf_sys_menu` VALUES ('8', '系统菜单管理', '1', '7', '1', '~/SysMenu', '50', null, null);
INSERT INTO `@pf_sys_menu` VALUES ('10', '项目管理', '1', '7', '0', '^/tool/index', '1', '', '');
INSERT INTO `@pf_sys_menu` VALUES ('11', '表单模型', '1', '7', '0', '^/tool/form', '2', '', '');
INSERT INTO `@pf_sys_menu` VALUES ('12', '列表模型', '1', '7', '0', '^/tool/lists', '3', '', '');

-- ----------------------------
-- Table structure for @pf_tool_app
-- ----------------------------
DROP TABLE IF EXISTS `@pf_tool_app`;
CREATE TABLE `@pf_tool_app` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) DEFAULT NULL,
    `namespace` varchar(255) DEFAULT NULL,
    `module` varchar(255) DEFAULT NULL,
    `isDefault` tinyint(1) DEFAULT '0',
    `dirName` varchar(255) DEFAULT NULL,
    `db_host` varchar(255) DEFAULT NULL,
    `db_port` int(11) DEFAULT NULL,
    `db_name` varchar(255) DEFAULT NULL,
    `db_user` varchar(255) DEFAULT NULL,
    `db_pwd` varchar(255) DEFAULT NULL,
    `db_prefix` varchar(255) DEFAULT NULL,
    `db_charset` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;


-- ----------------------------
-- Records of @pf_tool_app
-- ----------------------------
INSERT INTO `@pf_tool_app` VALUES ('1', '系统后台', 'app\\admin', 'admin', '1');

-- ----------------------------
-- Table structure for @pf_tool_field
-- ----------------------------
DROP TABLE IF EXISTS `@pf_tool_field`;
CREATE TABLE `@pf_tool_field` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `formId` int(11) NOT NULL DEFAULT '0' COMMENT '表单ID',
  `name` varchar(255) DEFAULT NULL,
  `label` varchar(255) DEFAULT NULL,
  `boxName` text,
  `type` varchar(255) DEFAULT NULL,
  `hideBox` tinyint(1) DEFAULT NULL,
  `dbfield` tinyint(1) DEFAULT NULL,
  `dbtype` varchar(255) DEFAULT NULL,
  `dblen` int(11) DEFAULT NULL,
  `dbpoint` varchar(500) DEFAULT NULL,
  `dbcomment` varchar(255) DEFAULT NULL,
  `db_def1` varchar(255) DEFAULT NULL,
  `db_def2` varchar(255) DEFAULT NULL,
  `beforeText` varchar(255) DEFAULT NULL,
  `afterText` varchar(255) DEFAULT NULL,
  `sort` int(11) DEFAULT NULL,
  `viewMerge` int(11) DEFAULT NULL,
  `close` tinyint(1) DEFAULT NULL,
  `viewClose` tinyint(1) DEFAULT NULL,
  `viewHide` tinyint(1) DEFAULT NULL,
  `offEdit` tinyint(1) DEFAULT NULL,
  `forceDefault` tinyint(1) DEFAULT NULL,
  `default` text,
  `extend` text,
  `custom` text,
  `dynamic` text,
  `boxPlaceholder` text,
  `boxClass` text,
  `boxStyle` text,
  `boxAttrs` text,
  `tips` text,
  `viewTemplate` varchar(255) DEFAULT NULL,
  `viewAsterisk` tinyint(4) DEFAULT NULL,
  `dataValRule` text,
  `dataValMessage` text,
  `dataValGroup` text,
  `dataValDefault` text,
  `dataValCorrect` text,
  `dataValOutput` varchar(255) DEFAULT NULL,
  `dataValDisabled` tinyint(1) DEFAULT NULL,
  `names` text,
  `tabIndex` varchar(255) DEFAULT NULL,
  `valueFunc` varchar(255) DEFAULT NULL,
  `validFunc` varchar(255) DEFAULT NULL,
  `unique` tinyint(1) DEFAULT 0,
  `remoteUrl` varchar(255) DEFAULT NULL,
  `remoteError` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
-- ----------------------------
-- Table structure for @pf_tool_form
-- ----------------------------
DROP TABLE IF EXISTS `@pf_tool_form`;
CREATE TABLE `@pf_tool_form` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appId` int(11) DEFAULT NULL,
  `namespace` varchar(255) DEFAULT NULL,
  `key` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `tbName` varchar(255) DEFAULT NULL,
  `extMode` int(11) DEFAULT NULL,
  `plugMode` varchar(10) DEFAULT NULL,
  `tbEngine` varchar(255) DEFAULT NULL,
  `tbCreate` tinyint(1) DEFAULT NULL,
  `version` int(255) DEFAULT NULL,
  `extType` int(11) DEFAULT NULL,
  `extTbname` varchar(255) DEFAULT NULL,
  `extFields` text,
  `useAjax` tinyint(4) DEFAULT NULL,
  `viewNotBack` tinyint(1) DEFAULT NULL,
  `validateMode` int(11) DEFAULT NULL,
  `template` varchar(255) DEFAULT NULL,
  `viewUseTab` tinyint(1) DEFAULT NULL,
  `viewTabs` text,
  `caption` text,
  `description` text,
  `information` text,
  `attention` text,
  `head` text,
  `script` text,
  `updateTime` int(11) DEFAULT NULL,
  `notSingleWrap` tinyint(1) DEFAULT NULL,
  `notMultipleWrap` tinyint(1) DEFAULT NULL,
  `plugStyle` int(11) DEFAULT NULL,
  `makeStatic` tinyint(11) DEFAULT NULL,
  `baseLayout` varchar(255) DEFAULT NULL,
  `withTpl` tinyint(1) DEFAULT NULL,
  `withForm` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
-- ----------------------------
-- Table structure for @pf_tool_list
-- ----------------------------
DROP TABLE IF EXISTS `@pf_tool_list`;
CREATE TABLE `@pf_tool_list` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `namespace` varchar(255) DEFAULT NULL,
  `key` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `caption` text,
  `formId` int(11) DEFAULT NULL,
  `appId` int(11) DEFAULT NULL,
  `fields` text,
  `orgFields` text,
  `listResize` tinyint(1) DEFAULT NULL,
  `usePageList` tinyint(1) DEFAULT NULL,
  `listRewrite` tinyint(1) DEFAULT NULL,
  `pageSize` int(11) DEFAULT NULL,
  `baseController` varchar(255) DEFAULT NULL,
  `useCustomTemplate` tinyint(1) DEFAULT NULL,
  `template` varchar(255) DEFAULT NULL,
  `templateHook` varchar(255) DEFAULT NULL,
  `baseLayout` varchar(255) DEFAULT NULL,
  `tbName` varchar(255) DEFAULT NULL,
  `tbAlias` varchar(255) DEFAULT NULL,
  `tbJoin` text,
  `tbField` text,
  `tbWhere` text,
  `tbOrder` text,
  `useSqlTemplate` tinyint(1) DEFAULT NULL,
  `sqlTemplate` text,
  `sqlCountTemplate` text,
  `actions` text,
  `topButtons` text,
  `thTitle` varchar(255) DEFAULT NULL,
  `thAlign` varchar(255) DEFAULT NULL,
  `thWidth` varchar(255) DEFAULT NULL,
  `thAttrs` text,
  `thOpName` varchar(255) DEFAULT NULL,
  `tdAlign` varchar(255) DEFAULT NULL,
  `tdAttrs` text,
  `listButtons` text,
  `useSelect` tinyint(4) DEFAULT NULL,
  `selectValue` varchar(255) DEFAULT NULL,
  `selectType` varchar(255) DEFAULT NULL,
  `selectStyle` varchar(255) DEFAULT NULL,
  `selectButtons` text,
  `searchTop`  text,
  `headTemplate` text,
  `footTemplate` text,
  `information` text,
  `attention` text,
  `assign` text,
  `viewTabs` text,
  `viewTabRight` text,
  `viewUseTab` tinyint(1) DEFAULT NULL,
  `fixed` tinyint(1) DEFAULT NULL,
  `leftFixed` int(11) DEFAULT NULL,
  `rightFixed` int(11) DEFAULT NULL,
  `useTwoLine` tinyint(1) DEFAULT NULL,
  `updateTime` int(11) DEFAULT NULL,
  `withTpl` tinyint(1) DEFAULT NULL,
  `withCtl` tinyint(1) DEFAULT NULL,
  `withSearch` tinyint(1) DEFAULT NULL,
  `renderMode` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for @pf_tool_search
-- ----------------------------
DROP TABLE IF EXISTS `@pf_tool_search`;
CREATE TABLE `@pf_tool_search` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `listId` int(11) NOT NULL DEFAULT '0' COMMENT '表单ID',
  `name` varchar(255) DEFAULT NULL,
  `label` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `hideBox` tinyint(1) DEFAULT NULL,
  `beforeText` varchar(255) DEFAULT NULL,
  `afterText` varchar(255) DEFAULT NULL,
  `sort` int(11) DEFAULT NULL,
  `viewMerge` int(11) DEFAULT NULL,
  `default` text,
  `forceDefault` tinyint(1) DEFAULT NULL,
  `extend` text,
  `custom` text,
  `boxPlaceholder` text,
  `boxClass` text,
  `boxStyle` text,
  `boxAttrs` text,
  `names` text,
  `tabIndex` varchar(255) DEFAULT NULL,
  `tbWhere` text,
  `tbWhereType` int(11) DEFAULT NULL,
  `varType` varchar(255) DEFAULT '',
  `close` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
