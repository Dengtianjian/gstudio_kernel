<?php

namespace gstudio_kernel\Model;

use gstudio_kernel\Foundation\Database\Model;

class AccessTokenModel extends Model
{
  public $tableName = "gstudio_kernel_access_token";
  public function getPlatformLast($platform)
  {
    return $this->where("platform", $platform)->order("createdAt", "DESC")->getOne();
  }
  public function getPlatformLatest($platform)
  {
    return $this->where("platform", $platform)->where("expiredAt", time(), ">")->getOne();
  }
  public function deleteExpired($platform = null)
  {
    $query = $this->where("expiredAt", time(), ">");
    if ($platform) {
      $query->where("platform", $platform);
    }
    return $query->delete(true);
  }
  public function add($accessToken, $platform, $expires)
  {
    $expiredAt = time() + $expires;
    return $this->insert([
      "accessToken" => $accessToken,
      "platform" => $platform,
      "expires" => $expires,
      "expiredAt" => $expiredAt
    ]);
  }
}
