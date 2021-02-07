<?php

namespace gstudio_kernel\Exception;

$AuthCodes = [
  "NOT_AUTH" => [401, "Auth_401001", "未登录"],
  "AUTH_FAILED" => [401, "Auth_401002", "验证失败，请重新登录"],
  "AUTH_EXPIRED" => [401, "Auth_401003", "登录已过期，请重新登录"],
  "UNAUTHORIZED_ACCESS" => [401, "Auth_401004", "非法访问"]
];

$ErrorCodes = \array_merge($AuthCodes);
