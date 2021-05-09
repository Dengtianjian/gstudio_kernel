<?php

namespace gstudio_kernel\App\Api\Baidu;

use gstudio_kernel\Foundation\Controller;
use gstudio_kernel\Foundation\Curl;
use gstudio_kernel\Foundation\Request;

class OAuthController extends Controller
{
  public function data(Request $request)
  {
    // $result = Http::get("https://pan.baidu.com/rest/2.0/xpan/multimedia?method=filemetas&access_token=121.d8f07311c091763d089b4bd7151027f0.Y3oAUKpgJgqKvpNVK3BkzpnpNt_AqcBWylYmuKA.f-Yl8w", [
    //   "fsids" => [
    //     501283284169406
    //   ]
    // ]);
    // $result = Http::sGet("https://openapi.baidu.com/oauth/2.0/token?grant_type=authorization_code&code=215e52c4682998e7bd1fd2757c5afdd8&client_id=sDz8dRXcbHcOHpES3DgI0I7UlQIcP1b3&client_secret=VU7v4Pr6HG2BT2rS8qGhLy6G5BGFW7et&redirect_uri=oob");
    // $result = Http::request("https://pan.baidu.com/rest/2.0/xpan/file?method=doclist&access_token=121.d8f07311c091763d089b4bd7151027f0.Y3oAUKpgJgqKvpNVK3BkzpnpNt_AqcBWylYmuKA.f-Yl8w");
    $result = Curl::init()->url("https://pan.baidu.com/rest/2.0/xpan/file", [
      "method" => "doclist",
      "access_token" => "121.d8f07311c091763d089b4bd7151027f0.Y3oAUKpgJgqKvpNVK3BkzpnpNt_AqcBWylYmuKA.f-Yl8w"
    ])->https(false)->get()->getData();
    debug($result);
  }
}
