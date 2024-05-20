<?php
//error_reporting("E_EOTICE");
$out_trade_no = $_POST['out_trade_no'];
$refund_fee = intval($_POST['refund_fee']);
$str_pwd = '/^[A-Za-z\d]*$/';



$ye = preg_match($str_pwd, $value, $pwd_jie);

if ($pwd_jie == null) {



	exit;

}



require_once "../../../admin/database.php";
require_once "../../../MySQLDB.class.php";
$arr = array();
$db = new MySQLDB();
// session_start();
// $sql="select pwd from admin where id=".$_SESSION['uid'];
//  $pwd=$db->GetOneData($sql);
// if(md5($_POST['ad_pwd'])!=$pwd   || empty($_POST['ad_pwd'])){
// echo 1;exit;
// }


$money = 0;
$ems_fee = 0;

$sql = "select * from `order` where no='" . $out_trade_no . "'";
$orderdata = $db->GetRows($sql);

if (!empty($orderdata)) {
	foreach ($orderdata as $values) {
		if ($ems_fee == 0) {
			$ems_fee = ($values['ems_fee']) * 100;
		}
		$money = $money + (($values['p_price'] * 100) - (($values['cash'] * 100) + ($values['score'] * 100) + ($values['coupon_money'] * 100) + ($values['ucoupon_money'] * 100)));
	}
} else {
	$arr['id'] = 3;
	echo json_encode($arr);
	exit;
}

$total_fee = intval($money + $ems_fee);

require_once "WxPay.Api.php";
require_once "WxPay.Data.php";




// 商品名称
$subject = '赋康医药';
// 订单号，示例代码使用时间值作为唯一的订单ID号

$unifiedOrder = new WxPayRefund();


$unifiedOrder->SetOut_trade_no($out_trade_no); //订单号
$unifiedOrder->SetOut_refund_no($out_trade_no); //退款订单号
$unifiedOrder->SetTotal_fee($total_fee); //订单总金额
$unifiedOrder->SetRefund_fee($refund_fee); //退款金额
$unifiedOrder->SetOp_user_id(WxPayConfig::MCHID); //操作id


$result = WxPayApi::refund($unifiedOrder);
if ($result['result_code'] == 'SUCCESS') {
	echo 2;
} else {
	echo 1;
}

?>