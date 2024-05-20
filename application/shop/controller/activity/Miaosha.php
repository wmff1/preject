<?php
namespace app\shop\controller\activity;

use think\Db;
use think\Cache;
use think\Controller;
use think\cache\driver\Redis;
 
class Miaosha extends Controller
{
    /*秒杀功能大致思路：获取缓存列表的长度，如果长度（llen）等于0，就停止秒杀，即秒杀失败，如果长度大于0，
    则继续运行，先从缓存中移除一个元素（lpop）,再进行数据库操作（添加订单表，商品库存数量减一），
    如果再进一个人秒杀，就再走一遍流程，循环往复*/
 
    private $redis = null;
    private $cachekey = null;    //缓存变量名
    private $basket = [];        //私有数组，存放商品信息
    private $store = 50;
 
    public function __construct()
    {
        parent::__construct();
        $this->redis = Cache::getHandler();
    }
 
    /**
     * 秒杀初始化
     */
    public function init()
    {
        $this->cachekey = 'ms';
        // 删除缓存列表
        $this->redis->handler()->del($this->cachekey);
        $len = $this->redis->handler()->llen($this->cachekey);
        $count = $this->store - $len;
 
        for ($i=0; $i < $count; $i++) { 
            // 向库存列表推进50个,模拟50个商品库存
            $this->redis->handler()->lpush($this->cachekey,1);
        }
        echo "库存初始化完成:".$this->redis->handler()->llen($this->cachekey);
    }
 
    public function index()
    {
        $goods_id = 2;    //商品编号
        
        if (empty($goods_id)) {
            // 记录失败日志
            return $this->writeLog(0,'商品编号不存在');    
        }
 
        // 计算库存列表长度
        $count = $this->redis->handler()->llen($this->cachekey);

        // 先判断库存是否为0,为0秒杀失败,不为0,则进行先移除一个元素,再进行数据库操作
        if ($count == 0) {    //库存为0
            $this->writeLog(0,'库存为0');
            return "库存为0";
        }else{
            // 有库存
            //先移除一个列表元素
            $this->redis->handler()->lpop($this->cachekey);
 
            $ordersn = $this->build_order_no();    //生成订单
            $uid = rand(1,2);    //随机生成用户id
            $status = 1;
            // 再进行数据库操作
            $data = Db::table('pro_sku_goods')->field('count,amount')->where('id',$goods_id)->find();    //查找商品
 
            if (!$data) {
                return $this->writeLog(0,'该商品不存在');
            }
 
            $insert_data = [
                'order_sn' => $ordersn,
                'user_id' => $uid,
                'goods_id' => $goods_id,
                'price'    => $data['amount'],
                'status' => $status,
                'addtime' => date('Y-m-d H:i:s')
            ];
 
            // 订单入库
            $result = Db::table('pro_sku_order')->insert($insert_data);
            // 自动减少一个库存
            $res = Db::table('pro_sku_goods')->where('id',$goods_id)->setDec('count');
 
            if ($res) {
                echo "第".$count."件秒杀成功";
                $this->writeLog(1,'秒杀成功');
            }else{
                echo "第".$count."件秒杀失败";
                $this->writeLog(0,'秒杀失败');
            }
        }
    }
 
    /**
     * 生成订单号
     */
    public function build_order_no()
    {
        return date('ymd').substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }
 
    /**
     * 生成日志  1成功 0失败
     */
    public function writeLog($status = 1,$msg)
    {
        $data['count'] = 1;
        $data['status'] = $status;
        $data['addtime'] = date('Y-m-d H:i:s');
        $data['msg'] = $msg;
        return Db::table('pro_sku_log')->insertGetId($data);
    }
 
}

