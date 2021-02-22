<?php

namespace gstudio_kernel\Foundation;

class Controller
{
  protected $Admin = false;
  protected $Auth = false;
  public function __get($name)
  {
    return $this->$name;
  }
}
