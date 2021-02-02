<?php

namespace gstudio_kernel\Foundation;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

class Response
{
  private static $errorCode = [];
  static function setError($codeName, $statusCode, $code, $message)
  {
    if (self::$errorCode[$codeName]) {
      throw new \Error("错误码已经存在", 500);
    }
    self::$errorCode[$codeName] = [
      "statusCode" => $statusCode,
      "code" => $code,
      "message" => $message
    ];
  }
  static function error($statusCode, $code = null, $message = "", $data = [])
  {
    if (\is_string($statusCode)) {
      $error = self::$errorCode[$statusCode];
      self::result($error['statusCode'], $error['code'], $data, $error['message']);
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
    header("Content-Type:application/json", true, $statusCode);
    $result = [
      "statusCode" => $statusCode,
      "code" => $code,
      "data" => $data,
      "message" => $message
    ];
    if (CHARSET === "gbk") {
      \print_r(\iconv("gbk", "utf-8", \json_encode($result)));
    } else {
      \print_r(\json_encode($result));
    }
    exit();
  }
  static function view($HTMLFileName, $fileDir = "/")
  {
    global $gstudio_kernel;
    return template($HTMLFileName, $gstudio_kernel['devingPluginId'], $GLOBALS[$gstudio_kernel['devingPluginId']]['pluginPath'] . "/Views/$fileDir");
  }
  static function systemView($HTMLFileName, $fileDir = "/")
  {
    global $gstudio_kernel;
    return template($HTMLFileName, $gstudio_kernel['devingPluginId'], $gstudio_kernel['pluginPath'] . "/Views/$fileDir");
  }
}
