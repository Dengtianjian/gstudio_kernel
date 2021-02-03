<?php

namespace gstudio_kernel\App\Dashboard\Controller;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

use gstudio_kernel\Foundation\Model;
use gstudio_kernel\Foundation\Response;

function filterHidden($item)
{
  $item['hidden'] == 0;
}

class ContainerController
{
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
      if ($setItem['set_value']) {
        $setItem['set_value'] = json_decode($setItem['set_value'], true);
        $values = [];
        foreach ($setItem['set_value'] as $setValueItem) {
          $setValueItem = explode("=", $setValueItem);
          $values[$setValueItem[0]] = $setValueItem[1];
        }
        $setItem['set_value'] = $values;
      }
      if ($setItem['set_subs'] != 0) {
        $setItem['set_subs'] = explode("\n", $setItem['set_subs']);
        $setItem['set_sub_sets'] = [];
      }
      if ($setItem['set_formtype'] == 'forum') {
        $setItem['set_content'] = json_decode($setItem['set_content']);
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
    $setCount = count($sets);

    include_once Response::systemView("sets", "dashboard");
  }
}
