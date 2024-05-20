<?php

namespace app\common\model\Product;

use think\Model;
use think\Db;
class Activity extends Model
{
    
    // 表名
    protected $name = 'activity';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;

    //软删除字段
    protected $deleteTime = 'deletetime';

    protected $append = [
        'starttime_text',
        'endtime_text',
        'type_text',
        'status_text',
        'status',
        'rule_arr'
    ];

    public static function getTypeList()
    {
        return ['seckill' => '秒杀', 'groupon' => '拼团', 'full_reduce' => '满额立减', 'full_discount' => '满额折扣', 'free_shipping' => '满额包邮'];
    }
    public static function getStatusList()
    {
        return ['nostart' => '未开始', 'ing' => '进行中', 'ended' => '已结束'];
    }

    public function getStarttimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['starttime']) ? $data['starttime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }
    public function getEndtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['endtime']) ? $data['endtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }
    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function getStatusTextAttr($value, $data)
    {
        return $this->getStatus($value, $data, 'text');
    }

    protected function getStatusAttr($value, $data)
    {
        return $this->getStatus($value, $data, 'status');
    }

    protected function getRuleArrAttr($value, $data)
    {
        $value = json_decode($data['rules'], true);
        return $value;
    }
    private function getStatus($value, $data, $type = 'status') {
        $status_text = '';
        $status = '';
        $time = time();

        if ($data['starttime'] < $time && $data['endtime'] > $time) {
            $status_text = '进行中';
            $status = 'ing';
        } else if ($data['starttime'] > $time) {
            $status_text = '未开始';
            $status = 'nostart';
        } else if ($data['endtime'] < $time) {
            $status_text = '已结束';
            $status = 'ended';
        }

        return $type == 'status' ? $status : $status_text;
    }
}
