<?php

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

function loader($className)
{
  chdir(DISCUZ_ROOT . "/source/plugin/");
  include_once("$className.php");
}
spl_autoload_register("loader", false, true);
