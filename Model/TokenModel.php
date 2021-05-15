<?php

namespace gstudio_kernel\Model;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

use gstudio_kernel\Foundation\GlobalVariables;
use gstudio_kernel\Foundation\Model;

class TokenModel extends Model
{
  public function __construct()
  {
    $this->tableName = GlobalVariables::getGG("id") . "_token";
  }
  public function getByContent($tokenContent)
  {
    return $this->where([
      "token_content" => $tokenContent
    ])->getOne();
  }
}
