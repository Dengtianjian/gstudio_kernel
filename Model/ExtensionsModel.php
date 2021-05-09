<?php

namespace gstudio_kernel\Model;

if (!defined("IN_DISCUZ")) {
  exit("Access Denied");
}

use gstudio_kernel\Foundation\Database\Model;

class ExtensionsModel extends Model
{
  public $tableName = "gstudio_kernel_extensions";
  public function getByExtensionId($extensionId)
  {
    return $this->where("extension_id", $extensionId)->get();
  }
}
