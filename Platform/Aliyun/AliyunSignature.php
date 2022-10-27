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
    $parameters = [
      // 公共参数
      'Format' => 'json',
      'Version' => '2019-12-30',
      'AccessKeyId' => "LTAI5tCotimM5YZ9mUs7AdAr",
      'SignatureVersion' => '1.0',
      'SignatureMethod' => 'HMAC-SHA1',
      'SignatureNonce' => uniqid(),
      'Timestamp' => date('Y-m-d\TH:i:s\Z'),
      "Action" => "SegmentCommonImage",
      "ImageURL" => "http://discuz.cooocc.com/bonsai/1.jpg"
    ];
    $accessKeySecret = "DgMk11hOKLwvXtPFkRBslgACzi9nGN";
    // 将参数Key按字典顺序排序
    ksort($parameters);
    // 生成规范化请求字符串
    $canonicalizedQueryString = '';
    foreach ($parameters as $key => $value) {
      $canonicalizedQueryString .= '&' . $this->percentEncode($key)
        . '=' . $this->percentEncode($value);
    }
    // 生成用于计算签名的字符串 stringToSign
    $stringToSign = 'GET&%2F&' . $this->percentencode(substr($canonicalizedQueryString, 1));
    // 计算签名，注意accessKeySecret后面要加上字符'&'
    $signature = base64_encode(hash_hmac('sha1', $stringToSign, $accessKeySecret . '&', true));

    $parameters['Signature'] = $signature;
    return $parameters;

    // //* LTAI5tCotimM5YZ9mUs7AdAr DgMk11hOKLwvXtPFkRBslgACzi9nGN
    // ksort($params);
    // $stringToSign = strtoupper($method) . '&' . $this->percentEncode('/') . '&';
    // // $stringToSign = strtoupper($method) . '&' . $this->percentEncode('/') . '&';
    // $tmp = "";
    // foreach ($params as $key => $val) {
    //   // $tmp .= '&' . $key . '=' . $val;
    //   $tmp .= '&' . $this->percentEncode($key) . '=' . $this->percentEncode($val);
    // }
    // $tmp = trim($tmp, '&');
    // $stringToSign = $stringToSign . $this->percentEncode($tmp);
    // $key = $this->AppSecret . '&';
    // // return $stringToSign;
    // $hmac = hash_hmac("sha1", $stringToSign, $key, true);
    // return base64_encode($hmac);
  }
}
