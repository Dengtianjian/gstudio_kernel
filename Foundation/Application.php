<?php

namespace gstudio_kernel\Foundation;

use Error;
use gstudio_kernel\Model\ExtensionsModel;

class Application
{
  protected $pluginId = null; //* 当前插件ID
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
  protected function executiveController()
  {

    $controllerParams = $this->router['controller'];
    $executeFunName = null;
    if (is_array($controllerParams)) {
      $length = count($controllerParams);
      $controller = $controllerParams[0];
      if ($length === 2) {
        $executeFunName = $controllerParams[1];
      }
    } else {
      $controller = $controllerParams;
    }

    if (\is_callable($controller)) {
      return $controller($this->request);
    } else {
      $instance = new $controller($this->request);

      if (empty($executeFunName)) {
        if ($this->router['type'] === "async" || $this->request->async()) {
          if (strtolower($this->request->method) === "get") {
            Response::error(500, "500:AsyncControlerNotAllowGetMethodRequest", "禁止Get请求");
          }
          if (!method_exists($instance, "async") && $this->router['type'] === "resource") {
            Response::error(500, "500:ControllerMissingAsyncFunction", "服务器错误", [], "控制器缺失async函数");
          } else if (!method_exists($instance, "data") && $this->router['type'] === "async") {
            if (!method_exists($instance, "post")) {
              Response::error(500, "500:ControllerMissingDataHandlerFunction", "服务器错误", [], "控制器缺少data|post函数");
            }
          }
        }
        if ($this->router['type'] === "resource" && $this->request->async()) {
          $executeFunName = "async";
        } else if ($this->router['type'] === "async") {
          $executeFunName = "data";
          if (!method_exists($instance, $executeFunName)) {
            $executeFunName = "post";
          }
        } else if (method_exists($instance, $this->request->method)) {
          $executeFunName = $this->request->method;
        } else {
          if (!method_exists($instance, "data")) {
            throw new Error("执行的控制器缺少data函数");
          }
          $executeFunName = "data";
        }
      }

      // if ($instance->Auth === true) {
      //   if (Auth::isVerified() === false) {
      //     Auth::check();
      //   }
      // }
      // if ($instance->Admin !== false) {
      //   $adminId = $instance->Admin;
      //   if (Auth::isVerified() == false) {
      //     Auth::check();
      //   }
      //   if (Auth::isVerifiedAdmin() == false) {
      //     Auth::checkAdmin($adminId);
      //   }
      // }
      // $instance->verifyFormhash();

      $result = $instance->data($this->request);

      if ($this->request->ajax() === NULL) {
        View::outputFooter();
      } else {
        if (gettype($instance->serialization) === "string" || (is_array($instance->serialization) && count($instance->serialization) > 0)) {
          if (gettype($instance->serialization) === "array") {
            $ruleName = "serializer_" . time();
            Serializer::addRule($ruleName, $instance->serialization);
            $instance->serialization = $ruleName;
          }
          $result = Serializer::use($instance->serialization, $result);
        }
      }

      return $result;
    }
  }
  protected function executiveMiddleware()
  {
    $middlewares = array_reverse($this->globalMiddlware);
    if (isset($this->router['middleware']) && $this->router['middleware']) {
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
    $charset = strtoupper(CHARSET);
    include_once(DISCUZ_ROOT . "source/plugin/gstudio_kernel/Langs/$charset.php");
    $langDirPath = F_APP_ROOT . "/Langs/";
    if (\file_exists($langDirPath)) {
      $langFilePath = F_APP_ROOT . "/Langs/$charset.php";
      if (\file_exists($langFilePath)) {
        include_once($langFilePath);
      }
    }
    Store::setApp([
      "langs"=>Lang::all()
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
      $attachmentRoot = \getglobal("setting/attachdir") . "plugin/" . F_APP_ID;
      $attachmentUrl = \getglobal("setting/attachurl") . "plugin/" . F_APP_ID;
      if (!is_dir($attachmentRoot)) {
        \dmkdir($attachmentRoot);
      }
      Config::set([
        "attachmentPath" => $attachmentUrl
      ]);
    }
  }
  protected function initAppStore()
  {
    //* 存放全局用到的数据
    $__App = [
      "id" => F_APP_ID, //* 当前运行中的应用ID
      "rewriteURL" => [], //* 重写的URL
      "mode" => Config::get("mode", F_APP_ID), //* 当前运行模式
      "langs" => [], //* 字典
      "kernel" => [], //* 内核
      "addon" => [ //* 当前运行中的应用信息
        "id" => $this->pluginId,
        "root" => F_APP_ROOT,
        "assets" => F_APP_ROOT . "/Assets",
        "views" => F_APP_ROOT . "/Views"
      ]
    ];
    Store::setApp($__App);
  }
  protected function initConfig()
  {
    $fileBase = F_APP_ROOT;
    $configFilePath = F_APP_ROOT . "/Config.php";
    if (!file_exists($configFilePath)) {
      $fileBase .= "/Configs";
      $configFilePath = "$fileBase/Config.php";
    }
    Config::read($configFilePath);

    //* 模式下的配置文件
    $modeConfigFilePath = "$fileBase/Config." . Config::get("mode") . ".php";
    if (file_exists($modeConfigFilePath)) {
      Config::read($modeConfigFilePath);
    }

    //* 本地下的配置文件
    $localConfigFilePath = "$fileBase/Config.local.php";
    if (file_exists($localConfigFilePath)) {
      Config::read($localConfigFilePath);
    }
  }
}
