<?php

namespace gstudio_kernel\Middleware;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}


use DB;
use gstudio_kernel\Foundation\Dashboard;
use gstudio_kernel\Foundation\View;

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
    global $app;
    $pageRenderAppendData = []; //* 渲染模板追加的数据
    $GLOBALS['gstudio_kernel']['dashboard']['viewPath'] = $GLOBALS['gstudio_kernel']['pluginPath'] . "/Views/dashboard";
    $pageRenderAppendData['viewPath'] = $GLOBALS['gstudio_kernel']['pluginPath'] . "/Views/dashboard";
    if (!$GLOBALS['gstudio_kernel']['dashboard']['navTableName']) {
      $navTableName = $GLOBALS['gstudio_kernel']['devingPluginId'] . "_dashboard_nav";
      $GLOBALS['gstudio_kernel']['dashboard']['navTableName'] = $navTableName;
    } else {
      $navTableName = $GLOBALS['gstudio_kernel']['dashboard']['navTableName'];
    }
    $pageRenderAppendData['navTableName'] = $navTableName;
    if (!$GLOBALS['gstudio_kernel']['dashboard']['setTableName']) {
      $setTableName = $GLOBALS['gstudio_kernel']['devingPluginId'] . "_dashboard_set";
      $GLOBALS['gstudio_kernel']['dashboard']['setTableName'] = $setTableName;
    } else {
      $setTableName = $GLOBALS['gstudio_kernel']['dashboard']['setTableName'];
    }
    $pageRenderAppendData['setTableName'] = $setTableName;

    $navsData = \DB::fetch_all("SELECT * FROM %t WHERE `nav_up`=0 ORDER BY `nav_sort` ASC", [
      $navTableName
    ]);
    $GLOBALS['gstudio_kernel']['dashboard']['mainNavCount'] = count($navsData);
    $pageRenderAppendData['mainNavCount'] = count($navsData);

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

    if (count($app->globalSetMarks) > 0) {
      $GLOBALS['GSETS'] = Dashboard::getSetValue($app->globalSetMarks);
    } else {
      $GLOBALS['GSETS'] = [];
    }

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

    View::addData([
      "_dashboard" => $pageRenderAppendData
    ]);
    $next();
  }
}
