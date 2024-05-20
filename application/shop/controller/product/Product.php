<?php

namespace app\shop\controller\product;

use think\Controller;

class Product extends Controller
{
    public function __construct()
    {
        // 继承父类
        parent::__construct();

        $this->TypeModel = model('Product.Type');
        $this->ProductModel = model('Product.Product');
        $this->UnitModel = model('Product.Unit');
        $this->OrderProductModel = model('Order.Product');
        $this->CartModel = model('Order.Cart');
        $this->BusinessModel = model('Business.Business');
        $this->OrderModel = model('Order.Order');

        //获取系统配置
        $this->url = config('site.url') ? config('site.url') : '';
    }
    //首页
    public function home()
    {
        if ($this->request->isAjax()) {

            // 获取系统配置的logo
            $sitelogo = config('site.logo');

            // 获取系统配置轮播图
            $siteSwiper = config('site.navadv');

            // 遍历去掉 "/",轮播图
            if ($siteSwiper && is_array($siteSwiper)) {
                foreach ($siteSwiper as &$item) {
                    $item = trim($item, '/');
                    $item = $this->url . '/' . $item;
                }
            }

            // 查找商品分类
            $typelist = $this->TypeModel->order('weight asc')->limit(8)->select();

            // 新品
            $newlist = $this->ProductModel->order('createtime desc')->limit(4)->select();

            //获取单张广告图
            $adv = config('site.adv');
            
            $adv = trim($adv, '/');
            $advlist = $this->url . '/' . $adv;

            // 查询热销商品
            $field = [
                'SUM(pronum)' => 'ordnum',
            ];


            $hotlist = $this->OrderProductModel
                ->with(['proinfo'])
                ->field($field)
                ->group('proid')
                ->order('ordnum desc')
                ->limit(6)
                ->select();


            // 组装返回的数据
            $result = [
                'sitelogo' => $sitelogo,
                'siteSwiper' => $siteSwiper,
                'typelist' => $typelist,
                'newlist' => $newlist,
                'advlist' => $advlist,
                'hotlist' => $hotlist
            ];

            $this->success('返回首页数据', '', $result);
        }
    }

    // 分类数据
    public function typelist()
    {
        if ($this->request->isAjax()) {

            $typelist = $this->TypeModel->field('id,name')->select();

            $this->success('返回商品分类', null, $typelist);
        }
    }

    // 商品列表数据
    public function prolist()
    {
        if ($this->request->isAjax()) {
            $typeid = $this->request->param('typeid', 0, 'trim');
            $keyword = $this->request->param('keyword', '', 'trim');
            $orderby = $this->request->param('orderby', 'createtime', 'trim');

            $where = [];

            if ($typeid) {
                $where['typeid'] = $typeid;
            }

            if (!empty($keyword)) {
                $where['name'] = ['LIKE', "%$keyword%"];
            }

            $prolist = $this->ProductModel->where($where)->order("$orderby desc")->select();

            $this->success('返回商品列表', null, $prolist);
            exit;
        }
    }

    //商品信息
    public function proinfo()
    {
        if ($this->request->isAjax()) {
            $proid = $this->request->param('proid', 0, 'trim');

            $product = $this->ProductModel->find($proid);

            if (!$product) {
                $this->error('商品不存在');
                exit;
            }

            $tel = config('site.tel');

            $result = [
                'product' => $product,
                'tel' => $tel
            ];

            $this->success('返回数据', null, $result);
            exit;
        }
    }

    // 获取商品评论列表
    public function comlist()
    {
        if ($this->request->isAjax()) {
            $proid = $this->request->param('proid', 0, 'trim');
            $page = $this->request->param('page', 1 , 'trim');
            
            // 判断商品存不存在
            $product = $this->ProductModel->find($proid);

            if(!$product){
                $this->error('商品暂时缺货');
                exit;
            }

            // 显示条数
            $limit = 8;

            //偏移量
            $offset = ($page-1)*$limit;

            $comlist = $this->OrderProductModel
                        ->with('proinfo')
                        ->where(['proid'=>$proid])
                        ->order('id desc')
                        ->limit($offset, $limit)
                        ->select();
            $this->success('返回评论数据', null, $comlist);
        }
    }
}
