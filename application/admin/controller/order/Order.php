<?php

namespace app\admin\controller\order;

use app\common\controller\Backend;
//引入Tp的数据库类
use think\Db;
/**
 * 订单管理
 *
 */
class Order extends Backend
{
    protected $searchFields = 'id,code,province,busid';
    protected $model = null;

    public function _initialize()
    {
        //是否为关联查询
        $this->relationSearch = true;
        
        parent::_initialize();
        $this->model = model('Order.Order');
        $this->BusinessModel = model('Business.Business');
        $this->ExpressQueryModel = model('Expressquery.Expressquery');
        $this->AdminModel = model('Admin.Admin');
        $this->RegionModel = model("Region");
        $this->BusinessAddressModel = model("Business.Address");
        $this->view->assign("statusList", $this->model->getStatusList());
    }

    /**
     * 查看
    */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = true;

        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }

            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $total = $this->model
                ->where($where)
                ->count();

            $list = $this->model
                ->with(['business','expressquery','admin'])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }

        return $this->view->fetch();
    }

    public function add(){

        $express = $this->ExpressQueryModel->order('id asc')->column('id,name');

        $this->view->assign('express', build_select('row[expressid]', $express, [], ['class' => 'form-control selectpicker']));

        $status = [
            '0' => __('未支付'), '1' => __('已支付'), '2' => __('待发货'), '3' => __('已发货'), '4' => __('已收货'),'5'=>__("已完成"),'6'=>__('退货'),'7'=>__('已退款')
        ];

        $this->view->assign('status', build_select('row[status]', $status, [], ['class' => 'form-control selectpicker']));

        $Admin = $this->AdminModel->where(['id' => $this->auth->id])->find();

        $this->view->assign('admin', build_select('row[status]', $Admin['nickname'], [], ['class' => 'form-control selectpicker']));

        if($this->request->isPost()){
            $params = $this->request->param('row/a');
        }

        return $this->view->fetch();

    }

    public function edit($ids = 0){

        $order = $this->model->with(['business','expressquery','admin'])->find($ids);

        if (!$order) {
            $this->error('订单不存在');
            exit;
        }
   
        if($this->request->isPost()){
            $params = $this->request->param('row/a');

            if(!empty($params["address"]))
            {    
                // 把字符串转换成数组
                $RegionList = explode('/', $params["address"]);

                // 获取到最后一个区级元素
                $lastElement = array_pop($RegionList);

                // 根据最后一个区的元素名查找数据库拿到地区码
                $areas = $this->RegionModel->where(["name" => $lastElement])->value("parentpath");
 
                // 字符串转换成数组
                $RegionCode=explode(",",$areas);

                $params['province'] = isset($RegionCode[0]) ? $RegionCode[0] : '';
                $params['city'] = isset($RegionCode[1]) ? $RegionCode[1] : '';
                $params['district'] = isset($RegionCode[2]) ? $RegionCode[2] : '';
            }

            $Data = [
                    'id' => $ids,
                    'expressid' => $params['expressid'],
                    'expresscode' => $params['expresscode'],
                    'status' => $params['status'],
                    'province' => $params['province'],
                    'city' => $params['city'],
                    'district' => $params['district'],
                    'addressinfo' => $params['addressinfo'],
                    'adminid' => $params['adminid'],
                    'checkmanid' => $params['checkmanid'],
                    'shipmanid' => $params['shipmanid'],
                    'remark' => $params['remark'],
            ];

            // $params['id'] = $ids;

            // $params = array_diff_key($params, ['busid' => $params['busid'], 'address' => $params['address']]);

            $OrderStatus = $this->model->isUpdate(true)->save($Data);

            if($OrderStatus === FALSE)
            {
                $this->error($this->model->getError());
                exit;
            }else {
                $this->success();
                exit;
            }

        }

        $express = $this->ExpressQueryModel->order('id asc')->column('id,name');

        $this->view->assign('express', build_select('row[expressid]', $express, $order['expressid'], ['class' => 'form-control selectpicker']));

        $status = [
            '0' => __('未支付'), '1' => __('已支付'), '2' => __('待发货'), '3' => __('已发货'), '4' => __('已收货'),'5'=>__("已完成"),'6'=>__('退货'),'7'=>__('已退款')
        ];

        $this->view->assign('status', build_select('row[status]', $status, $order['status'], ['class' => 'form-control selectpicker']));

        $admin = $this->AdminModel->order('id asc')->column('id,nickname');

        $this->view->assign('adminSell', build_select('row[adminid]', $admin, $order['adminid'], ['class' => 'form-control selectpicker']));
        $this->view->assign('adminCheckMan', build_select('row[checkmanid]', $admin, $order['checkmanid'], ['class' => 'form-control selectpicker']));
        $this->view->assign('adminShipMan', build_select('row[shipmanid]', $admin, $order['shipmanid'], ['class' => 'form-control selectpicker']));    

        $this->view->assign(['order' => $order]);

        return $this->view->fetch();
    }

    /**
     * 软删除
     */
    public function del($ids = 0)
    {
        $order = $this->model->where(['id' => ['in', $ids]])->select();

        if (!$order) {
            $this->error('订单不存在');
            exit;
        }

        $result = $this->model->destroy($ids);

        if ($result === FALSE) {
            $this->error('删除商品失败');
            exit;
        } else {
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

            $total = $this->model
                ->onlyTrashed()  //仅查询软删除的数据
                ->where($where)
                ->count();

            $list = $this->model
                ->onlyTrashed()  //仅查询软删除的数据
                ->with(['business'])
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
        $orderData = ['deletetime' => NULL];
        $result = Db::name('order')->where(['id' => ['in', $ids]])->update($orderData);

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

        $result = $this->model->destroy($ids, true);

        if ($result === FALSE) {
            $this->error('删除失败');
            exit;
        } else {
            $this->success();
            exit;
        }
    }

    // 发货
    public function delivery($ids = 0)
    {
        $order = $this->model->find($ids);

        if(!$order)
        {
            $this->error("订单不存在");
            exit;
        }

        $order = [
            'id' => ['in', $ids], 
            'status' => 2
        ];

        $OrderStatus = $this->model->isUpdate(true)->save($order);

        if($OrderStatus === FALSE)
        {
            $this->error($this->model->getError());
            exit;
        }else
        {
            $this->success();
            exit;
        } 
    }

    // 发货
    public function receipt($ids = 0)
    {
        $order = $this->model->find($ids);

        if(!$order)
        {
            $this->error("订单不存在");
            exit;
        }

        $order = [
            'id' => ['in', $ids], 
            'status' => 3
        ];

        $OrderStatus = $this->model->isUpdate(true)->save($order);

        if($OrderStatus === FALSE)
        {
            $this->error($this->model->getError());
            exit;
        }else
        {
            $this->success();
            exit;
        } 
    }

}