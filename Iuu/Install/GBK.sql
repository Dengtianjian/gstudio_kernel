DROP TABLE IF EXISTS `pre_gstudio_kernel_extensions`;

CREATE TABLE IF NOT EXISTS `pre_gstudio_kernel_extensions` (
  `id` int(12) NOT NULL AUTO_INCREMENT COMMENT '主键',
  `install_time` int(13) DEFAULT NULL COMMENT '安装时间',
  `upgrade_time` int(13) DEFAULT NULL COMMENT '更新时间',
  `local_version` varchar(20) DEFAULT NULL COMMENT '本地版本',
  `plugin_id` varchar(40) DEFAULT NULL COMMENT '所属插件id。kernel的是系统扩展',
  `extension_id` varchar(60) DEFAULT NULL COMMENT '扩展id',
  `enabled` tinyint(1) DEFAULT NULL COMMENT '已开启',
  `installed` tinyint(4) DEFAULT NULL COMMENT '已安装',
  `path` varchar(535) DEFAULT NULL COMMENT '扩展根路径',
  `parent_id` varchar(60) DEFAULT NULL COMMENT '父扩展ID',
  `created_time` int(13) DEFAULT NULL COMMENT '记录创建时间',
  `name` varchar(60) DEFAULT NULL COMMENT '扩展名称',
  PRIMARY KEY (`id`),
  UNIQUE KEY `extension_id` (`extension_id`),
  KEY `plugin_id` (`plugin_id`),
  KEY `parent_id` (`parent_id`)
) COMMENT = '扩展表';

-- ----------------------------
-- Table structure for pre_gstudio_kernel_logins
-- ----------------------------
DROP TABLE IF EXISTS `pre_gstudio_kernel_logins`;

CREATE TABLE `pre_gstudio_kernel_logins` (
  `id` varchar(26) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'id',
  `token` varchar(260) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'token值',
  `expiration` varchar(22) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '有效期至',
  `userId` varchar(26) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '所属用户',
  `appId` varchar(26) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '所属插件ID，如果为空即为通用',
  `createdAt` varchar(22) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '创建时间',
  `updatedAt` varchar(22) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '最后更新时间',
  `deletedAt` varchar(22) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '删除时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic COMMENT = '用户TOKEN表';

DROP TABLE IF EXISTS `pre_gstudio_kernel_wechat_users`;

CREATE TABLE `pre_gstudio_kernel_wechat_users` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `memberId` bigint(20) NOT NULL COMMENT '被绑定的会员ID',
  `openId` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'openId',
  `unionId` varchar(64) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'unionId',
  `phone` varchar(12) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '手机号码',
  `createdAt` varchar(12) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '创建时间',
  `updatedAt` varchar(12) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '最后更新时间',
  `deletedAt` varchar(12) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '软删除时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `memberId`(`memberId`) USING BTREE COMMENT '会员ID索引',
  INDEX `unionId`(`unionId`) USING BTREE COMMENT 'UnionId索引',
  INDEX `openId`(`openId`) USING BTREE COMMENT 'OpenId索引',
  INDEX `phone`(`phone`) USING BTREE COMMENT '手机号索引'
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic COMMENT = '微信用户表';