<?php

namespace gstudio_kernel\Service;

use gstudio_kernel\Foundation\Arr;
use gstudio_kernel\Foundation\Model;
use gstudio_kernel\Foundation\Service;

class UserService extends Service
{
  protected static $tableName = "common_member";
  public static function getUserCredit($userId = null)
  {
    if ($userId === null) {
      $userId = \getglobal("uid");
    }
    $CMCM = new Model("common_member_count");
    $memberCredit = $CMCM->where([
      "uid" => $userId
    ])->get();
    unset($CMCM);
    return $memberCredit;
  }
  public static function getUserGroup($groupId = null)
  {
    if ($groupId === null) {
      $groupId = \getglobal("member")['groupid'];
    }
    $CUGM = new Model("common_usergroup");
    $memberGroup = $CUGM->where([
      "groupid" => $groupId
    ])->get();
    return $memberGroup;
  }
  public static function getUserPrompt($userId = null)
  {
    if ($userId === null) {
      $userId = \getglobal("uid");
    }
    $CMNP = new Model("common_member_newprompt");
    $prompts = $CMNP->where([
      "uid" => $userId
    ])->get();
    foreach ($prompts as &$promptItem) {
      $promptItem = \array_merge($promptItem, \unserialize($promptItem['data']));
      unset($promptItem['data']);
    }
    return $prompts;
  }
  public static function getUser($userId = null, $detailed = true)
  {
    if ($userId === null) {
      $userId = \getglobal("uid");
    }
    $MM = new Model("common_member");
    $member = $MM->where([
      "uid" => $userId
    ])->get();
    if ($detailed) {
      $memberCredit = self::getUserCredit($userId);
      $memberCredit = Arr::valueToKey($memberCredit, 'uid');
      $memberGroupId = \array_column($member, "groupid");
      $memberGroup = self::getUserGroup($memberGroupId);
      $memberGroup = Arr::valueToKey($memberGroup, "groupid");
      $memberPrompt = self::getUserPrompt($userId);
      $memberPrompt = Arr::valueToKey($memberPrompt, "uid");
      foreach ($member as &$memberItem) {
        $memberItem['group'] = $memberGroup[$memberItem['groupid']];
        $memberItem['credit'] = $memberCredit[$memberItem['uid']];
        $memberItem['prompts'] = $memberPrompt[$memberItem['uid']];
        \ksort($memberItem);
      }
    }
    if (!\is_array($userId)) {
      $member = $member[0];
    }

    return $member;
  }
}
