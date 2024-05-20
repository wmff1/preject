<?php

namespace app\shop\controller\order;

use think\Controller;
use app\common\model\Product\Product;
use app\common\model\Order\Product as OrderProduct;
use app\common\model\Order\Order as OrderOrder;
use app\common\model\Order\Cart;
use app\common\model\Business\Business;
use app\common\model\Business\Address;
use app\common\model\Region;
use app\common\model\Expressquery\Expressquery;

/**
 * 订单控制器
 */
class Order extends Controller
{
    public function __construct()
    {
        // 继承父类
        parent::__construct();

        // $this->ProductModel = model('Product.Product');
        // $this->OrderProductModel = model('Order.Product');
        // $this->OrderModel = model('Order.Order');
        // $this->CartModel = model('Order.Cart');
        // $this->BusinessModel = model('Business.Business');
        // $this->AddressModel = model('Business.Address');
        // $this->RegionModel = model("Region");
        // $this->ExpressQueryModel = model('Expressquery.Expressquery');

        //获取系统配置里面的选项
        $this->url = config('site.url') ? config('site.url') : '';

        if ($this->request->isAjax()) {
            $busid = $this->request->param('busid', 0, 'trim');
            $business = new Business();
            $base = $business->where('id',$busid)->paginate();
            if (!$base) {
                $this->error('用户不存在');
                exit;
            }
        }
    }

    // 再一次提交

    /**
     * 订单入库操作
     */
    // public function add(){
    //     if($this->request->isAjax()){
    //         $busid = $this->request->param('busid', 0, 'trim');
    //         $proid = $this->request->param('proid', 0, 'trim');
    //         $price = $this->request->param('price', 0, 'trim');
    //         $total = $this->request->param('total', 0, 'trim');
    //         $addrid = $this->request->param('addrid', 0, 'trim');
    //         $remark = $this->request->param('remark', '', 'trim');
    //         $type = $this->request->param('type', 'all', 'trim');
            
    //         $product = Product::where('id',$proid)->find();
    //         if(!$product){
    //             $this->error('商品不存在');
    //             exit;
    //         }
    //         $address = Address::where('id',$addrid)->find();
    //         if(!$address){
    //             $this->error('请添加收货地址');
    //             exit;
    //         }
    //         if (empty($_SERVER['HTTP_REFERER'])) {
    //             $this->error('请求不合法');
    //             exit;
    //         }
    //         // 生成订单号
    //         $setOrderSn = build_code();

    //         $data = [
    //             'busid' => $busid,
    //             'product_id' => $proid,
    //             'price' => $price,
    //             'amount' => $total,
    //             'code' => $setOrderSn,
    //             'remark' => $remark,
    //             'referer' => $_SERVER['HTTP_REFERER']
    //         ];
    //         if($type == 'info'){
    //             try {
    //                 $orderId = model('Order.Order')->add($data);
    //             } catch (\Exception $e) {
    //                 $this->error('订单处理失败');
    //             }
    //         }
    //         // 重新获取订单
    //         $order = OrderOrder::where('id', $orderId)->find();
    //         // $this->redirect('pay/wechat',['id' => $orderId]);
    //         return $this->success('请支付','/order/pay/wechat',$order); 
    //     }
    // }
    
    // 下订单
    // public function add()
    // {

    //     if ($this->request->isAjax()) {
    //         $addrid = $this->request->param('addrid', 0, 'trim');
    //         $cartids = $this->request->param('cartids', 0, 'trim');
    //         $remark = $this->request->param('remark', 0, 'trim');

    //         //查询出用户信息
    //         $business = $this->BusinessModel->find($this->busid);

    //         if ($business['status'] == '0') {
    //             $this->error('您的账号，暂未通过邮箱认证，请认证后下单');
    //             exit;
    //         }

    //         // 判断收货地址是否存在
    //         $where = [
    //             'busid' => $this->busid,
    //             'id' => $addrid
    //         ];

    //         $address = $this->AddressModel->where($where)->find();

    //         if (!$address) {
    //             $this->error('收货地址不存在');
    //             exit;
    //         }

    //         // 地区码
    //         $regioncode = [];

    //         if(!empty($address["region_text"]))
    //         {    
    //             // 把字符串转换成数组
    //             $RegionList = explode('-', $address["region_text"]);

