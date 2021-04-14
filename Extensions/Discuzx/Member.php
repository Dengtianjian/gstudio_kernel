<?php

namespace gstudio_kernel\Extensions\Discuzx;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

class Member
{
  function getUser()
  {
    return \getglobal(['member']);
  }
}
