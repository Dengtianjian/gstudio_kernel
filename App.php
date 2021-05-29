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
    global $_G;

    \set_exception_handler("gstudio_kernel\Foundation\Exception\Exception::receive");

    //* 存放全局用到的数据
    $GlobalVariables = [
      "id" => $pluginId, //* 当前运行中的应用ID
      "sets" => [], //* 设置项，包含配置项里设置的全局设置项
      "rewriteURL" => [], //* 重写的URL
      "mode" => Config::get("mode", $pluginId), //* 当前运行模式
      "langs" => [], //* 字典
      "kernel" => [ //* 内核
        "root" => "source/plugin/gstudio_kernel",
        "fullRoot" => DISCUZ_ROOT . "source/plugin/gstudio_kernel",
        "URLRoot" => $_G['siteurl'] . "source/plugin/gstudio_kernel",
        "assets" => $_G['siteurl'] . "source/plugin/gstudio_kernel/Assets",
        "views" => $_G['siteurl'] . "source/plugin/gstudio_kernel/Views",
      ],
      "addon" => [ //* 当前运行中的应用信息
        "id" => $pluginId,  //* 应用ID
        "root" => "source/plugin/$pluginId",
        "fullRoot" => DISCUZ_ROOT . "source/plugin/$pluginId",
        "URLRoot" => $_G['siteurl'] . "source/plugin/$pluginId", //* 应用文件夹路径
        "assets" => $_G['siteurl'] . "source/plugin/$pluginId/Assets", //* 应用静态文件路径
        "views" => $_G['siteurl'] . "source/plugin/$pluginId/Views", //* 应用模板文件路径
      ],
      "request" => [ //* 请求相关
        "uri" => \addslashes($_GET['uri']), //* 请求的URI
        "method" => $_SERVER['REQUEST_METHOD'], //* 请求的方法
        "params" => [], //* 请求参数
        "query" => [], //* 请求的query
      ]
    ];

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
    $GlobalVariables['request']['query'] = $query;

    GlobalVariables::set([
      "_GG" => $GlobalVariables
    ]);
    include_once($GlobalVariables['kernel']['fullRoot'] . "/Routes.php"); //* 载入kernel用到的路由
    include_once($this->pluginPath . "/Routes.php"); //* 载入路由
  }
  function init()
  {
    $this->setMiddlware(Middleware\GlobalSetsMiddleware::class);
    if (Config::get("dashboard/use") === true) {
      $this->setMiddlware(Middleware\GlobalDashboardMiddleware::class);
    }

    Router::view("_download", Main\DownloadAttachmentView::class); //* 后期优化
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

    $router = Router::match($this->uri);
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
}