    //             // 获取到最后一个区级元素
    //             $lastElement = array_pop($RegionList);

    //             // 根据最后一个区的元素名查找数据库拿到地区码
    //             $areas = $this->RegionModel->where(["name" => $lastElement])->value("parentpath");

    //             // 字符串转换成数组
    //             $RegionCode=explode(",",$areas);

    //             $regioncode['province'] = isset($RegionCode[0]) ? $RegionCode[0] : '';
    //             $regioncode['city'] = isset($RegionCode[1]) ? $RegionCode[1] : '';
    //             $regioncode['district'] = isset($RegionCode[2]) ? $RegionCode[2] : '';
    //         }

    //         $where = [
    //             'cart.id' => ['in', $cartids]
    //         ];

    //         // 购物车列表
    //         $cartlist = $this->CartModel->with(['product'])->where($where)->select();

    //         //统计要下单购物车的总计
    //         $total = $this->CartModel->with(['product'])->where($where)->sum('total');

    //         if (!$cartlist) {
    //             $this->error('购物车记录为空');
    //             exit;
    //         }

    //         // 判断库存够不够
    //         foreach ($cartlist as $cart) {
    //             //库存数 < 购物数量
    //             if ($cart['product']['stock'] < $cart['pronum']) {
    //                 $this->error($cart['product']['name'] . "库存不足，无法下单");
    //                 exit;
    //             }
    //         }

    //         // 判断钱够不够
    //         $UpdateMoney = bcsub($business['money'], $total);

    //         if ($UpdateMoney < 0) {
    //             $this->error('余额不足请先充值');
    //             exit;
    //         }

    //         // 订单表 插入
    //         // 订单商品表 插入
    //         // 商品表 更新
    //         // 用户表 更新
    //         // 用户的消费记录表 插入
    //         // 购物车 删除

    //         $OrderModel = model('Order.Order');
    //         $OrderProductModel = model('Order.Product');
    //         $ProductModel = model('Product.Product');
    //         $RecordModel = model('Business.Record');
    //         $BusinessModel = model('Business.Business');
    //         $CartModel = model('Order.Cart');

    //         // 开启事务
    //         $OrderModel->startTrans();
    //         $OrderProductModel->startTrans();
    //         $ProductModel->startTrans();
    //         $RecordModel->startTrans();
    //         $BusinessModel->startTrans();
    //         $CartModel->startTrans();


    //         // 生成订单编号
    //         $code = build_code('FA');

    //         // 随机选中物流公司
    //         $expressQuery = $this->ExpressQueryModel->select();

    //         $expressid = rand(1,count($expressQuery));

    //         if($expressid <= 0 && $expressid == 'NULL'){
    //             $this->error($ExpressQueryModel->getError());
    //             exit;
    //         }

    //         // 生成物流编号
    //         $expressQueryCoding = $this->ExpressQueryModel->where(['id' => $expressid])->find();

    //         $coding = build_code($expressQueryCoding['coding']);

    //         //组装数据
    //         $OrderData = [
    //             'code' => $code,
    //             'busid' => $business['id'],
    //             // 'businessaddrid' => $addrid,
    //             "province"=>$regioncode['province'],
    //             "city"=> $regioncode['city'],
    //             "district"=>$regioncode['district'],
    //             "addressinfo"=>$address['address'],
    //             'amount' => $total,
    //             'remark' => $remark,
    //             'expressid' => $expressid,
    //             'expresscode' => $coding,
    //             'status' => 1,
    //             'adminid' => $business['adminid']
    //         ];

    //         // 插入订单数据
    //         $OrderStatus = $OrderModel->validate('common/Order/Order')->save($OrderData);

    //         if ($OrderStatus === FALSE) {
    //             $this->error($OrderModel->getError());
    //             exit;
    //         }

    //         //整理订单商品数据
    //         $OrderProductData = [];

    //         //整理商品表更新库存的数据
    //         $ProductData = [];
    //         foreach ($cartlist as $item) {
    //             $OrderProductData[] = [
    //                 'orderid' => $OrderModel->id, //获取上一步插入的自增id
    //                 'proid' => $item['proid'],
    //                 'pronum' => $item['pronum'],
    //                 'price' => $item['price'],
    //                 'total' => $item['total'],
    //             ];

