<?php

namespace gstudio_kernel\Extensions\Discuzx;

use gstudio_kernel\Foundation\Arr;

class Attachment
{
  public static function upload($files, $saveDir = "common", $extid = 0, $forcename = "")
  {
    include_once \libfile("discuz/upload", "class");
    $upload = new \discuz_upload();
    $uploadResult = [];
    $one = false;
    if (Arr::isAssoc($files)) {
      $one = true;
      $files = [$files];
    } else {
      $files = array_values($files);
    }
    foreach ($files as $fileItem) {
      $upload->init($fileItem, $saveDir, $extid, $forcename);
      if ($upload->error()) {
        $uploadResult[] =  [
          "error" => $upload->error(),
          "message" => $upload->errormessage()
        ];
        continue;
      } else {
        $upload->save(true);
        $saveFileName = explode("/", $upload->attach['attachment']);
        $path = $saveDir . "/" . $upload->attach['attachment'];
        $fileInfo = [
          "path" => $path,
          "extension" => $upload->attach['extension'],
          "sourceFileName" => $upload->attach['name'],
          "saveFileName" => $saveFileName[count($saveFileName) - 1],
          "size" => $upload->attach['size'],
          "type" => $upload->attach['type'],
          "fullPath" => \getglobal("setting/attachurl") . $path
        ];
        if ($upload->attach['isimage']) {
          $fileInfo['width'] = $upload->attach['imageinfo'][0];
          $fileInfo['height'] = $upload->attach['imageinfo'][1];
        }
        $uploadResult[] = $fileInfo;
      }
    }
    if ($one) {
      return $uploadResult[0];
    }

    return $uploadResult;
  }
}
