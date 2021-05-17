<?php

namespace gstudio_kernel\Foundation;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

class Dashboard
{
  private static $setCache = [];
  private static $setModel = null;
  private static function model()
  {
    if (self::$setModel === null) {
      self::$setModel = new Model(GlobalVariables::get("_GG/addon/dashboard/setTableName"));
    }
    return self::$setModel;
  }
  public static function handelValue($setData)
  {
    include_once libfile("function/discuzcode");
    $onlyOne = null;

    if (isset($setData['set_id']) && Arr::isAssoc($setData)) {
      $sets = [$setData['set_mark'] => $setData];
      $onlyOne = $setData['set_mark'];
    } else {
      $sets = $setData;
    }

    unset($setData);
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
        case "serialize":
          $set['set_content'] = unserialize(stripslashes($set['set_content']));
          break;
      }
    }

    if ($onlyOne !== null) {
      return $sets[$onlyOne];
    }
    return $sets;
  }
  public static function getSet($setMark = null)
  {
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

    if (is_string($setMark)) {
      self::$setCache[$setMark] = $sets;
    } else {
      self::$setCache = array_merge(self::$setCache, $sets);
    }
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
  /**
   * 增加自定义导航
   *
   * @param array $firstLevelNavs 第一级导航，二维关联数组
   * @param array $secondLevelNavs 第二级导航，二维关联数组
   * @param array $thirdLevelNavs 第三级导航，二维关联数组
   * @return void
   */
  public static function customNav($firstLevelNavs, $secondLevelNavs = [], $thirdLevelNavs = [])
  {
    GlobalVariables::set([
      "_GG" => [
        "addon" => ["dashboard" => [
          "firstLevelNavs" => $firstLevelNavs,
          "secondLevelNavs" => $secondLevelNavs,
          "secondLevelNavCount" => count($secondLevelNavs),
          "thirdLevelNavs" => $thirdLevelNavs,
          "thirdLevelNavCount" => count($thirdLevelNavs)
        ]]
      ]
    ]);
  }
}
