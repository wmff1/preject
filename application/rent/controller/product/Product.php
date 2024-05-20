<?php

namespace app\rent\controller\product;

use think\Controller;

class Product extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->BusinessModel = model('Business.Business');
        $this->ProductModel = model('Product.Product');
        $this->CollectionModel = model('Business.Collection');
    }

    public function index()
    {
        $page = $this->request->param('page', 1, 'trim');

        $limit = 10;

        $start = ($page - 1) * $limit;

        $list = $this->ProductModel->where(['status' => 2])->order('id desc')->limit($start, $limit)->select();

        if ($list) {
            $this->success('返回商品列表', null, $list);
        } else {
            $this->error('没有更多数据', null, []);
        }
    }

    public function info()
    {
        if ($this->request->isPost()) {
            $pid = $this->request->param('pid', 0, 'trim');
            $busid = $this->request->param('busid', 0, 'trim');

            $product = $this->ProductModel->find($pid);

            $where = [
                'busid' => $busid,
                'proid' => $pid
            ];

            if ($product) {
                //查看当前用户是否有收藏过该商品
                $check = $this->CollectionModel->where($where)->find();

                // 增加一个自定义属性
                $product['check'] = $check ? true : false;

                $this->success('返回商品详情', null, $product);
            } else {
                $this->error('暂无商品信息', null, $product);
            }
        }
    }

    // 收藏方法
    public function collection()
    {
        if ($this->request->isPost()) {
            $proid = $this->request->param('proid', 0, 'trim');
            $busid = $this->request->param('busid', 0, 'trim');

            //先找出商品是否存在
            $product = $this->ProductModel->find($proid);

            if (!$product) {
                $this->error('收藏商品不存在');
                exit;
            }

            //组装数据
            $data = [
                'busid' => $busid,
                'proid' => $proid
            ];

            $result = $this->CollectionModel->validate('common/Business/Collection.product')->save($data);

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
        $proid = $this->request->param('proid', 0, 'trim');
        $busid = $this->request->param('busid', 0, 'trim');

        //先找出商品是否存在
        $product = $this->ProductModel->find($proid);

        if (!$product) {
            $this->error('收藏商品不存在');
            exit;
        }

        //组装数据
        $where = [
            'busid' => $busid,
            'proid' => $proid
        ];

        //删除记录
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
