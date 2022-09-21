<?php

namespace gstudio_kernel;

use gstudio_kernel\Foundation\Router;
use gstudio_kernel\App\Main as Main;

Router::get("_download", Main\DownloadAttachmentView::class);

//* 扩展相关
Router::post("_extension/install", Main\Extensions\InstallExtensionController::class);
Router::post("_extension/upgrade", Main\Extensions\UpgradeExtensionController::class);
Router::post("_extension/uninstall", Main\Extensions\UninstallExtensionController::class);
Router::post("_extension/openClose", Main\Extensions\OpenCloseExtensionController::class);
