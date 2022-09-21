<?php

namespace gstudio_kernel\Model;

use gstudio_kernel\Foundation\Database\Model;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

class LoginsModel extends Model
{
  public function __construct()
  {
    $this->tableName = "gstudio_kernel_logins";
  }
  public function getByToken($token)
  {
    return $this->where([
      "token" => $token
    ])->getOne();
  }
  public function deleteByToken($token){
    return $this->where([
      "token" => $token
    ])->delete();
  }
}
