<?php

namespace gstudio_kernel\Foundation;

// if (!defined("IN_DISCUZ") || !defined('IN_ADMINCP')) {
//   exit('Access Denied');
// }

/** Install Upgrade Uninstall */
class Iuu
{
  private $pluginId = null;
  private $fromVersion = null;
  private $latestVersion = null;
  private $pluginPath = null;
  public function __construct($pluginId, $fromVersion)
  {
    $this->pluginId = $pluginId;
    $this->pluginPath = DISCUZ_ROOT . "/source/plugin/$pluginId";
    $this->fromVersion = $fromVersion;
    $this->latestVersion = \getglobal("setting/plugins/version/$pluginId");
  }
  public function install()
  {
    $installFile = \DISCUZ_ROOT . "/source/plugin/" . $this->pluginId . "/Iuu/Install/install.php";
    if (\file_exists($installFile)) {
      include_once($installFile);
      $namespace = "\\" . $this->pluginId . "\Iuu\Install\Install";
      $installInstance = new $namespace();
      $installInstance->handle();
    }
    return $this;
  }
  public function runInstallSql()
  {
    $multipleEncode = Config::get("multipleEncode");
    $sqlPath = DISCUZ_ROOT . "/source/plugin/" . $this->pluginId . "/Iuu/Install";
    if ($multipleEncode) {
      $sqlPath .= "/" . \CHARSET . "/install.sql";
      if (!\file_exists($sqlPath)) {
        $sqlPath .= "/" . \CHARSET . ".sql";
      }
    }
    if (!\file_exists($sqlPath)) {
      $sqlPath .= "/install.sql";
    }
    if (!\file_exists($sqlPath)) {
      return false;
    }
    $sql = \file_get_contents($sqlPath);
    \runquery($sql);

    return $this;
  }
  private function scanDirAndVersionCompare($upgradeRealtedFileDir, $callBack)
  {
    if (!\is_dir($upgradeRealtedFileDir)) {
      return true;
    }
    $upgradeFilesDir = @\scandir($upgradeRealtedFileDir);
    foreach ($upgradeFilesDir as $dirItem) {
      if ($dirItem === "." || $dirItem === "..") {
        continue;
      }
      $fileName = \substr($dirItem, 0, \strrpos($dirItem, "."));
      if (\strpos($fileName, "_")) {
        $version = \substr($fileName, \strpos($fileName, "_") + 1);
        $version = \implode(".", explode("_", $version));
      } else {
        $version = $fileName;
      }
      if (version_compare($this->fromVersion, $version, "<") === true) {
        $callBack($version, $fileName);
      }
    }
  }
  public function upgrade()
  {
    $this->scanDirAndVersionCompare($this->pluginPath . "/Iuu/Upgrade/Files", function ($version, $fileName) {
      $className = $this->pluginId . "\Iuu\Upgrade\Files\\$fileName";
      $upgradeItemInstance = new $className();
      $upgradeItemInstance->handle();
    });
    return $this;
  }
  public function runUpgradeSql()
  {
    $sqlFileDirPath = $this->pluginPath . "/Iuu/Upgrade";
    $multipleEncode = Config::get("multipleEncode");
    if ($multipleEncode) {
      $sqlFileDirPath .= "/" . \CHARSET;
      if (!is_dir($sqlFileDirPath)) {
        return false;
      }
    }

    $this->scanDirAndVersionCompare($sqlFileDirPath, function ($version, $fileName) use ($sqlFileDirPath) {
      $sqlFilePath = $sqlFileDirPath .= "/$fileName.sql";
      $sqlContent = \file_get_contents($sqlFilePath);
      \runquery($sqlContent);
    });
    return $this;
  }
  public function clean()
  {
    $this->cleanInstall();
    $this->cleanUpgrade();
  }
  public function cleanInstall()
  {
    return File::deleteDirectory($this->pluginPath . "/Iuu/Install");
  }
  public function cleanUpgrade()
  {
    return File::deleteDirectory($this->pluginPath . "/Iuu/Upgrade");
  }
}
