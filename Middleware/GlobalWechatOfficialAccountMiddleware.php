<?php

namespace gstudio_kernel\Middleware;

use gstudio_kernel\Foundation\Request;
use gstudio_kernel\Foundation\Store;
use gstudio_kernel\Model\AccessTokenModel;
use gstudio_kernel\Platform\Wechat\AccessToken;
use gstudio_kernel\Platform\Wechat\OfficialAccount\WechatOfficialAccount;

class GlobalWechatOfficialAccountMiddleware
{
  public function handle($next, Request $R, $params)
  {
    $ATM = new AccessTokenModel();
    $AppId = $params['appId'];
    $AppSecret = $params['appSecret'];
    $Platform = "wechatOfficialAccount";

    $LatestAccountToken = $ATM->where("platform", $Platform)->where("appId", $AppId)->where("expiredAt", time(), ">")->getOne();
    if (!$LatestAccountToken) {
      $AT = new AccessToken(null, $AppId, $AppSecret);
      $res = $AT->getAccessToken();
      $ATM->add($res['access_token'], $Platform, $res['expires_in'], $AppId);

      $LatestAccountToken = $ATM->where("platform", $Platform)->where("appId", $AppId)->where("expiredAt", time(), ">")->getOne();
    }

    Store::setApp([
      "Wechat" => [
        "OfficialAccount" => [
          "AccessToken" => $LatestAccountToken['accessToken'],
          "AppId" => $LatestAccountToken['appId']
        ]
      ]
    ]);

    $next();
  }
}
