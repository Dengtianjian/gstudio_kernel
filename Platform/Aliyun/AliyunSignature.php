<?php

namespace gstudio_kernel\Platform\Aliyun;

class AliyunSignature extends Aliyun
{
  private function percentEncode($value = null)
  {
    $en = urlencode($value);
    $en = str_replace("+", "%20", $en);
    $en = str_replace("*", "%2A", $en);
    $en = str_replace("%7E", "~", $en);
    return $en;
  }
  public function generate($parameters = [], $method = "GET")
  {
    date_default_timezone_set("GMT");
    // $parameters = [
    //   // 公共参数
    //   'Format' => 'json',
    //   'Version' => '2019-12-30',
    //   'AccessKeyId' => "LTAI5tL1j5jK42PbbCd5mgT2",
    //   'SignatureVersion' => '1.0',
    //   'SignatureMethod' => 'HMAC-SHA1',
    //   'SignatureNonce' => uniqid(),
    //   'Timestamp' => date('Y-m-d\TH:i:s\Z'),
    //   "Action" => "SegmentCommonImage",
    //   "ImageURL" => "https://1000pen.oss-cn-shanghai.aliyuncs.com/pic/20221031/1667216706095497_623.jpg"
    // ];
    $accessKeySecret = "n0vLa2pB3vuwvq9imbEKnYdlQQcwjI";
    // 将参数Key按字典顺序排序
    ksort($parameters);
    // 生成规范化请求字符串
    $canonicalizedQueryString = '';
    foreach ($parameters as $key => $value) {
      $canonicalizedQueryString .= '&' . $this->percentEncode($key)
        . '=' . $this->percentEncode($value);
    }
    // 生成用于计算签名的字符串 stringToSign
    $stringToSign = $method . '&%2F&' . $this->percentencode(substr($canonicalizedQueryString, 1));
    // 计算签名，注意accessKeySecret后面要加上字符'&'
    return base64_encode(hash_hmac('sha1', $stringToSign, $this->AppSecret . '&', true));
  }
}
