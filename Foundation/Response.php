<?php

namespace gstudio_kernel\Foundation;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

use gstudio_kernel\Exception\ErrorCode;

class Response
{
  private static $resultData = [];
  private static $responseData = [];
  static function error($statusCode, $code = null, $message = "", $data = [])
  {
    if (\is_string($statusCode)) {
      $error = ErrorCode::match($statusCode);
      self::result($error[0], $error[1], $data, $error[2]);
    } else {
      self::result($statusCode, $code, $data, $message);
    }
  }
  static function success($data, $statusCode = 200, $code = 200000, $message = "")
  {
    self::result($statusCode, $code, $data, $message);
  }
  static function result($statusCode = 200, $code = 200000, $data = null, $message = "")
  {
    global $app;
    $routerType = $app->router['type'];
    if ($routerType === "view") {
      \showmessage($message, $_SERVER['HTTP_REFERER'], [], [
        "alert" => "error"
      ]);
      exit();
    }
    header("Access-Control-Allow-Origin: *");
    header('Access-Control-Allow-Methods: *');
    header("Content-Type:application/json", true, $statusCode);
    if (!empty(self::$resultData)) {
      $data = \array_merge($data, self::$resultData);
    }
    $result = [
      "statusCode" => $statusCode,
      "code" => $code,
      "data" => $data,
      "message" => $message
    ];
    if (!empty(self::$responseData)) {
      $result = array_merge($result, self::$responseData);
    }
    if (CHARSET === "gbk") {
      \print_r(GJson::encode($result));
    } else {
      \print_r(\json_encode($result));
    }
    exit();
  }
  //! 准废弃
  static function render($fileName, $fileDir = "", $params = [])
  {
    global $_G, $gstudio_kernel, $GSETS, $GLANG;
    $Response = self::class;
    foreach ($params as $key => $value) {
      global ${$key};
    }
    include_once template($fileName, $gstudio_kernel['devingPluginId'], $fileDir);
    foreach ($params as $key => $value) {
      unset($GLOBALS[$key]);
    }
    return true;
  }
  //! 准废弃
  static function view($HTMLFileName, $fileDir = "/", $params = null)
  {
    global $gstudio_kernel;
    if (is_array($fileDir) || $params !== null) {
      $dir = "";
      if (\is_string($fileDir)) {
        $dir = $fileDir;
      } else {
        $params = $fileDir;
      }
      if (count($params) > 0) {
        foreach ($params as $key => $value) {
          $GLOBALS[$key] = $value;
        }
      }
      $fileDir = $GLOBALS[$gstudio_kernel['devingPluginId']]['pluginPath'] . "/Views/$dir";
      return self::render($HTMLFileName, $fileDir, $params);
    }
    return template($HTMLFileName, $gstudio_kernel['devingPluginId'], $GLOBALS[$gstudio_kernel['devingPluginId']]['pluginPath'] . "/Views/$fileDir");
  }
  //! 准废弃
  static function systemView($HTMLFileName, $fileDir = "/", $params = null)
  {
    global $gstudio_kernel;
    if (is_array($fileDir) || $params !== null) {
      $dir = "";
      if (\is_string($fileDir)) {
        $dir = $fileDir;
      } else {
        $params = $fileDir;
      }
      if (count($params) > 0) {
        foreach ($params as $key => $value) {
          $GLOBALS[$key] = $value;
        }
      }
      $fileDir = $gstudio_kernel['pluginPath'] . "/Views/$dir";
      return self::render($HTMLFileName, $fileDir, $params);
    }
    return template($HTMLFileName, $gstudio_kernel['devingPluginId'], $gstudio_kernel['pluginPath'] . "/Views/$fileDir");
  }
  static function addData($data)
  {
    self::$resultData = \array_merge(self::$resultData, $data);
    return self::$resultData;
  }
  static function add($data)
  {
    self::$responseData = \array_merge(self::$responseData, $data);
    return self::$responseData;
  }
}
