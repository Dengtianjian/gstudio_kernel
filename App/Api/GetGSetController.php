<?php

namespace gstudio_kernel\App\Api;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

use gstudio_kernel\Foundation\Config;
use gstudio_kernel\Foundation\Controller;
use gstudio_kernel\Foundation\Request;
use gstudio_kernel\Foundation\Response;

class GetGSetController extends Controller
{
  private $whiteListOfKeys = [
    "uid", "username", "adminid", "groupid", "formhash", "charset", "setting/accessemail"
  ];
  public function data(Request $request)
  {
    $this->whiteListOfKeys = array_merge($this->whiteListOfKeys, Config::get("DZXGlobalVariablesWhiteList"));
    $keys = $request->params("key");
    if ($keys === null) {
      Response::success([]);
    }
    $sets = [];
    if (\is_string($keys)) {
      $keys = \explode(",", $keys);
    }
    foreach ($keys as $key) {
      if (in_array($key, $this->whiteListOfKeys)) {
        $ex = \explode("/", $key);
        $newKey = $key;
        if (count($ex) > 2) {
          $ex = \array_slice($ex, count($ex) - 2);
          $newKey = \implode("/", $ex);
        }

        $sets[$newKey] = \getglobal($key);
      }
    }
    return $sets;
  }
}
