<?php

namespace app\common\model;

use think\Model;

/**
 * 地区模型
 */
class Region extends Model
{
    protected $name = 'region';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'value',
        'label'
    ];
    

    public function getValueAttr($value, $data) {
        return $data['code'];
    }

    public function getLabelAttr($value, $data)
    {
        return $data['name'];
    }
    
    public function children () 
    {
        return $this->hasMany(\app\common\model\Region::class, 'parentid', 'code');
    }
}
