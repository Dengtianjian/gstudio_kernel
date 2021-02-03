<?php

namespace gstudio_kernel\Foundation;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

use gstudio_kernel\Model\TokenModel;

class Auth
{
  private static $verified = false;
  public static function check()
  {
    global $app;

    if (!$app->request->params("_token")) {
      Response::error("NOT_AUTH");
    }
    $TM = new TokenModel();
    $time = time();
    $token = \addslashes($app->request->params("_token"));
    $tokenInfo = $TM->getByContent($token);
    if (!$tokenInfo) {
      Response::error("AUTH_FAILED");
    }
    if ($time > $tokenInfo['token_expire']) {
      Response::error("AUTH_EXPIRED");
    }
    include_once libfile("function/member");
    $user = \getuserbyuid($tokenInfo['token_uid']);
    \setloginstatus($user, 0);
    $less = 0.2 * $app->tokenValidPeriod; //* 计算token快过期时间 就刷新token值 例如：token30天有效期 取30的%0.2
    $less = $less * (60 * 60 * 24);
    if ($tokenInfo['token_expire'] - time()  < $less) {
      $token = Token::generate();
      $now = time();
      $expireTime = $now + (60 * 60 * 24 * $app->tokenValidPeriod);
      $TM->insert([
        "token_uid" => $user['uid'],
        "token_content" => $token,
        "token_expire" => $expireTime,
        "token_create" => $now,
        "token_update" => $now
      ])->save();
    }
    self::$verified = true;
    Response::addData([
      "user" => $user,
      "token" => $token
    ]);
  }
  public static function user()
  {
    return \getglobal("member");
  }
  public static function isVerified()
  {
    return self::$verified;
  }
}
