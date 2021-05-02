/*
 Navicat Premium Data Transfer

 Source Server         : mysql
 Source Server Type    : MySQL
 Source Server Version : 50729
 Source Host           : localhost:3306
 Source Schema         : ccjcw

 Target Server Type    : MySQL
 Target Server Version : 50729
 File Encoding         : 65001

 Date: 30/04/2021 20:14:37
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

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
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='TOOL应用';

-- ----------------------------
-- Records of @pf_tool_app
-- ----------------------------
BEGIN;
INSERT INTO `@pf_tool_app` VALUES (1, '系统后台', 'app\\admin', 'admin', 1, '', '', 0, '', '', '', '', '');
COMMIT;

-- ----------------------------
-- Table structure for @pf_tool_field
-- ----------------------------
DROP TABLE IF EXISTS `@pf_tool_field`;
CREATE TABLE `@pf_tool_field` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `formId` int(11) NOT NULL DEFAULT '0' COMMENT '表单ID',
  `name` varchar(255) DEFAULT NULL,
  `tabIndex` varchar(255) DEFAULT NULL,
  `label` varchar(255) DEFAULT NULL,
  `boxName` text,
  `type` varchar(255) DEFAULT NULL,
  `hidden` tinyint(1) DEFAULT NULL,
  `dbField` tinyint(1) DEFAULT NULL,
  `dbType` varchar(255) DEFAULT NULL,
  `dbLen` int(11) DEFAULT NULL,
  `dbPoint` varchar(500) DEFAULT NULL,
  `dbComment` varchar(255) DEFAULT NULL,
  `dbDefType` varchar(255) DEFAULT NULL,
  `dbDefValue` varchar(255) DEFAULT NULL,
  `dbUnique` tinyint(1) DEFAULT NULL,
  `before` varchar(255) DEFAULT NULL,
  `after` varchar(255) DEFAULT NULL,
  `sort` int(11) DEFAULT NULL,
  `default` text,
  `viewMerge` int(11) DEFAULT NULL,
  `close` tinyint(1) DEFAULT NULL,
  `viewClose` tinyint(1) DEFAULT NULL,
  `offEdit` tinyint(1) DEFAULT NULL,
  `extend` text,
  `attrClass` text,
  `attrStyle` text,
  `attrPlaceholder` text,
  `attrs` json DEFAULT NULL,
  `prompt` text,
  `dynamic` json DEFAULT NULL,
  `names` json DEFAULT NULL,
  `validRule` text,
  `validGroup` json DEFAULT NULL,
  `validDefault` text,
  `validCorrect` text,
  `validDisplay` varchar(255) DEFAULT NULL,
  `validDisabled` tinyint(1) DEFAULT NULL,
  `star` tinyint(4) DEFAULT NULL,
  `validFunc` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='TOOL字段数据';

-- ----------------------------
-- Records of @pf_tool_field
-- ----------------------------
BEGIN;
COMMIT;

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
  `viewBtns` json DEFAULT NULL,
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
  `plugStyle` int(11) DEFAULT NULL,
  `useWrap` tinyint(1) DEFAULT '0',
  `makeStatic` tinyint(11) DEFAULT NULL,
  `baseLayout` varchar(255) DEFAULT NULL,
  `withTpl` tinyint(1) DEFAULT NULL,
  `withForm` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='TOOL表单数据';

-- ----------------------------
-- Records of @pf_tool_form
-- ----------------------------
BEGIN;
COMMIT;

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
  `listRewrite` tinyint(1) DEFAULT NULL,
  `usePageList` tinyint(1) DEFAULT NULL,
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
  `selectType` varchar(255) DEFAULT NULL,
  `selectButtons` text,
  `searchTop` text,
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
  `selectStyle` varchar(255) DEFAULT NULL,
  `selectValue` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='TOOL列表数据';

-- ----------------------------
-- Records of @pf_tool_list
-- ----------------------------
BEGIN;
COMMIT;

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
  `hidden` tinyint(1) DEFAULT NULL,
  `before` varchar(255) DEFAULT NULL,
  `after` varchar(255) DEFAULT NULL,
  `sort` int(11) DEFAULT NULL,
  `viewMerge` int(11) DEFAULT NULL,
  `default` text,
  `extend` text,
  `attrPlaceholder` text,
  `attrClass` text,
  `attrStyle` text,
  `attrs` text,
  `names` text,
  `tabIndex` varchar(255) DEFAULT NULL,
  `tbWhere` text,
  `tbWhereType` int(11) DEFAULT NULL,
  `varType` varchar(255) DEFAULT '',
  `close` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='TOOL搜索数据';

-- ----------------------------
-- Records of @pf_tool_search
-- ----------------------------
BEGIN;
COMMIT;

SET FOREIGN_KEY_CHECKS = 1;