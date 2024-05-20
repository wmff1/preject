<?php

namespace app\shop\controller\business;

use think\Controller;

/**
 * 用户的收货地址
 */
class Address extends Controller
{
    public function __construct()
    {
        // 继承父类
        parent::__construct();

        $this->BusinessModel = model('Business.Business');
        $this->AddressModel = model('Business.Address');
        $this->RegionModel = model('Region');

        if ($this->request->isAjax()) {
            $id = $this->request->param('id', 0, 'trim');

            //判断用户是否存在
            $business = $this->BusinessModel->find($id);

            if (!$business) {
                $this->error('用户不存在');
                exit;
            }
        }
    }

    //收货地址列表
    public function index()
    {
        if ($this->request->isAjax()) {
            $id = $this->request->param('id', 0, 'trim');

            $address = $this->AddressModel->where(['busid' => $id])->select();

            $this->success('返回收货地址', null, $address);
        }
    }

    //收货地址添加
    public function add()
    {
        if ($this->request->isAjax()) {
            //接收数据
            $id = $this->request->param('id', 0, 'trim');
            $consignee = $this->request->param('consignee', '', 'trim');
            $mobile = $this->request->param('mobile', '', 'trim');
            $address = $this->request->param('address', '', 'trim');
            $code = $this->request->param('code', '', 'trim');
            $status = $this->request->param('status', 0, 'trim');

            //组装数据
            $data = [
                'busid' => $id,
                'consignee' => $consignee,
                'mobile' => $mobile,
                'address' => $address,
                'status' => $status,
            ];

            //查询地区
            $path = $this->RegionModel->where(['code' => $code])->value('parentpath');

            // 字符串转换为数组
            $RegionCode = explode(',', $path);

            $data['province'] = isset($RegionCode[0]) ? $RegionCode[0] : '';
            $data['city'] = isset($RegionCode[1]) ? $RegionCode[1] : '';
            $data['district'] = isset($RegionCode[2]) ? $RegionCode[2] : '';

            //勾选为默认的情况下
            if ($status) {
                //将这个人其他的地址，全部改成非默认的
                $this->AddressModel->where(['busid' => $id])->update(['status' => 0]);
            }

            //执行插入语句
            $result = $this->AddressModel->validate('common/Business/Address')->save($data);

            if ($result === FALSE) {
                $this->error($this->AddressModel->getError());
                exit;
            } else {
                $this->success('添加地址成功');
                exit;
            }
        }
    }

    //查找当前收货地址
    public function search()
    {
        if ($this->request->isAjax()) {
            $id = $this->request->param('addrid', 0, 'trim');
            $busid = $this->request->param('id', 0, 'trim');

            // 判断收货地址是否存在
            $address = $this->AddressModel->where(['id' => $id, 'busid' => $busid])->find();

            if (!$address) {
                $this->error('收货地址不存在');
                exit;
            } else {
                $this->success('返回收货地址', '', $address);
                exit;
            }
        }
    }

    // 编辑地址
    public function edit()
    {
        if ($this->request->isAjax()) {
            //接收数据
            $id = $this->request->param('id', 0, 'trim');
            $addrid = $this->request->param('addrid', 0, 'trim');
            $consignee = $this->request->param('consignee', '', 'trim');
            $mobile = $this->request->param('mobile', '', 'trim');
            $address = $this->request->param('address', '', 'trim');
            $code = $this->request->param('code', '', 'trim');
            $status = $this->request->param('status', 0, 'trim');

            //判断收货地址是否存在
            $where = [
                'id' => $addrid,
                'busid' => $id
            ];

            $check = $this->AddressModel->where($where)->find();

            if (!$check) {
                $this->error('收货地址不存在');
                exit;
            }

            //组装数据
            $data = [
                'id' => $addrid,
                'busid' => $id,
                'consignee' => $consignee,
                'mobile' => $mobile,
                'address' => $address,
                'status' => $status,
            ];

            //查询地区
            $path = $this->RegionModel->where(['code' => $code])->value('parentpath');

            // 字符串转换为数组
            $RegionCode = explode(',', $path);

            $data['province'] = isset($RegionCode[0]) ? $RegionCode[0] : '';
            $data['city'] = isset($RegionCode[1]) ? $RegionCode[1] : '';
            $data['district'] = isset($RegionCode[2]) ? $RegionCode[2] : '';

            //勾选为默认的情况下
            if ($status) {
                //将这个人其他的地址，全部改成非默认的
                $this->AddressModel->where(['busid' => $id])->update(['status' => 0]);
            }

            //执行语句
            $result = $this->AddressModel->validate('common/Business/Address')->isUpdate(true)->save($data);

            if ($result === FALSE) {
                $this->error($this->AddressModel->getError());
                exit;
            } else {
                $this->success('更新地址成功');
                exit;
            }
        }
    }

    // 删除地址
    public function del()
    {
        if ($this->request->isAjax()) {

            $id = $this->request->param('addrid', 0, 'trim');
            $busid = $this->request->param('id', 0, 'trim');

            $address = $this->AddressModel->where(['id' => $id, 'busid' => $busid])->find();

            if (!$address) {
                $this->error('收货地址不存在');
                exit;
            }

            // 存在执行删除语句
            $del = $this->AddressModel->destroy($id);

            if ($del === FALSE) {
                $this->error('删除地址失败');
                exit;
            } else {
                $this->success('删除地址成功');
                exit;
            }
        }
    }

    // 默认地址
    public function check()
    {
        if ($this->request->isAjax()) {

            $id = $this->request->param('addrid', 0, 'trim');
            $busid = $this->request->param('id', 0, 'trim');

            // 判断收货地址是否存在
            $address = $this->AddressModel->where(['id' => $id, 'busid' => $busid])->find();

            if (!$address) {
                $this->error('收货地址不存在');
                exit;
            }

            $this->AddressModel->startTrans();
            
            //要将这个人 全部的地址状态 都改成0  在去执行某一个改为默认地址
            $UpdateStatus = $this->AddressModel->where(['busid' => $busid])->update(['status' => 0]);

            if ($UpdateStatus === FALSE) {
                $this->error('更新默认收货地址失败');
                exit;
            }

            //更新选中的地址
            $result = $this->AddressModel->where(['id' => $id])->update(['status' => 1]);

            if ($result === FALSE) {
                //回滚事务
                $this->AddressModel->rollback();
                $this->error('更新默认地址失败');
                exit;
            } else {
                //提交事务
                $this->AddressModel->commit();
                $this->success('更新默认地址成功');
                exit;
            }
        }
    }
    // 下单返回地址
    public function default()
    {
        $busid = $this->request->param('id', 0, 'trim');

        $where = [
            'busid' => $busid,
            'status' => 1
        ];

        $address = $this->AddressModel->where($where)->find();

        if($address)
        {
            $this->success('返回默认地址', null, $address);
            exit;
        }else
        {
            $address = $this->AddressModel->where(['busid' => $busid])->find();

            if($address)
            {
                $this->success('返回默认地址', null, $address);
                exit;
            }else
            {
                $this->error('暂无收货地址');
                exit;
            }
        }
    }
}
