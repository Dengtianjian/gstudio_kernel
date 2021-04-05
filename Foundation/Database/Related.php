<?php

namespace gstudio_kernel\Foundation\Database;

use gstudio_kernel\Foundation\ORM;

class Related extends ORM
{
  /**
   * 相关查询
   *
   * @param assoc[] $relateTables [ 表名:[关联的key,被关联的key,[保存到的数组]] ]
   * @return array[]
   */
  function related($relateTables)
  {
    $this->executeType = "related";
  }
}
