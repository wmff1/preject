<?php

namespace app\rent\controller\business;

use think\Controller;

class Category extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->BusinessModel = model('Business.Business');
        $this->CategoryModel = model('Category');
        $this->CollectionModel = model('Business.Collection');
    }

    public function index()
    {
        $page = $this->request->param('page', 1, 'trim');

        $limit = 10;

        $start = ($page - 1) * $limit;

        $list = $this->CategoryModel->order('id desc')->limit($start, $limit)->select();

        if ($list) {
            $this->success('返回文章列表', null, $list);
        } else {
            $this->error('没有更多数据', null, []);
        }
    }

    public function info()
    {
        if ($this->request->isPost()) {
            $id = $this->request->param('id', 0, 'trim');
            $busid = $this->request->param('busid', 0, 'trim');

            //判断当前客户是否有收藏过这个文章
            $where = [
                'cateid' => $id,
                'busid' => $busid
            ];

            $collection = $this->CollectionModel->where($where)->find();

            $info = $this->CategoryModel->find($id);

            //找上一篇
            $prev = $this->CategoryModel->where("id < $id")->order('id desc')->limit(1)->find();

            //找下一篇
            $next = $this->CategoryModel->where("id > $id")->order('id asc')->limit(1)->find();

            if ($info) {
                $result = [
                    'info' => $info,
                    'prev' => $prev,
                    'next' => $next,
                    'collection' => $collection
                ];
                $this->success('返回文章详情', null, $result);
            } else {
                $this->error('暂无文章详情', null, $info);
            }
        }
    }

    // 收藏
    public function collection()
    {
        if ($this->request->isPost()) {
            $cateid = $this->request->param('cateid', 0, 'trim');
            $busid = $this->request->param('busid', 0, 'trim');

            // 判断文章是否存在
            $cate = $this->CategoryModel->find($cateid);

            if (!$cate) {
                $this->error('收藏文章不存在');
                exit;
            }

            //组装数据
            $data = [
                'busid' => $busid,
                'cateid' => $cateid
            ];

            $result = $this->CollectionModel->validate('common/Business/Collection.category')->save($data);

            if ($result === FALSE) {
                $this->error($this->CollectionModel->getError());
                exit;
            } else {
                $this->success('收藏成功');
                exit;
            }
        }
    }

    // 取消收藏方法
    public function cancel()
    {
        $cateid = $this->request->param('cateid', 0, 'trim');
        $busid = $this->request->param('busid', 0, 'trim');

        // 判断文章是否存在
        $cate = $this->CategoryModel->find($cateid);

        if (!$cate) {
            $this->error('收藏文章不存在');
            exit;
        }

        $where = [
            'busid' => $busid,
            'cateid' => $cateid
        ];

        //删除文章
        $result = $this->CollectionModel->destroy($where);

        if ($result === FALSE) {
            $this->error($this->CollectionModel->getError());
            exit;
        } else {
            $this->success('取消收藏成功');
            exit;
        }
    }
}
