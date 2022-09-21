<?php

namespace gstudio_kernel\Model;

use gstudio_kernel\Foundation\Database\Model;
use gstudio_kernel\Foundation\Store;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

class TokenModel extends Model
{
  public function __construct()
  {
    $this->tableName = F_APP_ID . "_token";
  }
  public function getByContent($tokenContent)
  {
    return $this->where([
      "token_content" => $tokenContent
    ])->getOne();
  }
}
