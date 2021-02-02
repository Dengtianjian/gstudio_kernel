<?php

namespace gstudio_kernel\Foundation;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

class ServerError
{
  public function __construct($message)
  {
    print_r($message);
  }
}

class ResponseError
{
  public function __construct($statusCode, $code, $message, $data)
  {
    Response::error($statusCode, $code, $message);
  }
}
