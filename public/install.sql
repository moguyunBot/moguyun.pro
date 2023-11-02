
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for admin
-- ----------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '编号',
  `username` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '用户名',
  `password` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '密码',
  `salt` char(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '密码盐',
  `login_fail_num` int(11) NOT NULL DEFAULT 0 COMMENT '登录失败次数',
  `last_login_ip` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '最后一次登录ip',
  `this_login_ip` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '当前登录ip',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
  `group_id` int(11) NOT NULL COMMENT '用户组',
  `create_time` int(11) NOT NULL COMMENT '添加时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  `google_secret` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '谷歌验证器秘钥',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `username`(`username`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of admin
-- ----------------------------
INSERT INTO `admin` VALUES (1, 'admin', '0e35b2f9d520c6f3dabfa7ec5ba19cbf', '5unXeV4rRGdMPK6JQ0lqomz1DZgs9Fv8', 0, '', '', 1, 1, 1, 1693125289, '');

-- ----------------------------
-- Table structure for group
-- ----------------------------
DROP TABLE IF EXISTS `group`;
CREATE TABLE `group`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '角色名称',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态',
  `create_time` int(11) NOT NULL COMMENT '添加时间',
  `update_time` int(11) NOT NULL COMMENT '更新时间',
  `rules` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `url` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL COMMENT '跳转节点',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of group
-- ----------------------------
INSERT INTO `group` VALUES (1, '创始人', 1, 0, 0, '*', NULL);
INSERT INTO `group` VALUES (2, '管理员', 1, 1682320572, 1698684599, '20,129,172,185,182', 'index/index');

-- ----------------------------
-- Table structure for rule
-- ----------------------------
DROP TABLE IF EXISTS `rule`;
CREATE TABLE `rule`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL,
  `title` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `icon` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `uri` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  `sort` int(11) NOT NULL DEFAULT 0,
  `is_menu` tinyint(1) NOT NULL DEFAULT 1,
  `options` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL COMMENT '扩展参数',
  `addon_name` varchar(128) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '' COMMENT '所属插件',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 192 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of rule
-- ----------------------------
INSERT INTO `rule` VALUES (2, 0, '权限管理', 'mdi mdi-account-group', '', 1, 1659936966, 1696873161, 0, 1, '[{\"key\":\"\",\"value\":\"\"}]', '');
INSERT INTO `rule` VALUES (3, 2, '管理员', '', 'admin/index', 1, 1659936987, 1696873166, 0, 1, '[{\"key\":\"\",\"value\":\"\"}]', '');
INSERT INTO `rule` VALUES (4, 2, '节点管理', '', 'rule/index', 1, 1659937034, 1698559157, 0, 1, '[]', '');
INSERT INTO `rule` VALUES (5, 2, '角色管理', '', 'group/index', 1, 1659937047, 1659937056, 0, 1, '[{\"key\":\"\",\"value\":\"\"}]', '');
INSERT INTO `rule` VALUES (7, 4, '添加节点', '', 'rule/add', 1, 1659937146, 1659937146, 0, 0, '[{\"key\":\"\",\"value\":\"\"}]', '');
INSERT INTO `rule` VALUES (8, 4, '修改节点', '', 'rule/edit', 1, 1659937155, 1659937155, 0, 0, '[{\"key\":\"\",\"value\":\"\"}]', '');
INSERT INTO `rule` VALUES (9, 4, '删除节点', '', 'rule/del', 1, 1659937163, 1659937163, 0, 0, '[{\"key\":\"\",\"value\":\"\"}]', '');
INSERT INTO `rule` VALUES (11, 5, '添加角色', '', 'group/add', 1, 1659937239, 1659937239, 0, 0, '[{\"key\":\"\",\"value\":\"\"}]', '');
INSERT INTO `rule` VALUES (12, 5, '修改角色', '', 'group/edit', 1, 1659937248, 1659937248, 0, 0, '[{\"key\":\"\",\"value\":\"\"}]', '');
INSERT INTO `rule` VALUES (13, 5, '删除角色', '', 'group/del', 1, 1659937257, 1659937257, 0, 0, '[{\"key\":\"\",\"value\":\"\"}]', '');
INSERT INTO `rule` VALUES (15, 3, '添加管理员', '', 'admin/add', 1, 1659937283, 1659937283, 0, 0, '[{\"key\":\"\",\"value\":\"\"}]', '');
INSERT INTO `rule` VALUES (16, 3, '修改管理员', '', 'admin/edit', 1, 1659937292, 1659937292, 0, 0, '[{\"key\":\"\",\"value\":\"\"}]', '');
INSERT INTO `rule` VALUES (17, 3, '删除管理员', '', 'admin/del', 1, 1659937301, 1659937301, 0, 0, '[{\"key\":\"\",\"value\":\"\"}]', '');
INSERT INTO `rule` VALUES (20, 0, '系统设置', 'mdi-settings', 'config/index', 1, 1659938689, 1698586638, -1, 1, '[{\"key\":\"key\",\"value\":\"basic\"}]', '');
INSERT INTO `rule` VALUES (129, 0, '用户管理', 'mdi mdi-account-box-multiple-outline', 'user/index', 1, 1683797696, 1693124803, 0, 1, '[{\"key\":\"\",\"value\":\"\"}]', '');
INSERT INTO `rule` VALUES (172, 0, '应用中心', 'mdi-apps', 'addon/index', 1, 1698579985, 1698586874, 0, 1, '[]', '');
INSERT INTO `rule` VALUES (185, 172, '应用入口', '', 'addon/entrance', 1, 1698684529, 1698684572, 0, 0, '[]', '');

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nickname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `username` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `balance` decimal(20, 2) NOT NULL DEFAULT 0.00,
  `from_id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `create_time` int(11) NOT NULL,
  `update_time` int(11) NOT NULL,
  `pid` int(11) NOT NULL DEFAULT 0 COMMENT '推荐人',
  `invitation` char(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`, `from_id`) USING BTREE,
  UNIQUE INDEX `from_id`(`from_id`) USING BTREE,
  UNIQUE INDEX `invitation`(`invitation`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES (3, '机器人开发', 'bot_kf', 0.00, '6431449029', 1697045038, 1697045038, 0, 'jrShXGq6eM', 1);

SET FOREIGN_KEY_CHECKS = 1;
