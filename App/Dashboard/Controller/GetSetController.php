<?php

namespace gstudio_kernel\App\Dashboard\Controller;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

use gstudio_kernel\Foundation\Controller;
use gstudio_kernel\Foundation\Dashboard;
use gstudio_kernel\Foundation\Request;

class GetSetController extends Controller
{
  public function data(Request $request)
  {
    $params = $request->params("set_mark", "setMark");
    $setMark = [];
    if ($params['set_mark']) {
      $setMark = $params['set_mark'];
    } else if ($params['setMark']) {
      $setMark = $params['setMark'];
    }
    $setMark = explode(",", $setMark);
    return Dashboard::getSet($setMark);
  }
}