    //             //更新  商品库存 -  购买数量
    //             $UpdateStock = bcsub($item['product']['stock'], $item['pronum']);

    //             $ProductData[] = [
    //                 'id' => $item['proid'],
    //                 'stock' => $UpdateStock
    //             ];
    //         }

    //         //多条插入
    //         $OrderProductStatus = $OrderProductModel->validate('common/Order/Product')->saveAll($OrderProductData);

    //         if ($OrderProductStatus === FALSE) {
    //             $OrderModel->rollback();
    //             $this->error($OrderProductModel->getError());
    //             exit;
    //         }

    //         //执行更新商品表库存语句
    //         $ProductStatus = $ProductModel->isUpdate(true)->saveAll($ProductData);

    //         if ($ProductStatus === FALSE) {
    //             $OrderProductModel->rollback();
    //             $OrderModel->rollback();
    //             $this->error($ProductModel->getError());
    //             exit;
    //         }

    //         //更新用户的余额
    //         $BusinessData = [
    //             'id' => $business['id'],
    //             'money' => $UpdateMoney
    //         ];

    //         $BusinessStatus = $BusinessModel->isUpdate(true)->save($BusinessData);

    //         if ($BusinessStatus === FALSE) {
    //             $ProductModel->rollback();
    //             $OrderProductModel->rollback();
    //             $OrderModel->rollback();
    //             $this->error($BusinessModel->getError());
    //             exit;
    //         }

    //         //用户消费记录表 插入
    //         $RecordData = [
    //             'total' => "-$total",
    //             'content' => "购买商品，订单号：【{$code}】",
    //             'busid' => $business['id']
    //         ];

    //         $RecordStatus = $RecordModel->validate('common/Business/Record')->save($RecordData);

    //         if ($RecordStatus === FALSE) {
    //             $BusinessModel->rollback();
    //             $ProductModel->rollback();
    //             $OrderProductModel->rollback();
    //             $OrderModel->rollback();
    //             $this->error($RecordModel->getError());
    //             exit;
    //         }

    //         // $RecordStatus = $RecordModel->find();

    //         // $BusinessData = [

    //         // ];

    //         // var_dump($RecordStatus->toArray());
    //         // exit;
    //         //删除购物车记录
    //         $where = ['id' => ['in', $cartids]];

    //         $CartStatus = $CartModel->where($where)->delete();

    //         if ($CartStatus === FALSE) {
    //             $RecordModel->rollback();
    //             $BusinessModel->rollback();
    //             $ProductModel->rollback();
    //             $OrderProductModel->rollback();
    //             $OrderModel->rollback();
    //             $this->error($CartModel->getError());
    //             exit;
    //         }

    //         if ($OrderStatus === FALSE || $OrderProductStatus === FALSE || $ProductStatus === FALSE || $BusinessStatus === FALSE || $RecordStatus === FALSE || $CartStatus === FALSE) {
    //             $CartModel->rollback();
    //             $RecordModel->rollback();
    //             $BusinessModel->rollback();
    //             $ProductModel->rollback();
    //             $OrderProductModel->rollback();
    //             $OrderModel->rollback();
    //             $this->error('下单失败');
    //             exit;
    //         } else {
    //             $OrderModel->commit();
    //             $OrderProductModel->commit();
    //             $ProductModel->commit();
    //             $BusinessModel->commit();
    //             $RecordModel->commit();
    //             $CartModel->commit();
    //             $this->success('下单成功，等待商家发货', '/order/order/index');
    //             exit;
    //         }
    //     }
    // }

    // 订单列表
    public function index()
    {
        if ($this->request->isAjax()) {
            $busid = $this->request->param('busid', 0, 'trim');
            $status = $this->request->param('status', 0, 'trim');

            // 组装条件
            $where = [
                'busid' => $busid,
            ];

            if ($status) {
                $where['status'] = $status;
            }

            $list = $this->OrderModel->where($where)->select();

            if ($list) {
                foreach ($list as &$item) {

                    //查询该订单下面的订单商品
                    $item['prolist'] = $this->OrderProductModel->with(['proinfo'])->where(['orderid' => $item['id']])->find();
                }   
            }

            $this->success('订单列表', null, $list);
        }
    }

