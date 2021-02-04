<?php

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

function loader($className)
{
  $className = str_replace("\\", "/", $className);
  include_once(DISCUZ_ROOT . "/source/plugin/$className.php");
}
spl_autoload_register("loader", false, true);
