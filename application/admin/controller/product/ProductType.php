<?php

namespace app\admin\controller\product;

use app\common\controller\Backend;

/**
 * 商品分类管理
 *
 * @icon fa fa-circle-o
 */
class ProductType extends Backend
{

    /**
     * Type模型对象
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Product.Type');
    }
    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */

    /**
     * 查看
     */
    public function index()
    {
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }

            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            //查询数据
            $list = $this->model
                ->where($where)
                ->limit($offset, $limit)
                ->select();

            $result = array("rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    public function add()
    {
        if ($this->request->isPost()) {

            $params = $this->request->param('row/a');

            $result = $this->model->validate('common/Product/Type')->save($params);

            if ($result === FALSE) {
                $this->error($this->model->getError());
                exit;
            } else {
                $this->success();
                exit;
            }
        }

        return $this->view->fetch();
    }

    public function edit($ids = 0)
    {
        $productType = $this->model->find($ids);

        if ($this->request->isPost()) {

            $params = $this->request->param('row/a');

            $params['id'] = $ids;

            $result = $this->model->validate('common/Product/Type')->isUpdate(true)->save($params);

            if ($result === FALSE) {
                $this->error($this->model->getError());
                exit;
            } else {
                if ($productType['thumb'] != $params['thumb']) {
                    @is_file("." . $productType['thumb']) && @unlink("." . $productType['thumb']);
                }

                $this->success();
                exit;
            }
        }

        $this->view->assign('productType', $productType);

        return $this->view->fetch();
    }

    /**
     * 真实删除
     */
    public function del($ids = 0)
    {

        $rows = $this->model->select($ids);

        if (!$rows) {
            $this->error('暂无删除的数据');
            exit;
        }

        //单独的查询图片字段
        $thumbs = $this->model->where(['id' => ['in', $ids]])->column('thumb');

        //去除空元素
        $thumbs = array_filter($thumbs);

        //先将数据删除了，然后在去删除图片
        $result = $this->model->destroy($ids, true);

        if ($result === FALSE) {
            $this->error('删除失败');
            exit;
        } else {
            //删除图片
            if (!empty($thumbs)) {
                foreach ($thumbs as $item) {
                    //先判断文件是否存在， 如果存在 再去做删除
                    @is_file("." . $item) && @unlink('.' . $item);
                }
            }

            $this->success();
            exit;
        }
    }
}
