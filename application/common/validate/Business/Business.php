<?php

namespace app\common\validate\Business;

use think\Validate;

/**
 * 用户验证模型
 */
class Business extends Validate
{
    //required <=> 必填
    //'regex:/^1[3456789]{1}\d{9}$/' <=> 手机号正则
    //'unique:business' <=> 在business用户表中，mobile为唯一值
    protected $rule = [
        'mobile' => ['require','regex:/^1[3456789]{1}\d{9}$/','unique:business'],
        'password' => 'require',
        'salt' => 'require',
        'gender' => 'number|in:0,1,2',
        'deal' => 'number|in:0,1',
        'status' => 'number|in:0,1',
        'email' => 'regex:/^[0-9a-zA-Z]+@(([0-9a-zA-Z]+)[.])+[a-z]{2,4}$/'
    ];

    //提示信息
    protected $message = [
        'mobile.require' => '手机号必填',
        'mobile.regex' => '手机号格式有误',
        'mobile.unique' => '手机号已存在',
        'password.require' => '密码必填',
        'salt.require' => '生成密码盐有误',
        'gender.number' => '性别必须得是个数字',
        'gender.in' => '性别选择有误',
        'status.in' => '邮箱认证有误',
        'email.regex' => '邮箱格式有误'
    ];
     //验证场景
     protected $scene = [
        //使用该场景 意味着 只会验证 这两个字段
        'ShopProfile' => ['gender','email'],
        'Shore' => []
    ];
}