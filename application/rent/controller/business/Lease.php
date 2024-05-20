<?php

namespace app\rent\controller\business;

use think\Controller;

/**
 * 租赁订单控制器
 */
class Lease extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->LeaseModel = model('Lease');
        $this->BusinessModel = model('Business.Business');
        $this->ProductModel = model('Product.Product');
        $this->RecordModel = model('Business.Record');
    }

    public function index()
    {
        $page = $this->request->param('page', 1, 'trim');
        $status = $this->request->param('status', 0, 'trim');

        $where = [];

        if ($status) {
            $where['lease.status'] = $status;
        }

        $limit = 10;

        $start = ($page - 1) * $limit;

        $list = $this->LeaseModel->with(['product'])->where($where)->order('id desc')->limit($start, $limit)->select();

        if ($list) {
            $this->success('返回订单列表', null, $list);
        } else {
            $this->error('没有更多数据', null, []);
        }
    }

    //下订单
    public function add()
    {
        if ($this->request->isPost()) {

            $proid = $this->request->param('proid', 0, 'trim');
            $busid = $this->request->param('busid', 0, 'trim');
            $day = $this->request->param('day', 0, 'trim');
            $endtime = $this->request->param('endtime', '', 'trim');
            $address = $this->request->param('address', '', 'trim');
            $province = $this->request->param('province', '', 'trim');
            $city = $this->request->param('city', '', 'trim');
            $district = $this->request->param('district', '', 'trim');

            //判断商品是否存在
            $product = $this->ProductModel->find($proid);

            if (!$product) {
                $this->error('商品不存在');
                exit;
            }

            $business = $this->BusinessModel->find($busid);

            if (!$business) {
                $this->error('客户不存在');
                exit;
            }

            if ($day <= 0) {
                $this->error('租用时间不能为空');
                exit;
            }

            // 先看订单价格是否充足
            // 押金
            $rent = $product['rent'];

            // 日租
            $price = $product['rent_price'];

            $total = bcmul($price, $day);
            $total = bcadd($total, $rent);

            //计算用户余额是否够减
            $UpdateMoney = bcsub($business['money'], $total);

            if ($UpdateMoney < 0) {
                $this->error('余额不足,请先充值');
                exit;
            }

            //租赁表(插入) - 客户表(更新) - 消费记录(插入)
            $LeaseData = [
                'busid' => $busid,
                'proid' => $proid,
                'rent' => $rent,
                'price' => $total, //含押金
                'endtime' => strtotime($endtime), //将标准时间结构转换为时间戳 //2022/11/15 转换为时间戳
                'address' => $address,
                'province' => $province,
                'city' => $city,
                'district' => $district,
                'status' => 1
            ];

            //接收文件上传
            if (isset($_FILES['card']) && $_FILES['card']['size'] > 0) {
                //调用公共方法
                $success = build_upload('card');

                if ($success['result']) {
                    //上传成功
                    $LeaseData['card'] = $success['data'];
                } else {
                    //上传失败
                    $this->error($success['msg']);
                    exit;
                }
            }

            // 开启事务
            $this->LeaseModel->startTrans();
            $this->BusinessModel->startTrans();
            $this->RecordModel->startTrans();

            //准备插入
            $LeaseStatus = $this->LeaseModel->validate('common/Lease')->save($LeaseData);

            if ($LeaseStatus === FALSE) {
                $this->error($this->LeaseModel->getError());
                exit;
            }

            //更新用户余额
            $BusinessData = [
                'id' => $busid,
                'money' => $UpdateMoney
            ];

            $BusinessStatus = $this->BusinessModel->isUpdate(true)->save($BusinessData);

            if ($BusinessStatus === FALSE) {
                $this->LeaseModel->rollback();
                $this->error($this->BusinessModel->getError());
                exit;
            }

            $proname = $product['name'];

            //插入消费记录
            $RecordData = [
                'total' => "-$total",
                'content' => "租赁了【{$proname}】商品，$day 天",
                'busid' => $busid
            ];

            $RecordStatus = $this->RecordModel->validate('common/Business/Record')->save($RecordData);

            if ($RecordStatus === FALSE) {
                $this->BusinessModel->rollback();
                $this->LeaseModel->rollback();
                $this->error($this->RecordModel->getError());
                exit;
            }

            if ($LeaseStatus === FALSE || $BusinessStatus === FALSE || $RecordStatus === FALSE) {
                $this->RecordModel->rollback();
                $this->BusinessModel->rollback();
                $this->LeaseModel->rollback();
                $this->error('租赁失败，请重新操作');
                exit;
            } else {
                $this->LeaseModel->commit();
                $this->BusinessModel->commit();
                $this->RecordModel->commit();
                $this->success('租赁成功', '/business/lease/index');
                exit;
            }
        }
    }

    // 详情
    public function info()
    {
        if ($this->request->isPost()) {
            $id = $this->request->param('id', 0, 'trim');

            $info = $this->LeaseModel->with(['product', 'business', 'express'])->find($id);

            $tel = config('site.tel');

            if ($info) {
                $this->success('返回订单详情', null, ['info' => $info, 'tel' => $tel]);
                exit;
            } else {
                $this->error('订单不存在');
                exit;
            }
        }
    }

    //查询物流
    public function express()
    {
        if ($this->request->isAjax()) {
            $id = $this->request->param('id', 0, 'trim');

            //判断订单是否存在
            $info = $this->LeaseModel->with(['express'])->find($id);

            if (!$info) {
                $this->error('订单不存在');
                exit;
            }

            if (empty($info['expcode'])) {
                $this->error('暂无物流单号');
                exit;
            }

            if (empty($info['express']['name'])) {
                $this->error('物流公司未知');
                exit;
            }

            //先判断缓存中是否有查询过的记录，如果有就不去在调用接口了
            $cache = cache($info['expcode']);
            if ($cache) {
                if ($cache) {
                    //返回缓存数据
                    $this->success('返回物流信息', null, $cache);
                    exit;
                } else {
                    $this->error('暂无物流信息');
                    exit;
                }
            } else {
                $success = query_express($info['expcode']);

                if ($success['result']) {
                    //存放缓存信息
                    cache($info['expcode'], $success['data']);
                    $this->success('返回物流信息', null, $success['data']);
                    exit;
                } else {
                    //存放缓存信息
                    cache($info['expcode'], []);
                    $this->error($success['msg']);
                    exit;
                }
            }
        }
    }

    // 确认收货
    public function receipt()
    {
        if ($this->request->isPost()) {
            $id = $this->request->param('id', 0, 'trim');

            //查询订单是否存在
            $info = $this->LeaseModel->find($id);

            if (!$info) {
                $this->error('订单不存在');
                exit;
            }

            //准备更新订单状态，收货
            $data = [
                'id' => $id,
                'status' => 3
            ];

            $result = $this->LeaseModel->isUpdate(true)->save($data);

            if ($result === FALSE) {
                $this->error($this->LeaseModel->getError());
                exit;
            } else {
                $this->success('收货成功');
                exit;
            }
        }
    }

    // 归还
    public function recovery()
    {
        if ($this->request->isPost()) {
            $action = $this->request->param('action', '', 'trim');

            //获取厂家信息
            if ($action == "factory") {
                $contact = config('site.contact');
                $mobile = config('site.mobile');
                $address = config('site.address');

                $result = [
                    'contact' => $contact,
                    'mobile' => $mobile,
                    'address' => $address,
                ];

                $this->success('返回厂家数据', null, $result);
                exit;
            }

            //更新归还信息
            if ($action == "recovery") {
                $id = $this->request->param('id', 0, 'trim');
                $busexpid = $this->request->param('busexpid', 0, 'trim');
                $busexpcode = $this->request->param('busexpcode', 0, 'trim');

                //根据id先判断订单记录是否存在
                $lease = $this->LeaseModel->find($id);

                if (!$lease) {
                    $this->error('订单不存在');
                    exit;
                }

                //归还数据更新
                $data = [
                    'id' => $id,
                    'busexpid' => $busexpid,
                    'busexpcode' => $busexpcode,
                    'status' => 4
                ];

                $validate = [
                    [
                        'busexpid' => 'require',
                        'busexpcode' => 'require|unique:lease',
                    ],
                    [
                        'busexpid.require' => '请选择物流公司',
                        'busexpcode.require' => '请填写物流单号',
                        'busexpcode.unique' => '物流单号已存在，请重新输入',
                    ]
                ];

                $result = $this->LeaseModel->validate(...$validate)->isUpdate(true)->save($data);

                if ($result === FALSE) {
                    $this->error($this->LeaseModel->getError());
                    exit;
                } else {
                    $this->success('归还成功');
                    exit;
                }
            }
        }
    }

    //查询快递
    public function explist()
    {
        if ($this->request->isPost()) {
            $field = [
                'name' => 'text',
                'id' => 'values'
            ];
            $result = model('Expressquery.Expressquery')->field($field)->select();

            $this->success('返回快递公司', null, $result);
            exit;
        }
    }

    //评价
    public function comment()
    {
        if ($this->request->isPost()) {
            $id = $this->request->param('id', 0, 'trim');
            $rate = $this->request->param('rate', 5, 'trim');
            $comment = $this->request->param('comment', '', 'trim');

            //根据id先判断订单记录是否存在
            $lease = $this->LeaseModel->find($id);

            if (!$lease) {
                $this->error('订单不存在');
                exit;
            }

            //组装数据
            $data = [
                'id' => $id,
                'rate' => $rate,
                'comment' => $comment,
                'status' => 6
            ];

            $validate = [
                [
                    'rate' => 'require|in:1,2,3,4,5',
                    'comment' => 'require'
                ],
                [
                    'rate.require' => '请选择评分',
                    'rate.in' => '您选择评分有误',
                    'comment.require' => '请输入评价内容'
                ]
            ];

            $result = $this->LeaseModel->validate(...$validate)->isUpdate(true)->save($data);

            if ($result === FALSE) {
                $this->error($this->LeaseModel->getError());
                exit;
            } else {
                $this->success('评论成功');
                exit;
            }
        }
    }
}
