<?php

namespace gstudio_kernel\Foundation;

class Dashboard
{
  private static $setCache = [];
  private static $setModel = null;
  private static function model()
  {
    if (self::$setModel === null) {
      global $gstudio_kernel;
      self::$setModel = new Model($gstudio_kernel['dashboard']['setTableName']);
    }
    return self::$setModel;
  }
  public static function getSet($setMark)
  {
    global $gstudio_kernel;
    if (is_string($setMark) && self::$setCache[$setMark]) {
      return self::$setCache[$setMark];
    }
    $sets = self::model()->where([
      "set_mark" => $setMark
    ])->get();
    self::$setCache = array_merge(self::$setCache, $sets);
    return $sets;
  }
}
