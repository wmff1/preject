<?php

namespace app\common\model\Product;
use think\Db;
use think\Model;
use traits\model\SoftDelete;

class SkuPrice extends Model
{

    use SoftDelete;

    protected $name = 'activity_sku_price';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = 'deletetime';

    // 追加属性
    protected $append = [
        'goods_sku_text'
    ];
    
    
    public function getGoodsSkuTextAttr($value, $data)
    {
        return array_filter(explode(',', $value));
    }


    public function activitySkuPrice()
    {
        return $this->hasOne(ActivitySkuPrice::class, 'sku_price_id', 'id');
    }

}
