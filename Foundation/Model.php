<?php

namespace gstudio_kernel\Foundation;

use DB;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

class Model
{
  private $chainCallOfQuery = [
    "page" => [],
    "order" => [],
  ];
  private $chainCallOfSave = [];
  public $tableName = null;
  public function __construct($tableName = null)
  {
    if ($tableName) {
      $this->tableName = $tableName;
    }
  }
  public function fetch($getObj)
  {
    $field = "*";
    if ($getObj['field']) {
      $field = $getObj['field'];
      if (\is_array($field)) {
        $field = \implode(",", $field);
      }
      $field = \addslashes($field);
    }
    $page = "";
    if ($getObj['page']['limit']) {
      $page = "LIMIT " . $getObj['page']['limit'];
      if ($getObj['page']['offset']) {
        $page .= " OFFSET " . $getObj['page']['offset'];
      }
    }

    $where = "";
    if ($getObj['filters']) {
      $where = SQL::where($getObj['filters']);
    }

    $order = "";
    if ($getObj['order']) {
      $order = SQL::order($getObj['order']);
    }

    $sql = "SELECT $field FROM `%t` $where $order $page";
    $getResult = DB::fetch_all($sql, [
      $this->tableName
    ]);
    return $getResult;
  }
  public function field($fields)
  {
    if (\is_array($fields)) {
      $fields = \implode(",", $fields);
    }
    $fields = \addslashes($fields);
    $this->chainCallOfQuery['field'] = $fields;
    return $this;
  }
  public function where($whereObj)
  {
    $this->chainCallOfQuery['filters'] = $whereObj;
    return $this;
  }
  public function limit($limit)
  {
    $this->chainCallOfQuery['page']['limit'] = $limit;
    return $this;
  }
  public function offset($offset)
  {
    $this->chainCallOfQuery['page']['offset'] = $offset;
    return $this;
  }
  public function page($count, $limit)
  {
    $this->chainCallOfQuery['page']['limit'] = $limit;
    $offset = $limit * $count - $count;
    if ($offset < 0) {
      $offset = 0;
    }
    $this->chainCallOfQuery['page']['offset'] = $offset;
    return $this;
  }
  public function order($field, $by = "DESC")
  {
    array_push($this->chainCallOfQuery['order'], [
      "field" => $field,
      "by" => $by
    ]);
    return $this;
  }
  public function get()
  {
    $fetchResult = $this->fetch($this->chainCallOfQuery);
    $this->chainCallOfQuery = [
      "page" => [],
      "order" => []
    ];
    return $fetchResult;
  }
  public function getOne()
  {
    $getResult = $this->get();
    return $getResult[0];
  }
  public function insert($fieldAndValues = [])
  {
    $this->chainCallOfSave['type'] = "insert";
    $this->chainCallOfSave['fv'] = $fieldAndValues;
    return $this;
  }
  public function update($fieldAndValues = [])
  {
    $this->chainCallOfSave['type'] = "update";
    $this->chainCallOfSave['fv'] = $fieldAndValues;
    return $this;
  }
  public function delete()
  {
    $where = "";
    if (!empty($this->chainCallOfQuery['filters'])) {
      $where = SQL::where($this->chainCallOfQuery['filters']);
    }

    $page = SQL::page($this->chainCallOfQuery['page']);

    $order = SQL::order($this->chainCallOfQuery['order']);

    $query = DB::query("DELETE FROM %t $where $order $page", [
      $this->tableName
    ]);

    return $query;
  }
  public function add($fieldName, $fieldValue)
  {
    $this->chainCallOfSave['fv'][$fieldName] = $fieldValue;
    return $this;
  }
  public function save()
  {
    if (count($this->chainCallOfSave['fv']) === 0) {
      Response::error("INSERT_DATA_EMPTY");
    }
    switch ($this->chainCallOfSave['type']) {
      case "insert":
        return DB::insert($this->tableName, $this->chainCallOfSave['fv'], true);
        break;
      case "update":
        $where = "";
        if (!empty($this->chainCallOfQuery['filters'])) {
          $where = SQL::where($this->chainCallOfQuery['filters']);
        }
        $fkSql = DB::implode($this->chainCallOfSave['fv']);
        $sql = "UPDATE %t SET $fkSql $where";
        $result = DB::query($sql, [
          $this->tableName
        ]);
        return $result;
        break;
    }
    $this->chainCallOfSave = [];
  }
  public function hasOne($modelClass, $localKey, $foreignKey)
  {
  }
}
