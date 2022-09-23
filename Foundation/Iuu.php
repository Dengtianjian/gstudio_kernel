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
    $this->pluginPath = File::genPath("source/plugin/$pluginId");
    $this->fromVersion = $fromVersion;
    $this->latestVersion = \getglobal("setting/plugins/version/$pluginId");
    $this->Charset = \strtoupper(\CHARSET);

    if (!defined("F_APP_ID")) {
      define("F_APP_ID", $this->pluginId);
    }
    if (!defined("F_APP_ROOT")) {
      define("F_APP_ROOT", $this->pluginPath);
    }
  }
  public function install()
  {
    $installFile =  $this->pluginPath . "/Iuu/Install/install.php";
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
    $multipleEncode = Config::get("multipleEncode");
    $sqlPath =  $this->pluginPath . "/Iuu/Install";
    if ($multipleEncode) {
      $sqlPath .= "/" . $this->Charset . "/install.sql";
      if (!\file_exists($sqlPath)) {
        $sqlPath .= "/" . $this->Charset . ".sql";
      }
    }
    if (!\file_exists($sqlPath)) {
      $sqlPath =  $this->pluginPath . "/Iuu/Install/install.sql";
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
      if (!is_dir(File::genPath($upgradeRealtedFileDir, $dirItem))) {
        continue;
      }

      if (version_compare($this->fromVersion, $dirItem, "<") === true) {
        $callBack($dirItem);
      }
    }
  }
  public function upgrade()
  {
    $upgradeDir = File::genPath($this->pluginPath, "Iuu/Upgrade");
    $this->scanDirAndVersionCompare($upgradeDir, function ($version) use ($upgradeDir) {
      $versionDir = "$upgradeDir/$version";
      if (\is_dir($versionDir)) {
        $namespace = "\\" . F_APP_ID . "\Iuu\Upgrade\\Upgrade_" . implode("_", explode(".", $version));
        new $namespace();
      }
    });
    return $this;
  }
  public static function upgradeFileHandle($className)
  {
    $classNameNamespace = substr($className, 0, strrpos($className, "\\"));
    $className = substr($className, strrpos($className, "\\") + 1, strlen($className));
    $version = substr($className, strpos($className, "_") + 1, strlen($className));
    $versionDir = implode(".", explode("_", $version));

    return implode("\\", [
      $classNameNamespace,
      $versionDir,
      $className
    ]);
  }
  public function runUpgradeSql()
  {
    $sqlFileDirPath = $this->pluginPath . "/Iuu/Upgrade";
    $multipleEncode = Config::get("multipleEncode");
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
  public function uninstall()
  {
    File::deleteDirectory(File::genPath(\getglobal("setting/attachurl"), "plugin/" . F_APP_ID));
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
