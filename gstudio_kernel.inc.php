<?php

if (!defined("IN_DISCUZ")) {
  exit("Access Denied");
}

if (!file_exists(DISCUZ_ROOT . "source/plugin/gstudio_kernel/Autoload.php")) {
  showmessage("Need to install the kernel plugin", null, [], [
    "alert" => "error"
  ]);
  exit;
}

include_once(DISCUZ_ROOT . "source/plugin/gstudio_kernel/Autoload.php");

use gstudio_kernel\App;
use gstudio_sitenav_bak\Middleware\GlobalQuickMenuMiddleware;
use gstudio_sitenav_bak\Middleware\MultipleTemplatesMiddleware;
use gstudio_sitenav_bak\Middleware as Middleware;

$app = new App("gstudio_kernel");
$app->init();