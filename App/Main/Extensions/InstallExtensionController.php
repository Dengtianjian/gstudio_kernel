<?php

namespace gstudio_kernel\App\Main\Extensions;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

use gstudio_kernel\Foundation\Request;
use gstudio_kernel\Foundation\View;
use gstudio_kernel\Foundation\Controller;
use gstudio_kernel\Foundation\Extension\ExtensionIuu;
use gstudio_kernel\Foundation\Extension\Extensions;
use gstudio_kernel\Foundation\Response;
use gstudio_kernel\Model\ExtensionsModel;

/**
 * 安装扩展API
 */
class InstallExtensionController extends Controller
{
  protected $Admin = true;
  public function data(Request $request)
  {
    $extensionId = \addslashes($request->params("extension_id"));
    $EM = new ExtensionsModel();
    $extension = $EM->getByExtensionId($extensionId);
    if (empty($extension)) {
      Response::error(404, 404001, "扩展不存在");
    }
    $extension = $extension[0];
    if ($extension['installed'] && $extension['install_time']) {
      Response::error(400, 400001, "扩展已安装，请勿重复安装");
    }
    $extensionConfig = Extensions::config($extension['extension_id'], $extension['path']);

    $ext = new ExtensionIuu($extension['plugin_id'], $extension['extension_id'], NULL);
    $ext->install()->runInstallSql()->cleanInstall();
    $EM->where("extension_id", $extension['extension_id'])->where("plugin_id", $extension['plugin_id'])->update([
      "install_time" => time(),
      "installed" => 1,
      "local_version" => $extensionConfig['version']
    ])->save();

    return true;
  }
}
