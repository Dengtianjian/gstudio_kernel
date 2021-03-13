<?php

namespace gstudio_kernel\Foundation;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

class Arr
{
  static function isAssoc($array)
  {
    return array_keys($array) !== range(0, count($array) - 1);
  }
  static function valueToKey($array, $key)
  {
    $result = [];
    foreach ($array as $item) {
      $result[$item[$key]] = $item;
    }
    return $result;
  }
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
}
