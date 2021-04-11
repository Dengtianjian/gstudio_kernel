<?php

namespace gstudio_kernel\Foundation;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

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
  private $queryParams = [];
  private $relatedQuerySQL = [];
  public function __construct($tableName = null)
  {
    if ($tableName) {
      $this->tableName = $tableName;
    }
  }
  public function sql()
  {
    $getObj = $this->chainCallOfQuery;
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
    $relatedSQL = "";
    $relatedQuerySQL = $this->relatedQuerySQL;

    foreach ($relatedQuerySQL as $sql) {
      $relatedSQL .= " " . $sql;
    }

    $sql = "SELECT $field FROM `%t` $relatedSQL $where $order $page";
    return $sql;
  }
  public function fetch($getObj, $params = [])
  {
    $this->chainCallOfQuery = $getObj;
    $sql = $this->sql();
    $params = \array_merge([
      $this->tableName
    ], $this->queryParams);
    $getResult = DB::fetch_all($sql, $params);
    return $getResult;
  }
  public function field($fields)
  {
    if (\is_array($fields)) {
      $fields = \implode(",", $fields);
    } else if (func_num_args() > 1) {
      $fields = \func_get_args();
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
  public function get($queryParams = [])
  {
    $this->queryParams = $queryParams;
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
  public function batchInsertByMS($assocData)
  {
    $this->chainCallOfSave['type'] = "batchInsertByMultiSql";
    $this->chainCallOfSave['fv'] = $assocData;
    return $this;
  }
  public function update($fieldAndValues = [])
  {
    $this->chainCallOfSave['type'] = "update";
    $this->chainCallOfSave['fv'] = $fieldAndValues;
    return $this;
  }
  public function batchUpdateByMS($assocData, $conditions)
  {
    //* UPDATE TABLE SET
    $this->chainCallOfSave['type'] = "batchUpdateByMultiSql";
    $this->chainCallOfSave['fv'] = $assocData;
    $this->chainCallOfSave['MSBatchUpdateConditions'] = $conditions;
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
      case "batchUpdateByMultiSql":
        $sqls = [];
        $data = $this->chainCallOfSave['fv'];
        $data = \array_values($data);
        $conditions = $this->chainCallOfSave['MSBatchUpdateConditions'];
        $conditionValues = \array_keys($conditions);
        $conditionFields = \array_values($conditions);
        $tableName = DB::table($this->tableName);
        foreach ($data as $dataItemIndex => $dataItem) {
          $sets = [];
          foreach ($dataItem as $field => $value) {
            if (\is_string($value)) {
              $value = "'$value'";
            }
            $sets[] = "`$field`=" . $value;
          }
          $sets = \implode(",", $sets);
          $conditionSql = "";
          $conditionField = $conditionFields[$dataItemIndex];
          $conditionValue = $conditionValues[$dataItemIndex];
          if (\is_string($conditionField) && !\is_array($conditionValue)) {
            $conditionSql = "WHERE `$conditionField` = $conditionValue";
          } else {
            $conditionSql = SQL::where($conditionValue);
          }
          $sqls[] = "UPDATE $tableName SET $sets $conditionSql;\n";
        }
        $sqls = \implode("", $sqls);
        include_once libfile("function/plugin");
        \runquery($sqls);
        break;
      case "batchInsertByMultiSql":
        $data = $this->chainCallOfSave['fv'];
        $data = \array_values($data);
        $sql = [];
        $tableName = DB::table($this->tableName);
        $valueString = [];
        foreach ($data as $dataItem) {
          $values = array_values($dataItem);
          foreach ($values as &$valueItem) {
            if (\is_string($valueItem)) {
              $valueItem = "'$valueItem'";
            }
          }
          $valueString[] = "(" . \implode(",", $values) . ")";
        }
        $valueString = \implode(",", $valueString);
        $fields = array_keys($data[0]);
        foreach ($fields as &$fieldItem) {
          $fieldItem = "`$fieldItem`";
        }
        $fields = \implode(",", $fields);
        $sql = "INSERT INTO `$tableName`($fields) VALUES$valueString;";
        $result = DB::query($sql);
        return $result;
        break;
    }
    $this->chainCallOfSave = [];
  }
  public function count($field = "*")
  {
    $sql = "SELECT COUNT($field) FROM %t";
    return DB::result(DB::query($sql, [
      $this->tableName
    ]));
  }
}
