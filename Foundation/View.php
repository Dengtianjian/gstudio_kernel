<?php

namespace gstudio_kernel\Foundation;

use gstudio_kernel\Foundation\Response;

class View
{
  static private $page = [
    "pageTitle" => "",
    "pageKeyword" => "",
    "pageDescription" => ""
  ];
  static private function renderPage($fileName, $fileDir = "", $viewData = [])
  {
    global $_G, $gstudio_kernel, $GSETS, $GLANG;
    global ${$gstudio_kernel['devingPluginId']};
    $Response = Response::class;
    $View = self::class;

    $pageTitle = self::$page['pageTitle'];
    $pageKeyword = self::$page['pageKeyword'];
    $pageDescription = self::$page['pageDescription'];
    foreach ($viewData as $key => $value) {
      global ${$key};
    }
    include_once template($fileName, $gstudio_kernel['devingPluginId'], $fileDir);
    foreach ($viewData as $key => $value) {
      unset($GLOBALS[$key]);
    }

    return true;
  }
  static function render($viewFile, $viewDirOfViewData = "/", $viewData = [])
  {
    if (is_array($viewDirOfViewData)) {
      $viewData = $viewDirOfViewData;
      $viewDirOfViewData = "/";
    }
    if (count($viewData) > 0) {
      foreach ($viewData as $key => $value) {
        $GLOBALS[$key] = $value;
      }
    }
    if (!\file_exists($viewDirOfViewData . "/$viewFile.htm")) {
      Response::error("VIEW_TEMPLATE_NOT_EXIST");
    }
    return self::renderPage($viewFile, $viewDirOfViewData, $viewData);
  }
  static function page($viewFile, $viewDirOfViewData = "/", $viewData = [])
  {
    global $gstudio_kernel;
    if (is_array($viewDirOfViewData)) {
      $viewData = $viewDirOfViewData;
      $viewDirOfViewData = "/";
    }
    $viewDirOfViewData = $GLOBALS[$gstudio_kernel['devingPluginId']]['pluginPath'] . "/Views/$viewDirOfViewData";
    return self::render($viewFile, $viewDirOfViewData, $viewData);
  }
  static function dashboard($viewFile, $viewDirOfViewData = "/", $viewData = [])
  {
    global $gstudio_kernel;
    if (is_array($viewDirOfViewData)) {
      $viewData = $viewDirOfViewData;
      $viewDirOfViewData = "/";
    }
    $viewDirOfViewData = $gstudio_kernel['pluginPath'] . "/Views/$viewDirOfViewData";
    return self::render($viewFile, $viewDirOfViewData, $viewData);
  }
  static function title($titleSourceString, $params = [])
  {
    self::$page['pageTitle'] = $titleSourceString;
  }
  static function keyword($keywordSourceString, $params = [])
  {
    self::$page['pageKeyword'] = $keywordSourceString;
  }
  static function description($descriptionSourceString, $params = [])
  {
    self::$page['pageDescription'] = $descriptionSourceString;
  }
}
