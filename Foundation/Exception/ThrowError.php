<?php

namespace gstudio_kernel\Foundation\Exception;

use gstudio_kernel\Foundation\Response;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

class ThrowError
{
  public $statusCode = null;
  public $code = null;
  public $message = null;
  public $data = null;
  public $details = null;
  public function __construct($statusCode, $code, $message, $data = [], $details = [])
  {
    $this->statusCode = $statusCode;
    $this->code = $code;
    $this->message = $message;
    $this->data = $data;
    $this->details = $details;
  }
  public function response()
  {
    Response::error($this->statusCode, $this->code, $this->message, $this->data, $this->details);
  }
  public static function is($target)
  {
    return $target instanceof ThrowError;
  }
}
