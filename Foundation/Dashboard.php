<?php

namespace gstudio_kernel\Foundation;

use bbcode;

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
  public static function handelValue($sets)
  {
    include_once libfile("function/discuzcode");
    foreach ($sets as &$set) {
      switch ($set['set_formtype']) {
        case "bbcode":
          $set['set_content'] = \discuzcode(\urldecode($set['set_content']), false, false, 1, 1, 1, 1, 1, 1, "0", "0", "1", 0, 1, 0);
          break;
        case "html":
          $set['set_content'] = urldecode(Str::unescape($set['set_content']));
          break;
        case "groups":
          $set['set_content'] = unserialize($set['set_content']);
          break;
      }
    }
    return $sets;
  }
  public static function getSet($setMark = null)
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
    $sets = self::model();
    if ($setMark !== null) {
      $sets = $sets->where([
        "set_mark" => $setMark
      ]);
    }
    if (is_string($setMark)) {
      $sets = $sets->getOne();
    } else {
      $sets = $sets->get();
      $sets = Arr::valueToKey($sets, "set_mark");
    }
    $sets = self::handelValue($sets);
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
  public static function updateSet($setMark, $setContent)
  {
    $sets = self::model();
    return $sets->where([
      "set_mark" => $setMark
    ])->update([
      "set_content" => $setContent
    ])->save();
  }
}
