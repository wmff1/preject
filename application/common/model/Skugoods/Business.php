<?php

namespace app\common\model\Skugoods;

use think\Model;

class Business extends Model
{
    // 表名
    protected $name = 'sku_business';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = 'deletetime';
}