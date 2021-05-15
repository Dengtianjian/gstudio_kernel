<?php

namespace gstudio_kernel\Middleware;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}


use DB;
use gstudio_kernel\Foundation\Config;
use gstudio_kernel\Foundation\GlobalVariables;
use gstudio_kernel\Foundation\Router;
use gstudio_kernel\Foundation\View;
use gstudio_kernel\App\Dashboard\Controller as DashboardController;
use gstudio_kernel\Foundation\Request;

class GlobalDashboardMiddleware
{
  //! 临时使用，后面必须用别的方法判断是否是拥有后台的
  private $hasDashboardPlugins = [
    "gstudio_c20210203",
    "gstudio_sitenav_bak",
    "gstudio_20210303",
    "gstudio_kernel"
  ];
  public function handle($next, Request $request)
  {
    $pageRenderAppendData = []; //* 渲染模板追加的数据
    $pageRenderAppendData['viewPath'] =  GlobalVariables::get("_GG/addon/fullRoot") . "/Views/dashboard";

    if (Config::get("dashboard/navTableName")) {
      $navTableName = Config::get("dashboard/navTableName");
    } else {
      $navTableName = $request->pluginId . "_dashboard_nav";
    }
    $pageRenderAppendData['navTableName'] = $navTableName;
    if (Config::get("dashboard/setTableName")) {
      $setTableName = Config::get("dashboard/setTableName");
    } else {
      $setTableName = $request->pluginId . "_dashboard_set";
    }
    $pageRenderAppendData['setTableName'] = $setTableName;

    $navsData = \DB::fetch_all("SELECT * FROM %t WHERE `nav_up`=0 ORDER BY `nav_sort` ASC", [
      $navTableName
    ]);

    $mainNavId = $_GET['mid'] ? intval($_GET['mid']) : 0;
    $subNavId = $_GET['sid'] ? intval($_GET['sid']) : 0;
    $thirdLevelNav = $_GET['tid'] ? intval($_GET['tid']) : 0;
    $uri = $request->uri;
    $pageRenderAppendData['subNavId'] = $subNavId;
    $pageRenderAppendData['thirdNavId'] = $thirdLevelNav;
    $pageRenderAppendData['uri'] = $uri;

    $mainNavs = [];
    foreach ($navsData as $nav) {
      $mainNavs[$nav['nav_id']] = $nav;
    }

    if (!$mainNavId) {
      $mainNavId = $mainNavs[array_keys($mainNavs)[0]]['nav_id'];
    }
    $pageRenderAppendData['mainNavId'] = $mainNavId;
    $pageRenderAppendData['mainNavs'] = $mainNavs;

    $main = $mainNavs[$mainNavId];
    $subsData = \DB::fetch_all("SELECT * FROM %t WHERE `nav_up`=%d", [
      $navTableName, $mainNavId
    ]);

    $subNavs = [];
    foreach ($subsData as $sub) {
      $subNavs[$sub['nav_id']] = $sub;
    }
    $pageRenderAppendData['subNavs'] = $subNavs;
    $thirdNavs = [];
    if ($subNavId) {
      $thirdNavs = \DB::fetch_all("SELECT * FROM %t WHERE `nav_up`=%d", [
        $navTableName, $subNavId
      ]);
      $pageRenderAppendData['currentSubNav'] = $subNavs[$subNavId];
    }
    $pageRenderAppendData['thirdNavs'] = $thirdNavs;
    $pageRenderAppendData['thirdNavCount'] = count($thirdNavs);

    $pluginData = DB::fetch_all("SELECT * FROM `%t` WHERE `identifier` LIKE (%i)", [
      "common_plugin", "'%gstudio_%'"
    ]);
    $plugins = [];
    foreach ($pluginData as $plugin) {
      if (\in_array($plugin['identifier'], $this->hasDashboardPlugins)) {
        $plugins[$plugin['identifier']] = $plugin['name'];
      }
    }
    $pageRenderAppendData['plugins'] = $plugins;

    $setTableName = Config::get("dashboard/setTableName");
    $navTableName = Config::get("dashboard/navTableName");
    if ($setTableName === NULL) {
      $setTableName = GlobalVariables::get("_GG/id") . "_dashboard_set";
    }
    if ($navTableName === NULL) {
      $navTableName = GlobalVariables::get("_GG/id") . "_dashboard_nav";
    }
    $pageRenderAppendData['setTableName'] = $setTableName;
    $pageRenderAppendData['navTableName'] = $navTableName;
    Router::view("dashboard", DashboardController\ContainerController::class);
    Router::postView("_dashboard_save", DashboardController\SaveSetController::class);
    Router::put("_dashboard_cleansetimg", DashboardController\CleanSetImageController::class);

    View::addData([
      "_dashboard" => $pageRenderAppendData
    ]);

    GlobalVariables::set([
      "_GG" => [
        "addon" => [
          "dashboard" => $pageRenderAppendData
        ]
      ]
    ]);
    $next();
  }
}
