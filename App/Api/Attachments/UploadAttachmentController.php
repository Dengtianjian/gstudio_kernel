<?php

namespace gstudio_kernel\App\Api\Attachments;

use gstudio_kernel\Foundation\AuthController;
use gstudio_kernel\Foundation\Response;
use gstudio_kernel\Platform\Discuzx\Attachment;

class UploadAttachmentController extends AuthController
{
  public function post()
  {
    if (count($_FILES) === 0 || !$_FILES['file']) {
      Response::error(400, "Attachment:400001", "请上传文件", $_FILES);
    }
    $file = $_FILES['file'];
    return Attachment::upload($file);
    // $uploadResult = AttachmentService::upload($file, "Data/Attachments");

    // return $uploadResult;
  }
}
