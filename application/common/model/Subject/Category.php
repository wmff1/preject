<?php

namespace app\common\model\Subject;

use think\Model;


class Category extends Model
{

    // 表名
    protected $name = 'subject_category';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

   // 忽略数据表不存在的字段
   protected $field = true;
}
