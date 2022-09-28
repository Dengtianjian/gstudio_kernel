<?php

namespace gstudio_kernel\Platform\WechatMiniprogram;

use gstudio_kernel\Foundation\Network\Curl;
use gstudio_kernel\Foundation\Output;

class AccessToken
{
  static function get($appId, $secret)
  {
    $CURL = new Curl();
    $request = $CURL->url("https://api.weixin.qq.com/cgi-bin/token", [
      "grant_type" => "client_credential",
      "appid" => $appId,
      "secret" => $secret
    ]);
    return $request->get()->getData();
  }
}
