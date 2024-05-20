<?php

namespace app\home\controller\business;

use app\common\controller\Home;

/* 
    联系我们
*/

class Contact extends Home
{
    //构造函数
    public function __construct()
    {
        //父类继承
        parent::__construct();

        //全局概念模型
        $this->SubjectModel = model('Subject.Subject');
       
    }

    //订单
    public function index()
    {

        //查询课程点赞量
        $toplist = $this->SubjectModel->order("likes DESC")->limit(8)->select();
        // var_dump(collection($toplist)->toArray());
        // exit;

        $this->view->assign([
            'toplist' => $toplist
        ]);
        return $this->view->fetch();
    }
}
