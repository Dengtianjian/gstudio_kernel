DROP TABLE IF EXISTS `pre_gstudio_kernel_extensions`;
CREATE TABLE IF NOT EXISTS `pre_gstudio_kernel_extensions` (
  `id` int(12) NOT NULL AUTO_INCREMENT COMMENT '����',
  `install_time` int(13) DEFAULT NULL COMMENT '��װʱ��',
  `upgrade_time` int(13) DEFAULT NULL COMMENT '����ʱ��',
  `local_version` varchar(20) DEFAULT NULL COMMENT '���ذ汾',
  `plugin_id` varchar(40) DEFAULT NULL COMMENT '�������id��kernel����ϵͳ��չ',
  `extension_id` varchar(60) DEFAULT NULL COMMENT '��չid',
  `enabled` tinyint(1) DEFAULT NULL COMMENT '�ѿ���',
  `installed` tinyint(4) DEFAULT NULL COMMENT '�Ѱ�װ',
  `path` varchar(535) DEFAULT NULL COMMENT '��չ��·��',
  `parent_id` varchar(60) DEFAULT NULL COMMENT '����չID',
  `created_time` int(13) DEFAULT NULL COMMENT '��¼����ʱ��',
  `name` varchar(60) DEFAULT NULL COMMENT '��չ����',
  PRIMARY KEY (`id`),
  UNIQUE KEY `extension_id` (`extension_id`),
  KEY `plugin_id` (`plugin_id`),
  KEY `parent_id` (`parent_id`)
) COMMENT = '��չ��';

-- ----------------------------
-- Table structure for pre_gstudio_kernel_logins
-- ----------------------------
DROP TABLE IF EXISTS `pre_gstudio_kernel_logins`;
CREATE TABLE `pre_gstudio_kernel_logins`  (
  `id` varchar(26) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT 'id',
  `token` varchar(260) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'tokenֵ',
  `expiration` varchar(22) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '��Ч����',
  `userId` varchar(26) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '�����û�',
  `appId` varchar(26) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '�������ID�����Ϊ�ռ�Ϊͨ��',
  `createdAt` varchar(22) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '����ʱ��',
  `updatedAt` varchar(22) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '������ʱ��',
  `deletedAt` varchar(22) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT 'ɾ��ʱ��',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic COMMENT = '�û�TOKEN��';