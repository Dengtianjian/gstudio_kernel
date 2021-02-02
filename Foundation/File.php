<?php

namespace gstudio_kernel\Foundation;

use discuz_upload;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

class File
{
  public static function upload($files, $saveDir = "common", $extid = 0, $forcename = "")
  {
    $upload = new discuz_upload();
    $uploadResult = [];
    $one = false;
    if (Arr::isAssoc($files['name'])) {
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
        $upload->save();
        $saveFileName = explode("/", $upload->attach['attachment']);
        $fileInfo = [
          "path" => $saveDir . "/" . $upload->attach['attachment'],
          "extension" => $upload->attach['extension'],
          "sourceFileName" => $upload->attach['name'],
          "saveFileName" => $saveFileName[count($saveFileName) - 1],
          "size" => $upload->attach['size'],
          "type" => $upload->attach['type']
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
  public static function copyFolder($sourcePath, $destPath)
  {
    if (is_dir($sourcePath) && \is_dir($destPath)) {
      $source = \opendir($sourcePath);
      while ($handle = \readdir($source)) {
        if ($handle == "." || $handle == "..") {
          continue;
        }
        if (is_dir($sourcePath . "/" . $handle)) {
          $targetDir = $destPath . "/" . $handle;
          if (!is_dir($targetDir)) {
            mkdir($targetDir);
          }
          self::copyFolder($sourcePath . "/" . $handle, $targetDir);
        } else {
          copy($sourcePath . "/" . $handle, $destPath . "/" . $handle);
        }
      }
    }
  }
  public static function createFile($filePath, $fileContent = "")
  {
    $touchResult = \touch($filePath);
    if ($touchResult) {
      $file = \fopen($filePath, "w+");
      \fwrite($file, $fileContent);
      \fclose($file);
      return true;
    } else {
      return false;
    }
  }
}
