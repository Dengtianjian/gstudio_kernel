<?php

namespace gstudio_kernel;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

use Exception;
use gstudio_kernel\Foundation\Request;
use gstudio_kernel\Foundation\Response;
use gstudio_kernel\Foundation\Router;
use gstudio_kernel\App\Dashboard\Controller as DashboardController;
use gstudio_kernel\Middleware\GlobalDashboardMiddleware;

class App
{
  public $pluginId = null;
  public $uri = null;
  private $globalMiddlware = [];
  private $router = null;
  private $request = null;
  private $mode = "production";
  private $useDashboard = false;
  function __construct($pluginId = null)
  {
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
    $uri = \addslashes($_GET['uri']);

    if ($this->useDashboard === true) {
      $this->setMiddlware(GlobalDashboardMiddleware::class);
      Router::view("dashboard", DashboardController\ContainerController::class);
    }

    $router = Router::match($uri);
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
    if ($result) {
      Response::success($result);
    }
  }
  private function executiveController()
  {
    $controller = $this->router['controller'];
    if (\is_callable($controller)) {
      return $controller($this->request);
    } else {
      $instance = new $controller();
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
        $middlewareInstance->handle($this->request, function () {
          $GLOBALS['ISNEXT'] = true;
        });
        if ($GLOBALS['ISNEXT'] == false) {
          break;
        } else {
          $executeCount++;
        }
      }
    }

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
}
