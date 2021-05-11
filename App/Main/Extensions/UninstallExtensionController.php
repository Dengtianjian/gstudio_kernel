<?php

namespace gstudio_kernel\App\Main\Extensions;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

use gstudio_kernel\Foundation\Request;
use gstudio_kernel\Foundation\Controller;
use gstudio_kernel\Foundation\File;
use gstudio_kernel\Model\ExtensionsModel;

class UninstallExtensionController extends Controller
{
  public function data(Request $request)
  {
    $extensionId = \addslashes($request->params("extension_id"));
    $EM = new ExtensionsModel();
    $extension = $EM->getByExtensionId($extensionId);
    if (empty($extension)) {
      return true;
    }
    $extension = $extension[0];
    $installFile = \DISCUZ_ROOT . $extension['path'] . "/Iuu/Uninstall.php";
    if (\file_exists($installFile)) {
      $namespace = "\\" . $extension['plugin_id'] . "\\Extensions\\" . $extension['extension_id'] . "\\Iuu\\Uninstall";
      $instance = new $namespace();
      $instance->handle();
    }
    $result = $EM->where("extension_id", $extensionId)->delete(true);
    if ($result) {
      $extensionRootPath = \DISCUZ_ROOT . $extension['path'];
      File::deleteDirectory($extensionRootPath);
    }
    return $result;
  }
}
