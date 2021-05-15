<?php

namespace gstudio_kernel\Foundation;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

use gstudio_kernel\Foundation\Exception\ErrorCode;

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
    global $app, $_G;
    $routerType = $app->router['type'];
    if (!$routerType) {
      $routerType = "view";
    }
    if ($routerType === "view") {
      $currentUrl = $_G['siteurl'];
      $currentUrl = substr($currentUrl, 0, \strlen($currentUrl) - 1) . $_SERVER['REQUEST_URI'];
      $redirectUrl = $_SERVER['HTTP_REFERER'];
      if ($redirectUrl == $currentUrl || !$redirectUrl) {
        $redirectUrl = $_G['siteurl'];
      }
      \showmessage($message, $redirectUrl, [], [
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
