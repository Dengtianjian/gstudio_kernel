<?php

namespace gstudio_kernel\Middleware;

use gstudio_kernel\Foundation\Config;
use gstudio_kernel\Foundation\Dashboard;
use gstudio_kernel\Foundation\GlobalVariables;
use gstudio_kernel\Foundation\Router;
use gstudio_kernel\App\Dashboard\Controller as DashboardController;

class GlobalSetsMiddleware
{
  public function handle($next)
  {
    if (count(Config::get("globalSets")) > 0) {
      $GLOBALS['GSETS'] = Dashboard::getSetValue(Config::get("globalSets"));
      GlobalVariables::set([
        "_GG" => [
          "sets" => $GLOBALS['GSETS']
        ]
      ]);
    } else {
      $GLOBALS['GSETS'] = [];
      GlobalVariables::set([
        "_GG" => [
          "sets" => []
        ]
      ]);
    }
    Router::get("_set", DashboardController\GetSetController::class);
    $next();
  }
}