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
  private static $renderParams = [
    "fileName" => "",
    "fileDir" => "/",
    "params" => []
  ];
  static function render()
  {
    global $_G, $gstudio_kernel, $GSETS, $GLANGS;
    $Response = self::class;
    foreach (self::$renderParams['params'] as $key => $value) {
      global ${$key};
    }
    include_once template(self::$renderParams['fileName'], $gstudio_kernel['devingPluginId'], $GLOBALS[$gstudio_kernel['devingPluginId']]['pluginPath'] . "/Views/" . self::$renderParams['fileDir']);
    return true;
  }
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
      self::$renderParams['fileName'] = $HTMLFileName;
      self::$renderParams['fileDir'] = $fileDir;
      self::$renderParams['params'] = $params;
      if (count($params) > 0) {
        foreach ($params as $key => $value) {
          $GLOBALS[$key] = $value;
        }
      }
      self::render();
      return true;
    }
    return template($HTMLFileName, $gstudio_kernel['devingPluginId'], $GLOBALS[$gstudio_kernel['devingPluginId']]['pluginPath'] . "/Views/$fileDir");
  }
  static function systemView($HTMLFileName, $fileDir = "/")
  {
    global $gstudio_kernel;
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
