<?php

namespace gstudio_kernel\Exception;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

class ErrorCode
{
  private static $errorCodes = [];
  public static function load()
  {
    include_once(\DISCUZ_ROOT . "source/plugin/gstudio_kernel/Exception/ErrorCodes.php");
    self::$errorCodes = \array_merge(self::$errorCodes, $ErrorCodes);
  }
  public static function add($keyCode, $statusCode, $code, $message)
  {
    self::$errorCodes[$keyCode] = [
      $statusCode, $code, $message
    ];
    return self::$errorCodes[$keyCode];
  }
  public static function match($keyCode)
  {
    return self::$errorCodes[$keyCode];
  }
  public static function all()
  {
    return self::$errorCodes;
  }
}
