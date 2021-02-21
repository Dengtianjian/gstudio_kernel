<?php

namespace gstudio_kernel\Exception;

use gstudio_kernel\Foundation\Lang;

$AuthCodes = [
  "NOT_AUTH" => [401, "Auth_401001", Lang::value("kernel")['not_logged_in']],
  "AUTH_FAILED" => [401, "Auth_401002",  Lang::value("kernel")['authentication_failed_need_to_log_in_again']],
  "AUTH_EXPIRED" => [401, "Auth_401003", Lang::value("kernel")['login_has_expired_please_log_in_again']],
  "UNAUTHORIZED_ACCESS" => [401, "Auth_401004", Lang::value("kernel")['unauthorized_access']]
];

$ErrorCodes = \array_merge($AuthCodes);
