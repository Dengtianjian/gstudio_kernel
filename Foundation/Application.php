<?php

namespace gstudio_kernel\Foundation;

use gstudio_kernel\Model\ExtensionsModel;

class Application
{
  protected $pluginId = null; //* 当前插件ID
  protected $pluginPath = ""; //* 当前插件路径
  protected $uri = null; //* 请求的URI
  protected $globalMiddlware = []; //*全局中间件
  protected $router = null; //* 路由相关
  protected $request = null; //* 请求相关
  private function __clone()
  {
  }
  private function __construct()
  {
  }
  public function __get($name)
  {
    return $this->$name;
  }
  /**
   * 获取当前实例
   *
   */
  public static function ins()
  {
    return $GLOBALS['app'];
  }
  function setMiddlware($middlwareNameOfFunction)
  {
    array_push($this->globalMiddlware, $middlwareNameOfFunction);
  }
  //! 待废弃
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
  protected function executiveController()
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
      if ($this->request->ajax() === NULL) {
        View::outputFooter();
      }
      return $result;
    }
  }
  protected function executiveMiddleware()
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
  /**
   * 加载语言包
   *
   * @return void
   */
  protected function loadLang()
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
  protected function loadExtensions()
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
  protected function setAttachmentPath()
  {
    if (Config::get("attachmentPath") === NULL) {
      $attachmentRoot = \getglobal("setting/attachdir") . "plugin/" . GlobalVariables::getGG("id");
      $attachmentUrl = \getglobal("setting/attachurl") . "plugin/" . GlobalVariables::getGG("id");
      if (!is_dir($attachmentRoot)) {
        \dmkdir($attachmentRoot);
      }
      Config::set([
        "attachmentPath" => $attachmentUrl
      ]);
    }
  }
  protected static function initGlobalVariables($pluginId)
  {
    global $_G;
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
    GlobalVariables::set([
      "_GG" => $GlobalVariables
    ]);
  }
}
