<?php

namespace gstudio_kernel\Foundation\Exception;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

use gstudio_kernel\Foundation\Config as Config;
use gstudio_kernel\Foundation\Response;
use gstudio_kernel\Foundation\View;

class Exception
{
  public static function handle($code = 0, $message = "", $file = "", $line = null, $trace = "", $traceString = NULL, $previous = null)
  {
    $traceString = \explode(\PHP_EOL, $traceString);
    if ($GLOBALS['app']->router === NULL || $GLOBALS['app']->router['type'] === "view") {
      if (Config::get("mode") === "production") {
        View::systemPage("error", [
          "code" => $code, "message" => $message, "file" => $file, "line" => $line, "trace" => $trace, "traceString" => $traceString, "previous" => $previous
        ]);
      } else {
        View::systemPage("error", [
          "code" => $code, "message" => $message, "file" => $file, "line" => $line, "trace" => $trace, "traceString" => $traceString, "previous" => $previous
        ]);
      }
    } else {
      if (Config::get("mode") === "production") {
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
  public static function receive($exception)
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
  public static function out($message)
  {
    self::handle(0, $message);
  }
}
