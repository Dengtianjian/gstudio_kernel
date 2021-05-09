<?php

namespace gstudio_kernel\Foundation\Extension;

use gstudio_kernel\Foundation\Config;
use gstudio_kernel\Foundation\Iuu;

include_once \libfile("function/plugin");

class ExtensionIuu extends Iuu
{
  private $extensionsPath = NULL;
  private $extensionId = NULL;
  private $namespace = NULL;
  public function __construct($pluginId, $extensionId, $fromVersion, $extensionsPath = NULL)
  {
    parent::__construct($pluginId, $fromVersion);
    if ($extensionsPath) {
      $this->extensionsPath = $this->pluginPath . "/" . $extensionsPath;
      $this->namespace = "\\" . $pluginId . "\\" . \str_replace("/", "\\", $extensionsPath);
    } else {
      $this->extensionsPath = $this->pluginPath . "/Extensions/$extensionId";
      $this->namespace = "\\" . $pluginId . "\\Extensions\\" . $extensionId;
    }
    $this->extensionId = $extensionId;
  }
  public function install()
  {
    $installFile = $this->extensionsPath . "/Iuu/Install/Install.php";
    if (\file_exists($installFile)) {
      $className = $this->namespace . "\\Iuu\\Install\\Install";
      $installInstance = new $className();
      $installInstance->handle();
    }
    return $this;
  }
  public function runInstallSql()
  {
    $multipleEncode = Config::get("multipleEncode", $this->pluginId);
    $sqlPath = $this->extensionsPath . "/Iuu/Install";

    if ($multipleEncode) {
      $sqlPath .= "/" . \CHARSET . "/install.sql";
      if (!\file_exists($sqlPath)) {
        $sqlPath .= "/" . \CHARSET . ".sql";
      }
    }
    if (!\file_exists($sqlPath)) {
      $sqlPath = $this->extensionsPath . "/Iuu/Install/install.sql";
    }
    if (!\file_exists($sqlPath)) {
      return $this;
    }
    $sql = \file_get_contents($sqlPath);
    \runquery($sql);

    return $this;
  }
  // TODO 升级相关
}
