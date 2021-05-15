<?php

namespace gstudio_kernel\Foundation;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

class Request
{
  private $paramData = [];
  private $includeData = [];
  private $query = [];
  public function __construct()
  {
    $this->serializationParams();
    $this->filterParams();
  }
  private function serializationParams()
  {
    $params = \file_get_contents("php://input");
    if ($params) {
      $params = \json_decode($params, true);
      if ($params === null) {
        $params = [];
      }
    } else {
      $params = [];
    }
    $params = \array_merge($params, $_GET, $_POST);
    $this->uri = \addslashes($params['uri']);
    $this->pluginId = \addslashes($params['id']);
    unset($params['id']);
    unset($params['uri']);
    $this->paramData = $params;
  }
  private function filterParams()
  {
    $params = $this->paramData;
    $filters = [];
    $page = [];
    $fields = [];
    $includes = [];
    foreach ($params as $field => $value) {
      if (preg_match("/filter\|(\w+)/", $field) === 1) {
        $fieldName = explode("|", $field);
        if (strpos($value, ",") !== false) {
          $value = \explode(",", $value);
        }
        $filters[$fieldName[1]] = $value;
        unset($params[$field]);
      } else if ($field === "limit" || $field === "offset") {
        $page[$field] = intval($value);
        unset($params[$field]);
      } else if (preg_match("/^field$/", $field) === 1) {
        $fields = \addslashes($_GET['field']);
        unset($params[$field]);
      } else if (\strpos($field, "|") > 1) {
        list($includeName, $fieldName) = \explode("|", $field);
        if (!$includes[$includeName]) {
          $includes[$includeName] = [
            "filters" => [],
            "page" => [
              "offset" => null,
              "limit" => null
            ]
          ];
        }
        switch ($fieldName) {
          case "filter":
            list($name, $fieldValue) = \explode("|", $value);
            $includes[$includeName]['filters'][$name] = $fieldValue;
            break;
          case "offset":
          case "limit":
            $includes[$includeName]['page'][$fieldName] = intval($value);
            break;
          default:
            $includes[$includeName][$fieldName] = $value;
        }

        unset($params[$field]);
      }
    }
    $this->includeData = $includes;
    $this->query = [
      "filters" => $filters,
      "page" => $page,
      "field" => $fields
    ];
    $this->paramData = $params;
  }
  private function getArrayData($arr, $keys)
  {
    if (\is_string($keys)) {
      return $arr[$keys];
    } else if (\is_array($keys)) {
      $returns = [];
      foreach ($arr as $key => $item) {
        if (\in_array($key, $keys)) {
          $returns[$key] = $item;
        }
      }
      return $returns;
    }

    return $arr;
  }
  public function params($paramsKey = null)
  {
    if (count(func_get_args()) > 1) {
      $paramsKey = func_get_args();
    }
    return $this->getArrayData($this->paramData, $paramsKey);
  }
  public function includes($fieldName = null)
  {
    return $this->getArrayData($this->includeData, $fieldName);
  }
  public function query($key = null)
  {
    return $this->getArrayData($this->query, $key);
  }
  public function remove($key)
  {
    unset($this->paramData[$key]);
  }
}
