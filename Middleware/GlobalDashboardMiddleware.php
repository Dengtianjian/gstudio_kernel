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

class GlobalDashboardMiddleware
{
  //! 临时使用，后面必须用别的方法判断是否是拥有后台的
  private $hasDashboardPlugins = [
    "gstudio_c20210203",
    "gstudio_sitenav_bak",
    "gstudio_20210303",
    "gstudio_kernel"
  ];
  public function handle($next)
  {
    $pageRenderAppendData = []; //* 渲染模板追加的数据
    $GLOBALS['gstudio_kernel']['dashboard']['viewPath'] = GlobalVariables::get("_GG/addon/fullRoot") . "/Views/dashboard";
    $pageRenderAppendData['viewPath'] =  GlobalVariables::get("_GG/addon/fullRoot") . "/Views/dashboard";

    if (Config::get("dashboard/navTableName")) {
      $navTableName = Config::get("dashboard/navTableName");
    } else {
      $navTableName = $GLOBALS['gstudio_kernel']['devingPluginId'] . "_dashboard_nav";
      $GLOBALS['gstudio_kernel']['dashboard']['navTableName'] = $navTableName;
    }
    $pageRenderAppendData['navTableName'] = $navTableName;
    if (Config::get("dashboard/setTableName")) {
      $setTableName = Config::get("dashboard/setTableName");
    } else {
      $setTableName = $GLOBALS['gstudio_kernel']['devingPluginId'] . "_dashboard_set";
      $GLOBALS['gstudio_kernel']['dashboard']['setTableName'] = $setTableName;
    }
    $pageRenderAppendData['setTableName'] = $setTableName;

    $navsData = \DB::fetch_all("SELECT * FROM %t WHERE `nav_up`=0 ORDER BY `nav_sort` ASC", [
      $navTableName
    ]);
    $GLOBALS['gstudio_kernel']['dashboard']['mainNavCount'] = count($navsData);

    $mainNavId = $_GET['mid'] ? intval($_GET['mid']) : 0;
    $subNavId = $_GET['sid'] ? intval($_GET['sid']) : 0;
    $thirdLevelNav = $_GET['tid'] ? intval($_GET['tid']) : 0;
    $GLOBALS['gstudio_kernel']['dashboard']['subNavId'] = $subNavId;
    $GLOBALS['gstudio_kernel']['dashboard']['thirdNavId'] = $thirdLevelNav;
    $pageRenderAppendData['subNavId'] = $subNavId;
    $pageRenderAppendData['thirdNavId'] = $thirdLevelNav;

    $mainNavs = [];
    foreach ($navsData as $nav) {
      $mainNavs[$nav['nav_id']] = $nav;
    }

    if (!$mainNavId) {
      $mainNavId = $mainNavs[array_keys($mainNavs)[0]]['nav_id'];
    }
    $GLOBALS['gstudio_kernel']['dashboard']['mainNavId'] = $mainNavId;
    $GLOBALS['gstudio_kernel']['dashboard']['mainNavs'] = $mainNavs;
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
    $GLOBALS['gstudio_kernel']['dashboard']['subNavs'] = $subNavs;
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
    $GLOBALS['gstudio_kernel']['plugins'] = $plugins;
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
