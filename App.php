<?php

namespace gstudio_kernel;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

use Exception;
use gstudio_kernel\Middleware as Middleware;
use gstudio_kernel\Foundation\Request;
use gstudio_kernel\Foundation\Response;
use gstudio_kernel\Foundation\Router;
use gstudio_kernel\App\Dashboard\Controller as DashboardController;
use gstudio_kernel\Exception\ErrorCode;
use gstudio_kernel\Foundation\Auth;

class App
{
  private $pluginId = null; //* 当前插件ID
  private $uri = null; //* 请求的URI
  private $globalMiddlware = []; //*全局中间件
  private $router = null; //* 路由相关
  private $request = null; //* 请求相关
  private $mode = "production"; //* 当前运行模式
  private $useDashboard = false; //* 是否有后台功能
  private $salt = "gstudio_kernel"; //* salt
  public function __get($name)
  {
    return $this->$name;
  }
  function __construct($pluginId = null)
  {
    ErrorCode::load(); //* 加载错误码
    $GLOBALS["gstudio_kernel"] = [
      "mode" => $this->mode,
      "pluginId" => "gstudio_kernel",
      "pluginPath" => "source/plugin/gstudio_kernel",
      "assets" => "source/plugin/gstudio_kernel/Assets",
      "devingPluginId" => $pluginId
    ];
    $devingPlguinPath = "source/plugin/$pluginId";
    $GLOBALS[$pluginId] = [
      "mode" => $this->mode,
      "pluginId" => $pluginId,
      "pluginPath" => "source/plugin/$pluginId",
      "assets" => "source/plugin/$pluginId/Assets"
    ];
    $this->pluginId = $pluginId;
    $this->uri = \addslashes($_GET['uri']);
    include_once(DISCUZ_ROOT . "$devingPlguinPath/routes.php");
  }
  function setMiddlware($middlwareNameOfFunction)
  {
    array_push($this->globalMiddlware, $middlwareNameOfFunction);
  }
  function setMode($mode)
  {
    $this->mode = $mode;
    $GLOBALS[$this->pluginId]['mode'] = $mode;
  }
  function init()
  {
    if ($this->useDashboard === true) {
      $this->setMiddlware(Middleware\GlobalDashboardMiddleware::class);
      Router::view("dashboard", DashboardController\ContainerController::class);
      Router::postView("_dashboard_save", DashboardController\SaveSetController::class);
    }
    $this->setMiddlware(Middleware\GlobalAuthMiddleware::class);

    $router = Router::match($this->uri);
    if (!$router) {
      Response::error("ROUTE_DOES_NOT_EXIST");
    }
    $this->router = $router;
    $this->request = new Request();
    $executeMiddlewareResult = true;
    try {
      $executeMiddlewareResult = $this->executiveMiddleware();
    } catch (Exception $e) {
      $code = $e->getCode();
      $message = $e->getMessage();
      $file = $e->getFile();
      $line = $e->getLine();
      $trace = $e->getTrace();
      $previous = $e->getPrevious();
      $traceSimple = $e->getTraceAsString();
      $traceSimple = \explode("\n", $traceSimple);
      if ($router['type'] === "view") {
        if ($GLOBALS[$this->pluginId]['mode'] === "production") {
          include Response::systemView("error");
        } else {
          include Response::systemView("error");
        }
      } else {
        if ($GLOBALS[$this->pluginId]['mode'] === "production") {
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

    if ($executeMiddlewareResult === false) {
      return;
    }

    $result = null;
    try {
      $result = $this->executiveController();
    } catch (Exception $e) {
      $code = $e->getCode();
      $message = $e->getMessage();
      $file = $e->getFile();
      $line = $e->getLine();
      $trace = $e->getTrace();
      $previous = $e->getPrevious();
      $traceSimple = $e->getTraceAsString();
      $traceSimple = \explode("\n", $traceSimple);
      if ($router['type'] === "view") {
        if ($GLOBALS[$this->pluginId]['mode'] === "production") {
          include Response::systemView("error");
        } else {
          include Response::systemView("error");
        }
      } else {
        if ($GLOBALS[$this->pluginId]['mode'] === "production") {
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
    Response::success($result);
  }
  private function executiveController()
  {
    $controller = $this->router['controller'];
    if (\is_callable($controller)) {
      return $controller($this->request);
    } else {
      $instance = new $controller();
      if (\property_exists($controller, "Auth") && $instance::Auth) {
        if (Auth::isVerified() === false) {
          Auth::check();
        }
      }
      return $instance->data($this->request);
    }
  }
  private function executiveMiddleware()
  {
    $middlewares = $this->globalMiddlware;
    if ($this->router['middleware']) {
      if (\is_array($this->router['middleware'])) {
        $middlewares = \array_merge($this->router['middleware']);
      } else {
        $middlewares[] = $this->router['middleware'];
      }
    }

    $middlewareCount = count($middlewares);
    if ($middlewareCount === 0) {
      return;
    }
    $executeCount = 0;

    foreach ($middlewares as $middlewareItem) {
      if (\is_callable($middlewareItem)) {
        $middlewareItem($this->request, function () {
          global $executeCount;
          $executeCount++;
        });
      } else {
        $middlewareInstance = new $middlewareItem();
        $GLOBALS['ISNEXT'] = false;
        $middlewareInstance->handle(function () {
          $GLOBALS['ISNEXT'] = true;
        });
        if ($GLOBALS['ISNEXT'] == false) {
          break;
        } else {
          $executeCount++;
        }
      }
    }
    unset($GLOBALS['ISNEXT']);

    return $executeCount === $middlewareCount;
  }
  public function useDashboard($isUse = true)
  {
    $this->useDashboard = $isUse;
  }
  public function setDashboardTable($globalVarKey, $tableName)
  {
    $GLOBALS['gstudio_kernel']['dashboard'][$globalVarKey] = $tableName;
  }
  public function setTokenValidPeriod($count)
  {
    $this->tokenValidPeriod = $count;
  }
  public function setSalt($salt)
  {
    $this->salt = $salt;
  }
}
