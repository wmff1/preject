<?php

namespace app\stock\controller;

use think\Controller;

class Index extends Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->BusinessModel = model('Business.Business');
        $this->OrderModel = model('Order.Order');
        $this->VisitModel = model('Business.Visit');
        $this->ReceiveModel = model('Business.Receive');
        $this->SourceModel = model('Business.Source');

        // 获取当前年份
        $year = date("Y");

        $this->TimeList = [];

        // 获取每个月的第一天和最后一天
        for ($i = 1; $i <= 12; $i++) {
            // 拼接月份,转化为时间戳
            $time = strtotime($year . "-" . $i);

            // 获取每个月的第一天
            $start = date("Y-m-01", $time);

            // 获取每个月的最后一天
            $end = date("Y-m-t", $time);

            $this->TimeList[] = [strtotime($start), strtotime($end)];
        }
    }
    // 数量统计
    public function total()
    {
        // 总订单数量
        $OrderTotal = $this->OrderModel->count();

        // 总销售额
        $AmountTotal = $this->OrderModel->sum('amount');

        // 总客户数量
        $BusinessTotal = $this->BusinessModel->count();

        $total = [
            'OrderTotal' => $OrderTotal,
            'AmountTotal' => $AmountTotal,
            'BusinessTotal' => $BusinessTotal,
        ];

        $this->success('返回总数量', null, $total);
    }

    // 客户分析
    public function business()
    {
        // 根据数据库用户表的认证状态
        $status0 = [];
        $status1 = [];

        foreach ($this->TimeList as $item) {

            // 未认证
            $where = [
                'status' => 0,
                'createtime' => ['between', $item]
            ];
            // 查找数量
            $status0[] = $this->BusinessModel->where($where)->count();

            // 已认证
            $where = [
                'status' => 1,
                'createtime' => ['between', $item]
            ];

            $status1[] = $this->BusinessModel->where($where)->count();
        }

        $status = [
            'status0' => $status0,
            'status1' => $status1
        ];

        $this->success('返回客户统计', null, $status);
    }

    // 客户回访
    public function visit()
    {
        $VisitCount = [];

        foreach ($this->TimeList as $item) {

            $where = [
                'createtime' => ['between', $item]
            ];

            $VisitCount[] = $this->VisitModel->where($where)->count();
        }

        $this->success('返回回访统计', null, $VisitCount);
    }

    //领取统计
    public function receive()
    {
        $apply = [];
        $allot = [];
        $recovery = [];

        foreach ($this->TimeList as $item) {
            $where = [
                'status' => 'apply',
                'applytime' => ['between', $item]
            ];

            $apply[] = $this->ReceiveModel->where($where)->count();

            $where = [
                'status' => 'allot',
                'applytime' => ['between', $item]
            ];

            $allot[] = $this->ReceiveModel->where($where)->count();

            $where = [
                'status' => 'recovery',
                'applytime' => ['between', $item]
            ];

            $recovery[] = $this->ReceiveModel->where($where)->count();
        }

        $result = [
            'apply' => $apply,
            'allot' => $allot,
            'recovery' => $recovery,
        ];

        $this->success('领取统计', null, $result);
    }

    // 订单分析
    public function order()
    {
        // 查询订单不同状态
        $status1 = $this->OrderModel->where(['status' => 1])->count();
        $status2 = $this->OrderModel->where(['status' => 2])->count();
        $status3 = $this->OrderModel->where(['status' => 3])->count();
        $status4 = $this->OrderModel->where(['status' => 4])->count();

        $result = [
            ['name' => '已支付', 'value' => $status1],
            ['name' => '已发货', 'value' => $status2],
            ['name' => '已收货', 'value' => $status3],
            ['name' => '已完成', 'value' => $status4],
        ];

        $this->success('订单统计', null, $result);
    }

    // 来源分析
    public function source()
    {
        $data = [];

        //查询来源
        $sourcelist = $this->SourceModel->select();

        foreach ($sourcelist as $item) {
            $count = $this->BusinessModel->where(['sourceid' => $item['id']])->count();

            $data[] = [
                'name' => $item['name'],
                'value' => $count
            ];
        }

        $this->success('来源统计', null, $data);
    }
}
