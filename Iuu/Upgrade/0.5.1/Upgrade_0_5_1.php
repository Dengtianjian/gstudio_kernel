<?php

namespace gstudio_kernel\Iuu\Upgrade;

use gstudio_kernel\Foundation\Response;

class Upgrade_0_5_1
{
  public function __construct()
  {
    Response::add([
      "0.5.1" => 1
    ]);
  }
}
