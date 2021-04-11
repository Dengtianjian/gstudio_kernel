<?php

namespace gstudio_kernel\Foundation;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

class Controller
{
  protected $Admin = false;
  protected $Auth = false;
  protected $DZHash = false;
  public function __get($name)
  {
    return $this->$name;
  }
}
