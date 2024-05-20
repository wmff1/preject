<?php

namespace app\common\validate\Order;

//引入底层的验证器类
use think\Validate;

/**
 * 订单商品验证器
 */
class Product extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'orderid' => 'require', //必填
        'proid' => 'require', //必填
        'pronum' => 'require|gt:0', //必填
        'price' => 'require|egt:0', //必填
        'total' => 'require|egt:0', //必填
    ];

    /**
     * 提示消息
     */
    protected $message = [
        'orderid.require'    => '订单ID未知',
        'proid.require'    => '商品ID未知',
        'pronum.require'    => '请填写商品数量',
        'price.require'    => '请填写商品的单价',
        'total.require'    => '请填写商品的总价',
    ];
    
    /**
     * 验证场景
     */
    protected $scene = [
    ];
}
