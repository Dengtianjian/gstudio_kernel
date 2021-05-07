<?php

namespace gstudio_kernel\Foundation;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

class Controller
{
  protected $Admin = false;
  protected $Auth = false;
  protected $DZHash = false;
  protected $Formhash = false;
  public function __get($name)
  {
    return $this->$name;
  }
  public function verifyFormhash()
  {
    global $app;
    if ($this->DZHash || $this->Formhash) {
      if (!$app->request->params("DZHash") || (!$app->request->params("DZHash") && !$app->request->params("formhash"))) {
        Response::error("LLLEGAL_SUBMISSION");
      }
      if ($app->request->params("DZHash") != \FORMHASH || (!$app->request->params("DZHash") && $app->request->params("formhash") != \FORMHASH)) {
        Response::error("LLLEGAL_SUBMISSION");
      }
      $app->request->remove("DZHash");
      $app->request->remove("formhash");
    }
  }
}
