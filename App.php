<?php

namespace gstudio_kernel;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

use Exception;
use gstudio_kernel\App\Api\GetGSetController;
use gstudio_kernel\Middleware as Middleware;
use gstudio_kernel\Foundation\Request;
use gstudio_kernel\Foundation\Response;
use gstudio_kernel\Foundation\Router;
use gstudio_kernel\App\Dashboard\Controller as DashboardController;
use gstudio_kernel\Exception\ErrorCode;
use gstudio_kernel\Exception\Excep;
use gstudio_kernel\Foundation\Auth;
use gstudio_kernel\Foundation\Lang;

class App
{
  private $pluginId = null; //* 当前插件ID
  private $uri = null; //* 请求的URI
  private $globalMiddlware = []; //*全局中间件
  private $router = null; //* 路由相关
  private $request = null; //* 请求相关
  private $mode = "production"; //* 当前运行模式
  private $useDashboard = false; //* 是否有后台功能
  private $salt = "gstudio_kernel"; //* token用到的 salt
  private $BigGKeyWhiteList = []; //* DZX大G key白名单。用于查询 大G 值时用到，一般是cache/plugin插件的变量
  private $isMultipleEncode = false; //* 多种编码
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
    Router::any("_gset", GetGSetController::class);
    if ($this->useDashboard === true) {
      $this->setMiddlware(Middleware\GlobalDashboardMiddleware::class);
      Router::view("dashboard", DashboardController\ContainerController::class);
      Router::postView("_dashboard_save", DashboardController\SaveSetController::class);
      Router::get("_set", DashboardController\GetSetController::class);
    }
    $this->setMiddlware(Middleware\GlobalAuthMiddleware::class);
    include_once($GLOBALS['gstudio_kernel']['pluginPath'] . "/Langs/" . CHARSET . ".php");
    $GLOBALS['GLANG'] = [];

    if ($this->isMultipleEncode === true) {
      $langFilePath = $GLOBALS[$this->pluginId]['pluginPath'] . "/Langs/" . CHARSET . ".php";
      if (!\file_exists($langFilePath)) {
        Excep::t(Lang::value('kernel')['dictionary_file_does_not_exist']);
      }
      include_once($langFilePath);
      $GLOBALS['GLANG'] = Lang::all();
    }

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
      Excep::exception($e);
    }

    if ($executeMiddlewareResult === false) {
      return;
    }

    $result = null;
    try {
      $result = $this->executiveController();
    } catch (Exception $e) {
      Excep::exception($e);
    }
    if ($result !== NULL) {
      Response::success($result);
    }
  }
  private function executiveController()
  {
    global $_G;
    $controller = $this->router['controller'];
    if (\is_callable($controller)) {
      return $controller($this->request);
    } else {
      $instance = new $controller();
      if ($instance->Auth === true) {
        if (Auth::isVerified() === false) {
          Auth::check();
        }
      }
      if ($instance->Admin !== false) {
        $adminId = $instance->Admin;
        if (Auth::isVerified() == false) {
          Auth::check();
        }
        if (Auth::isVerifiedAdmin() == false) {
          Auth::checkAdmin($adminId);
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
  public function addBigGKey($key)
  {
    \array_push($this->BigGKeyWhiteList, $key);
  }
  public function multipleEncode($open = true)
  {
    $this->isMultipleEncode = $open;
  }
}
