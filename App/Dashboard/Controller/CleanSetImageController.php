<?php

namespace gstudio_kernel\App\Dashboard\Controller;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

use gstudio_kernel\Foundation\Controller;
use gstudio_kernel\Foundation\GlobalVariables;
use gstudio_kernel\Foundation\Model;
use gstudio_kernel\Foundation\Request;

class CleanSetImageController extends Controller
{
  protected $Admin = 1;
  public function data(Request $request)
  {
    $setId = $request->params("set_id");
    $setModel = new Model(GlobalVariables::get("_GG/addon/dashboard/setTableName"));
    $deletedResult = $setModel->where([
      "set_id" => $setId
    ])->update([
      "set_content" => ""
    ])->save();
    return $deletedResult;
  }
}
