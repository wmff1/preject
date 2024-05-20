<?php

namespace app\stock\controller\subject;

use think\Controller;

class Category extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->CategoryModel = model('Subject.Category');
        $this->AdminModel = model('Admin.Admin');

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

            $list = $this->CategoryModel->select();

            $this->success('返回课程分类记录', null, $list);
            exit;
        }
    }

    //添加课程分类信息
    public function add()
    {
        if ($this->request->isPost()) {

            $name = $this->request->param('name', '', 'trim');
            $weight = $this->request->param('weight', 0, 'trim');

            // 查找课程权重是否已存在
            $subWeight = $this->CategoryModel->find($weight);

            if ($subWeight['weight']) {
                $this->error('课程权重已存在');
                exit;
            }

            $data = [
                'name' => $name,
                'weight' => $weight
            ];

            $result = $this->CategoryModel->save($data);

            if ($result === FALSE) {
                $this->error('添加失败');
                exit;
            } else {
                $this->success('添加课程分类成功', '/pages/subject/category/index');
                exit;
            }
        }
    }

    //查找编辑用户信息
    public function check()
    {
        if ($this->request->isPost()) {

            $categoryid = $this->request->param('categoryid', 0, 'trim');

            //根据id查询记录是否存在
            $category = $this->CategoryModel->find($categoryid);

            if ($category) {
                $this->success('返回课程分类', null, $category);
                exit;
            } else {
                $this->error('课程分类不存在');
                exit;
            }
        }
    }

    // 编辑课程分类 
    public function edit()
    {
        if ($this->request->isPost()) {
            $params = $this->request->param();
            
            $weight = $this->request->param('weight', 0, 'trim');

            $categoryid = $this->request->param('id', 0, 'trim');

            //根据id查询记录是否存在
            $category = $this->CategoryModel->find($categoryid);

            if (!$category) {
                $this->error('课程分类不存在');
                exit;
            }

            $weight = $this->request->param('weight', 0, 'trim');

            // 查找课程权重是否已存在
            $subWeight = $this->CategoryModel->find($weight);

            if ($subWeight['weight']) {
                $this->error('课程权重已存在');
                exit;
            }

            $result = $this->CategoryModel->isUpdate(true)->save($params);

            if ($result === FALSE) {
                $this->error('更新失败');
                exit;
            } else {
                $this->success('更新课程分类成功', '/pages/subject/category/index');
                exit;
            }
        }
    }

    // 删除课程分类 
    public function del()
    {
        if ($this->request->isPost()) {

            $categoryid = $this->request->param('categoryid', 0, 'trim');

            $category = $this->CategoryModel->find($categoryid);

            if (!$category) {
                $this->error('课程分类不存在');
                exit;
            }

            $result = $this->CategoryModel->destroy($categoryid);

            if ($result === FALSE) {
                $this->error($this->CategoryModel->getError());
                exit;
            } else {
                $this->success('删除课程分类成功');
                exit;
            }
        }
    }
}
