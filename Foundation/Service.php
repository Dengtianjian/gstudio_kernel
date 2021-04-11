<?php

namespace gstudio_kernel\Foundation;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

class Service
{
  protected static $tableName = "";
  private static $ModelInstance = null;
  protected static function Model()
  {
    $callClass = \get_called_class();
    if (!$callClass::$tableName) {
      Response::error(500, 500001, "Service的TableName未填写");
    }
    self::$tableName = $callClass::$tableName;
    if (self::$ModelInstance === null) {
      self::$ModelInstance = new Model(self::$tableName);
    }

    return self::$ModelInstance;
  }
}
