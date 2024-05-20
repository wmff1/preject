<?php

namespace app\common\validate;

//引入底层的验证器类
use think\Validate;

/**
 * 租赁订单验证器
 */
class Lease extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'busid' => 'require', //必填
        'proid' => 'require', //必填
        'rent' => 'require|number|egt:0', //必填
        'price' => 'require|number|egt:0', //必填
        'endtime' => 'require', //结束时间
        'address' => 'require', //详细地址
        'card' => 'require', //详细地址
        'expcode' => 'unique:lease',
        'busexpcode' => 'unique:lease',
        'status' => 'number|in:1,2,3,4,5,6',  //给字段设置范围
    ];

    /**
     * 提示消息
     */
    protected $message = [
        'busid.require'  => '用户信息未知',
        'proid.require'  => '租用商品信息未知',
        'rent.require'  => '押金必填',
        'rent.number'  => '押金必须是数字',
        'rent.egt'  => '押金必须大于0元',
        'price.require'  => '租金必填',
        'price.number'  => '租金必须是数字',
        'price.egt'  => '租金必须大于0元',
        'endtime.require'  => '结束时间未知',
        'address.require'  => '请输入详细地址',
        'card.require'  => '请上传身份证照片',
        'expcode.unique' => '物流单号已存在，请重新输入',
        'busexpcode.unique' => '物流单号已存在，请重新输入',
        'status.in' => '租赁状态有误',
    ];
    
    /**
     * 验证场景
     */
    protected $scene = [
    ];
}
