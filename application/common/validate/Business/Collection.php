<?php

namespace app\common\validate\Business;

use think\Validate;

/**
 * 客户收藏记录
 */
class Collection extends Validate
{
    /**
     * 验证规则
     */
    protected $rule = [
        'busid' => 'require',
        'cateid' => 'require',
        'proid' => 'require',
    ];
    /**
     * 提示消息
     */
    protected $message = [
        'busid.require' => '客户信息未知',
        'cateid.require' => '收藏文章信息未知',
        'proid.require' => '收藏商品信息未知',
    ];
    
    /**
     * 验证场景
     */
    protected $scene = [
        'category' => ['busid', 'cateid'],
        'product' => ['busid', 'proid']
    ];    
}
