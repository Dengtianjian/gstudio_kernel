<?php

namespace gstudio_kernel\Extensions\Discuzx;

use gstudio_kernel\Foundation\Arr;
use gstudio_kernel\Foundation\Model;

class Attachment
{
  public static function upload($files, $tableName = "", $tid = 0, $pid = 0, $price = 0, $remote = 0, $saveDir = "common", $extid = 0, $forcename = "")
  {
    include_once \libfile("discuz/upload", "class");
    $upload = new \discuz_upload();
    $uploadResult = [];
    $onlyOny = false;
    if (Arr::isAssoc($files)) {
      $onlyOny = true;
      $files = [$files];
    } else {
      $files = array_values($files);
    }
    $uid = \getglobal("uid");
    $timestamp = \getglobal("timestamp");
    $insertDatas = [];
    $updateDatas = []; //TODO 更新 form_attachment 表的新增数据
    if ($tableName) {
      $insertDatas[$tableName] = [];
    }
    include_once \libfile("function/core");
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
        $aid = getattachnewaid($uid);
        $width = 0;
        $fileInfo = [];
        if ($upload->attach['isimage']) {
          $fileInfo['width'] = $upload->attach['imageinfo'][0];
          $fileInfo['height'] = $upload->attach['imageinfo'][1];
          $width = $fileInfo['width'];
          if (!$width) {
            $width = 0;
          }
        }
        $insertData = array(
          'aid' => $aid,
          "tid" => $tid,
          "pid" => $pid,
          'uid' => $uid,
          'dateline' => $timestamp,
          'filename' => dhtmlspecialchars(censor($upload->attach['name'])),
          'filesize' => $upload->attach['size'],
          'attachment' => $upload->attach['attachment'],
          'remote' => $remote,
          "description" => "",
          "readperm" => 0,
          "price" => $price,
          'isimage' => $upload->attach['isimage'],
          'width' => $width,
          'thumb' => 0,
          "picid" => 0
        );
        if (!$tableName) {
          $tableId = null;
          if ($tid) {
            $tableId = getattachtableid($tid);
          } else {
            $tableId = getattachtableid(time());
          }
          $tableName = "forum_attachment_" . $tableId;
          if (!$insertDatas[$tableName]) {
            $insertDatas[$tableName] = [];
          }
          array_push($insertDatas[$tableName], $insertData);
        } else {
          array_push($insertDatas[$tableName], $insertData);
        }
        $fileInfo = [
          "path" => $path,
          "extension" => $upload->attach['extension'],
          "sourceFileName" => $upload->attach['name'],
          "saveFileName" => $saveFileName[count($saveFileName) - 1],
          "size" => $upload->attach['size'],
          "type" => $upload->attach['type'],
          "fullPath" => \getglobal("setting/attachurl") . $path,
          "aid" => $aid,
          "tableId" => $tableId,
          "tableName" => $tableName,
          "aidencode" => \aidencode($aid, 0, $tid)
        ];
        $uploadResult[] = $fileInfo;
      }
    }
    foreach ($insertDatas as $tableName => $insertData) {
      $attachmenModel = new Model($tableName);
      $attachmenModel->batchInsertByMS($insertData)->save();
    }

    if ($onlyOny) {
      return $uploadResult[0];
    }

    return $uploadResult;
  }
}
