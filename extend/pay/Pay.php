<?php
namespace pay;

use EasyWeChat\Factory;
use app\common\model\Order\Order;
// use think\Facade\Log;
class Pay {

    protected $config;
    protected $pay;
    protected $order;

    public function __construct($config){
        $this->config = $config;
        $this->pay = Factory::payment($config); //支付组件
    }
    public function pay($order){
        $result = $this->pay->order->unify($order); //调用微信下单接口,返回支付链接
        /*"appid":"wx0773e6333dcb346d",
            "bank_type":"OTHERS",
            "cash_fee":"1",
            "fee_type":"CNY",
            "is_subscribe":"N",
            "mch_id":"1281732101",
            "nonce_str":"64b00f1910727",
            "openid":"ok5q-4q4_cppMewefUyTeps_X4DQ",
            "out_trade_no":"16892598012264",
            "result_code":"SUCCESS",
            "return_code":"SUCCESS",
            "sign":"F24C6C222523B9BE6DD4D4091F606711",
            "time_end":"20230713225037",
            "total_fee":"1",
            "trade_type":"NATIVE",
            "transaction_id":"4200001861202307130043237152"}*/
     
        if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
            $code_url = $result['code_url'];
            return $code_url;
        }
        return [];
    }

    public function notify(){
        // $message = [
        //     "appid"        => "wx0773e6333dcb346d",
        //     "bank_type"    => "OTHERS",
        //     "cash_fee"     => "1",
        //     "fee_type"     => "CNY",
        //     "is_subscribe" => "N",
        //     "mch_id"       => "1281732101",
        //     "nonce_str"    => "64b00f1910727",
        //     "openid"       => "ok5q-4q4_cppMewefUyTeps_X4DQ",
        //     "out_trade_no" => "202307151527099951527",
        //     "result_code"  => "SUCCESS",
        //     "return_code"  => "SUCCESS",
        //     "sign"         => "F24C6C222523B9BE6DD4D4091F606711",
        //     "time_end"     => "20230713225037",
        //     "total_fee"    => "1",
        //     "trade_type"   => "NATIVE",
        //     "transaction_id" => "4200001861202307130043237152"
        // ];
        $response = $this->pay->handlePaidNotify(function ($message, $fail) {
            \think\Log::write(json_encode($message));
            $order = new Order();
            $orderId = $order->getOrderId($message['out_trade_no']);
            if($message['return_code'] == 'SUCCESS' && $message['message_code'] == 'SUCCESS'){
                $orderData = [
                    'id' => $orderId,
                    'fee_type'     => $message['fee_type'],
                    'status'       => '1',
                    'pay_status'       => '1',
                    'transaction_id' => $message['transaction_id']
                ];
                try {
                    $order->isUpdate(true)->save($orderData);
                } catch (\Exception $e) {
                    $e->getMessage();
                }


                //积分增加
                //订单状态
                //优惠卷
                return true;
            }else{
                // 或者错误消息
                $fail('Order not exists.');
            }
        });
        return $response->send(); //xml格式，停止一直抛送
    }

    public function refund($order){
        return $this->pay->refund->byTransactionId($order['transaction_id'],$order['out_trade_no'],$order['total_fee'],$order['total_fee'], $config = [
            'notify_url' => 'http://www.project.com/wmfproject/public/index.php/shop/pay/refNotify'
        ]);
    }

    public function refNotify(){
        $response = $this->pay->handleRefundedNotify(function ($message, $reqInfo, $fail) {
            // 其中 $message['req_info'] 获取到的是加密信息
            // $reqInfo 为 message['req_info'] 解密后的信息
            \think\Log::write(json_encode($message));
            // "return_code":"SUCCESS","appid":"wx69c7b8c4501fa3f4","mch_id":"1518358141","nonce_str":"45097aa3f41640488294143e24f8a8c6","req_info":"VqpWqJYTVfsU+PS9qrGWzkyaRkKZKoEMxyAhcgEQP7kGpqGx+f8eyha23Zx4Ak74AEus5vi56CvExIC7bODBbfJtbg1j+c8QyTZixkr2rlIwgbtADasmOIWcVI0djIpOUl9hhIgkxR4P++UX2po9uuIULg+00Tmn\/e5kJR4c\/TvZm0Pg0sW8Ia4RkNTBIK0NMIG7QA2rJjiFnFSNHYyKTig5s0z4uOVaKBKtmtFsmTkbDnfxWiDctCCCxqYrBQeaQ67sq+B27mo6hmlrMWrA0d124VO8stupyX9zJMKjimvtyLnyy\/3jFLUbPetM2blaQbghowkL40W1S3ogyYl2WXFHrh\/QOo9tUAaA5ozZ+5AdrTnzXMpkbpejd9plAjP7SaqfviGS1FvWflTOIyVZpFKBxPnR8ArNrlMUqConRvrl2tToUNcXyB6M8NaLYC7I+Cr91L8BGpS20pt68rm1mz7prqHtdGreCyUtzWqWDCHVKCSFeDYFY8w4PXt9u8cVoz8zNGHFfGig7RY5kDAgGl\/FhBBVKU3mZSBjIKwONkNFCaEBA5UidTTisnDs8dyySvBjyWKBsotZL16KZQIo7GL9HiyE1CPOnB+kwJpr6I6nubqSPX6CrhfyEQSYzsKkQ\/mfFQL\/LT27Yy2Hcm1ZxMOok\/u3gcQdheHW5IvrzZZaIahtcPwZxyQuymW5EpDftX9djfdd\/54cJInx3nF3Sl2dYJ2lbgFYUYBLSqC1\/JLQfB8hhVPYaMXGjBmyuS6mIF1XsP96mbUUbMd50+1qlePa0APY4zzV1xqVGhjFTlJbYsX0zUMKep511CqP6LffsvZyzfQhE0xrFfEIwcckJ\/9M7aXGbuvfRogUmbfLN7H21CmETcyHL0GcR5bvUbsXiUSFTMxYpbyT+UThuSr4QuEQaBQusbzxQ5SggvSGH\/hmUt5yvJnRoBXlG9Gj9PnTSwvrxAkKmV+WYWZkoWlLNth61y6iCNgsagrEs2DaxBTk1Hs1qCnLaU0FA+PJWM\/CUtZ55YdwSvPnX5XnmM9k5qF8Fw7sTfCOammBQeygTgiAqIS9sN8lsDTjUnY2F66GOfqgHdMG\/6CEitCYdKKOzHNMXMz5P7roEOck4SyiX2o="
            \think\Log::write(json_encode($reqInfo));
            /*"cash_refund_fee":"1",
            "out_refund_no":"202307181535351608535",
            "out_trade_no":"202307181535351608535",
            "refund_account":"REFUND_SOURCE_RECHARGE_FUNDS",
            "refund_fee":"1",
            "refund_id":"50300006562023071808723393314",
            "refund_recv_accout":"\u652f\u4ed8\u7528\u6237\u96f6\u94b1",
            "refund_request_source":"API",
            "refund_status":"SUCCESS",
            "settlement_refund_fee":"1",
            "settlement_total_fee":"1",
            "success_time":"2023-07-18 15:41:01",
            "total_fee":"1",
            "transaction_id":"4200001956202307189590783201"*/
            $order = new Order();
            $orderId = $order->getOrderId($reqInfo['out_trade_no']);
            if($reqInfo['refund_status'] == 'SUCCESS') {
                $orderStatus = [
                    'id' => $orderId,
                    'status' => 5,
                    'refund_id' => $reqInfo['refund_id'],
                    'refund_fee' => $reqInfo['refund_fee'],
                    'cash_refund_fee' => $reqInfo['cash_refund_fee']
                ];

                try {
                    $order->isUpdate(true)->save($orderStatus);
                } catch (\Exception $e) {
                    $e->getMessage();
                }

                return true; // 返回 true 告诉微信“我已处理完成”
            }else {
                $fail('退款失败');    // 或返回错误原因 $fail('参数格式校验错误');
            }
        });
        return $response->send();
    }
    // public function refund($transactionId,$refundNumber,$totalFee,$refundFee,$config=[]){
        // return $this->pay->refund->byTransactionId($transactionId, $refundNumber, $totalFee, $refundFee, $config = [
            // 'out_refund_no' => '202307171502006915544',
        // ]);
        // Example:
        // $result = $this->pay->refund->byTransactionId('transaction-id-xxx', 'refund-no-xxx', 10000, 10000, [
        //     // 可在此处传入其他参数，详细参数见微信支付文档
        //     // 'refund_desc' => '商品已售完',
        // ]);
    // }
}
