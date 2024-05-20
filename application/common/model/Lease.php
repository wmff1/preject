<?php

namespace app\common\model;

use think\Model;

/**
 * 租赁模型
 */
class Lease extends Model
{

    // 开启自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    // 追加属性
    protected $append = [
        'createtime_text',
        'endtime_text',
        'card_text',
        'status_text'
    ];


    public function getCreatetimeTextAttr($value, $data)
    {
        $createtime = isset($data['createtime']) ? $data['createtime'] : 0;

        return date('Y-m-d H:i', $createtime);
    }

    public function getEndtimeTextAttr($value, $data)
    {
        $endtime = isset($data['endtime']) ? $data['endtime'] : 0;

        return date('Y-m-d H:i', $endtime);
    }

    //给追加的新字段赋值
    public function getCardTextAttr($value, $data)
    {
        $card = isset($data['card']) ? $data['card'] : '';

        //路径判断 要用相对路径   ./  
        if (!is_file("." . $card)) {
            //给个默认图
            $card = '/assets/home/images/avatar.jpg';
        }

        //获取系统配置里面的选项
        $url = config('site.url') ? config('site.url') : '';

        //拼上域名信息
        $card = trim($card, '/');
        $card = $url . '/' . $card;

        return $card;
    }

    public function getStatusTextAttr($value, $data)
    {
        $status = $data['status'];
        $text = '';

        switch ($status) {
            case 1:
                $text = '已下单';
                break;
            case 2:
                $text = '已发货';
                break;
            case 3:
                $text = '已收货';
                break;
            case 4:
                $text = '已归还';
                break;
            case 5:
                $text = '已退押金';
                break;
            case 6:
                $text = '已完成';
                break;
            default:
                $text = '未知状态';
        }

        return $text;
    }

    //查询订单关联的商品信息
    public function product()
    {
        return $this->belongsTo('app\common\model\Product\Product', 'proid', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function business()
    {
        return $this->belongsTo('app\common\model\Business\Business', 'busid', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    // 查询物流
    public function express()
    {
        return $this->belongsTo('app\common\model\Expressquery\Expressquery', 'expid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
