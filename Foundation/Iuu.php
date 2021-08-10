<?php

namespace gstudio_kernel\Foundation;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

/** Install Upgrade Uninstall */
class Iuu
{
  protected $pluginId = null;
  protected $fromVersion = null;
  protected $latestVersion = null;
  protected $pluginPath = null;
  protected $Charset = null;
  public function __construct($pluginId, $fromVersion)
  {
    $this->pluginId = $pluginId;
    $this->pluginPath = DISCUZ_ROOT . "/source/plugin/$pluginId";
    $this->fromVersion = $fromVersion;
    $this->latestVersion = \getglobal("setting/plugins/version/$pluginId");
    $this->Charset = \strtoupper(\CHARSET);
  }
  public function install()
  {
    $installFile = \DISCUZ_ROOT . "/source/plugin/" . $this->pluginId . "/Iuu/Install/install.php";
    if (\file_exists($installFile)) {
      include_once($installFile);
      $className = "\\" . $this->pluginId . "\Iuu\Install\Install";
      $installInstance = new $className();
      $installInstance->handle();
    }
    return $this;
  }
  public function runInstallSql()
  {
    $multipleEncode = Config::get("multipleEncode", $this->pluginId);
    $sqlPath = DISCUZ_ROOT . "/source/plugin/" . $this->pluginId . "/Iuu/Install";
    if ($multipleEncode) {
      $sqlPath .= "/" . $this->Charset . "/install.sql";
      if (!\file_exists($sqlPath)) {
        $sqlPath .= "/" . $this->Charset . ".sql";
      }
    }
    if (!\file_exists($sqlPath)) {
      $sqlPath = DISCUZ_ROOT . "/source/plugin/" . $this->pluginId . "/Iuu/Install/install.sql";
    }

    if (!\file_exists($sqlPath)) {
      return $this;
    }
    $sql = \file_get_contents($sqlPath);
    \runquery($sql);

    return $this;
  }
  protected function scanDirAndVersionCompare($upgradeRealtedFileDir, $callBack)
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
      $filePath = $this->pluginPath . "/Iuu/Upgrade/Files/$fileName.php";
      if (\file_exists($filePath)) {
        $className = $this->pluginId . "\Iuu\Upgrade\Files\\$fileName";
        $upgradeItemInstance = new $className();
        $upgradeItemInstance->handle();
      }
    });
    return $this;
  }
  public function runUpgradeSql()
  {
    $sqlFileDirPath = $this->pluginPath . "/Iuu/Upgrade";
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
    return File::deleteDirectory($this->pluginPath . "/Iuu");
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
