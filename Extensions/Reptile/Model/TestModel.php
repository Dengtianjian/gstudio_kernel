<?php

namespace gstudio_kernel\Extensions\Reptile\Model;

if (!defined("IN_DISCUZ")) {
  exit("Access Denied");
}

use gstudio_kernel\Foundation\Database\Model;

class TestModel extends Model
{
  public $tableName = "pre_test";
}