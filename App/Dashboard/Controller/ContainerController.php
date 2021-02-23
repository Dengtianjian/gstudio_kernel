<?php

namespace gstudio_kernel\App\Dashboard\Controller;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

use gstudio_kernel\Foundation\Controller;
use gstudio_kernel\Foundation\Model;
use gstudio_kernel\Foundation\Response;
use gstudio_kernel\Foundation\Str;

function filterHidden($item)
{
  $item['hidden'] == 0;
}

class ContainerController extends Controller
{
  protected $Admin = true;
  private $serializeDataTypes = ["select", "radio", "checkbox"];
  public function data($request)
  {
    global $_G, $gstudio_kernel;
    $Response = Response::class;

    $DASHBOARD = $gstudio_kernel['dashboard'];
    $SetModel = new Model($DASHBOARD['setTableName']);
    $navId = [];
    if ($DASHBOARD['subNavId']) {
      $navId = $DASHBOARD['subNavId'];
    } else {
      $navId = \array_keys($DASHBOARD['subNavs']);
    }
    $setsData = $SetModel->where([
      "set_nav" => $navId,
      "set_hidden" => 0
    ])->order("set_sort", "ASC")->get();

    $sets = [];
    include_once libfile("function/discuzcode");
    foreach ($setsData as &$setItem) {
      switch ($setItem['set_formtype']) {
        case "forum":
          $setItem['set_content'] = json_decode($setItem['set_content']);
          break;
        case "bbcode":
          $setItem['set_view_content'] = urldecode($setItem['set_content']);
          $setItem['set_preview_content'] = \discuzcode(urldecode($setItem['set_content']), false, false, 1, 1, 1, 1, 1, 1, "0", "0", "1", 0, 1, 0);
          break;
        case "html":
          $setItem['set_view_content'] = urldecode(Str::unescape($setItem['set_content']));
          break;
      }
      if ($setItem['set_value']) {
        $setItem['set_value'] = unserialize($setItem['set_value']);
        if (\in_array($setItem['set_formtype'], $this->serializeDataTypes)) {
          $values = [];
          foreach ($setItem['set_value'] as $setValueItem) {
            $setValueItem = explode("=", $setValueItem);
            $values[$setValueItem[0]] = $setValueItem[1];
          }
          $setItem['set_value'] = $values;
        }
      }
      if ($setItem['set_subs'] != 0) {
        $setItem['set_subs'] = explode(",", $setItem['set_subs']);
        $setItem['set_sub_sets'] = [];
      }
      if ($setItem['set_tips']) {
        $setItem['set_tips'] = discuzcode($setItem['set_tips'], false, false, 1);
      }
      $sets[$setItem['set_id']] = $setItem;
    }

    foreach ($sets as &$setItem) {
      if ($setItem['set_subs'] > 0) {
        foreach ($setItem['set_subs'] as $setMergeItem) {
          array_push($setItem['set_sub_sets'], $sets[$setMergeItem]);
          unset($sets[$setMergeItem]);
        }
      }
    }
    $sets = \array_values($sets);
    // debug($sets);
    // debug(\urldecode("%5Bcolor=#bbbbbb%5D%5Bbackcolor=rgb(243,%20243,%20243)%5D%5Bfont=Helvetica,%20Arial,%20&quot;%5D%5Bsize=12px%5DCopyright%20%C2%A9%202016-2019%20%E5%88%9B%E9%80%A0%E7%8B%AE%20%E5%88%9B%E6%84%8F%E5%B7%A5%E4%BD%9C%E8%80%85%E5%AF%BC%E8%88%AA%20%5B/size%5D%5B/font%5D%5B/backcolor%5D%5B/color%5D"));
    $setCount = count($sets);

    include_once Response::systemView("sets", "dashboard");
  }
}
