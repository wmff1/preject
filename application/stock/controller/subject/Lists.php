<?php

namespace app\stock\controller\subject;

use think\Controller;

class Lists extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->CategoryModel = model('Subject.Category');
        $this->AdminModel = model('Admin.Admin');
        $this->SubjectModel = model('Subject.Subject');

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

            $list = $this->SubjectModel->select();

            $this->success('返回课程列表记录', null, $list);
            exit;
        }
    }

    // 查找课程分类
    public function category()
    {
        $category = $this->CategoryModel->select();

        $this->success('课程分类', null, $category);
        exit;
    }

    // 上传课程封面
    public function thumbs()
    {

        if ($this->request->isPost()) {

            $subid = $this->request->param('subid', 0, 'trim');

            $subject = $this->SubjectModel->where(['id' => $subid])->find();

            if (!$subject) {
                $this->error('课程不存在');
                exit;
            }

            $data = [
                'id' => $subid
            ];

            //判断是否有文件上传
            if (isset($_FILES['thumbs'])) {

                //调用公共方法
                $success = build_upload('thumbs');

                if ($success['result']) {
                    //上传成功
                    $data['thumbs'] = $success['data'];
                } else {
                    //上传失败
                    $this->error($success['msg']);
                    exit;
                }
            }

            $result = $this->SubjectModel->isUpdate(true)->save($data);

            if ($result === FALSE) {
                $this->error('更新课程封面失败');
                exit;
            } else {
                //删除旧图片
                if (isset($data['thumbs'])) {
                    //判断旧图片是否存在，如果存在就删除掉
                    @is_file("." . $subject['thumbs']) && unlink("." . $subject['thumbs']);
                }

                //返回新图片的地址
                //获取系统配置里面的选项
                $url = config('site.url') ? config('site.url') : '';

                //拼上域名信息
                $thumbs = trim($data['thumbs'], '/');
                $thumbs = $url . '/' . $thumbs;

                $this->success('课程封面更新成功', null, $thumbs);
                exit;
            }
        }
    }

    // 添加课程
    public function add()
    {
        if ($this->request->isPost()) {

            $params = $this->request->param();

            //插入语句
            $result = $this->SubjectModel->validate('common/Subject/Subject')->save($params);

            if ($result === FALSE) {
                $this->error($this->SubjectModel->getError());
                exit;
            } else {
                $this->success('添加课程成功', '/pages/subject/lists/index');
                exit;
            }
        }
    }

    // 编辑课程页面初始化数据
    public function check()
    {
        if ($this->request->isPost()) {

            $subid = $this->request->param('subid', 0, null);

            $subject = $this->SubjectModel->with(['category'])->find($subid);

            //获取系统配置里面的选项
            $url = config('site.url') ? config('site.url') : '';

            $result = [
                'subject' => $subject,
                'url' => $url
            ];

            if ($subject) {
                $this->success('返回数据', null, $result);
                exit;
            } else {
                $this->error('课程分类不存在');
                exit;
            }
        }
    }

    // 编辑课程
    public function edit()
    {
        if ($this->request->isPost()) {
            $id = $this->request->param('subid', 0, 'trim');
            $title = $this->request->param('title', 0, 'trim');
            $price = $this->request->param('price', 0, 'trim');
            $content = $this->request->param('content', 0, 'trim');
            $cateid = $this->request->param('categoryid', 0, 'trim');

            $data = [
                'id' => $id,
                'title' => $title,
                'price' => $price,
                'content' => $content,
                'cateid' => $cateid,
            ];

            $result = $this->SubjectModel->isUpdate(true)->save($data);

            if ($result === FALSE) {
                $this->error('更新失败');
                exit;
            } else {
                $this->success('更新课程成功', '/pages/subject/lists/index');
                exit;
            }
        }
    }

    // 删除课程
    public function del()
    {
        if ($this->request->isPost()) {

            $subid = $this->request->param('subid', 0, 'trim');

            $subject = $this->SubjectModel->find($subid);

            if (!$subject) {
                $this->error('课程不存在');
                exit;
            }

            $result = $this->SubjectModel->destroy($subid);

            if ($result === FALSE) {
                $this->error($this->SubjectModel->getError());
                exit;
            } else {
                $this->success('删除课程成功');
                exit;
            }
        }
    }
}
