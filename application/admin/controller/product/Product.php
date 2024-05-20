<?php

namespace app\admin\controller\product;

use app\common\controller\Backend;
//引入Tp的数据库类
use think\Db;
/**
 * 商品管理
 *
 */
class Product extends Backend
{
    protected $model = null;

    // 当前是否为关联查询
    protected $relationSearch = true;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Product.Product');
        $this->ProductTypeModel = model('Product.Type');
        $this->ProductUnitModel = model('Product.Unit');
    }
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

            $total = $this->model
                ->where($where)
                ->count();

            //查询数据
            $list = $this->model
                ->with(['type'])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加商品
     */
    public function add()
    {
        //判断是否有post请求
        if ($this->request->isPost()) {
            //获取请求中 row 名称的数组元素
            $params = $this->request->param('row/a');

            $params['unitid'] = $params['unitid'] + 1;

            // 插入语句
            $result = $this->model->validate('common/Product/Product.add')->save($params);

            if ($result === FALSE) {
                $this->error($this->model->getError());
                exit;
            } else {
                $this->success();
                exit;
            }
        }

        //查询所有的商品分类
        $ProductType = $this->ProductTypeModel->order('weight asc')->column('name');

        //将生成好的select下拉框赋值到模板
        $this->view->assign('productType', build_select('row[typeid]', $ProductType, [], ['class' => 'form-control selectpicker']));

        $ProductUnit = $this->ProductUnitModel->order('id asc')->column('name');

        $this->view->assign('productUnit', build_select('row[unitid]', $ProductUnit, [], ['class' => 'form-control selectpicker']));

        $StatusData = [
            '1' => '普通商品',
            '2' => '租赁商品'
        ];

        $this->view->assign('statusData', build_select('row[status]', $StatusData, [], ['class' => 'form-control selectpicker']));
        
        $FlagData = [
            '0' => '下架',
            '1' => '上架'
        ];

        $this->view->assign('flagData', build_select('row[flag]', $FlagData, [1], ['class' => 'form-control selectpicker']));

        return $this->view->fetch();
    }

    public function edit($ids = 0){
        //根据id查询课程存不存在
        $product = $this->model->find($ids);

        if (!$product) {
            $this->error('暂时没有课程');
            exit;
        }
        //判断是否有post请求
        if ($this->request->isPost()) {
            //获取请求中 row 名称的数组元素
            $params = $this->request->param('row/a');

            //将id添加到$params
            $params['id'] = $ids;
  
            $result = $this->model->validate('common/Product/Product.edit')->isUpdate(true)->save($params);

            if ($result === FALSE) {
                $this->error($this->model->getError());
                exit;
            } else {
                //判断图片有没有上传，判断图片路劲是否相等
                if ($product['thumbs'] != $params['thumbs']) {
                    @is_file("." . $product['thumbs']) && @unlink("." . $product['thumbs']);
                }

                $this->success();
                exit;
            }
        }

        //查询所有的商品分类
        $ProductType = $this->ProductTypeModel->order('weight asc')->column('id,name');

        //将生成好的select下拉框赋值到模板
        $this->view->assign('productType', build_select('row[typeid]', $ProductType, $product['typeid'], ['class' => 'form-control selectpicker']));

        $ProductUnit = $this->ProductUnitModel->order('id asc')->column('id,name');

        $this->view->assign('productUnit', build_select('row[unitid]', $ProductUnit, $product['unitid'], ['class' => 'form-control selectpicker']));
        
        $StatusData = [
            '1' => '普通商品',
            '2' => '租赁商品'
        ];

        $this->view->assign('statusData', build_select('row[status]', $StatusData, $product['status'], ['class' => 'form-control selectpicker']));
        
        $FlagData = [
            '0' => '下架',
            '1' => '上架'
        ];

        $this->view->assign('flagData', build_select('row[flag]', $FlagData, $product['flag'], ['class' => 'form-control selectpicker']));

        $this->view->assign('product', $product);

        return $this->view->fetch();
    }

    /**
     * 软删除
     */
    public function del($ids = 0)
    {
        $products = $this->model->where(['id' => ['in', $ids]])->select();

        if (!$products) {
            $this->error('暂无数据');
            exit;
        }

        $result = $this->model->destroy($ids);

        if ($result === FALSE) {
            $this->error('删除商品失败');
            exit;
        } else {
            $proData = [
                'id' => $ids,
                'flag' => 0
            ];

            $result = $this->model->isUpdate(true)->save($proData);

            $this->success();
            exit;
        }
    }

    public function recyclebin()
    {
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }

            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            //查询总数
            $total = $this->model
                ->onlyTrashed()  //仅查询软删除的数据
                ->where($where)
                ->count();

            //查询数据
            $list = $this->model
                ->onlyTrashed()  //仅查询软删除的数据
                ->with(['type'])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 还原
     */
    public function restore($ids = 0)
    {
        $proData = [
            'flag' => 1,
            'deletetime' => NULL
        ];
        $result = Db::name('product')->where(['id' => ['in', $ids]])->update($proData);

        if ($result) {

            $this->success();
            exit;
        } else {
            $this->error(__('还原失败'));
            exit;
        }
    }

    /**
     * 真实删除
     */
    public function destroy($ids = 0)
    {
        //查询删除的数据
        $rows = $this->model->onlyTrashed()->select($ids);

        if (!$rows) {
            $this->error('暂无删除的数据');
            exit;
        }

        //单独的查询图片字段
        $thumbs = $this->model->onlyTrashed()->where(['id' => ['in', $ids]])->column('thumbs');

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
