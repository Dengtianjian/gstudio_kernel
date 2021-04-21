<?php

namespace gstudio_kernel\Foundation;

class Config
{
  private static $configs = [];
  static function read($pluginId, $filePath = null)
  {
    if (!$filePath) {
      $filePath = DISCUZ_ROOT . "/source/plugin/$pluginId/Config.php";
    }
    if (!\file_exists($filePath)) {
      return false;
    }
    include_once($filePath);
    if (isset($Config)) {
      self::$configs[$pluginId] = $Config;
      return self::$configs;
    }
    return false;
  }
  static function get($key, $pluginId = null)
  {
    global $app;
    $configs = [];
    if ($pluginId === null) {
      $pluginId = $app->pluginId;
    }

    if (!isset(self::$configs[$pluginId])) {
      if (self::read($pluginId) === false) {
        return null;
      }
    }
    $configs = self::$configs[$pluginId];
    $key = \explode(",", $key);
    $values = [];
    foreach ($key as $keyItem) {
      $keyItem = \explode("/", $keyItem);
      $value = $configs;
      $lastKey = $keyItem[0];
      foreach ($keyItem as $kkItem) {
        $value = $value[$kkItem];
        $lastKey = $kkItem;
      }
      $values[$lastKey] = $value;
    }
    if (count($key) === 1) {
      return \array_pop($values);
    }
    return $values;
  }
}
