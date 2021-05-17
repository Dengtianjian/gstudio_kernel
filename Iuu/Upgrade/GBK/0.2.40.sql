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
) COMMENT = '��չ��'