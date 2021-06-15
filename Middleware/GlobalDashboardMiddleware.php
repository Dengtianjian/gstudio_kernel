<?php

namespace gstudio_kernel\Middleware;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}


use DB;
use gstudio_kernel\Foundation\Config;
use gstudio_kernel\Foundation\GlobalVariables;
use gstudio_kernel\Foundation\Router;
use gstudio_kernel\App\Dashboard\Controller as DashboardController;
use gstudio_kernel\Foundation\Arr;
use gstudio_kernel\Foundation\Request;
use gstudio_kernel\Foundation\Response;

class GlobalDashboardMiddleware
{
  //! 临时使用，后面必须用别的方法判断是否是拥有后台的
  private $hasDashboardPlugins = [
    "gstudio_c20210203",
    "gstudio_sitenav_bak",
    "gstudio_20210303",
    "gstudio_kernel",
    "gstudio_20210519"
  ];
  public function handle($next, Request $request)
  {
    global $_GG;
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

    //* 查询所有主导航
    $firstLevelNavs = \DB::fetch_all("SELECT * FROM %t WHERE `nav_up`=0 ORDER BY `nav_sort` ASC", [
      $navTableName
    ]);
    if (GlobalVariables::getGG("addon/dashboard/firstLevelNavs")) {
      $firstLevelNavs = Arr::merge($firstLevelNavs, GlobalVariables::getGG("addon/dashboard/firstLevelNavs"));
    }
    $firstLevelNavs = Arr::indexToAssoc($firstLevelNavs, "nav_id");

    $navId = $request->params("nav_id");
    $uri = \addslashes($_GET['uri']);

    // TODO 当是动态添加导航时，要自定义设置页面
    $firstLevelNav = null;
    $secondLevelNav = null;
    $secondLevelNavs = [];
    if (GlobalVariables::getGG("addon/dashboard/secondLevelNavs")) {
      $secondLevelNavs = Arr::merge($secondLevelNavs, GlobalVariables::getGG("addon/dashboard/secondLevelNavs"));
    }
    $thirdLevelNav = null;
    $thirdLevelNavs = [];
    if (GlobalVariables::getGG("addon/dashboard/thirdLevelNavs")) {
      $thirdLevelNavs = Arr::merge($thirdLevelNavs, GlobalVariables::getGG("addon/dashboard/thirdLevelNavs"));
    }

    if ($uri && !$navId) {
      if ($uri === "dashboard" && !$navId) {
        //* 没有输入URI 也没有nav_id 就获取第一个第一级导航
        $firstNav = array_values($firstLevelNavs)[0];
        $navId = $firstNav['nav_id'];
        if ($firstNav['nav_custom']) {
          $uri = $firstNav['nav_uri'];
          $request->uri = $uri;
        }
      } else {
        $hasUriNavs = Arr::indexToAssoc($firstLevelNavs, "nav_uri");
        $firstLevelNav = $hasUriNavs[$uri];
        if ($firstLevelNav) {
          $navId = $firstLevelNav['nav_id'];
        }

        $navId = \intval($navId);
        $nav = \DB::fetch_first("SELECT * FROM %t WHERE `nav_uri`=%s ", [
          $navTableName, $uri
        ]);
        if (!empty($nav)) {
          $navId = $nav['nav_id'];
        }
      }
    }
    if ($navId) {
      if ($firstLevelNavs[$navId]) {
        $firstLevelNav = $firstLevelNavs[$navId];
        $secondLevelNav = $firstLevelNav;
        $secondLevelNavs = \DB::fetch_all("SELECT * FROM %t WHERE `nav_up`=%i ORDER BY `nav_sort` ASC", [
          $navTableName, $firstLevelNav['nav_id']
        ]);
      } else {
        $nav = DB::fetch_first("SELECT * FROM `%t` WHERE `nav_id` = %i ", [
          $navTableName,
          $navId
        ]);
        if (empty($nav)) {
          Response::error(500, 500001, "导航不存在");
        }
        $firstLevelNav = $firstLevelNavs[$nav['nav_up']];
        if ($firstLevelNav) {
          //* 说明是第二级导航，获取第三级导航，并且查询同级导航
          $secondLevelNav = $nav;
          $thirdLevelNavs = \DB::fetch_all("SELECT * FROM %t WHERE `nav_up`=%i ORDER BY `nav_sort` ASC", [
            $navTableName, $nav['nav_id']
          ]);
          $secondLevelNavs = \DB::fetch_all("SELECT * FROM %t WHERE `nav_up`=%i ORDER BY `nav_sort` ASC", [
            $navTableName, $secondLevelNav['nav_up']
          ]);
        } else {
          //* 说明是第三级导航，查询第二级导航，并且查询第二级导航的同级导航以及同级导航
          $thirdLevelNav = $nav;
          $secondLevelNav = \DB::fetch_first("SELECT * FROM %t WHERE `nav_id`=%i", [
            $navTableName, $nav['nav_up']
          ]);
          if (empty($secondLevelNav)) {
            Response::error(500, 500002, "导航不存在");
          }
          $secondLevelNavs = \DB::fetch_all("SELECT * FROM %t WHERE `nav_up`=%i ORDER BY `nav_sort` ASC", [
            $navTableName, $secondLevelNav['nav_up']
          ]);
          $firstLevelNav = $firstLevelNavs[$secondLevelNav['nav_up']];
          $thirdLevelNavs = \DB::fetch_all("SELECT * FROM %t WHERE `nav_up`=%i ORDER BY `nav_sort` ASC", [
            $navTableName, $secondLevelNav['nav_id']
          ]);
        }
      }
    } else {
      $secondLevelNavs = Arr::indexToAssoc($secondLevelNavs, "nav_uri");
      $thirdLevelNavs = Arr::indexToAssoc($thirdLevelNavs, "nav_uri");
      if ($firstLevelNavs[$uri]) {
        $firstLevelNav = $firstLevelNavs[$uri];
        $secondLevelNav = $firstLevelNav;
      } else if ($secondLevelNavs[$uri]) {
        $secondLevelNav = $secondLevelNavs[$uri];
        $firstLevelNav = $firstLevelNavs[$secondLevelNav['nav_up']];
      } else if ($thirdLevelNavs[$uri]) {
        $thirdLevelNav = $thirdLevelNavs[$uri];
        $secondLevelNav = $secondLevelNavs[$thirdLevelNav['nav_up']];
        $firstLevelNav = $firstLevelNavs[$secondLevelNav['nav_up']];
      }
    }

    //* 一级导航
    $pageRenderAppendData['firstLevelNav'] = $firstLevelNav;
    $pageRenderAppendData['firstLevelNavs'] = $firstLevelNavs;
    //* 二级导航
    $pageRenderAppendData['secondLevelNav'] = $secondLevelNav;
    $pageRenderAppendData['secondLevelNavs'] = $secondLevelNavs;
    //* 三级导航
    $pageRenderAppendData['thirdLevelNav'] = $thirdLevelNav;
    $pageRenderAppendData['thirdLevelNavs'] = $thirdLevelNavs;
    // debug($secondLevelNavs);

    //* 应用
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

    Router::view("dashboard", DashboardController\ContainerController::class);
    Router::postView("_dashboard_save", DashboardController\SaveSetController::class);
    Router::put("_dashboard_cleansetimg", DashboardController\CleanSetImageController::class);

    $_GG['addon']['dashboard'] = $pageRenderAppendData;
    GlobalVariables::set([
      "_GG" => [
        "addon" => [
          "dashboard" => [
            "secondLevelNavCount" => count(GlobalVariables::getGG("addon/dashboard/secondLevelNavs")),
            "thirdLevelNavCount" => count(GlobalVariables::getGG("addon/dashboard/thirdLevelNavs")),
          ]
        ]
      ]
    ]);
    $next();
  }
}
