<?php
header('Access-Control-Allow-Origin: *');
header('Content-type: text/plain');
error_reporting("E_NOITCE");

if (empty($_POST)) {

    exit;

}

if (!empty($_GET)) {

    exit;

}

$str_pwd = '/^[A-Za-z\d]*$/';

$ye = preg_match($str_pwd, $_POST['user'], $pwd_jie);

if ($pwd_jie == null) {

    exit;

} else {

    $user = $_POST['user'];
    $no = $_POST['no'];
    $tonk = $_POST['tonk'];
    $type = $_POST['type'];
    $openid = $_POST['openid'];

}

require_once "../../../admin/database.php";
require_once "../../../MySQLDB.class.php";
$db = new MySQLDB();

$arr = array();

$sql = "select  id from `user` where md5(sha(md5(id+'@123.\zhcx')))='" . $user . "' and key_tonk='" . $tonk . "'";

$pwds = $db->GetOneData($sql);

if (empty($pwds)) {

    exit;

}
$out_trade_no = date('YmdHis', time()) . rand(10000, 90000);
$sql = "select  p_price from `order` where no='" . $no . "'";

$total = $db->GetOneData($sql);
$sql = "update `order` set no_s='" . $out_trade_no . "' where no='" . $no . "'";

$tc = $db->exec($sql);
$total = $total * 100;

require_once "WxPay.Api.php";
require_once "WxPay.Data.php";

// 商品名称
$subject = '帕劳通';
// 订单号，示例代码使用时间值作为唯一的订单ID号
$unifiedOrder = new WxPayUnifiedOrder();
$unifiedOrder->SetBody($subject); //商品或支付单简要描述
$unifiedOrder->SetOut_trade_no($out_trade_no);
$unifiedOrder->SetTotal_fee($total);
if ($type == 'wxmini') {
    $unifiedOrder->SetTrade_type('JSAPI');
    $unifiedOrder->SetOpenid($openid);
} else {
    $unifiedOrder->SetTrade_type('APP');
}
$result = WxPayApi::unifiedOrder($unifiedOrder);
if (is_array($result)) {

    echo json_encode($result);
}
