<?php

namespace gstudio_kernel\Platform\Aliyun;

use gstudio_kernel\Foundation\Network\Curl;

class AliyunRequest extends Aliyun
{
  public function params($params = [])
  {
    $publicParams = [
      "Format" => "json",
      "Version" => "2019-12-30",
      "AccessKeyId" => $this->AppId,
      "SignatureMethod" => "HMAC-SHA1",
      "Timestamp" => date('Y-m-d\TH:i:s\Z', time() - date('Z')),
      "SignatureVersion" => "1.0",
      "SignatureNonce" => substr(md5(rand(1, 99999999)), rand(1, 9), 14),
    ];

    return array_merge($publicParams, $params);
  }
  public function send()
  {
    $AS = new AliyunSignature($this->AppId, $this->AppSecret);
    // return $AS->generate($this->params());
    // $params = $this->params([
    //   "Signature" => $AS->generate($this->params()),
    //   "Action" => "SegmentCommonImage",
    //   "ImageURL" => "http://viapi-test.oss-cn-shanghai.aliyuncs.com/viapi-3.0domepic/imageseg/SegmentCommonImage/SegmentCommonImage1.jpg"
    // ]);
    $params = $AS->generate();
    // return $params;
    $request = new Curl();
    $request->url("http://imageseg.cn-shanghai.aliyuncs.com", $params);
    return $request->get()->getData();
  }
}
