<?php

namespace gstudio_kernel\App\Dashboard\Controller;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

use gstudio_kernel\Foundation\Controller;

class SaveSetController extends Controller
{
  protected $Admin = true;
  public function data()
  {
    global $_G, $gstudio_kernel, $GLANG;
    $updateData = $_POST;
    if ($updateData['DZHash'] !== \FORMHASH) {
      \showmessage($GLANG["kernel"]['llleal_submission'], $_SERVER['HTTP_REFERER'], [], [
        "alert" => "error"
      ]);
      exit;
    }
    unset($updateData['DZHash']);

    include_once libfile("class/discuz_upload");
    $discuzUpload = new \discuz_upload();
    foreach ($_FILES as $key => $file) {
      $discuzUpload->init($file, "common");
      $discuzUpload->save();
      if ($discuzUpload->error() == 0) {
        $updateData[$key] = $_G['setting']['attachurl'] . "/common/" . $discuzUpload->attach['attachment'];
      }
    }

    $updateSql = "UPDATE " . \DB::table($gstudio_kernel['dashboard']['setTableName']) . " SET `set_content` = CASE `set_id` ";
    $updateIds = [];
    foreach ($updateData as $dataKey => $dataItem) {
      switch (gettype($dataItem)) {
        case "string":
          $dataItem = addslashes($dataItem);
          break;
        case "array":
          $dataItem = \serialize($dataItem);
          break;
      }
      if ($dataItem != null) {
        $updateSql .= sprintf("WHEN %d THEN '%s' ", $dataKey, $dataItem);
        array_push($updateIds, $dataKey);
      }
    }
    $ids = implode(",", $updateIds);
    $updateSql .= " END WHERE `set_id` IN($ids)";

    \DB::query($updateSql);

    showmessage($GLANG["kernel"]['saved_successfully'], $_SERVER['HTTP_REFERER'], [], ["alert" => "right"]);
  }
}
