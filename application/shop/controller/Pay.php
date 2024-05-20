<?php

namespace app\shop\controller;

use think\Config;
use think\Controller;
use EasyWeChat\Factory;
use app\common\model\Order\Order;
use pay\Pay as WechatPay;
use addons\epay\library\Service;

class Pay extends Controller
{
    protected $pay;

    public function __construct(){
        $config = Config::get('wechat');
        $this->pay = new WechatPay($config); //支付组件
        // $this->getConfig = Service::getConfig(); 
    }
    public function pay(){
        $orderData = [
            'body' => '测试',
            'out_trade_no' => build_code(),
            'trade_type'   => 'NATIVE',
            'total_fee'    => 1,
            'notify_url'   => config('wechat.notify_url'),
        ];

        // 'referer'=> $_SERVER['HTTP_REFERER'],
        try {
            $order = new Order();
            $orderAddId = $order->add($orderData);
        } catch (\Exception $e) {
            $this->error('订单处理失败');
        }

        $url = $this->pay->pay($orderData);

        $res = $this->createImg($url);   

        $result = $order->where('id',$orderAddId)->whereNull('deletetime')->order('id','desc')->paginate(10);

        return '<img src="http://www.project.com'.$res.'" alt="使用微信扫描支付">';
    }
    public function notify(){
        return $this->pay->notify();
    }

    public function refund(){
        $order = new Order();
        $orderRes = $order->where('out_trade_no','202307172042471767929')->whereNull('deletetime')->find();
        $result = $this->pay->refund($orderRes);
        // if($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
        //     $orderStatus = [
        //         'status' => 5,
        //         'refund_id' => $result['refund_id'],
        //         'refund_fee' => $result['refund_fee'],
        //         'cash_fee' => $result['cash_fee']
        //     ];
        // }
        // array(18) {
        //     ["return_code"]=>
        //     string(7) "SUCCESS"
        //     ["return_msg"]=>
        //     string(2) "OK"
        //     ["appid"]=>
        //     string(18) "wx69c7b8c4501fa3f4"
        //     ["mch_id"]=>
        //     string(10) "1518358141"
        //     ["nonce_str"]=>
        //     string(16) "8QlYiTmJIzWCO4rz"
        //     ["sign"]=>
        //     string(32) "9D1AB303B0DE9203C069F627ADC47F88"
        //     ["result_code"]=>
        //     string(7) "SUCCESS"
        //     ["transaction_id"]=>
        //     string(28) "4200001945202307172208242499"
        //     ["out_trade_no"]=>
        //     string(21) "202307172042471767929"
        //     ["out_refund_no"]=>
        //     string(21) "202307172042471767929"
        //     ["refund_id"]=>
        //     string(29) "50301306652023071734048628177"
        //     ["refund_channel"]=>
        //     NULL
        //     ["refund_fee"]=>
        //     string(1) "1"
        //     ["coupon_refund_fee"]=>
        //     string(1) "0"
        //     ["total_fee"]=>
        //     string(1) "1"
        //     ["cash_fee"]=>
        //     string(1) "1"
        //     ["coupon_refund_count"]=>
        //     string(1) "0"
        //     ["cash_refund_fee"]=>
        //     string(1) "1"
        //   }
    }

    public function refNotify(){
        return $this->pay->refNotify();
    }

    // public function refund(){
    //     $order = Order::where('out_trade_no','202307171829579472222')->whereNull('deletetime')->find();
    //     $refund_no = build_code();
    //     $RefundInfo = Service::submitRefund($order["price"],$order["price"],$order["out_trade_no"],$refund_no,"wechat",$order['remark'], $this->pz['notify_url'],$this->pz["return_url"],"app");
        // ["items":protected]=>
        //   array(18) {
        //     ["return_code"]=>
        //     string(7) "SUCCESS"
        //     ["return_msg"]=>
        //     string(2) "OK"
        //     ["appid"]=>
        //     string(18) "wx69c7b8c4501fa3f4"
        //     ["mch_id"]=>
        //     string(10) "1518358141"
        //     ["nonce_str"]=>
        //     string(16) "P4PaA9WSBCFOnAhd"
        //     ["sign"]=>
        //     string(32) "13A997F74C7C6FF47642F505CC110D0F"
        //     ["result_code"]=>
        //     string(7) "SUCCESS"
        //     ["transaction_id"]=>
        //     string(28) "4200001954202307170570615552"
        //     ["out_trade_no"]=>
        //     string(21) "202307171829579472222"
        //     ["out_refund_no"]=>
        //     string(21) "202307171936024252044"
        //     ["refund_id"]=>
        //     string(29) "50300906592023071764021831089"
        //     ["refund_channel"]=>
        //     array(0) {
        //     }
        //     ["refund_fee"]=>
        //     string(1) "1"
        //     ["coupon_refund_fee"]=>
        //     string(1) "0"
        //     ["total_fee"]=>
        //     string(1) "1"
        //     ["cash_fee"]=>
        //     string(1) "1"
        //     ["coupon_refund_count"]=>
        //     string(1) "0"
        //     ["cash_refund_fee"]=>
        //     string(1) "1"
        //   }
    // }

