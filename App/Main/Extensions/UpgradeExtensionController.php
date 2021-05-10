<?php

namespace gstudio_kernel\App\Main\Extensions;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

use gstudio_kernel\Foundation\Request;
use gstudio_kernel\Foundation\Controller;
use gstudio_kernel\Foundation\Extension\ExtensionIuu;
use gstudio_kernel\Foundation\Extension\Extensions;
use gstudio_kernel\Foundation\Response;
use gstudio_kernel\Model\ExtensionsModel;

class UpgradeExtensionController extends Controller
{
  public function data(Request $request)
  {
    $extensionId = \addslashes($request->params("extension_id"));
    $EM = new ExtensionsModel();
    $extension = $EM->getByExtensionId($extensionId);
    if (empty($extension)) {
      Response::error(404, 404001, "扩展不存在");
    }
    $extension = $extension[0];
    $extensionConfig = Extensions::config($extension['extension_id']);
    if (\version_compare($extension['local_version'], $extensionConfig['version']) !== -1) {
      Response::error(400, 400001, "扩展已是最新版，无需升级");
    }

    $ext = new ExtensionIuu($extension['plugin_id'], $extension['extension_id'], NULL);
    $ext->upgrade()->runUpgradeSql()->cleanUpgrade();
    $EM->where("extension_id", $extension['extension_id'])->where("plugin_id", $extension['plugin_id'])->update([
      "upgrade_time" => time(),
      "local_version" => $extensionConfig['version']
    ])->save();

    return true;
  }
}
