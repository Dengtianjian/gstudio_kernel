<?php

namespace gstudio_kernel;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

function errorHandler()
{
  if (\func_num_args() > 0) {
    debug(\func_get_args());
  } else {
    debug(\error_get_last());
  }
}

// error_reporting(\E_ALL);
\set_error_handler("gstudio_kernel\\errorHandler", 0);

use gstudio_kernel\App\Api as Api;
use gstudio_kernel\Foundation\Application;
use gstudio_kernel\Middleware as Middleware;
use gstudio_kernel\Foundation\Request;
use gstudio_kernel\Foundation\Response;
use gstudio_kernel\Foundation\Router;
use gstudio_kernel\Foundation\Config as Config;
use gstudio_kernel\Foundation\GlobalVariables;
use gstudio_kernel\Foundation\Exception\ErrorCode;
use gstudio_kernel\Middleware\GlobalExtensionsMiddleware;
use gstudio_kernel\Middleware\GlobalMultipleEncodeMiddleware;

class App extends Application
{
  /**
   * 构造函数
   *
   * @param string $pluginId 应用id
   */
  function __construct($pluginId = null)
  {
    \set_exception_handler("gstudio_kernel\Foundation\Exception\Exception::receive");

    //* 初始化全局数据
    self::initGlobalVariables($pluginId);

    $this->pluginId = $pluginId;
    $this->pluginPath = DISCUZ_ROOT . "source/plugin/$pluginId";
    $this->uri = \addslashes($_GET['uri']);

    $this->loadLang();
    ErrorCode::load(); //* 加载错误码

    //* 分析query
    //! 待废弃
    $queryString = $_SERVER['QUERY_STRING'];
    $queryString = explode("&", $queryString);
    $query = [];
    foreach ($queryString as $item) {
      $item = explode("=", $item);
      $query[$item[0]] = $item[1];
    }
    $GlobalVariables = [
      "request" => [
        "query" => $query
      ]
    ];

    GlobalVariables::set([
      "_GG" => $GlobalVariables
    ]);
    
    include_once(GlobalVariables::getGG("kernel/fullRoot") . "/Routes.php"); //* 载入kernel用到的路由
    include_once($this->pluginPath . "/Routes.php"); //* 载入路由
  }
  function init()
  {
    $this->setMiddlware(Middleware\GlobalSetsMiddleware::class);
    if (Config::get("dashboard/use") === true) {
      $this->setMiddlware(Middleware\GlobalDashboardMiddleware::class);
    }

    Router::view("_baidu_oauth", Api\Baidu\OAuthController::class); //* 后期通过扩展增加，待去掉

    $this->setMiddlware(Middleware\GlobalAuthMiddleware::class);

    $request = new Request();
    $this->request = $request;

    //* 设置附件目录
    $this->setAttachmentPath();

    //* 载入扩展
    if (Config::get("extensions")) {
      $this->loadExtensions();
      $this->setMiddlware(GlobalExtensionsMiddleware::class);
    }

    if ($this->request->ajax() === NULL) {
      $this->setMiddlware(GlobalMultipleEncodeMiddleware::class);
    }

    $executeMiddlewareResult = $this->executiveMiddleware();

    $router = Router::match($request->uri);
    $this->router = $router;
    if (!$router) {
      Response::error("ROUTE_DOES_NOT_EXIST");
    }

    if ($executeMiddlewareResult === false) {
      Response::error("MIDDLEWARE_EXECUTION_ERROR");
      return;
    }

    $result = $this->executiveController();

    if ($result !== NULL) {
      Response::success($result);
    }
  }
  public static function hook($pluginId)
  {
    self::initGlobalVariables($pluginId);
  }
}
