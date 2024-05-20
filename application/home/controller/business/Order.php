<?php

namespace app\home\controller\business;

use app\common\controller\Home;

/* 
    交易订单
*/

class Order extends Home
{
    //构造函数
    public function __construct()
    {
        //父类继承
        parent::__construct();

        //全局概念模型
        $this->OrderModel = model('Subject.Order');
        $this->RecordModel = model('Business.Record');
    }

    //订单
    public function index()
    {

        //链式操作
        $orderlist = $this->OrderModel
            ->with(['subject', 'business'])
            ->where(['busid' => $this->LoginAuth['id']])
            ->order('createtime desc')
            ->select();

        //消费记录
        $recordlist = $this->RecordModel->where(['busid' => $this->LoginAuth['id']])->select();

        //传值给模板
        $this->view->assign([
            'orderlist' => $orderlist,
            'recordlist' => $recordlist
        ]);

        return $this->view->fetch();
    }

    //评价
    public function comment($orderid = 0)
    {
       

        //根据订单id查找订单存不存在
        $order = $this->OrderModel->with('subject')->find($orderid);

        if (!$order) {
            $this->error('订单不存在');
            exit;
        }

        //接收post提交
        if ($this->request->isPost()) {

            //获取输入的内容
            $comment = $this->request->param();

            // 更新数据
            $data = [
                'id' => $order['id'],
                'comment' => $comment['comment'],
                'rate' => $comment['rate']
            ];

            //自定义验证器
            $validate = [
                //规则
                [
                    'comment' => 'require',
                    'rate' => 'number|in:0,1,2,3,4,5',
                ],
                [
                    'comment.require' => '评论必填',
                    'rate.number' => '评分必须为数字',
                    'rate.in' => '评分必须是0,1,2,3,4,5'
                ]
            ];

            //更新
            $retult = $this->OrderModel->validate(...$validate)->isUpdate(true)->save($data);

            if ($retult === false) {
                $this->error($this->OrderModel->getError());
                exit;
            } else {
                $this->success('评价成功', '/home/business/order/index');
                exit;
            }
        }

        $this->view->assign([
            'order' => $order
        ]);
        return $this->view->fetch();
    }
}
