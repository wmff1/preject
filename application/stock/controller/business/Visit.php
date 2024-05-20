<?php

namespace app\stock\controller\business;

use think\Controller;


class Visit extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->AdminModel = model('Admin.Admin');
        $this->VisitModel = model('Business.Visit');
        $this->BusinessModel = model('Business.Business');

        $this->adminid = $this->request->param('adminid', 0, 'trim');

        $admin = $this->AdminModel->find($this->adminid);

        if (!$admin) {
            $this->error('管理员不存在');
            exit;
        }
    }

    // 回访列表
    public function index()
    {
        if ($this->request->isPost()) {
            $list = $this->VisitModel->with(['business'])->where(['visit.adminid' => $this->adminid])->order('createtime desc')->select();

            $this->success('回访列表', null, $list);
            exit;
        }
    }

    // 查找当前管理员下面的客户
    public function business()
    {
        $business = $this->BusinessModel->where(['adminid' => $this->adminid])->select();

        $this->success('客户列表', null, $business);
        exit;
    }

    //添加客户回访信息· 
    public function add()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();

            $result = $this->VisitModel->validate('common/Business/Visit')->save($params);

            if ($result === FALSE) {
                $this->error($this->VisitModel->getError());
                exit;
            } else {
                $this->success('添加回访记录成功', '/pages/business/visit/index');
                exit;
            }
        }
    }

    //查找编辑用户信息
    public function check()
    {
        if ($this->request->isPost()) {
            $visitid = $this->request->param('visitid', 0, 'trim');

            //根据id查询记录是否存在
            $visit = $this->VisitModel->with(['business'])->find($visitid);

            if ($visit) {
                $this->success('返回回访记录', null, $visit);
                exit;
            } else {
                $this->error('回访记录不存在');
                exit;
            }
        }
    }

    //编辑客户回访信息· 
    public function edit()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();

            $visitid = $this->request->param('id', 0, 'trim');

            //根据id查询记录是否存在
            $visit = $this->VisitModel->find($visitid);

            if (!$visit) {
                $this->error('回访记录不存在');
                exit;
            }
            $result = $this->VisitModel->validate('common/Business/Visit')->isUpdate(true)->save($params);

            if ($result === FALSE) {
                $this->error($this->VisitModel->getError());
                exit;
            } else {
                $this->success('更新回访记录成功', '/pages/business/visit/index');
                exit;
            }
        }
    }

    // 删除回访记录
    public function del()
    {
        if ($this->request->isPost()) {

            $visitid = $this->request->param('visitid', 0, 'trim');

            $visit = $this->VisitModel->find($visitid);

            if (!$visit) {
                $this->error('回访记录不存在');
                exit;
            }

            $result = $this->VisitModel->destroy($visitid);

            if ($result === FALSE) {
                $this->error($this->VisitModel->getError());
                exit;
            } else {
                $this->success('删除回访记录成功');
                exit;
            }
        }
    }
}
