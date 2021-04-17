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
   *
   * @param array $array 原数组 索引数组
   * @param string $key 键名
   * @return array
   */
  static function valueToKey($array, $key)
  {
    $result = [];
    foreach ($array as $item) {
      $result[$item[$key]] = $item;
    }
    return $result;
  }
  //! grade替换
  static function sortByParentChild($arr, $dataPrimaryKey, $relatedParentKey, $childArrayKeys = "childs")
  {
    $returnData = [];
    foreach ($arr as $arrItem) {
      if ($arrItem[$relatedParentKey] == 0) {
        if ($returnData[$arrItem[$dataPrimaryKey]]) {
          $returnData[$arrItem[$dataPrimaryKey]] = \array_merge($returnData[$arrItem[$dataPrimaryKey]], $arrItem);
        } else {
          $arrItem[$childArrayKeys] = [];
          $returnData[$arrItem[$dataPrimaryKey]] = $arrItem;
        }
      } else {
        if ($returnData[$arrItem[$relatedParentKey]]) {
          \array_push($returnData[$arrItem[$relatedParentKey]][$childArrayKeys], $arrItem);
        } else {
          $returnData[$arrItem[$relatedParentKey]] = [
            $childArrayKeys => [$arrItem]
          ];
        }
      }
    }
    return \array_values($returnData);
  }
  /**
   * 分级
   *
   * @param array $arr 原数组
   * @param string $dataPrimaryKey 主键，也是父子都有的一个唯一值
   * @param string $relatedParentKey 关联键名，用于关联父子
   * @param string $childArrayKeys = childs 子级保存在指定的键值下的数组名称
   * @return array 分级后的数组
   */
  static function tree($arr, $dataPrimaryKey, $relatedParentKey, $childArrayKeys = "childs")
  {
    $arr = self::valueToKey($arr, $dataPrimaryKey);
    $result = [];
    foreach ($arr as &$arrItem) {
      if (!$arrItem[$relatedParentKey]) { //* 最高级
        if (!$result[$arrItem[$dataPrimaryKey]]) { //* 判断结果数组里是否存在，没有就加进去
          $result[$arrItem[$dataPrimaryKey]] = $arrItem;
          $arrItem['reference'] = &$result[$arrItem[$dataPrimaryKey]];
          $arrItem['reference'][$childArrayKeys] = [];
        }
      } else { //* 下级
        if ($arr[$arrItem[$relatedParentKey]]['reference']) {
          $arr[$arrItem[$relatedParentKey]]['reference'][$childArrayKeys][$arrItem[$dataPrimaryKey]] = $arrItem;
          $arrItem['reference'] = &$arr[$arrItem[$relatedParentKey]]['reference'][$childArrayKeys][$arrItem[$dataPrimaryKey]];
        }
      }
    }
    return $result;
  }
}
