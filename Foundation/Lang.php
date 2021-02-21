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
  public static function value($key)
  {
    return self::$langs[$key];
  }
  public static function all()
  {
    return self::$langs;
  }
}
