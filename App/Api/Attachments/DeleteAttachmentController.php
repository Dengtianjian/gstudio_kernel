<?php

namespace gstudio_kernel\App\Api\Attachments;

if (!defined('IN_DISCUZ')) {
  exit('Access Denied');
}

use DB;
use gstudio_kernel\Foundation\Config;
use gstudio_kernel\Foundation\Controller\AuthController;
use gstudio_kernel\Foundation\Database\Model;
use gstudio_kernel\Foundation\File;
use gstudio_kernel\Foundation\Output;
use gstudio_kernel\Foundation\Response;

class DeleteAttachmentController extends AuthController
{
  public $body = [
    "aid" => "integer"
  ];
  public function delete()
  {
    $AttachmentId = $this->body['aid'];

    $AM = new Model("forum_attachment");
    $attachment = $AM->where("aid", $AttachmentId)->getOne();
    if (!$attachment) {
      Response::error(403, "403001:AttachmentNotExist", "附件不存在或已删除", [], "附件记录不存在");
    }
    $TableId = $attachment['tableid'];
    $SAM = new Model("forum_attachment_$TableId");
    $attachment = $SAM->where("aid", $AttachmentId)->getOne();
    if (!$attachment) {
      Response::error(403, "403002:AttachmentNotExist", "附件不存在或已删除", [], "附件记录不存在");
    }
    $attachmentPath = File::genPath(Config::get("attachmentPath"), $attachment['attachment']);
    if (!file_exists($attachmentPath)) {
      $AM->where("aid", $AttachmentId)->delete(true);
      $SAM->where("aid", $AttachmentId)->delete(true);
      return true;
    }
    $unlinkResult = unlink($attachmentPath);
    if ($unlinkResult) {
      $AM->where("aid", $AttachmentId)->delete(true);
      $SAM->where("aid", $AttachmentId)->delete(true);
    } else {
      Response::error(500, "500:DeleteAttachmentFalied", "附件删除失败");
    }

    return true;
  }
}
