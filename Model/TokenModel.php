<?php

namespace gstudio_kernel\Model;

use gstudio_kernel\Foundation\Model;

class TokenModel extends Model
{
  public function __construct()
  {
    $this->tableName = $GLOBALS['gstudio_kernel']['devingPluginId'] . "_token";
  }
  public function getByContent($tokenContent)
  {
    return $this->where([
      "token_content" => $tokenContent
    ])->getOne();
  }
}
