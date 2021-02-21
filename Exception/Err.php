<?php

namespace gstudio_kernel\Exception;

use gstudio_kernel\Foundation\Response;

class Err
{
  public static function exception($exception)
  {
    $router = $GLOBALS['app']['router'];
    $code = $exception->getCode();
    $message = $exception->getMessage();
    $file = $exception->getFile();
    $line = $exception->getLine();
    $trace = $exception->getTrace();
    $previous = $exception->getPrevious();
    $traceSimple = $exception->getTraceAsString();
    $traceSimple = \explode("\n", $traceSimple);
    if ($router['type'] === "view") {
      if ($GLOBALS[$GLOBALS['app']['pluginId']]['mode'] === "production") {
        include Response::systemView("error");
      } else {
        include Response::systemView("error");
      }
    } else {
      if ($GLOBALS[$GLOBALS['app']['pluginId']]['mode'] === "production") {
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
}
