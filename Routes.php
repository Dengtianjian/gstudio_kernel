<?php

namespace gstudio_kernel;

use gstudio_kernel\Foundation\Router;
use gstudio_kernel\App\Api\GetGSetController;
use gstudio_kernel\App\Main as Main;

Router::get("_gset", GetGSetController::class);
Router::view("_download", Main\DownloadAttachmentView::class);

//* 扩展相关
Router::view("_extensions", Main\Extensions\ExtensionListViewController::class);
Router::post("_extension/install", Main\Extensions\InstallExtensionController::class);
Router::post("_extension/upgrade", Main\Extensions\UpgradeExtensionController::class);
Router::post("_extension/uninstall", Main\Extensions\UninstallExtensionController::class);
Router::post("_extension/openClose", Main\Extensions\OpenCloseExtensionController::class);
