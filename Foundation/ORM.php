<?php

namespace gstudio_kernel\Foundation;

/** 最后必须走get、getOne、save才会执行sql
 * 流程
 * 增
 * Ins->insert([data])->save();
 *  批量
 *  Ins->batchInsert([data])->save();
 * 删
 * Ins->where([conditions])[->where([conditions])]->delete();
 * 改
 * Ins->
 * 查
 *
 */

class ORM
{
}
