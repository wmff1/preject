<?php

namespace app\common\model\Business;

use think\Model;

/**
 * 客户领取模型
 */
class Visit extends Model
{
    // 表名
    protected $name = 'business_visit';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名 在插入语句的时候 会自动写入时间戳
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 忽略数据表不存在的字段
    protected $field = true;

    //追加自定义字段属性 别名
    protected $append = [
        // 'status_text',
    ];



    public function business()
    {
        return $this->belongsTo('app\common\model\Business\Business', 'busid', 'id')->setEagerlyType(0);
    }

    public function admin()
    {
        return $this->belongsTo('app\admin\model\Admin', 'adminid', 'id')->setEagerlyType(0);
    }
}
