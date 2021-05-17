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
use gstudio_kernel\Middleware as Middleware;
use gstudio_kernel\Foundation\Request;
use gstudio_kernel\Foundation\Response;
use gstudio_kernel\Foundation\Router;
use gstudio_kernel\Foundation\Auth;
use gstudio_kernel\Foundation\Lang;
use gstudio_kernel\Foundation\Config as Config;
use gstudio_kernel\Foundation\GlobalVariables;
use gstudio_kernel\Model\ExtensionsModel;
use gstudio_kernel\Foundation\Exception\ErrorCode;
use gstudio_kernel\Middleware\GlobalExtensionsMiddleware;

class App
{
  private $pluginId = null; //* 当前插件ID
  private $pluginPath = ""; //* 当前插件路径
  private $uri = null; //* 请求的URI
  private $globalMiddlware = []; //*全局中间件
  private $router = null; //* 路由相关
  private $request = null; //* 请求相关
  public function __get($name)
  {
    return $this->$name;
  }
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
  function setMiddlware($middlwareNameOfFunction)
  {
    array_push($this->globalMiddlware, $middlwareNameOfFunction);
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

    $this->request = new Request();

    //* 载入扩展
    if (Config::get("extensions")) {
      $this->loadExtensions();
      $this->setMiddlware(GlobalExtensionsMiddleware::class);
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
    if ($this->router['type'] === "view") {
      $multipleEncodeJSScript = "";
      if (CHARSET === "gbk") {
        $langJson = \serialize(GlobalVariables::get("_GG/langs"));
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
        $langJson = \json_encode(GlobalVariables::get("_GG/langs"));
        if ($langJson === false) {
          $langJson = \json_encode([]);
        }
        $multipleEncodeJSScript = "
<script>
  const GLANG=JSON.parse('$langJson');
</script>
    ";
      }
      if (Config::get("mode") === "development") {
        $multipleEncodeJSScript .= "
<script>
  console.log(GLANG);
</script>
          ";
      }
      print_r($multipleEncodeJSScript);
    }
    if ($result !== NULL) {
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
      $instance->verifyFormhash();
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
        $middlewareItem(function () use (&$executeCount) {
          $executeCount++;
        }, $this->request);
      } else {
        $middlewareInstance = new $middlewareItem();
        $isNext = false;
        $middlewareInstance->handle(function () use (&$isNext) {
          $isNext = true;
        }, $this->request);
        if ($isNext == false) {
          break;
        } else {
          $executeCount++;
        }
      }
    }

    return $executeCount === $middlewareCount;
  }
  public function setDashboardTable($globalVarKey, $tableName)
  {
    GlobalVariables::set([
      "_GG" => [
        "addon" => [
          "dashboard" => [
            $globalVarKey => $tableName
          ]
        ]
      ]
    ]);
  }
  /**
   * 加载语言包
   *
   * @return void
   */
  public function loadLang()
  {
    include_once(DISCUZ_ROOT . "source/plugin/gstudio_kernel/Langs/" . CHARSET . ".php");
    $langDirPath = $this->pluginPath . "/Langs/";
    if (\file_exists($langDirPath)) {
      $langFilePath = $this->pluginPath . "/Langs/" . CHARSET . ".php";
      if (\file_exists($langFilePath)) {
        include_once($langFilePath);
      }
    }
    GlobalVariables::set([
      "_GG" => [
        "langs" => Lang::all()
      ]
    ]);
  }
  /**
   * 载入扩展
   * 获取已开启的扩展，然后访问扩展Main入口文件，执行handle方法
   *
   * @return void
   */
  public function loadExtensions()
  {
    $EM = new ExtensionsModel();
    $enabledExtensions = $EM->where("enabled", 1)->get();
    foreach ($enabledExtensions as $extensionItem) {
      $mainFilepath = DISCUZ_ROOT . $extensionItem['path'] . "/Main.php";
      if (!\file_exists($mainFilepath)) {
        Response::error(500, 500, $extensionItem['name'] . " 扩展文件已损坏，请重新安装");
      }
      $namespace = "\\" . $extensionItem['plugin_id'] . "\\Extensions\\" . $extensionItem['extension_id'] . "\\Main";
      if (!\class_exists($namespace)) {
        Response::error(500, 500, $extensionItem['name'] . " 扩展文件已损坏，请重新安装");
      }
      $MainInstance = new $namespace();
      $MainInstance->handle();
    }
  }
}
