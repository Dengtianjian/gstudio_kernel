<?php

namespace gstudio_kernel\Platform\Wechat\Miniprogram;

use gstudio_kernel\Foundation\Exception\ThrowError;
use gstudio_kernel\Foundation\Store;
use gstudio_kernel\Model\WechatUsersModel;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

class User extends WechatMiniProgram
{
  public function JSCode2Session($code)
  {
    $request = $this->get("sns/jscode2session", [
      "appid" => $this->AppId,
      "secret" => $this->AppSecret,
      "grant_type" => "authorization_code",
      "js_code" => $code
    ], false)->getData();

    if ($request['errcode']) {
      return new ThrowError(400, "400:CodeBeenUsed", "登录失败，请稍后重试", [], "Code已经使用过了");
    }
    return $request;
  }
  public function bind($code)
  {
    $res = $this->JSCode2Session($code);
    if (ThrowError::is($res)) {
      $res->response();
    }
    $WUM = new WechatUsersModel();
    $member = Store::getApp("member");
    return $WUM->bind($member['uid'], $res['openid'], $res['unionid']);
  }
  public function register($code)
  {
    $res = $this->JSCode2Session($code);
    if (ThrowError::is($res)) {
      $res->response();
    }
    $WUM = new WechatUsersModel();
    return $WUM->register($res['openid'], $res['unionid']);
  }
}
