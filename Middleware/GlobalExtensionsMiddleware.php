<?php

namespace gstudio_kernel\Middleware;

if (!defined("IN_DISCUZ")) {
  exit("Access Denied");
}

use gstudio_kernel\Foundation\Config;
use gstudio_kernel\Foundation\GlobalVariables;
use gstudio_kernel\Foundation\Request;
use gstudio_kernel\Foundation\View;

class GlobalExtensionsMiddleware
{
  private $uris = [
    "_extensions/muri",
    "_extensions/muri2",
    "_extensions",
    "_extensions/suri",
    "_extensions/suri2"
  ];
  function handle($next, Request $request)
  {
    global $_GG;
    if (Config::get("dashboard/use")) {
      $_GG['addon']['dashboard']['mainNavs']['extensions'] = [
        "nav_id" => null,
        "nav_up" => 0,
        "nav_uri" => "_extensions",
        "nav_name" => "扩展",
        "nav_sort" => count($_GG['addon']['dashboard']['mainNavs'])
      ];
      if (\in_array($request->uri, $this->uris)) {
        $_GG['addon']['dashboard']['subNavs'] = [
          "_extensions/muri" => [
            "nav_id" => null,
            "nav_up" => "_extensions",
            "nav_uri" => "_extensions/muri",
            "nav_name" => "Muri",
            "nav_sort" => 0
          ], "_extensions/muri2" => [
            "nav_id" => null,
            "nav_up" => "_extensions",
            "nav_uri" => "_extensions/muri2",
            "nav_name" => "Muri2",
            "nav_sort" => 0
          ]
        ];
        $_GG['addon']['dashboard']['currentSubNav'] = $_GG['addon']['dashboard']['subNavs'][$request->uri];
        $_GG['addon']['dashboard']['thirdNavs'] = [
          "_extensions/suri" => [
            "nav_id" => null,
            "nav_up" => "_extensions",
            "nav_uri" => "_extensions/suri",
            "nav_name" => "suri",
            "nav_sort" => 0
          ],
          "_extensions/suri2" => [
            "nav_id" => null,
            "nav_up" => "_extensions",
            "nav_uri" => "_extensions/suri2",
            "nav_name" => "suri2",
            "nav_sort" => 0
          ]
        ];
        $_GG['addon']['dashboard']['thirdNavCount'] = 2;
      }
    }
    // \debug(GlobalVariables::getGG());

    $next();
  }
}
