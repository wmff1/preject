<?php

namespace app\common\model\Subject;

use think\Model;

/* 课程章节模型 */
class Lists extends Model
{
      // 表名
      protected $name = 'subject_lists';

      // 自动写入时间戳字段
      protected $autoWriteTimestamp = 'int';
  
      // 定义时间戳字段名 在插入语句的时候 会自动写入时间戳
      protected $createTime = 'createtime';
      protected $updateTime = false;
      protected $deleteTime = false;
  
      // 忽略数据表不存在的字段
      protected $field = true;
}
