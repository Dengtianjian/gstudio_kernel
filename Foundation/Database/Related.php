<?php

namespace gstudio_kernel\Foundation\Database;

use gstudio_kernel\Foundation\Arr;
use gstudio_kernel\Foundation\ORM;

class Related
{
  public $relateds = [];
  public function set(ORM $modelInstance, $foreignKey, $relatedKey, $saveArrayKey = null)
  {
    $relatedTableName = $modelInstance->tableName;
    if (!$saveArrayKey) {
      $tableName = \explode("_", $relatedTableName);
      $lastIndex = count($tableName) - 1;
      $saveArrayKey = $tableName[$lastIndex];
    }
    $this->relateds[$relatedTableName] = [
      "model" => $modelInstance,
      "tableName" => $relatedTableName,
      "foreignKey" => $foreignKey,
      "relatedKey" => $relatedKey,
      "saveArrayKey" => $saveArrayKey
    ];
    return $this;
  }
  public function handle($sourceData)
  {
    $relateds = $this->relateds;
    if (count($sourceData) == 0) {
      return [];
    }
    foreach ($relateds as $relatedItem) {
      $relatedKeyValues = [];
      foreach ($sourceData as $dataItem) {
        array_push($relatedKeyValues, $dataItem[$relatedItem['relatedKey']]);
      }
      $data = $relatedItem['model']->get();
      if (count($data) > 0) {
        $data = Arr::valueToKey($data, $relatedItem['foreignKey']);
        foreach ($sourceData as &$dataItem) {
          if ($dataItem[$relatedItem['relatedKey']] !== null) {
            $dataItem[$relatedItem['saveArrayKey']] = $data[$dataItem[$relatedItem['relatedKey']]];
          }
        }
      }
    }

    return $sourceData;
  }
}
