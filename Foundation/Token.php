<?php

namespace gstudio_kernel\Foundation;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

class Token
{
  public static function generate()
  {
    // $user = Auth::user();
    // $pass = time() . Config::get("token/salt") . "_" . $user['uid'];
    // $hash = \password_hash($pass, PASSWORD_BCRYPT);
    // return $hash;
  }
  public static function verify($password, $hash)
  {
    return \password_verify($password, $hash);
  }
}
