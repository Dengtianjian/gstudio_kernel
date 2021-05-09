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

// error_reporting(\E_ALL ^ \E_NOTICE);
// \set_error_handler("gstudio_kernel\\errorHandler", 0);
use gstudio_kernel\Middleware as Middleware;
use gstudio_kernel\Foundation\Request;
use gstudio_kernel\Foundation\Response;
use gstudio_kernel\Foundation\Router;
use gstudio_kernel\Exception\ErrorCode;
use gstudio_kernel\Foundation\Auth;
use gstudio_kernel\Foundation\Lang;
use gstudio_kernel\Foundation\Config as Config;
use gstudio_kernel\Foundation\GlobalVariables;

class App
{
  private $pluginId = null; //* 当前插件ID
  private $pluginPath = ""; //* 当前插件路径
  private $uri = null; //* 请求的URI
  private $globalMiddlware = []; //*全局中间件
  private $router = null; //* 路由相关
  private $request = null; //* 请求相关
  private $mode = "production"; //* 当前运行模式
  public function __get($name)
  {
    return $this->$name;
  }
  function __construct($pluginId = null)
  {
    global $_G;

    \set_exception_handler("gstudio_kernel\Exception\Excep::exception");

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

    //! 老代码 待去除
    $GLOBALS["gstudio_kernel"] = [
      "mode" => $this->mode,
      "pluginId" => "gstudio_kernel",
      "pluginPath" => "source/plugin/gstudio_kernel",
      "assets" => "source/plugin/gstudio_kernel/Assets",
      "devingPluginId" => $pluginId
    ];
    $GLOBALS[$pluginId] = [
      "mode" => $this->mode,
      "pluginId" => $pluginId,
      "pluginPath" => "source/plugin/$pluginId",
      "assets" => "source/plugin/$pluginId/Assets"
    ];
    $this->pluginId = $pluginId;
    $this->pluginPath = DISCUZ_ROOT . "source/plugin/$pluginId";
    $this->uri = \addslashes($_GET['uri']);
    $GLOBALS['GURLS'] = [];

    $this->loadLang();
    $GlobalVariables['langs'] = $GLOBALS['GLANG'];
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

    include_once(GlobalVariables::getGG("kernel/fullRoot") . "/Routes.php"); //* 载入kernel用到的路由
    include_once($this->pluginPath . "/routes.php"); //* 载入路由
    GlobalVariables::set([
      "_GG" => $GlobalVariables
    ]);
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

    $this->setMiddlware(Middleware\GlobalAuthMiddleware::class);

    $this->request = new Request();

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
        $middlewareItem($this->request, function () {
          global $executeCount;
          $executeCount++;
        });
      } else {
        $middlewareInstance = new $middlewareItem();
        $isNext = false;
        $middlewareInstance->handle(function () use (&$isNext) {
          $isNext = true;
        });
        if ($isNext == false) {
          break;
        } else {
          $executeCount++;
        }
      }
    }
    unset($GLOBALS['ISNEXT']);

    return $executeCount === $middlewareCount;
  }
  public function setDashboardTable($globalVarKey, $tableName)
  {
    $GLOBALS['gstudio_kernel']['dashboard'][$globalVarKey] = $tableName;
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
    $GLOBALS['GLANG'] = Lang::all();
  }
}
