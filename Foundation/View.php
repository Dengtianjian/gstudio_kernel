<?php

namespace gstudio_kernel\Foundation;

use gstudio_kernel\Foundation\Response;

class View
{
  private static $viewData = [];
  /**
   * 渲染模板文件
   * global渲染的数据，载入渲染的模板文件，并且删除掉$GLOBALS中渲染的数据
   *
   * @param string|array $fileName 模板的文件名称。可数组或单一字符串
   * @param string $fileDir 文件的路径或者渲染的数据。传入的如果是数组就是渲染的数据，否则就是模板路径。
   * @param array $viewData渲染的数据
   * @return boolean 一直都是 true
   */
  static private function renderPage($fileName, $fileDir = "", $viewData = [])
  {
    global $_G, $gstudio_kernel, $GSETS, $GLANG, $GURLS, $_GG, ${$gstudio_kernel['devingPluginId']};
    $Response = Response::class;
    $View = self::class;

    $viewData = \array_merge(self::$viewData, $viewData);
    foreach ($viewData as $key => $value) {
      global ${$key};
    }

    if (\is_array($fileName)) {
      if (Arr::isAssoc($fileName)) {
        foreach ($fileName as $name => $dir) {
          include_once template($name, $_GG['id'], $dir);
        }
      } else {
        foreach ($fileName as $name) {
          include_once template($name, $_GG['id'], $fileDir);
        }
      }
    } else {
      include_once template($fileName, $_GG['id'], $fileDir);
    }

    foreach ($viewData as $key => $value) {
      unset($GLOBALS[$key]);
    }

    return true;
  }
  /**
   * 渲染前做的事情
   * 主要是检查模板文件是否存在以及把渲染的数据加入到全局($GLOBALS)里
   *
   * @param string|array $viewFile 模板的文件名称。可数组或单一字符串
   * @param string $viewDirOfViewData? 文件的路径或者渲染的数据。传入的如果是数组就是渲染的数据，否则就是模板路径。
   * @param array $viewData? 渲染的数据
   * @return void
   */
  static function render($viewFile, $viewDirOfViewData = "/", $viewData = [])
  {
    if (is_array($viewDirOfViewData)) {
      $viewData = $viewDirOfViewData;
      $viewDirOfViewData = "/";
    }
    $viewData = \array_merge(self::$viewData, $viewData);
    if (count($viewData) > 0) {
      foreach ($viewData as $key => $value) {
        $GLOBALS[$key] = $value;
      }
    }

    if (\is_array($viewFile)) {
      if (Arr::isAssoc($viewFile)) {
        foreach ($viewFile as $name => $dir) {
          if (!\file_exists($dir . "/$name.htm")) {
            Response::error("VIEW_TEMPLATE_NOT_EXIST");
          }
        }
      } else {
        foreach ($viewFile as $name) {
          if (!\file_exists($viewDirOfViewData . "/$name.htm")) {
            Response::error("VIEW_TEMPLATE_NOT_EXIST");
          }
        }
      }
    } else {
      if (!\file_exists($viewDirOfViewData . "/$viewFile.htm")) {
        Response::error("VIEW_TEMPLATE_NOT_EXIST");
      }
    }

    return self::renderPage($viewFile, $viewDirOfViewData, $viewData);
  }
  /**
   * 渲染页面
   *
   * @param [type] $viewFile $viewFile 模板的文件名称。可数组或单一字符串
   * @param string $viewDirOfViewData? 文件的路径或者渲染的数据。传入的如果是数组就是渲染的数据，否则就是模板路径。基于根路径也就是当前插件的根目录下的Views文件夹
   * @param array $viewData? 渲染的数据
   * @return void
   */
  static function page($viewFile, $viewDirOfViewData = "/", $viewData = [])
  {
    if (is_array($viewDirOfViewData)) {
      $viewData = $viewDirOfViewData;
      $viewDirOfViewData = "/";
    }
    $viewDirOfViewData = GlobalVariables::get("_GG/addon/root") . "/Views/$viewDirOfViewData";
    return self::render($viewFile, $viewDirOfViewData, $viewData);
  }
  /**
   * 渲染后台模板页面
   *
   * @param string|array $viewFile 模板的文件名称。可数组或单一字符串
   * @param string $viewDirOfViewData? 文件的路径或者渲染的数据。传入的如果是数组就是渲染的数据，否则就是模板路径。基于根路径也就是当前插件的根目录的Views文件夹
   * @param array $viewData? 渲染模板的数据
   * @return void
   */
  static function dashboard($viewFile, $viewDirOfViewData = "dashboard", $viewData = [])
  {
    if (is_array($viewDirOfViewData)) {
      $viewData = $viewDirOfViewData;
      $viewDirOfViewData = "dashboard";
    }
    $realTemplateDir = $viewDirOfViewData;
    $viewDirOfViewData = GlobalVariables::get("_GG/addon/root") . "/Views/$viewDirOfViewData";
    return self::render("container", $viewDirOfViewData, [
      "_fileName" => $viewFile,
      "_templateDir" => $realTemplateDir,
      "_viewData" => $viewData
    ]);
  }

  /**
   * 渲染系统(kernel)页面
   *
   * @param string|array $viewFile 模板的文件名称。可数组或单一字符串
   * @param string $viewDirOfViewData? 文件的路径或者渲染的数据。传入的如果是数组就是渲染的数据，否则就是模板路径。基于根路径也就是核心插件的根目录下的Views文件夹
   * @param array $viewData? 渲染的数据
   * @return void
   */
  static function systemPage($viewFile, $viewDirOfViewData = "", $viewData = [])
  {
    if (is_array($viewDirOfViewData)) {
      $viewData = $viewDirOfViewData;
      $viewDirOfViewData = "";
    }
    $viewDirOfViewData = GlobalVariables::get("_GG/kernel/root") . "/Views/$viewDirOfViewData";
    return self::render($viewFile, $viewDirOfViewData, $viewData);
  }
  /**
   * 添加渲染的数据到渲染的模板中
   *
   * @param array $data 关联索引的数组
   * @return void
   */
  static function addData($data)
  {
    self::$viewData = \array_merge(self::$viewData, $data);
  }
  /**
   * 设置页面标题
   *
   * @param string $titleSourceString 页面标题字符串。例如：{bbname}- - 首页 - {$keyword}
   * @param array $params? 替换字符串中的参数
   * @return void
   */
  static function title($titleSourceString, $params = [])
  {
    self::addData([
      "navtitle" => Str::replaceParams($titleSourceString, $params),
      "pageTitle" => Str::replaceParams($titleSourceString, $params),
    ]);
  }
  /**
   * 设置页面的keywords
   *
   * @param string $keywordSourceString 页面mate关键词值。例如：{bbname},Discuzx,{$keyword1}
   * @param array $params 替换字符串中的参数
   * @return void
   */
  static function keyword($keywordSourceString, $params = [])
  {
    self::addData([
      "metakeywords" => Str::replaceParams($keywordSourceString, $params),
      "pageKeyword" => Str::replaceParams($keywordSourceString, $params),
    ]);
  }
  /**
   * 设置页面的描述
   *
   * @param string $descriptionSourceString 描述字符串。例如：{bbname}是专业的DZX应用开发者，应用列表：{addonsUrl}
   * @param array $params 替换字符串中的参数
   * @return void
   */
  static function description($descriptionSourceString, $params = [])
  {
    self::addData([
      "metadescription" => Str::replaceParams($descriptionSourceString, $params),
      "pageDescription" => Str::replaceParams($descriptionSourceString, $params),
    ]);
  }
}