    // 物流
    public function express()
    {
        if ($this->request->isAjax()) {
            $orderid = $this->request->param('orderid', 0, 'trim');
            $busid = $this->request->param('busid', 0, 'trim');

            // 查找订单是否存在
            $order = $this->OrderModel->with('expressquery')->find($orderid);

            if (!$order) {
                $this->error('订单不存在');
                exit;
            }

            if (empty($order['expresscode'])) {
                $this->error('物流单号不存在');
                exit;
            }

            if (empty($order['expressquery']['name'])) {
                $this->error('物流公司未知');
                exit;
            }

            //先判断缓存中是否有查询过的记录，如果有就不去在调用接口了
            $cache = cache($order['expresscode']);

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
                $success = query_express($order['expresscode']);

                if ($success['result']) {
                    //存放缓存信息
                    cache($order['expresscode'], $success['data']);
                    $this->success('返回物流信息', null, $success['data']);
                    exit;
                } else {
                    //存放缓存信息
                    cache($order['expresscode'], []);
                    $this->error($success['msg']);
                    exit;
                }
            }
        }
    }

    // 订单详情
    public function info()
    {
        if ($this->request->isAjax()) {
            $busid = $this->request->param('busid', 0, 'trim');
            $orderid = $this->request->param('orderid', 0, 'trim');

            $orderinfo = $this->OrderModel->find($orderid);

            if (!$orderinfo) {
                $this->error('订单不存在');
                exit;
            }

            // 查找商品订单是否存在
            $product = $this->OrderProductModel->with(['proinfo'])->where(['orderid' => $orderid])->select();

            if (!$product) {
                $this->error('商品订单不存在');
                exit;
            }

            //获取订单的收货地址
            $business = $this->BusinessModel->where(['id' => $busid])->find();

            if (!$business) {
                $this->error('未找到用户');
                exit;
            }

            $data = [
                'order' => $orderinfo,
                'product' => $product,
                'business' => $business
            ];

            $this->success('返回数据', null, $data);
            exit;
        }
    }

    // 确认收货
    public function confirm()
    {
        if ($this->request->isAjax()) {
            $busid = $this->request->param('busid', 0);
            $orderid = $this->request->param('orderid', 0);

            $order = $this->OrderModel->find($orderid);

            if (!$order) {
                $this->error('订单不存在');
                exit;
            }

            $OrderData = [
                'id' => $orderid,
                'status' => '3'
            ];

            $result = $this->OrderModel->isUpdate(true)->save($OrderData);

            if ($result === FALSE) {
                $this->error($this->OrderModel->getError());
                exit;
            }

            $this->success('收货成功', '/order/order/index');
            exit;
        }
    }

    // 待评价
    public function comment()
    {
        if ($this->request->isAjax()) {
            // 商品id
            $orderid = $this->request->param('orderid', '', 'trim');
            // 用户id
            $busid = $this->request->param('busid', 0);
            // 分页
            $page = $this->request->param('page', 1, 'trim');

            $order = $this->OrderModel->find($orderid);

            if (!$order) {
                $this->error('订单不存在');
                exit;
            }

            $list = $this->OrderProductModel->with(['proinfo', 'orderinfo'])->where(['orderid' => $orderid])->select();

            $this->success('返回带评论订单', null, $list);
            exit;
        }
    }

    // 评价
    public function assess()
    {
        if ($this->request->isAjax()) {
            $orderid = $this->request->param('orderid', 0, 'trim');

            $order = $this->OrderProductModel->with(['proinfo', 'orderinfo'])->find($orderid);

            if (!$order) {
                $this->error('订单商品不存在');
                exit;
            }

            $this->success('返回订单信息', null, $order);
            exit;
        }
    }

    // 提交评价
    public function submit()
    {
        if ($this->request->isAjax()) {

            $busid = $this->request->param('busid', 0, 'trim');
            // 订单商品id
            $orderid = $this->request->param('orderid', 0, 'trim');
            // 评分
            $rate = $this->request->param('rate', 0, 'trim');
            // 商品评价内容
            $assess = $this->request->param('assess', 0, 'trim');


            $order = $this->OrderProductModel->find($orderid);

            if (!$order) {
                $this->error('订单商品不存在');
                exit;
            }

            $OrderModel = model('Order.Order');
            $OrderProductModel = model('Order.Product');

            $OrderModel->startTrans();
            $OrderProductModel->startTrans();

            $data = [
                'id' => $orderid,
                'rate' => $rate,
                'comment' => $assess,
            ];

            $OrderProductStatus = $this->OrderProductModel->isUpdate(true)->save($data);

            if ($OrderProductStatus === FALSE) {
                $this->error($OrderProductModel->getError());
                exit;
            }

            $OrderData = [
                'id' => $order['orderid'],
                'status' => 4
            ];

            $OrderStatus = $this->OrderModel->isUpdate(true)->save($OrderData);
     
            if ($OrderStatus === FALSE) {
                $OrderProductModel->rollback();
                $this->error($OrderModel->getError());
                exit;
            }

            if ($OrderStatus === FALSE || $OrderProductStatus === FALSE) {
                $OrderProductModel->rollback();
                $OrderModel->rollback();
                $this->error('评价失败');
                exit;
            } else {
                $OrderModel->commit();
                $OrderProductModel->commit();
                $this->success('评价成功', '/order/order/index');
                exit;
            }
        }
    }

    // 退货
    public function refund()
    {
        if($this->request->isAjax()){
            $busid = $this->request->param('busid', 0);
            $orderid = $this->request->param('orderid', 0);

            $order = $this->OrderModel->find($orderid);

            if (!$order) {
                $this->error('订单不存在');
                exit;
            }

            $business = $this->BusinessModel->find($this->busid);

            // 用户表 更新
            // 商品表 更新
            // 用户的消费记录表 删除
            // 订单商品表 软删除
            // 订单表 更新

            $BusinessModel = model('Business.Business');
            $ProductModel = model('Product.Product');
            $OrderModel = model('Order.Order');

            $BusinessModel->startTrans();
            $ProductModel->startTrans();
            $OrderModel->startTrans();

            $UpdateMoney = bcadd($order['amount'],$business['money']);

            $BusinessData = [
                'id' => $business['id'],
                'money' => $UpdateMoney
            ];

            $BusinessStatus = $BusinessModel->isUpdate(true)->save($BusinessData);

            if ($BusinessStatus === FALSE) {
                $this->error($BusinessModel->getError());
                exit;
            }

            $Product = $this->OrderProductModel->with('proinfo')->where(['orderid' => $orderid])->find();
           
            $stock = bcadd($Product['pronum'],$Product['proinfo']['stock']);

            $ProductData = [
                'id' => $Product['proid'],
                'stock' => $stock
            ];

            $ProductStatus = $this->ProductModel->isUpdate(true)->save($ProductData);

            if ($ProductStatus === FALSE) {
                $BusinessModel->rollback();
                $this->error($ProductModel->getError());
                exit;
            }

             $OrderData = [
                'id' => $order['id'],
                'status' => 5
            ];

            $OrderStatus = $OrderModel->isUpdate(true)->save($OrderData);

            if ($OrderStatus === FALSE) {
                $ProductModel->rollback();
                $BusinessModel->rollback();
                $this->error($OrderModel->getError());
                exit;
            }

            if ($BusinessStatus === FALSE || $ProductStatus === FALSE ||  $OrderStatus === FALSE) {
                $OrderModel->rollback();
                $ProductModel->rollback();
                $BusinessModel->rollback();
                $this->error('退货失败');
                exit;
            } else {
                $BusinessModel->commit();
                $ProductModel->commit();
                $OrderModel->commit();
                $this->success('退货成功', '/order/order/index');
                exit;
            }
        }
    }

    // 评价详情
    public function assesslist()
    {
        if($this->request->isAjax()){

            $orderid = $this->request->param('orderid', 0, 'trim');
            $page = $this->request->param('page', 1 , 'trim');
            
            $order = $this->OrderProductModel->where(['orderid'=>$orderid])
            ->find();

            if (!$order) {
                $this->error('订单商品不存在');
                exit;
            }
            // 显示条数
            $limit = 8;

            //偏移量
            $offset = ($page-1)*$limit;

            $assesslist = $this->OrderProductModel
                        ->with('proinfo')
                        ->where(['orderid'=>$orderid])
                        ->order('id desc')
                        ->limit($offset, $limit)
                        ->select();

            $this->success('返回评论数据', null, $assesslist);
        }
    }
}
