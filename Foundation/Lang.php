<?php

namespace gstudio_kernel\Foundation;

class Lang
{
  private static $langs = [];
  public static function add($langs, $key = null)
  {
    if (\is_array($langs)) {
      self::$langs = array_merge(self::$langs, $langs);
    } else {
      self::$langs[$key] = $langs;
    }
  }
  public static function change($key, $value)
  {
    self::$langs[$key] = $value;
  }
  public static function value($keys)
  {
    $string = "";
    if (!\is_array($keys)) {
      $keys = func_get_args();
    }
    if (count($keys) === 1) {
      return self::$langs[$keys[0]];
    }
    foreach ($keys as $key) {
      $string .= self::$langs[$key];
    }

    return $string;
  }
  public static function all()
  {
    return self::$langs;
  }
}
