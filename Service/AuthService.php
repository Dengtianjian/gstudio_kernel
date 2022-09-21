<?php

namespace gstudio_kernel\Service;

use DB;
use gstudio_kernel\Foundation\Response;
use gstudio_kernel\Foundation\Service;

class AuthService extends Service
{
  protected static $tableName = "gstudio_kernel_logins";
  static function generateToken(string $userId, array $tokenSalt = [], int $expiration = 30, array $extraFields = []): array
  {
    array_push($tokenSalt, $userId);
    $hashString = time() . ":" . implode(":", $tokenSalt);
    $hashString = password_hash($hashString, PASSWORD_DEFAULT);
    $nowTime = time();
    $expiration = 86400 * $expiration;
    self::Model()->insert([
      "id" => self::Model()->genId(),
      "token" => $hashString,
      "expiration" => $expiration,
      "userId" => $userId,
      "createdAt" => $nowTime,
      "updatedAt" => $nowTime,
      ...$extraFields
    ]);
    $expirationDate = $nowTime + $expiration;
    Response::header("Authorization", $hashString . "/" . $expirationDate, true);
    return [
      "value" => $hashString,
      "expirationDate" => $expirationDate,
      "expiration" => $expiration
    ];
  }
}
