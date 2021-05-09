<?php

namespace gstudio_kernel\App\Main\Extensions;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

use gstudio_kernel\Foundation\Request;
use gstudio_kernel\Foundation\View;
use gstudio_kernel\Foundation\Controller;
use gstudio_kernel\Foundation\Response;
use gstudio_kernel\Model\ExtensionsModel;

class InstallExtensionController extends Controller
{
  protected $Admin = true;
  public function data(Request $request)
  {
    $extensionId = \addslashes($request->params("extension_id"));
    $extension = ExtensionsModel::ins()->getByExtensionId($extensionId);
    if (empty($extension)) {
      Response::error(404, 404001, "扩展不存在");
    }
    $extension = $extension[0];


    return $extension;
  }
}
