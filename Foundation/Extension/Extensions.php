<?php

namespace gstudio_kernel\Foundation\Extension;

class Extensions
{
  public static function scanDir($rootPath, $extensionFolderName = "Extensions")
  {
    $extensionPath = $rootPath . "/" . $extensionFolderName;
    if (!is_dir($extensionPath)) {
      return false;
    }
    $dirs = \scandir($extensionPath);
    $extensions = [];

    foreach ($dirs as $dirItem) {
      if ($dirItem === "." || $dirItem === "..") {
        continue;
      }
      //* 配置文件
      $extensionRootPath = $extensionPath . "/" . $dirItem;
      $extensionJsonFilePath = $extensionRootPath . "/extension.json";
      $configJson = \file_get_contents($extensionJsonFilePath);
      $configJson = \json_decode($configJson, true);

      //* 子扩展
      if (is_dir($extensionRootPath . "/" . $extensionFolderName)) {
        $subExtensions = NULL;
        $subExtensions = self::scanDir($extensionRootPath, $extensionFolderName);
        foreach ($subExtensions as &$extensionConfig) {
          $extensionConfig['sub'] = true;
          if (!isset($extensionConfig['parent'])) {
            $extensionConfig['parent'] = $configJson['id'];
          }
        }
        $extensions = \array_merge($extensions, $subExtensions);
      }

      $configJson['root'] = $extensionRootPath;
      $configJson['icon'] = $extensionRootPath . "/icon.png";

      $extensions[$configJson['id']] = $configJson;
    }
    return $extensions;
  }
}
