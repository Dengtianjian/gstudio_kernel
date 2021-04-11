<?php

namespace gstudio_kernel\Foundation;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

class Str
{
  static function unescape($str)
  {
    $str = rawurldecode($str);
    preg_match_all("/%u.{4}|&#x.{4};|&#\d+;|.+/U", $str, $r);
    $ar = $r[0];
    foreach ($ar as $k => $v) {
      if (substr($v, 0, 2) == "%u")
        $ar[$k] = iconv("UCS-2", \strtoupper(CHARSET), pack("H4", substr($v, -4)));
      elseif (substr($v, 0, 3) == "&#x")
        $ar[$k] = iconv("UCS-2", \strtoupper(CHARSET), pack("H4", substr($v, 3, -1)));
      elseif (substr($v, 0, 2) == "&#") {
        $ar[$k] = iconv("UCS-2", \strtoupper(CHARSET), pack("n", substr($v, 2, -1)));
      }
    }
    return join("", $ar);
  }
}