    public function createImg($url='')
    {
        vendor('phpqrcode.phpqrcode');//引入类库tp5
        $path = $url;         //二维码内容
        $errorCorrectionLevel = 'H';  //容错级别
        $matrixPointSize = 6;      //生成图片大小
        if (!is_dir("qrcode")) {
            mkdir("qrcode");
        }
        $filename = './qrcode/' . time() . rand(10000, 9999999) . '.png';
        \QRcode::png($path, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
        return $filename = substr($filename,1);
    }

    // public function refund(){
    //     // $order = [
    //     //     'transaction_id' => '', //微信支付订单号,微信生成的订单号，在支付通知中有返回
    //     //     // 'out_trade_no' => time().mt_rand(1000,9999),
    //     //     'out_trade_no' => build_code(),
    //     //     'total_fee' => '1',
    //     //     'trade_type' => 'NATIVE ',
    //     //     'notify_url' => config('wechat.notify_url')
    //     // ];
    //     $order = [
    //         'amount' => '1',
    //         'refund_money' => '1',
    //         'orderid' => '202307170019089023838',
    //         'refund_sn' => build_code(),
    //         'type' => 'wechat',
    //         'remark' => '测试订单',
    //         'notifyurl' => $this->pz['notify_url'],
    //         'returnurl' => $this->pz["return_url"],
    //         'method' => 'app',
    //     ];
    //     // $RefundInfo = Service::submitRefund($order["amount"],$order["amount"],$order["code"],$refund_no,"wechat",$remark, $this->config['notify_url'],$this->config["return_url"],"app");
    //     $RefundInfo = Service::submitRefund($order["amount"],$order["refund_money"],$order["orderid"],$order['refund_sn'],"wechat",$order['remark'], $order['notifyurl'],$order['returnurl'],"app");
    // }

    public function pay1(){
        // $orderSn = $this->request->param('codeOrderSn', 0, 'trim');
        $orderSn = build_code();

        Vendor('WxpayV3.WxPayPubHelper');
        $notify = new \NativePay();
        $input = new \WxPayUnifiedOrder();
        $input->SetBody('测试');
        $input->SetAttach('测试订单');
        $input->SetOut_trade_no($orderSn);
        $input->SetTotal_fee('1'); // 价格 1 分
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 3600));
        $input->SetGoods_tag("QRCode"); // 商品标记
        $input->SetNotify_url("http://www.project.com/shop/pay/notify1");//线上回调地址
        $input->SetTrade_type("NATIVE");
        $input->SetProduct_id('23'); //商品id
        $result = $notify->GetPayUrl($input);
        if (empty($result['code_url'])) {
            $url = '';
        }else{
            $url = $result["code_url"];
            $res = $this->createImg($url);
        }
        // 生成二维码
        return '<img alt="扫码支付" src="http://www.project.com'.$res.'" style="width:300px;height:300px;"/>';
    }
    
    public function notify1(){
        Vendor('WxpayV3.WxPayPubHelper');
        $weixinData = file_get_contents("php://input");
        // file_put_contents('/tmp/2.txt',$weixinData, FILE_APPEND);
        try {
            $resultObj = new \WxPayResults();
            var_dump($resultObj);
            $weixinData = $resultObj->init($weixinData);
        } catch (\Exception $e) {
            $resultObj->SetData('return_code','FAIL');
            $resultObj->SetData('return_msg',$e->getMessage());
            return $resultObj->ToXml();
        }
        if($weixinData['return_code'] === 'FAIL' || $weixinData['return_code'] !== 'SUCCESS'){
            $resultObj->SetData('return_code','FAIL');
            $resultObj->SetData('return_msg','ERROR');
            return $resultObj->ToXml();
        }
        // 根据out_trade_to来查询订单数据
        $outTradeNo = $weixinData['out_trade_no'];
        $order = model('order.Order')->get(['out_trade_no' => $outTradeNo]);
        if (!$order || $order->pay_status == 1) {
            $resultObj->SetData('return_code','SUCCESS');
            $resultObj->SetData('return_msg','OK');
            return $resultObj->ToXml();
        }
        //更新订单表 商品表
        try {
            // $orderRes = model('order.Order')->updateOrderByOutTradeNo($outTradeNo, $weixinData);
            // $dealRes = model('Deal')->updateBuyCountById($order->deal_id,$order->deal_count);
            // 消费卷生成
            $coupons = [
                'sn' => $outTradeNo,
                'password' => rand(10000,99999),
                'user_id' => $order->user_id,
                'deal_id' => $order->deal_id,
                'order_id' => $order->id
            ];
            var_dump($coupons);
            // model('order.Coupons')->add($coupons);
            // 发送邮箱给 用户
        } catch (\Exception $e) {
            // 说明 没有更新 告诉微信 服务器 我们还需要 回调
            return false;
        }
        $resultObj->SetData('return_code','SUCCESS');
        $resultObj->SetData('return_msg','OK');
        return $resultObj->ToXml();
    }

    // public function FromXml($xml){
    //     if(!$xml){
    //         echo "xml数据异常！";
    //     }
    //     //将XML转为array
    //     //禁止引用外部xml实体
    //     libxml_disable_entity_loader(true);
    //     $data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
    //     return $data;
    // }
}