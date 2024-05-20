<?php

namespace app\common\model\business;

use think\Model;

/**
 * 客户收藏记录
 */
class Collection extends Model
{
    // 表名
    protected $name = 'business_collection';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;

    public function cate()
    {
        return $this->belongsTo('app\common\model\Category', 'cateid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    
    public function business()
    {
        return $this->belongsTo('app\common\model\Business\Business', 'busid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    
    public function product()
    {
        return $this->belongsTo('app\common\model\Product\Product', 'proid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
