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

    if (is_string($setMark)) {
      if (self::$setCache[$setMark]) {
        return self::$setCache[$setMark];
      }
      if (\func_num_args() > 1) {
        $setMark = func_get_args();
      }
    }
    $sets = self::model()->where([
      "set_mark" => $setMark
    ]);
    if (is_string($setMark)) {
      $sets = $sets->getOne();
    } else {
      $sets = $sets->get();
      $sets = Arr::valueToKey($sets, "set_mark");
    }
    self::$setCache = array_merge(self::$setCache, $sets);
    return $sets;
  }
  public static function getSetValue($setMark)
  {
    if (is_string($setMark)) {
      if (\func_num_args() > 1) {
        $setMark = func_get_args();
      }
    }
    $sets = self::getSet($setMark);
    if (func_num_args() == 1 && is_string($setMark)) {
      return $sets['set_content'];
    }
    foreach ($sets as &$set) {
      $set = $set['set_content'];
    }
    return $sets;
  }
}
