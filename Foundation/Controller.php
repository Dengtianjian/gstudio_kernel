<?php

namespace gstudio_kernel\Foundation;

class Controller
{
  protected $Admin = false;
  protected $Auth = false;
  public function __get($name)
  {
    return $this->$name;
  }
  public function __construct()
  {
    $langJson = \json_encode($GLOBALS['GLANG']);
    $multipleEncodeJSScript = <<<EOT
      <script>
      const GLANG=JSON.parse('$langJson');
      console.log(GLANG);
      </script>
EOT;
    print_r($multipleEncodeJSScript);
  }
}
