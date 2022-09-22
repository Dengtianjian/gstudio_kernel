<?php

namespace gstudio_kernel\App;

if (!defined('IN_DISCUZ')) {
  exit('Access Denied');
}

use gstudio_kernel\Foundation\Controller\AuthController;
use gstudio_kernel\Foundation\File;

class IndexController extends AuthController
{
  public function data()
  {
    return File::genPath(DISCUZ_ROOT, "/aaa/bb", "c", "ddd/we/q/");
  }
}
