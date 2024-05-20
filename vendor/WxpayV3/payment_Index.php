<?php
header('Access-Control-Allow-Origin: *');
header('Content-type: text/plain');
error_reporting("E_NOITCE");
$posts=$GLOBALS['HTTP_RAW_POST_DATA'];
$post=json_decode($posts);

$user=$post->user;
$user_key=$post->user_key;
$yhpz=$post->yhpz;
$noarr=$post->noarr;//付款订单
$type=$post->type;//区分app与jssdk
$openid=$post->openid;//用户id
$moneys = $post->money;

foreach($noarr as $value){//检查订单
	$str_pwd='/^[A-Za-z\d]*$/';
	
	
	
	  $ye=preg_match($str_pwd,$value,$pwd_jie);
	
	if( $pwd_jie==null ){
	
	           
	
	             exit;
	
	
	
	}
}

require_once "../../../admin/database.php";
require_once "../../../MySQLDB.class.php";
$arr=array();
$db=new MySQLDB();

$sql="select  id from `user` where md5(CONCAT(id,'ywi_++d5e4GG45)(4**56'))='".$user."' and user_key='".$user_key."' and yhpz='".$yhpz."'"; 
$users=$db->GetOneData($sql);
if(empty($users)){
$sql="select  login_message from `user` where md5(CONCAT(id,'ywi_++d5e4GG45)(4**56'))='".$user."'";
$user_name=$db->GetOneData($sql);
if(empty($user_name)){
  $user_name='你的账号于'.date("Y.m.d H:i:s",(time()-3600*24)).' 在另外一台 <span style="color:red;">其他</span> 手机登录, 如非本人操作,请及时修改密码';
}
       $arr['id']=1;
       $arr['name']=$user_name;
       echo json_encode($arr);
       exit;
}
$money=0;
$ems_fee=0;
foreach($noarr as $value){
		$sql="select * from `order` where no='".$value."' and o_uid='".$users."'";
		$orderdata=$db->GetRows($sql);
		
		if(!empty($orderdata)){
				foreach($orderdata as $values){
					if($ems_fee==0){
						$ems_fee=($values['ems_fee'])*100;
					}
					$money=$money+(($values['p_price']*100)-(($values['cash']*100)+($values['score']*100)+($values['coupon_money']*100)+($values['ucoupon_money']*100)));
				}
		}else{
			$arr['id']=3;
			echo json_encode($arr);
			exit; 
		}
}
$total=$money+$ems_fee;
//var_dump($total); exit;
$out_trade_no = implode('-',$noarr);
$nostring = implode(',',$noarr);
$sql="update `order` set payno='".$out_trade_no."' where no in (".$nostring.") and o_uid='".$users."'";
$db->exec($sql);

require_once "WxPay.Api.php";
require_once "WxPay.Data.php";

// 商品名称
$subject = '创新生活馆';
// 订单号，示例代码使用时间值作为唯一的订单ID号

$unifiedOrder = new WxPayUnifiedOrder();
$unifiedOrder->SetBody($subject);//商品或支付单简要描述
$unifiedOrder->SetOut_trade_no($out_trade_no);
$unifiedOrder->SetTotal_fee($total);

if($type=='wxmini'){//微信小程序支付参数
$unifiedOrder->SetTrade_type('JSAPI');
$unifiedOrder->SetOpenid($openid); 
}else{//app支付参数
$unifiedOrder->SetTrade_type('APP'); 
}

$result = WxPayApi::unifiedOrder($unifiedOrder,$type);

if (is_array($result)) {
foreach ($result as $key => $value) {
	$arr[$key]=$value;
}
$arr['IMG']=$out_trade_no;
    echo json_encode($arr);
}

?>