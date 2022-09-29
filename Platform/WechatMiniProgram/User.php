<?php

namespace gstudio_kernel\Platform\WechatMiniprogram;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

class User extends WechatMiniProgram
{
  public function JSCode2Session($code)
  {
    return $this->get("sns/jscode2session", [
      "appid" => $this->AppId,
      "secret" => $this->AppSecret,
      "grant_type" => "authorization_code",
      "js_code" => $code
    ], false)->getData();
  }
}
