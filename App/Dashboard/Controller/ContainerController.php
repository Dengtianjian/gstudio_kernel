<?php

namespace gstudio_kernel\App\Dashboard\Controller;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

use gstudio_kernel\Foundation\Response;

class ContainerController
{
  public function data($request)
  {
    global $_G, $gstudio_kernel;
    $Response = Response::class;
    include_once Response::systemView("sets", "dashboard");
  }
}
