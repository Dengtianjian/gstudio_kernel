<?php

namespace gstudio_kernel\Exception;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

use gstudio_kernel\Foundation\Response;

class Excep
{
  public static function handle($code = 0, $message = "", $file = "", $line = null, $trace = "", $traceString = NULL, $previous = null)
  {
    $traceString = \explode(\PHP_EOL, $traceString);
    if ($GLOBALS['app']->router === NULL || $GLOBALS['app']->router['type'] === "view") {
      if ($GLOBALS[$GLOBALS['app']->pluginId]['mode'] === "production") {
        include Response::systemView("error");
      } else {
        include Response::systemView("error");
      }
    } else {
      if ($GLOBALS[$GLOBALS['app']->pluginId]['mode'] === "production") {
        Response::error(500, 500000, "SERVER ERROR");
      } else {
        Response::error(500, 500000, "SERVER ERROR", [
          "code" => $code,
          "message" => $message,
          "file" => $file,
          "line" => $line,
          "trace" => $trace,
          "previous" => $previous
        ]);
      }
    }
    exit();
  }
  public static function exception($exception)
  {
    $code = $exception->getCode();
    $message = $exception->getMessage();
    $file = $exception->getFile();
    $line = $exception->getLine();
    $trace = $exception->getTrace();
    $previous = $exception->getPrevious();
    $traceString = $exception->getTraceAsString();
    self::handle($code, $message, $file, $line, $trace, $traceString, $previous);
  }
  public static function t($message)
  {
    self::handle(0, $message);
  }
}
