<?php

namespace gstudio_kernel\Middleware;

use gstudio_kernel\Foundation\Auth;

class GlobalAuthMiddleware
{
  public function handle($next)
  {
    global $app;
    $GLOBALS['gstudio_kernel']['tokenTableName'] = $GLOBALS['gstudio_kernel']['devingPluginId'] . "_token";

    if ($app->request->params("_token")) {
      Auth::check();
    }

    $next();
  }
}
