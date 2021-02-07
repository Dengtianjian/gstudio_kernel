<?php

namespace gstudio_kernel\Foundation;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

use gstudio_kernel\Model\TokenModel;

class Auth
{
  private static $verified = false;
  private static $verifiedAdmin = false;
  public static function check()
  {
    global $_G, $app;
    if ($_G['uid']) {
      self::$verified = true;
      return true;
    }

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
    Response::add([
      "user" => $user,
      "token" => $token
    ]);
    return true;
  }
  public static function checkAdmin($adminId)
  {
    $currentAdminId = \getglobal("adminid");
    if (\is_array($adminId)) {
      if (!in_array($currentAdminId, $adminId)) {
        Response::error("UNAUTHORIZED_ACCESS");
      }
    } else if (\is_bool($adminId)) {
      if ($currentAdminId == 0) {
        Response::error("UNAUTHORIZED_ACCESS");
      }
    } else if (\is_int($adminId)) {
      if ($currentAdminId != $adminId) {
        Response::error("UNAUTHORIZED_ACCESS");
      }
    }
    self::$verifiedAdmin = true;
  }
  public static function user()
  {
    return \getglobal("member");
  }
  public static function isVerified()
  {
    return self::$verified;
  }
  public static function isVerifiedAdmin()
  {
    return self::$verifiedAdmin;
  }
}
