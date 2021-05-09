<?php

use gstudio_kernel\Foundation\Iuu;

if (!defined("IN_DISCUZ") || !defined('IN_ADMINCP')) {
  exit('Access Denied');
}

if (!file_exists(DISCUZ_ROOT . "source/plugin/gstudio_kernel/Autoload.php")) {
  showmessage("Need to install The Core Plugin", null, [], [
    "alert" => "error"
  ]);
  exit;
}

include_once(DISCUZ_ROOT . "source/plugin/gstudio_kernel/Autoload.php");

$Iuu = new Iuu("gstudio_kernel", $_GET['fromversion']);
$Iuu->install()->runInstallSql()->cleanInstall();

$finish = TRUE;