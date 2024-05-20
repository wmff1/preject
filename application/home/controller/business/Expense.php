<?php

namespace app\home\controller\business;

use app\common\controller\Home;
use Matrix\Functions;
use PhpOffice\PhpSpreadsheet\Worksheet\AutoFilter\Column;

/* 
    消费记录
*/

class Expense extends Home
{
    //构造函数
    public function __construct()
    {
        //父类继承
        parent::__construct();

        //全局概念模型
        $this->SubjectModel = model('Subject.Subject');
        $this->OrderModel = model('Subject.Order');
        $this->RecordModel = model('Business.Record');
    }

    //订单
    public function index($busid = 0)
    {

        if ($this->request->isAjax()) {

            //查找课程和价格
            $data = $this->SubjectModel->column('title,price');

            //查找订单
            $order = $this->OrderModel->with(['subject'])->where(['busid' => $busid])->select();

            $keys = array_keys($data);

            $values = array_values($data);

            $arr = [
                $keys, $values
            ];
            $this->success('', null, $arr);
        }
        return $this->view->fetch();
    }

    //年度
    public function isyear()
    {
        if ($this->request->isAjax()) {

            //查找消费表的消费金额，消费时间
            $record = $this->RecordModel->where(['busid' => $this->LoginAuth['id']])->column('total, createtime');

            $val = [];

            $k = substr_replace(array_keys($record),'',0,1);

            foreach(array_values($record) as $item){
                $item = date('Y-m-d H:i:s',$item);
                $val[] = $item;
            }

            //查找课程
            $subname = $this->SubjectModel->column('title');

            $subname = array_values($subname);

            $arr = [
                $k, $val,$subname
            ];
            return $this->success('',null,$arr);
        }
        return $this->view->fetch();
    }
}
