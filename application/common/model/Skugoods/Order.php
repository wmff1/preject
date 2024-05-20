<?php

namespace app\common\model\Skugoods;

use think\Model;

class Order extends Model
{
    // 表名
    protected $name = 'sku_order';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = 'deletetime';

    public function getCountBySkuAndUser($skuId, $userId){
        $order = self::where('sku_user_id',$userId)->where('sku_goods_id',$skuId)->count();
        return $order;
    }
}