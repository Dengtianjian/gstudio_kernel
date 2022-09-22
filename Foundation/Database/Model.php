<?php

namespace gstudio_kernel\Foundation\Database;

if (!defined('IN_DISCUZ')) {
  exit('Access Denied');
}

use DB;
use gstudio_kernel\Foundation\Data\Str;
use gstudio_kernel\Foundation\Database\Query;
use gstudio_kernel\Foundation\Date;
use gstudio_kernel\Foundation\Output;

class Model
{
  public $tableName = "";
  private Query $query;
  private $returnSql = false;
  function __construct(string $tableName = null)
  {
    if ($tableName) {
      $this->tableName = $tableName;
    }
    $this->tableName = DB::table($this->tableName);
    $this->query = new Query($this->tableName);
  }
  function order(string $field, string $by = "ASC")
  {
    $this->query->order($field, $by);
    return $this;
  }
  function field(...$fieldNames)
  {
    $this->query->field($fieldNames);
    return $this;
  }
  function limit(int $startOrNumber, int $number = null)
  {
    $this->query->limit($startOrNumber, $number);
    return $this;
  }
  function page(int $pages, int $pageLimit = 110)
  {
    $this->query->page($pages, $pageLimit);
    return $this;
  }
  function skip($number)
  {
    $this->query->skip($number);
    return $this;
  }
  function where($fieldNameOrFieldValue, $value = null, $glue = "=", $operator = "AND")
  {
    $this->query->where($fieldNameOrFieldValue, $value, $glue, $operator);
    return $this;
  }
  function sql($yes = true)
  {
    $this->returnSql = $yes;
    return $this;
  }
  function insert(array $data, bool $isReplaceInto = false)
  {
    $sql = $this->query->insert($data, $isReplaceInto)->sql();
    if ($this->returnSql) return $sql;
    return DB::query($sql);
  }
  function insertId()
  {
    return DB::insert_id();
  }
  function batchInsert(array $fieldNames, array $values, bool $isReplaceInto = false)
  {
    $sql = $this->query->batchInsert($fieldNames, $values, $isReplaceInto)->sql();
    if ($this->returnSql) return $sql;
    return DB::query($sql);
  }
  function update(array $data)
  {
    $sql = $this->query->update($data)->sql();
    if ($this->returnSql) return $sql;
    return DB::query($sql);
  }
  function batchUpdate(array $fieldNames, array $values)
  {
    $sql = $this->query->batchUpdate($fieldNames, $values)->sql();
    if ($this->returnSql) return $sql;
    return DB::query($sql);
  }
  function delete(bool $directly = false)
  {
    $sql = $this->query->delete($directly)->sql();
    if ($this->returnSql) return $sql;
    return DB::query($sql);
  }
  function getAll()
  {
    $sql = $this->query->get()->sql();
    if ($this->returnSql) return $sql;
    return DB::fetch_all($sql);
  }
  function getOne()
  {
    $sql = $this->query->limit(1)->get()->sql();
    if ($this->returnSql) return $sql;
    return DB::fetch_first($sql);
  }
  function count($field = "*")
  {
    $sql = $this->query->count($field)->sql();
    if ($this->returnSql) return $sql;
    $countResult = DB::query($sql);
    if (!empty($countResult)) {
      return (int)$countResult['0']["COUNT('$field')"];
    }
    return null;
  }
  function genId($prefix = "", $suffix = "")
  {
    $nowTime = Date::milliseconds();
    return $nowTime . substr(md5($prefix . time() . Str::generateRandomString(8) . $suffix), 0, 24 - strlen($nowTime));
  }
  function exist()
  {
    $sql = $this->query->exist()->sql();
    if ($this->returnSql) return $sql;
    $exist = DB::query($sql);
    if (empty($exist)) {
      return 0;
    }
    return intval($exist[0]["1"]);
  }
}
