<?php

namespace gstudio_kernel\Middleware;

if (!defined("IN_DISCUZ")) {
  exit("Access Denied");
}

use gstudio_kernel\Foundation\Config;
use gstudio_kernel\Foundation\Dashboard;
use gstudio_kernel\Foundation\Request;

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
    if (Config::get("dashboard/use")) {
      $navs = [
        "_extensions" => [
          "nav_id" => "_extensions",
          "nav_up" => 0,
          "nav_uri" => "_extensions",
          "nav_name" => "扩展",
          "nav_sort" => 99,
          "nav_custom" => 1
        ],
        "_extensions/muri" => [
          "nav_id" => "_extensions/muri",
          "nav_up" => "_extensions",
          "nav_uri" => "_extensions/muri",
          "nav_name" => "Muri",
          "nav_sort" => 0,
          "nav_custom" => 1
        ], "_extensions/muri2" => [
          "nav_id" => "_extensions/muri2",
          "nav_up" => "_extensions",
          "nav_uri" => "_extensions/muri2",
          "nav_name" => "Muri2",
          "nav_sort" => 0,
          "nav_custom" => 1
        ],
        "_extensions/suri" => [
          "nav_id" => "_extensions/suri",
          "nav_up" => "_extensions/muri",
          "nav_uri" => "_extensions/suri",
          "nav_name" => "suri",
          "nav_sort" => 0,
          "nav_custom" => 1
        ],
        "_extensions/suri2" => [
          "nav_id" => "_extensions/suri2",
          "nav_up" => "_extensions/muri2",
          "nav_uri" => "_extensions/suri2",
          "nav_name" => "suri2",
          "nav_sort" => 0,
          "nav_custom" => 1
        ]
      ];
      Dashboard::customNav([$navs['_extensions']]);
      if (\in_array($request->uri, $this->uris)) {
        $thirdNavs = [];
        switch ($request->uri) {
          case '_extensions/muri':
          case "_extensions/suri":
            $thirdNavs['_extensions/suri'] = $navs['_extensions/suri'];
            break;
          case '_extensions/muri2':
          case '_extensions/suri2':
            $thirdNavs['_extensions/suri2'] = $navs['_extensions/suri2'];
            break;
        }
        Dashboard::customNav([], [
          $navs['_extensions/muri'],
          $navs['_extensions/muri2']
        ], $thirdNavs);
      }
    }

    $next();
  }
}
