<?php

namespace app\common\model\Product;

use think\Db;
use think\Model;
use traits\model\SoftDelete;

class Sku extends Model
{
    protected $name = 'activity_sku';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];


    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }

    

    public function children() {
        return $this->hasMany(self::class, 'pid', 'id');
    }
}