<?php

namespace gstudio_kernel\Foundation;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

class Http
{
  /**
   * 获取用户IP地址
   *
   * @return string IP地址
   */
  public static function realClientIp()
  {
    $ip = null;
    if (getenv("HTTP_CLIENT_IP")) {
      $ip = getenv("HTTP_CLIENT_IP");
    } else if (getenv("HTTP_X_FORWARDED_FOR")) {
      $ip = getenv("HTTP_X_FORWARDED_FOR");
    } else if (getenv("REMOTE_ADDR")) {
      $ip = getenv("REMOTE_ADDR");
    }
    return $ip;
  }
  //! 准废弃
  public static function sGet($url, $post_data = [])
  {
    $postdata = http_build_query($post_data);
    $options = array(
      'http' => array(
        'method' => 'GET',
        'header' => 'Content-type:application/x-www-form-urlencoded',
        'timeout' => 15 * 60 // 超时时间（单位:s）
      )
    );
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    return $result;
  }
  //! 准废弃
  public static function sPost($url, $post_data)
  {
    $postdata = http_build_query($post_data);
    $options = array(
      'http' => array(
        'method' => 'POST',
        'header' => 'Content-type:application/x-www-form-urlencoded',
        'content' => $postdata,
        'timeout' => 15 * 60 // 超时时间（单位:s）
      )
    );
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    return $result;
  }
}
