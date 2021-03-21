<?php

namespace gstudio_kernel\Middleware;

use gstudio_kernel\Foundation\Dashboard;

class GlobalDashboardMiddleware
{
  public function handle($next)
  {
    $GLOBALS['gstudio_kernel']['dashboard']['viewPath'] = $GLOBALS['gstudio_kernel']['pluginPath'] . "/Views/dashboard";
    if (!$GLOBALS['gstudio_kernel']['dashboard']['navTableName']) {
      $navTableName = $GLOBALS['gstudio_kernel']['devingPluginId'] . "_dashboard_nav";
      $GLOBALS['gstudio_kernel']['dashboard']['navTableName'] = $navTableName;
    } else {
      $navTableName = $GLOBALS['gstudio_kernel']['dashboard']['navTableName'];
    }
    if (!$GLOBALS['gstudio_kernel']['dashboard']['setTableName']) {
      $setTableName = $GLOBALS['gstudio_kernel']['devingPluginId'] . "_dashboard_set";
      $GLOBALS['gstudio_kernel']['dashboard']['setTableName'] = $setTableName;
    } else {
      $setTableName = $GLOBALS['gstudio_kernel']['dashboard']['setTableName'];
    }

    $navsData = \DB::fetch_all("SELECT * FROM %t WHERE `nav_up`=0 ORDER BY `nav_sort` ASC", [
      $navTableName
    ]);
    $GLOBALS['gstudio_kernel']['dashboard']['mainNavCount'] = count($navsData);

    $mainNavId = $_GET['mid'] ? intval($_GET['mid']) : 0;
    $subNavId = $_GET['sid'] ? intval($_GET['sid']) : 0;
    $GLOBALS['gstudio_kernel']['dashboard']['subNavId'] = $subNavId;

    $mainNavs = [];
    foreach ($navsData as $nav) {
      $mainNavs[$nav['nav_id']] = $nav;
    }

    if (!$mainNavId) {
      $mainNavId = $mainNavs[array_keys($mainNavs)[0]]['nav_id'];
    }
    $GLOBALS['gstudio_kernel']['dashboard']['mainNavId'] = $mainNavId;
    $GLOBALS['gstudio_kernel']['dashboard']['mainNavs'] = $mainNavs;

    $main = $mainNavs[$mainNavId];
    $subsData = \DB::fetch_all("SELECT * FROM %t WHERE `nav_up`=%d", [
      $navTableName, $mainNavId
    ]);

    $subNavs = [];
    foreach ($subsData as $sub) {
      $subNavs[$sub['nav_id']] = $sub;
    }
    $GLOBALS['gstudio_kernel']['dashboard']['subNavs'] = $subNavs;
    if ($this->globalSetMarks) {
      $GLOBALS['GSETS'] = Dashboard::getSetValue($this->globalSetMarks);
    }

    $next();
  }
}
