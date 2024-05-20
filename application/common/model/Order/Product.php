<?php

namespace app\common\model\Order;

use think\Model;
use traits\model\SoftDelete;

/**
 * 订单商品模型
 */
class Product extends Model
{
    //引用软删除
    use SoftDelete;

    // 表名
    protected $name = 'order_product';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'photos_text',
        'thumbs_text'
    ];

    public function getPhotosTextAttr($value, $data)
    {
        $photos = isset($data['photos']) ? $data['photos'] : '';

        //路径判断 要用相对路径
        if(!is_file(".".$photos))
        {
            //给个默认图
           $photos = '/assets/home/images/logo.png'; 
        }

        //获取系统配置里面的选项
        $url = config('site.url') ? config('site.url') : '';

        //拼上域名信息
        $photos = trim($photos, '/');
        $photos = $url.'/'.$photos;

        return $photos;
    }

    public function getThumbsTextAttr($value, $data)
    {
        $thumbs = isset($data['thumbs']) ? $data['thumbs'] : '';

        //路径判断 要用相对路径
        if(!is_file(".".$thumbs))
        {
            //给个默认图
           $thumbs = '/assets/home/images/thumb.jpg'; 
        }

        //获取系统配置里面的选项
        $url = config('site.url') ? config('site.url') : '';

        //拼上域名信息
        $thumbs = trim($thumbs, '/');
        $thumbs = $url.'/'.$thumbs;

        return $thumbs;
    }

    //查询订单关联的商品信息
    public function proinfo()
    {
        return $this->belongsTo('app\common\model\Product\Product', 'proid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    //查询订单的商品信息
    public function orderinfo()
    {
        return $this->belongsTo('app\common\model\Order\Order', 'orderid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
