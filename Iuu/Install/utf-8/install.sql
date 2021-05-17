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
) COMMENT = '扩展表'