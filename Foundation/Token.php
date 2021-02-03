<?php

namespace gstudio_kernel\Foundation;

class Token
{
  public static function generate()
  {
    global $app;
    $user = Auth::user();
    $pass = time() . $app->salt . "_" . $user['uid'];
    $hash = \password_hash($pass, PASSWORD_BCRYPT);
    return $hash;
  }
  public static function verify($password, $hash)
  {
    return \password_verify($password, $hash);
  }
}
