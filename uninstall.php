<?php

if (!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
  exit('Access Denied');
}

$sql = <<<SQL
DROP TABLE IF EXISTS `pre_gstudio_kernel_extensions`;
SQL;

runquery($sql);

// TODO 删除插件遗留的相关文件夹
include_once libfile("function/cloudaddons");
cloudaddons_deltree(DISCUZ_ROOT . "data/attachment/plugin/gstudio_kernel");

$finish = TRUE;
