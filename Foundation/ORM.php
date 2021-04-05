<?php

namespace gstudio_kernel\Foundation;

use DB;
use gstudio_kernel\Exception\Excep;
use PDO;

class ORM
{
  protected $tableName = "";
  private $querySql = "";
  private $executeType = "";
  private $returnSql = false;
  private $data = null;
  private $params = [];
  private $conditions = [];
  private $extra = [];
  private $relatedTables = [];
  function __construct($tableName = null)
  {
    if ($tableName) {
      $this->tableName = $tableName;
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
    } else {
      $fields = \explode(",", $fieldNames);
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
      return $sql;
    }
    $result = DB::result(\DB::query($this->querySql, $this->params));
    switch ($this->executeType) {
      case "insert":
        $result = DB::insert_id();
        break;
      case "batchInsert":
      case "update":
      case "batchUpdate";
        $result = DB::affected_rows();
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
    if ($this->returnSql) {
      return  $this->querySql;
    }
    DB::result(DB::query($this->querySql, $this->params));
    return DB::affected_rows();
  }
  function get()
  {
    $this->executeType = "get";
    $this->generateSql();
    if ($this->returnSql) {
      return  $this->querySql;
    }
    $fetchData = DB::fetch_all($this->querySql, $this->params);
    if (count($fetchData) > 0) {
      $relatedTables = $this->relatedTables;
      if (count($relatedTables) > 0) {
        $relatedKeys = [];
        $relatedKeyValue = [];
        foreach ($relatedTables as $tableItem) {
          array_push($relatedKeys, $tableItem['relatedKey']);
          $relatedKeyValue[$tableItem['relatedKey']] = [];
        }
        foreach ($fetchData as $dataItem) {
          foreach ($relatedKeys as $keyItem) {
            if ($dataItem[$keyItem] !== null) {
              array_push($relatedKeyValue[$keyItem], $dataItem[$keyItem]);
            }
          }
        }
        foreach ($relatedTables as &$tableItem) {
          $M = new ORM($tableItem['tableName']);
          $data = $M->where([
            $tableItem['foreignKey'], $relatedKeyValue[$tableItem['relatedKey']]
          ])->get();
          $data = Arr::valueToKey($data, $tableItem['foreignKey']);
          $tableItem['data'] = $data;
        }
        foreach ($fetchData as &$dataItem) {
          foreach ($relatedTables as $relatedItem) {
            if ($dataItem[$relatedItem['relatedKey']] !== null) {
              // debug($relatedItem['data']);
              $dataItem[$relatedItem['saveArrayKey']] = $relatedItem['data'][$dataItem[$relatedItem['relatedKey']]];
            } else {
              $dataItem[$relatedItem['saveArrayKey']] = null;
            }
          }
        }
        debug($fetchData);
      }
    }

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
    $result = $this->field("COUNT($field)")->get();
    if (!empty($result)) {
      return $result[0]["COUNT($field)"];
    }
    return 0;
  }
  function related($relatedTableName, $foreignKey, $relatedKey, $saveArrayKey = null)
  {
    if (!$saveArrayKey) {
      $tableName = \explode("_", $relatedTableName);
      $lastIndex = count($tableName) - 1;
      $saveArrayKey = $tableName[$lastIndex];
    }
    $this->relatedTables[$relatedTableName] = [
      "tableName" => $relatedTableName,
      "foreignKey" => $foreignKey,
      "relatedKey" => $relatedKey,
      "saveArrayKey" => $saveArrayKey
    ];
    return $this;
  }
}
