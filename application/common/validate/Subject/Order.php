<?php

namespace app\common\validate\Subject;

//引入TP的验证器
use think\Validate;

/**
 * 课程订单验证器
 */
class Order extends Validate
{
    //验证规则
    protected $rule = [
        'subid' => 'require',
        'busid' => 'require',
        'total' => 'require|egt:0', //egt 大于等于0
        'code' => ['require', 'unique:subject_order'],
    ];

    //提示文案
    protected $message = [
        'subid.require' => '购买课程信息未知',
        'busid.require' => '客户信息未知',
        'total.require' => '订单价格未知',
        'total.egt' => '订单价格必须大于0',
        'code.require' => '订单号未知',
        'code.unique' => '订单号已存在',
    ];
}