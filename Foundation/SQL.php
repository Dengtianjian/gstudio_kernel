<?php

namespace gstudio_kernel\Foundation;

use DB;

if (!defined("IN_DISCUZ")) {
  exit('Access Denied');
}

class SQL
{
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
}
