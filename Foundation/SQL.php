<?php

namespace gstudio_kernel\Foundation;

use DB;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

class SQL
{
  static function addQuote($strings, $quote = "`", $judgeType = false)
  {
    foreach ($strings as &$item) {
      if ($judgeType) {
        if (!is_string($item)) {
          continue;
        }
      }
      if (\is_bool($item)) {
        $item = $item ? 1 : 0;
      }
      $item = $quote . $item . $quote;
    }
    return $strings;
  }
  static function where($fieldValues)
  {
    if (count($fieldValues) === 0) {
      return "";
    }
    $sql = "";
    foreach ($fieldValues as $name => $value) {
      if (\is_array($value)) {
        if (Arr::isAssoc($value)) {
          $glue =  '=';
          if (isset($value['glue'])) {
            $glue = $value['glue'];
          }
          $operator = "AND";
          if (isset($value['operator'])) {
            $operator = $value['operator'];
          }
          $fieldSql = DB::field($name, $value['value'], $glue);
          $sql .= " $operator $fieldSql";
        } else {
          foreach ($value as &$v) {
            if (is_string($v)) {
              $v = "'$v'";
            }
          }
          $value = \implode(",", $value);
          $sql .= " AND `$name` IN($value)";
        }
      } else {
        $sql .= " AND `$name` = '$value'";
      }
    }
    \preg_match("([\w\s]{3,4})", $sql, $matchs);
    if (count($matchs)) {
      $sql = \substr($sql, \strlen($matchs[0]));
    }
    return "WHERE " . $sql;
  }
  static function condition($field, $value, $operator = null, $glue = null)
  {
    $count = func_num_args();
    $sql = "";
    if ($count == 3) {
      $sql = DB::field($field, $value, \strtolower($operator));
      if ($glue) {
        $sql .= " $glue ";
      }
    } else if ($count == 4) {
      $sql = DB::field($field, $value, \strtolower($operator)) . " " . $glue . " ";
    } else {
      $sql = DB::field($field, $value);
      if ($glue) {
        $sql .= " $glue ";
      }
    }
    return $sql;
  }
  static function conditions($params)
  {
    $sql = "WHERE ";
    $lastIndex = count($params) - 1;
    $last = &$params[$lastIndex];
    if (is_array($last) && count($last) == 4) {
      \array_splice($last, 3);
    }
    foreach ($params as $itemIndex => $paramItem) {
      if (is_string($paramItem)) {
        $sql .= $paramItem;
      } else if (is_array($paramItem) && Arr::isAssoc($paramItem)) {
        $condition = [];
        // $paramItem = self::addQuote($paramItem, "'", true);
        foreach ($paramItem as $field => $value) {
          $condition[] = DB::field($field, $value);
        }
        $sql .= implode("AND", $condition);
      } else {
        $count = count($paramItem);
        if ($count == 3) {
          $itemSql = SQL::condition($paramItem[0], $paramItem[1], $paramItem[2]);
          if ($itemIndex != $lastIndex) {
            $itemSql .= " AND ";
          }
          $sql .= $itemSql;
        } else if ($count == 4) {
          $sql = SQL::condition($paramItem[0], $paramItem[1], $paramItem[2], $paramItem[3]);
        } else {
          $itemSql = SQL::condition($paramItem[0], $paramItem[1]);
          if ($itemIndex != $lastIndex) {
            $itemSql .= " AND ";
          }
          $sql .= $itemSql;
        }
      }
    }
    return $sql;
  }
  static function order($orders)
  {
    if (empty($orders)) {
      return "";
    }
    foreach ($orders as &$orderItem) {
      if (!$orderItem['field']) {
        continue;
      }
      $by = $orderItem['by'] ? $orderItem['by'] : 'ASC';
      $orderItem = "`" . $orderItem['field'] . "` " . $by;
    }
    $order = "ORDER BY " . \implode(", ", $orders);
    return $order;
  }
  static function page($page)
  {
    if (!$page['limit'] || empty($page)) {
      return "";
    }
    if ($page['limit']) {
      $pageString = "LIMIT " . $page['limit'];
      if ($page['offset']) {
        $pageString .= " OFFSET " . $page['offset'];
      }
    }
    return $pageString;
  }
  static function limit($startOrNumbers, $numbers = null)
  {
    $sql = "LIMIT ";
    if ($numbers) {
      $sql .= "$startOrNumbers,$numbers";
    } else {
      $sql .= "$startOrNumbers";
    }
    return $sql;
  }
  static function insert($data, $isReplaceInto = false)
  {
    $fields = \array_keys($data);
    $fields = self::addQuote($fields);
    $fields = \implode(",", $fields);
    $values = array_values($data);
    $values = self::addQuote($values, "'", true);
    $values = \implode(",", $values);

    $startSql = "INSERT INTO";
    if ($isReplaceInto) {
      $startSql = "REPLACE INTO";
    }
    return "$startSql `%t`($fields) VALUES($values);";
  }
  static function batchInsert($fields, $datas, $isReplaceInto = false)
  {
    $fields = self::addQuote($fields);
    $fields = \implode(",", $fields);
    $valueSql = [];
    foreach ($datas as $dataItem) {
      $dataItem = self::addQuote($dataItem, "'", true);
      $valueSql[] = "(" . \implode(",", $dataItem) . ")";
    }
    $valueSql = \implode(",", $valueSql);
    $startSql = "INSERT INTO";
    if ($isReplaceInto) {
      $startSql = "REPLACE INTO";
    }
    return "$startSql `%t`($fields) VALUES$valueSql";
  }
  static function delete($condition)
  {
    return "DELETE FROM %t $condition";
  }
  static function update($data, $extraStatement = "")
  {
    $data = self::addQuote($data, "'", true);
    foreach ($data as $field => &$value) {
      $value = "`$field` = $value";
    }
    $data = implode(",", $data);
    $sql = "UPDATE `%t` SET $data $extraStatement";
    return $sql;
  }
  static function batchUpdate($fields, $datas, $extraStatement = "")
  {
    $sql = self::batchInsert($fields, $datas, true);
    $sql .= " $extraStatement";
    return $sql;
  }
  static function select($fields = "*", $extraStatement = "")
  {
    if (is_array($fields)) {
      $fields = self::addQuote($fields, "`");
      $fields = implode(",", $fields);
    } else if ($fields === null) {
      $fields = "*";
    }
    return "SELECT $fields FROM `%t` $extraStatement";
  }
}
