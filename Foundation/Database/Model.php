<?php

namespace gstudio_kernel\Foundation\Database;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

use DB;
use gstudio_kernel\Exception\Excep;
use gstudio_kernel\Foundation\SQL;

class Model
{
  public $tableName = "";
  private $querySql = "";
  private $executeType = "";
  private $returnSql = false;
  private $data = null;
  private $params = [];
  private $conditions = [];
  private $extra = [];
  function __construct($tableName = null)
  {
    if ($tableName) {
      $this->tableName = $tableName;
      $this->params[] = $this->tableName;
    } else {
      $this->params[] = $this->tableName;
    }
    return $this;
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
      case "batchUpdate":
        $sql = SQL::batchUpdate($this->data['fields'], $this->data['datas']);
        break;
      case "delete":
        $sql = SQL::delete($this->querySql);
        break;
      case "get":
        $sql = SQL::select($this->extra['fields'], $this->querySql);
        break;
      case "count":
        $sql = SQL::count($this->extra['field'], $this->querySql);
        break;
    }

    if ($this->extra['order']) {
      $limitSql = SQL::order($this->extra['order']);

      $sql .= " $limitSql";
    }
    if ($this->extra['limit']) {
      if ($this->extra['limit']['start'] && $this->executeType != "delete") {
        $limitSql = SQL::limit($this->extra['limit']['start'], $this->extra['limit']['numbers']);
      } else {
        $limitSql = SQL::limit($this->extra['limit']['numbers']);
      }

      $sql .= " $limitSql";
    }

    $this->querySql = $sql;
  }
  function sql($yes = true)
  {
    $this->returnSql = $yes;
    return $this;
  }
  function restoreDefaultMemberValues()
  {
    $this->querySql = "";
    $this->executeType = "";
    $this->returnSql = false;
    $this->data = null;
    $this->params = [
      $this->tableName
    ];
    $this->conditions = [];
    $this->extra = [];
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
  function skip($numbers)
  {
    if ($this->extra['limit']) {
      $this->extra['limit']['start'] = $numbers;
    } else {
      $this->extra['limit'] = [
        "start" => $numbers
      ];
    }
    return $this;
  }
  function limit($startOrNumbers, $numbers = null)
  {
    $data = [];
    if ($numbers === null) {
      $data['numbers'] = $startOrNumbers;
    } else {
      $data['start'] = $startOrNumbers;
      $data['numbers'] = $numbers;
    }
    if ($this->extra['limit']) {
      $this->extra['limit'] = \array_merge($this->extra['limit'], $data);
    } else {
      $this->extra['limit'] = $data;
    }

    return $this;
  }
  function page($pages, $pageLimt = 10)
  {
    $start = $pages * $pageLimt - $pageLimt;
    $this->limit($start, $pageLimt);
    return $this;
  }
  function field($fieldNames)
  {
    $fields = [];
    if (\func_num_args() > 1) {
      $fields = \func_get_args();
    } else if (\is_string($fieldNames)) {
      $fields = \explode(",", $fieldNames);
    } else {
      $fields = $fieldNames;
    }
    if (!$this->extra['fields']) {
      $this->extra['fields'] = $fields;
    } else {
      $this->extra['fields'] = \array_merge($this->extra['fields'], $fields);
    }
    return $this;
  }
  function order($field, $by = "ASC")
  {
    if (!$this->extra['order']) {
      $this->extra['order'] = [
        [
          "field" => $field,
          "by" => $by
        ]
      ];
    } else {
      array_push($this->extra['order'], [
        "field" => $field,
        "by" => $by
      ]);
    }
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
    if ($this->data === null) {
      $this->data = [
        "fields" => $fields,
        "datas" => $datas
      ];
    } else {
      $this->data['fields'] = \array_merge($this->data['fields'], $fields);
      $this->data['datas'] = \array_merge($this->data['datas'], $datas);
    }
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
  function batchUpdate($fields, $datas)
  {
    if ($this->data === null) {
      $this->data = [
        "fields" => $fields,
        "datas" => $datas
      ];
    } else {
      $this->data['fields'] = \array_merge($this->data['fields'], $fields);
      $this->data['datas'] = \array_merge($this->data['datas'], $datas);
    }

    $this->executeType = "batchUpdate";
    return $this;
  }
  function save()
  {
    $this->generateSql();
    $sql = $this->querySql;
    if ($this->returnSql) {
      $this->restoreDefaultMemberValues();
      return $sql;
    }
    $result = \DB::result(\DB::query($sql, $this->params));
    $this->restoreDefaultMemberValues();
    switch ($this->executeType) {
      case "insert":
        $result = \DB::insert_id();
        break;
      case "batchInsert":
      case "update":
      case "batchUpdate";
        $result = \DB::affected_rows();
        break;
    }
    return $result;
  }
  function delete($directly = false)
  {
    if ($directly) {
      $this->executeType = "delete";
    } else {
      $this->executeType = "softDelete";
    }
    $this->generateSql();
    $sql = $this->querySql;
    if ($this->returnSql) {
      $this->restoreDefaultMemberValues();
      return $sql;
    }
    \DB::result(\DB::query($this->querySql, $this->params));
    $this->restoreDefaultMemberValues();
    return \DB::affected_rows();
  }
  function get()
  {
    $this->executeType = "get";
    $this->generateSql();
    $sql = $this->querySql;
    if ($this->returnSql) {
      $this->restoreDefaultMemberValues();
      return $sql;
    }
    $fetchData = \DB::fetch_all($this->querySql, $this->params);
    $this->restoreDefaultMemberValues();

    return $fetchData;
  }
  function getOne()
  {
    $resultData = $this->get();
    if (count($resultData) > 0) {
      return $resultData[0];
    }
    return [];
  }
  function count($field = "*")
  {
    $this->executeType = "count";
    $this->extra['field'] = $field;
    $this->generateSql();
    $sql = $this->querySql;
    if ($this->returnSql) {
      return $sql;
    }
    $result = DB::result(DB::query($sql, $this->params));
    $this->restoreDefaultMemberValues();
    return $result;
  }
}
