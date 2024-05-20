<?php

namespace app\stock\controller\business;

use think\Controller;

class Receive extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->AdminModel = model('Admin.Admin');
        $this->ReceiveModel = model('Business.Receive');

        $this->adminid = $this->request->param('adminid', 0, 'trim');

        $admin = $this->AdminModel->find($this->adminid);

        if (!$admin) {
            $this->error('管理员不存在');
            exit;
        }
    }

    public function index()
    {
        if ($this->request->isPost()) {
            
            $list = $this->ReceiveModel->with(['business'])->where(['applyid' => $this->adminid])->select();

            $this->success('返回领取记录', null, $list);
            exit;
        }
    }
}
