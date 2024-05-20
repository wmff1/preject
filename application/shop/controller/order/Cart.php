<?php

namespace app\shop\controller\order;

use think\Controller;
use think\Db;
use app\common\model\Product\Product;

/**
 * 用户的收货地址
 */
class Cart extends Controller
{
    public function __construct()
    {
        // 继承父类
        parent::__construct();

        $this->BusinessModel = model('Business.Business');
        $this->CartModel = model('Order.Cart');
        $this->ProductModel = model('Product.Product');
        $this->AddressModel = model('Business.Address');

        //获取系统配置
        $this->url = config('site.url') ? config('site.url') : '';

        // 每一个方法进入前都判断是否登录
        if ($this->request->isAjax()) {
            $this->busid = $this->request->param('busid', 0, 'trim');

            $business = $this->BusinessModel->find($this->busid);

            if (!$business) {
                $this->error('用户不存在');
                exit;
            }
        }
    }

    public function index()
    {
        if ($this->request->isAjax()) {

            // 从cart文件点击提交订单事件传递ids过去，
            // 再在confirm文件的周期函数获取ids（cartids）
            // 后端获取传过来的ids === cartids

            $type = $this->request->param('type');
            if($type == 'cart'){
                $cartids = $this->request->param('cartids', 0, 'trim');
    
                $where = [
                    'busid' => $this->busid
                ];
    
                if($cartids)
                {
                    $cartids = explode(',', $cartids);
                    $where['cart.id'] = ['in', $cartids];
                }
    
                $productList = $this->CartModel->with('product')->where($where)->select();
                
                if (!$productList) {
                    $this->error('购物商品不存在');
                    exit;
                }
            }
            if($type == 'info'){
                $proid = $this->request->param('proid', 0, 'trim');
                $product = new Product();
                $productList = $product->where('id',$proid)->where('flag',1)->field('id,name,stock,price,thumbs,flag')->select();
                
                if (!$productList) {
                    $this->error('商品不存在');
                    exit;
                }
                if($productList){
                    foreach($productList as &$row){
                        $row['total'] = $product->where('id',$proid)->sum('price');
                    }
                }
            }
            $this->success('返回购物车数据', '', $productList);
        }
    }

    // 获取数量
    public function badge()
    {
        if ($this->request->isAjax()) {
            $total = $this->CartModel->where(['busid' => $this->busid])->sum('pronum');

            $this->success('返回购物车数量', null, $total);
        }
    }

    // 添加购物车
    public function add()
    {
        if ($this->request->isAjax()) {
            $proid = $this->request->param('proid', 0, 'trim');

            $product = $this->ProductModel->find($proid);

            if (!$product) {
                $this->error('商品不存在');
                exit;
            }

            $CartData = [];
            // 要执行插入语句还是更新语句
            $result = false;

            $where = [
                'busid' => $this->busid,
                'proid' => $proid
            ];

            // 判断购物车是否有当前商品的记录
            $cart = $this->CartModel->where($where)->find();

            // 如果断购物车存在当前商品的记录执行更新语句
            if ($cart) {

                $pronum = $cart['pronum'] + 1;

                $total = bcmul($pronum, $cart['price']);

                $CartData = [
                    'id' => $cart['id'],
                    'busid' => $cart['busid'],
                    'proid' => $cart['proid'],
                    'price' => $cart['price'],
                    'pronum' => $pronum,
                    'total' => $total
                ];

                $result = $this->CartModel->validate('common/Order/Cart')->isUpdate(true)->save($CartData);
            } else {
                // 不存在执行插入语句
                $CartData = [
                    'busid' => $this->busid,
                    'proid' => $proid,
                    'pronum' => 1,
                    'price' => $product['price'],
                    'total' => $product['price']
                ];
                $result = $this->CartModel->validate('common/Order/Cart')->save($CartData);
            }

            if ($result === FALSE) {
                $this->error($this->CartModel->getError());
                exit;
            } else {
                $this->success('加入购物车成功,是否跳转到购物车', '/order/order/cart');
                exit;
            }
        }
    }

    // 编辑购物车
    public function edit()
    {
        if ($this->request->isAjax()) {
            $id = $this->request->param('id', 0, 'trim');
            $pronum = $this->request->param('pronum', 0, 'trim');

            // 组装数据判断购物车是否存在
            $where = [
                'id' => $id,
                'busid' => $this->busid
            ];

            $cart = $this->CartModel->where($where)->find();

            if (!$cart) {
                $this->error('购物车不存在');
                exit;
            }

            // 判断购物车的数量不能小于等于0
            if ($pronum <= 0) {
                $this->error('数量不能小于零');
                exit;
            }

            // 组装准备更新的数据
            $data = [
                'id' => $cart['id'],
                'busid' => $cart['busid'],
                'proid' => $cart['proid'],
                'pronum' => $pronum,
                'price' => $cart['price'],
                'total' => bcmul($cart['price'], $pronum)
            ];

            // 更新购物车表
            $result = $this->CartModel->validate('common/Order/Cart')->isUpdate(true)->save($data);

            if ($result === FALSE) {
                $this->error($this->CartModel->getError());
                exit;
            } else {
                $this->success('更新购物车成功');
                exit;
            }
        }
    }

    // 购物车删除
    public function del()
    {
        if ($this->request->isAjax()) {
            $cartid = $this->request->param('cartid', 0, 'trim');

            //判断购物车记录是否存在
            $where = [
                'id' => $cartid,
                'busid' => $this->busid,
            ];

            $cart = $this->CartModel->where($where)->find();

            if (!$cart) {
                $this->error('购物车记录不存在');
                exit;
            }

            //删除
            $result = $this->CartModel->destroy($cartid);

            if ($result === FALSE) {
                $this->error('购物车删除失败');
                exit;
            } else {
                $this->success('购物车删除成功');
                exit;
            }
        }
    }

    // 立即购买
    public function buy(){
        if($this->request->isAjax()) {

            $busid = $this->request->param('busid', 0, 'trim');
            $proid = $this->request->param('proid', 0, 'trim');
            $price = $this->request->param('price', 0, 'trim');
            $total = $this->request->param('total', 0, 'trim');

            $product = $this->ProductModel->find($proid);

            if (!$product) {
                $this->error('商品不存在');
                exit;
            }

            $CartData = [
                'busid' => $this->busid,
                'proid' => $proid,
                'pronum' => 1,
                'price' => $price,
                'total' => $total
            ];

            // 开启事务
            $this->CartModel->startTrans();

            $result = $this->CartModel->validate('common/Order/Cart')->save($CartData);

            if ($result === FALSE) {
                $this->error($this->CartModel->getError());
                exit;
            } else {
                
                $where = [
                    'busid' => $this->busid,
                    'proid' => $proid
                ];
    
                // 判断购物车是否有当前商品的记录
                $cart = $this->CartModel->where($where)->find();

                if (!$cart) {
                    $this->error('购物车记录不存在');
                    exit;
                }
                
                $cartid = $cart['id'];
                
                $this->CartModel->commit();
                $this->success('立即购买','/order/order/confirm', $cartid);
            }
        }
    }
}
