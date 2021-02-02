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
}
