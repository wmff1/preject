<?php

namespace app\common\model\Subject;

use think\Model;

/**
 * 课程购买订单表
 */
class Order extends Model
{
   // 表名
   protected $name = 'subject_order';

   // 自动写入时间戳字段
   protected $autoWriteTimestamp = 'int';

   // 定义时间戳字段名 在插入语句的时候 会自动写入时间戳
   protected $createTime = 'createtime';
   protected $updateTime = false;
   protected $deleteTime = false;

   // 忽略数据表不存在的字段
   protected $field = true;

   //链式操作
   //关联课程表
   public function subject(){
      return $this->belongsTo('app\common\model\Subject\Subject','subid','id')->setEagerlyType(0);
   }
   // 关联用户表
   public function business(){
      return $this->belongsTo('app\common\model\Business\Business','busid','id')->setEagerlyType(0);
   }
}
