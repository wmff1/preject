<?php

namespace app\common\model\Skugoods;

use think\Model;

class Goods extends Model
{
    // 表名
    protected $name = 'sku_goods';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = 'deletetime';

    public function getById($goodsId){
        $goods = self::field('id,name,stock,max_buy,sold')->where('id',$goodsId)->find();
        return $goods;
    }
    public function getAllIds(){
        // $goods = self::field('id,name,stock,max_buy,sold')->where('id',$goodsId)->find();
        // return $goods;
    }
}