<?php

namespace gstudio_kernel\App\Api\Attachments;

use gstudio_kernel\Foundation\Controller\AuthController;
use gstudio_kernel\Foundation\Request;
use gstudio_kernel\Foundation\Response;
use gstudio_kernel\Platform\Discuzx\Attachment;

class GetAttachmentController extends AuthController
{
  public $body = [
    "aid" => "integer"
  ];
  // static $Admin = false;
  // static function Admin()
  // {
  //   return true;
  // }
  // static function verifyAdmin(){
  //   return false;
  // }
  // static $Auth = false;
  static function Auth()
  {
    return true;
  }
  static function verifyAuth(): void
  {
    // Response::error()
  }
  public function get(Request $R)
  {
    $AttachmentId = $R->params("attachmentId");

    $attachment = Attachment::getAttachment($AttachmentId);
    if (!$attachment) {
      Response::error(403, "403002:AttachmentNotExist", "附件不存在或已删除", [], "附件记录不存在");
    }

    return $attachment;
  }
}
