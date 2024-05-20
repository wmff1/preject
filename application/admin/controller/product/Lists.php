<?php

namespace app\admin\controller\product;

use app\common\controller\Backend;
use think\Db;

/**
 * 商品管理
 *
 * @icon fa fa fa-info
 */
class Lists extends Backend
{
    /**
     * Product模型对象
     * @var \app\common\model\app\common\model\Product\Product
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Product.Product');
    }

    /**
     * 查看资料
     */
    public function info($ids = 0)
    {

        $adminid = $this->auth->id;

        $product = $this->model
            ->with(['type', 'unit'])
            ->find($ids);

        if (!$product) {
            $this->error('商品不存在');
            exit;
        }

        $this->view->assign('info', $product);

        return $this->view->fetch();
    }
}
