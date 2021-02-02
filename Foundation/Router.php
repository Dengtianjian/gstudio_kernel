<?php

namespace gstudio_kernel\Foundation;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

class Router
{
  private static $routes = [];
  static function get($uri, $controllerNameOfFunction, $middlewareName = null)
  {
    self::register("api", "get", $uri, $controllerNameOfFunction, $middlewareName);
  }
  static function post($uri, $controllerNameOfFunction, $middlewareName = null)
  {
    self::register("api", "post", $uri, $controllerNameOfFunction, $middlewareName);
  }
  static function put($uri, $controllerNameOfFunction, $middlewareName = null)
  {
    self::register("api", "put", $uri, $controllerNameOfFunction, $middlewareName);
  }
  static function patch($uri, $controllerNameOfFunction, $middlewareName = null)
  {
    self::register("api", "patch", $uri, $controllerNameOfFunction, $middlewareName);
  }
  static function delete($uri, $controllerNameOfFunction, $middlewareName = null)
  {
    self::register("api", "delete", $uri, $controllerNameOfFunction, $middlewareName);
  }
  static function view($uri, $controllerNameOfFunction, $middlewareName = null)
  {
    self::register("view", "get", $uri, $controllerNameOfFunction, $middlewareName);
  }
  static function postView($uri, $controllerNameOfFunction, $middlewareName = null)
  {
    self::register("view", "post", $uri, $controllerNameOfFunction, $middlewareName);
  }
  static function putView($uri, $controllerNameOfFunction, $middlewareName = null)
  {
    self::register("view", "put", $uri, $controllerNameOfFunction, $middlewareName);
  }
  static function patchView($uri, $controllerNameOfFunction, $middlewareName = null)
  {
    self::register("view", "patch", $uri, $controllerNameOfFunction, $middlewareName);
  }
  static function deleteView($uri, $controllerNameOfFunction, $middlewareName = null)
  {
    self::register("view", "delete", $uri, $controllerNameOfFunction, $middlewareName);
  }
  static function any($uri, $controllerNameOfFunction, $middlewareName = null)
  {
    self::register("api", "any", $uri, $controllerNameOfFunction, $middlewareName);
  }
  static function match($uri)
  {
    $method = \strtolower($_SERVER['REQUEST_METHOD']);
    if ($_GET['_method']) {
      $method = \addslashes(\strtolower($_GET['_method']));
    }
    if (!self::$routes[$method][$uri]) {
      if (self::$routes['any'][$uri]) {
        return self::$routes['any'][$uri];
      }
      Response::error("METHOD_NOT_ALLOWED");
    }
    return self::$routes[$method][$uri];
  }
  static function register($type, $method, $uri, $controllerNameOfFunction, $middlewareName = null)
  {
    self::$routes[$method][$uri] = [
      "controller" => $controllerNameOfFunction,
      "middleware" => $middlewareName,
      "type" => $type,
      "method" => $method
    ];
    return self::$routes;
  }
}
