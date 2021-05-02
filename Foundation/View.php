<?php

namespace gstudio_kernel\Foundation;

use gstudio_kernel\Foundation\Response;

class View
{
  private static $viewData = [];
  static private function renderPage($fileName, $fileDir = "", $viewData = [])
  {
    global $_G, $gstudio_kernel, $GSETS, $GLANG, ${$gstudio_kernel['devingPluginId']};
    $Response = Response::class;
    $View = self::class;

    $viewData = \array_merge(self::$viewData, $viewData);
    foreach ($viewData as $key => $value) {
      global ${$key};
    }
    if (\is_array($fileName)) {
      if (Arr::isAssoc($fileName)) {
        foreach ($fileName as $name => $dir) {
          include_once template($name, $gstudio_kernel['devingPluginId'], $dir);
        }
      } else {
        foreach ($fileName as $name) {
          include_once template($name, $gstudio_kernel['devingPluginId'], $fileDir);
        }
      }
    } else {
      include_once template($fileName, $gstudio_kernel['devingPluginId'], $fileDir);
    }

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
  static function systemPage($viewFile, $viewDirOfViewData = "/", $viewData = [])
  {
    global $gstudio_kernel;
    if (is_array($viewDirOfViewData)) {
      $viewData = $viewDirOfViewData;
      $viewDirOfViewData = "/";
    }
    $viewDirOfViewData = $gstudio_kernel['pluginPath'] . "/Views/$viewDirOfViewData";
    return self::render($viewFile, $viewDirOfViewData, $viewData);
  }
  static function addData($data)
  {
    self::$viewData = \array_merge(self::$viewData, $data);
  }
  static function title($titleSourceString, $params = [])
  {
    self::addData([
      "navtitle" => Str::replaceParams($titleSourceString, $params),
      "pageTitle" => Str::replaceParams($titleSourceString, $params),
    ]);
  }
  static function keyword($keywordSourceString, $params = [])
  {
    self::addData([
      "metakeywords" => Str::replaceParams($keywordSourceString, $params),
      "pageKeyword" => Str::replaceParams($keywordSourceString, $params),
    ]);
  }
  static function description($descriptionSourceString, $params = [])
  {
    self::addData([
      "metadescription" => Str::replaceParams($descriptionSourceString, $params),
      "pageDescription" => Str::replaceParams($descriptionSourceString, $params),
    ]);
  }
}
