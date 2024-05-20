<?php

namespace app\common\validate\Product;

use think\Validate;

class Product extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'name' => ['require','unique:product'],
        'prop' => ['require'],
        'content' => ['require'],
        'thumbs' => ['require'],
        'flag' => ['require','number','in:0,1'],
        'typeid' => ['require'],
        'unitid' => ['require'],
        'createtime' => ['require'],
        'status' => ['number','in:1,2'],
    ];
    /**
     * 提示消息
     */
    protected $message = [
        'name.require' => '商品名称必填',
        'name.unique' => '该商品名称已存在，请重新输入',
        'prop.require' => '商品属性必填',
        'content.require' => '商品描述必填',
        'flag.require' => '商品状态必填',
        'flag.in' => '商品状态未知',
        'typeid.require' => '商品分类未知',
        'unitid.require' => '商品单位未知',
        'status.in' => '商品状态未知',
        'status.number' => '商品状态类型有误',
    ];
    /**
     * 验证场景
     */
    protected $scene = [
        'add'  => ['name','prop','content','thumbs','flag','typeid','unitid','status'],
        'edit' => ['prop','content','thumbs','flag','typeid','unitid','status'],
    ];
    
}
