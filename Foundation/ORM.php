<?php

namespace gstudio_kernel\Foundation;

use DB;
use gstudio_kernel\Exception\Excep;

class ORM
{
  protected $tableName = "";
  private $querySql = "";
  private $executeType = "";
  private $returnSql = false;
  private $data = null;
  private $params = [];
  private $conditions = [];
  function __construct($tableName = null)
  {
    if ($tableName) {
      $this->tableName = $tableName;
      $this->params[] = $this->tableName;
    }
  }
  private function generateSql()
  {
    $sql = "";
    switch ($this->executeType) {
      case "insert":
      case "replace":
        $sql .= SQL::insert($this->data, $this->executeType === "replace");
        break;
      case "batchInsert":
      case "batchReplace":
        $sql .= SQL::batchInsert($this->data['fields'], $this->data['datas'], $this->executeType === "batchReplace");
        break;
      case "update":
        $sql = SQL::update($this->data, $this->querySql);
        break;
    }
    $this->querySql = $sql;
  }
  function sql($yes = true)
  {
    $this->returnSql = $yes;
    return $this;
  }
  function params($params)
  {
    if (!is_array($params)) {
      $params = func_get_args();
    }
    $this->params = \array_merge($this->params, $params);
    return $this;
  }
  function where($params, $value = "AND", $operator = null, $glue = null)
  {
    if (\preg_match_all("/=|<|>|BETWEEN|IN|LIKE|NULL|REGEXP/i", $params)) {
      array_push($this->conditions, $params);
    } else if (is_array($params)) {
      array_push($this->conditions, $params);
    } else {
      array_push($this->conditions, \func_get_args());
    }
    $this->querySql = SQL::conditions($this->conditions);

    return $this;
  }
  function insert($data, $isReplaceInto = false)
  {
    if (!is_array($data)) {
      Excep::t("insert方法传入的参数必须是关联数组");
    }
    if ($isReplaceInto) {
      $this->executeType = "replace";
    } else {
      $this->executeType = "insert";
    }
    $this->data = $data;
    return $this;
  }
  function batchInsert($fields, $datas, $isReplaceInto = false)
  {
    if ($isReplaceInto) {
      $this->executeType = "batchReplace";
    } else {
      $this->executeType = "batchInsert";
    }
    $this->data = [
      "fields" => $fields,
      "datas" => $datas
    ];
    return $this;
  }
  function update($data)
  {
    if (!$this->data) {
      $this->data = [];
    }
    $this->data = \array_merge($this->data, $data);
    $this->executeType = "update";
    return $this;
  }
  function save()
  {
    $this->generateSql();
    $sql = $this->querySql;
    if ($this->returnSql) {
      return $sql;
    }
    $result = DB::result(\DB::query($this->querySql, $this->params));
    switch ($this->executeType) {
      case "insert":
        $result = DB::insert_id();
        break;
      case "batchInsert":
        $result = DB::affected_rows();
      case "update":
        $result = DB::affected_rows();
        break;
    }
    return $result;
  }
  function delete($directly = false)
  {
    $conditionsSql =  $this->querySql;
    if ($directly === false) {
    } else {
    }
    if ($this->returnSql) {
      return  $this->querySql;
    }
  }
}
