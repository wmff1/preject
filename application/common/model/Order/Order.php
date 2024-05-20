<?php

namespace app\common\model\Order;

use think\Model;
use traits\model\SoftDelete;
/**
 * 订单模型
 */
class Order extends Model
{
    use SoftDelete;
    
    // 表名
    protected $name = 'order';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = 'deletetime';

    
    protected $append = [
        'createtime_text',
        'status_text',
        'address_text',
        'addressinfo_text',
        'express_text',
        'sell_text',
        'check_text',
        'deliver_text',
    ];

    public function add($orderData){
        $orderData['status'] = 0;
        $orderData['bus_id'] = 13;
        $orderData['product_id'] = 23;
        $orderData['product_count'] = 1;
        $orderData['price'] = 0.01;
        $orderData['product_count'] = 1;
        $orderData['total_price'] = '1';
        $orderData['province'] = '广东省';
        $orderData['city'] = '广州市';
        $orderData['district'] = '越秀区';
        $orderData['address'] = '情侣路33号';
        $orderData['remark'] = '测试订单';
        $this->save($orderData);
        return $this->id;
    }

    public function getOrderId($orderSn){
        $orderId = $this->where('out_trade_no',$orderSn)->whereNull('deletetime')->paginate();
        if(!$orderId){
            return $this->error('订单不存在');
        }
        foreach ($orderId as &$row) {
            return $row['id'];
        }
    }

    public function getCreatetimeTextAttr($value, $data)
    {
        $createtime = isset($data['createtime']) ? $data['createtime'] : 0;

        return date('Y-m-d H:i', $createtime);
    }

    // 选项卡列表
    public function getStatusList()
    {
        return ['0' => __('未支付'), '1' => __('已支付'), '2' => __('已发货'), '3' => __('已收货'),'4'=>__("已完成"),'5'=>__('已退货')];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getAddressTextAttr($value, $data)
    {
        $province = model('Region')->where(['code' => $data['province']])->find();
        $city = model('Region')->where(['code' => $data['city']])->find();
        $district = model('Region')->where(['code' => $data['district']])->find();

        $output = [];
        if($province)
        {
            $output[] = $province['name'];
        }

        if($city)
        {
            $output[] = $city['name'];
        }

        if($district)
        {
            $output[] = $district['name'];
        }

        return implode('-', $output);
    }

    public function getAddressInfoTextAttr($value, $data)
    {
        return $data['addressinfo'];
    }

    public function getExpressTextAttr($value, $data)
    {
        $result = model('Expressquery.Expressquery')->where(['id' => $data['expressid']])->find();
    
        return $result['name'];
    }

    public function getSellTextAttr($value, $data)
    {

        $SellAdmin = model('Admin.Admin')->where(['id' => $data['adminid']])->find();

        if(empty($SellAdmin) && $SellAdmin == null){
            return '';
        }else {
            return $SellAdmin['nickname'];
        }
    }

    public function getCheckTextAttr($value, $data)
    {
        $CeheckAdmin = model('Admin.Admin')->where(['id' => $data['checkmanid']])->find();

        if(empty($CeheckAdmin) && $CeheckAdmin == null){
            return '';
        }else {
            return $CeheckAdmin['nickname'];
        }
    }

    public function getDeliverTextAttr($value, $data)
    {
        $ShipAdmin = model('Admin.Admin')->where(['id' => $data['shipmanid']])->find();

        if(empty($ShipAdmin) && $ShipAdmin == null){
            return '';
        }else {
            return $ShipAdmin['nickname'];
        }
    }
    
    public function business()
    {
        return $this->belongsTo('app\common\model\Business\Business', 'busid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function product()
    {
        return $this->belongsTo('app\common\model\Product\Product', 'busid', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function address()
    {
        return $this->belongsTo('app\common\model\Business\Address', 'businessaddrid', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    //查询订单关联的物流公司
    public function expressquery()
    {
        return $this->belongsTo('app\common\model\Expressquery\Expressquery', 'expressid', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function admin()
    {
        return $this->belongsTo('app\common\model\Admin\Admin', 'adminid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
