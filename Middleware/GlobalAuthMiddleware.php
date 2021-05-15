<?php

namespace gstudio_kernel\Middleware;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

use gstudio_kernel\Foundation\Auth;
use gstudio_kernel\Foundation\GlobalVariables;
use gstudio_kernel\Foundation\Request;

class GlobalAuthMiddleware
{
  public function handle($next, Request $request)
  {
    GlobalVariables::set([
      "token" => [
        "tableName" => $request->pluginId . "_token"
      ]
    ]);

    if ($request->params("_token")) {
      Auth::check();
    }

    $next();
  }
}
