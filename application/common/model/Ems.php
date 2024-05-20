<?php

namespace app\common\model;

use think\Model;

/**
 * 邮箱验证码
 */
class Ems Extends Model
{
    //数据库表名
    protected $name = 'ems';

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleTime = false;

    //忽略不存在的表字段
    protected $field = true;
    // 追加属性
    protected $append = [
    ];

}
