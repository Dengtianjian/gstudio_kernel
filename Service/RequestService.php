<?php

namespace gstudio_kernel\Service;

use gstudio_kernel\Foundation\Request;
use gstudio_kernel\Foundation\Service;

class RequestService extends Service
{
  static function request(): Request
  {
    return $GLOBALS['App']->request;
  }
}
