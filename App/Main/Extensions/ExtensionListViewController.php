<?php

namespace gstudio_kernel\App\Main\Extensions;

use gstudio_kernel\Foundation\Arr;
use gstudio_kernel\Foundation\Controller;
use gstudio_kernel\Foundation\Extension\ExtensionIuu;
use gstudio_kernel\Foundation\Extension\Extensions;
use gstudio_kernel\Foundation\GlobalVariables;
use gstudio_kernel\Foundation\Request;
use gstudio_kernel\Foundation\View;
use gstudio_kernel\Model\ExtensionsModel;

class ExtensionListViewController extends Controller
{
  protected $Admin = true;
  public function data(Request $request)
  {
    $extensions = Extensions::scanDir("source/plugin/gstudio_20210303");
    $extensionIds = array_keys($extensions);
    $EM = new ExtensionsModel();
    $DBExtensions = $EM->getByExtensionId($extensionIds);
    $DBExtensions = Arr::valueToKey($DBExtensions, "extension_id");
    $insertNewData = [];
    $now = time();
    $pluginId = GlobalVariables::getGG("id");
    foreach ($extensions as $id => &$extension) {
      if ($DBExtensions[$id]) {
        unset($extension['id']);
        $extension = \array_merge($extension, $DBExtensions[$id]);
      } else {
        $insertData = [
          "created_time" => $now,
          "install_time" => 0,
          "upgrade_time" => 0,
          "local_version" => "",
          "plugin_id" => $pluginId,
          "extension_id" => $extension['id'],
          "enabled" => 0,
          "installed" => 0,
          "path" => $extension['root'],
          "parent_id" => $extension['parent'] ? $extension['parent'] : 0
        ];
        array_push($insertNewData, $insertData);
        $extension = \array_merge($extension, $insertData);
      }
    }
    if (count($insertNewData)) {
      $EM->batchInsert(array_keys($insertNewData[0]), $insertNewData)->save();
    }

    View::title("扩展列表");
    View::systemDashboard("extensions/list", [
      "extensions" => $extensions
    ]);
  }
}