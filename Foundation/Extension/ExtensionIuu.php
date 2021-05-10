<?php

namespace gstudio_kernel\Foundation\Extension;

use gstudio_kernel\Foundation\Config;
use gstudio_kernel\Foundation\File;
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
      $sqlPath .= "/" . $this->Charset . "/install.sql";
      if (!\file_exists($sqlPath)) {
        $sqlPath .= "/" . $this->Charset . ".sql";
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
  public function upgrade()
  {
    $upgradeFilesRootPath = $this->extensionsPath . "/Iuu/Upgrade/Files";
    $namespace = $this->namespace;
    $this->scanDirAndVersionCompare($upgradeFilesRootPath, function ($version, $fileName) use ($upgradeFilesRootPath, $namespace) {
      $filePath = $upgradeFilesRootPath . "/$fileName.php";
      if (\file_exists($filePath)) {
        $className = $namespace . "\Iuu\Upgrade\Files\\$fileName";
        $upgradeItemInstance = new $className();
        $upgradeItemInstance->handle();
      }
    });
    return $this;
  }
  public function runUpgradeSql()
  {
    $sqlFileDirPath = $this->extensionsPath . "/Iuu/Upgrade";
    $multipleEncode = Config::get("multipleEncode", $this->pluginId);
    if ($multipleEncode) {
      $sqlFileDirPath .= "/" . $this->Charset;
    } else {
      $sqlFileDirPath .= "/SQL";
    }
    if (!is_dir($sqlFileDirPath)) {
      return $this;
    }

    $this->scanDirAndVersionCompare($sqlFileDirPath, function ($version, $fileName) use ($sqlFileDirPath) {
      $sqlFilePath = $sqlFileDirPath .= "/$fileName.sql";
      if (!\file_exists($sqlFilePath)) {
        return $this;
      }
      $sqlContent = \file_get_contents($sqlFilePath);
      \runquery($sqlContent);
    });
    return $this;
  }
  public function clean()
  {
    $this->cleanInstall();
    $this->cleanUpgrade();
    return File::deleteDirectory($this->extensionsPath . "/Iuu");
  }
  public function cleanInstall()
  {
    return File::deleteDirectory($this->extensionsPath . "/Iuu/Install");
  }
  public function cleanUpgrade()
  {
    return File::deleteDirectory($this->extensionsPath . "/Iuu/Upgrade");
  }
}
