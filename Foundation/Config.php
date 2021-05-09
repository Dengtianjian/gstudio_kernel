<?php

namespace gstudio_kernel\Foundation;

class Config
{
  private static $configs = [];
  /**
   * 读取应用Config文件
   *
   * @param string $pluginId 应用Id
   * @param string $filePath 应用配置文件所在路径
   * @return array
   */
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
      self::$configs[$pluginId] = Arr::merge(self::$configs, $Config);
      return self::$configs;
    }
    return false;
  }
  /**
   * 获取配置项
   *
   * @param string $key 配置项数组路径字符串，用 / 分隔
   * @param string $pluginId 配置文件应用Id
   * @return array|string|integer|boolean
   */
  static function get($key, $pluginId = null)
  {
    $configs = [];
    if ($pluginId === null) {
      $pluginId = GlobalVariables::get("_GG/id");
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
  /**
   * 覆盖式设置Config的值
   * 修改后的值只会在当前运行中有效，并不会修改到文件的实际值
   *
   * @param any $value 新值
   * @param string $pluginId 应用Id
   * @return void
   */
  static function set($value, $pluginId = null)
  {
    if ($pluginId === null) {
      $pluginId = GlobalVariables::get("_GG/id");
    }

    self::$configs[$pluginId] = Arr::merge(self::$configs[$pluginId], $value);
  }
}