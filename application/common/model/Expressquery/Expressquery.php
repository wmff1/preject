<?php

namespace app\common\model\Expressquery;

use think\Model;

/**
 * 订单模型
 */
class Expressquery extends Model
{
    // 表名
    protected $name = 'expressquery';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
}
