<?php

namespace gstudio_kernel;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

// error_reporting(E_ALL ^ E_NOTICE);

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
  private $globalSetMarks = []; //* 全局设置项标记
  public function __get($name)
  {
    return $this->$name;
  }
  function __construct($pluginId = null)
  {
    $GLOBALS['GLANG'] = [];

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

    include_once($GLOBALS['gstudio_kernel']['pluginPath'] . "/Langs/" . CHARSET . ".php");
    $langDirPath = $GLOBALS[$this->pluginId]['pluginPath'] . "/Langs/";
    if (\file_exists($langDirPath)) {
      $langFilePath = $GLOBALS[$this->pluginId]['pluginPath'] . "/Langs/" . CHARSET . ".php";
      if (\file_exists($langFilePath)) {
        include_once($langFilePath);
      }
    }
    $GLOBALS['GLANG'] = Lang::all();
    ErrorCode::load(); //* 加载错误码

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
  function globalSets($setMarks)
  {
    if (is_string($setMarks)) {
      if (\func_num_args() > 1) {
        $setMarks = func_get_args();
      }
    }
    if (\is_array($setMarks)) {
      $this->globalSetMarks = \array_merge($this->globalSetMarks, $setMarks);
    } else {
      array_push($this->globalSetMarks, $setMarks);
    }
  }
  function init()
  {
    Router::any("_gset", GetGSetController::class);
    if ($this->useDashboard === true) {
      $this->setMiddlware(Middleware\GlobalDashboardMiddleware::class);
      Router::view("dashboard", DashboardController\ContainerController::class);
      Router::postView("_dashboard_save", DashboardController\SaveSetController::class);
      Router::get("_set", DashboardController\GetSetController::class);
      Router::put("_dashboard_cleansetimg", DashboardController\CleanSetImageController::class);
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
      Excep::exception($e);
    }

    if ($executeMiddlewareResult === false) {
      return;
    }

    $result = null;
    try {
      $result = $this->executiveController();
      if ($this->router['type'] === "view") {
        $multipleEncodeJSScript = "";
        if (CHARSET === "gbk") {
          $langJson = \serialize($GLOBALS['GLANG']);
          if ($langJson === false) {
            $langJson = \serialize([]);
          }
          $multipleEncodeJSScript = "
    <script src='source/plugin/gstudio_kernel/Assets/js/unserialize.js'></script>
    <script>
      const GLANG=unserialize('$langJson');
    </script>
    ";
        } else {
          $langJson = \json_encode($GLOBALS['GLANG']);
          if ($langJson === false) {
            $langJson = \json_encode([]);
          }
          $multipleEncodeJSScript = "
    <script>
      const GLANG=JSON.parse('$langJson');
    </script>
    ";
        }
        if ($this->mode === "development") {
          $multipleEncodeJSScript .= "
          <script>
          console.log(GLANG);
        </script>
          ";
        }
        print_r($multipleEncodeJSScript);
      }
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
      if ($instance->DZHash === true) {
        if (!$this->request->params("DZHash") || (!$this->request->params("DZHash") && !$this->request->params("formhash"))) {
          Response::error("LLLEGAL_SUBMISSION");
        }
        if ($this->request->params("DZHash") != \FORMHASH || (!$this->request->params("DZHash") && $this->request->params("formhash") != \FORMHASH)) {
          Response::error("LLLEGAL_SUBMISSION");
        }
      }
      $result = $instance->data($this->request);
      return $result;
    }
  }
  private function executiveMiddleware()
  {
    $middlewares = array_reverse($this->globalMiddlware);
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
}
