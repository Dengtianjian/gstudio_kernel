<?php

namespace gstudio_kernel\Foundation;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

class Arr
{
  /**
   * 判断不是关联数组
   *
   * @param array $array 原数组
   * @return boolean
   */
  static function isAssoc($array)
  {
    return array_keys($array) !== range(0, count($array) - 1);
  }
  /**
   * 抽取元素的指定键值为当前元素的键
    //! 准废弃
   * @param array $array 原数组 索引数组
   * @param string $key 键名
   * @return array
   */
  static function valueToKey($array, $key)
  {
    return self::indexToAssoc($array, $key);
  }
  /**
   * 索引数组转关联数组
   * @param array $array 原数组 索引数组
   * @param string $key 键名
   * @return array
   */
  static function indexToAssoc($array, $key)
  {
    $result = [];
    foreach ($array as $item) {
      $result[$item[$key]] = $item;
    }
    return $result;
  }
  /**
   * 分级
   *
   * @param array $arr 原数组
   * @param string $dataPrimaryKey 主键，也是父子都有的一个唯一值
   * @param string $relatedParentKey 关联键名，用于关联父子
   * @param string $childArrayKeys = childs 子级保存在指定的键值下的数组名称
   * @param any $isParentValue = 0 用于判断是父级的值 例如 xxx===isParentValue=true 就说明他是父级 
   * @return array 分级后的数组
   */
  static function tree($arr, $dataPrimaryKey, $relatedParentKey, $childArrayKeys = "childs", $isParentValue = 0)
  {
    $arr = self::valueToKey($arr, $dataPrimaryKey);
    $result = [];
    foreach ($arr as &$arrItem) {
      if ($arrItem[$relatedParentKey] == $isParentValue) {
        if (!$result[$arrItem[$dataPrimaryKey]]) {
          $arrItem[$childArrayKeys] = [];
          $result[$arrItem[$dataPrimaryKey]] = $arrItem;
        }
      } else {
        if (!$result[$arrItem[$relatedParentKey]][$childArrayKeys]) {
          $arr[$arrItem[$relatedParentKey]][$childArrayKeys] = [];
          $result[$arrItem[$relatedParentKey]] = $arr[$arrItem[$relatedParentKey]];
        }
        array_push($result[$arrItem[$relatedParentKey]][$childArrayKeys], $arrItem);
      }
    }
    return $result;
  }
  /**
   * 合并数组。支持多维数组合并
   *
   * @param array ...$arrs 要合并的数组
   * @return array 合并完后的数组
   */
  static function merge(...$arrs)
  {
    $merged = [];
    while ($arrs) {
      $array = array_shift($arrs);
      if (!$array) {
        continue;
      }
      foreach ($array as $key => $value) {
        if (is_string($key)) {
          if (
            is_array($value) && array_key_exists($key, $merged)
            && is_array($merged[$key])
          ) {
            $merged[$key] = self::merge(...[$merged[$key], $value]);
          } else {
            $merged[$key] = $value;
          }
        } else {
          $merged[] = $value;
        }
      }
    }

    return $merged;
  }
  /**
   * 分隔字符串转换成多级数组
   *
   * @param string $string 字符串
   * @param string $separator 用于分割字符串的字符。默认是 /
   * @return void
   */
  static function stringToMultiLevelArray($string, $separator = "/")
  {
    $strings = explode($separator, $string);
    $result = [];
    $previous = NULL;
    foreach ($strings as $stringItem) {
      if (\is_array($previous)) {
        $previous[$stringItem] = [];
        $previous = &$previous[$stringItem];
      } else {
        $result[$stringItem] = [];
        $previous = &$result[$stringItem];
      }
    }
    unset($previous);
    return $result;
  }
}
