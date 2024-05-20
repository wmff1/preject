<?php

namespace app\rent\controller;

use think\Controller;

class Index extends Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->CategoryModel = model('Category');
        $this->ProductModel = model('Product.Product');
    }

    public function index()
    {
        $case = $this->CategoryModel->where(['flag' => ['like', '%index%']])->limit(5)->select();
        $hot = $this->CategoryModel->where(['flag' => ['like', '%hot%']])->limit(5)->select();
        //获取首页商品 租赁的商品
        $product = $this->ProductModel->where(['status' => 2])->limit(5)->select();

        $result = [
            'case' => $case,
            'hot' => $hot,
            'product' => $product,
        ];

        $this->success('首页数据', null, $result);
    }

    public function about()
    {
        $SiteName = config('site.name');
        $SiteBeian = config('site.beian');
        $SiteTel = config('site.tel');

        $result = [
            'SiteName' => $SiteName,
            'SiteBeian' => $SiteBeian,
            'SiteTel' => $SiteTel,
        ];

        // 关于我们的文章
        $about = $this->CategoryModel->where(['name' => ['like', '%关于我们%']])->find();

        if ($about) {
            $result['about'] = $about;
        }

        $this->success('关于我们', null, $result);
    }
}
