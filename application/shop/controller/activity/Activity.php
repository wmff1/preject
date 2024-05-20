<?php

namespace app\shop\controller\activity;

use think\Cache;
use think\Config;
use think\Controller;
use app\common\model\Order\Order;
use think\cache\Driver;
use think\cache\driver\Redis;
use app\common\model\Product\Product;
use think\Db;
// use think\facade\Cache;


class Activity extends Controller
{
    public function __construct(){
        $this->redis = Cache::getHandler();
        $this->goods = new Product();
    }
    public function index1(){
        // $this->redis->handler()->hset('user', 'name', 'John');
        // $this->redis->handler()->hset('user', 'age', 20);
        // $user = $this->redis->handler()->hGetAll('user');
        // var_dump($user);

        // $this->redis->handler()->rPush('orderSn', '123');
        // $this->redis->handler()->rPush('orderSn', '456');
        $user = $this->redis->handler()->lRange('orderSn',0,-1);
        return json($user);
    }

    public function index()
    {
        $order_sn = build_code();
        // 获取当前用户的ID，这里假设为用户1
        // $userId = mt_rand(34,40);
        $userId = 34;

        // 获取商品ID，这里假设为商品1
        $goodsId = 26;

        // 获取商品库存
        $stock = $this->getGoodsStock($goodsId);

        // 判断库存是否大于0
        if ($stock > 0) {
            // 使用Redis的原子减操作减少库存
            // 构造商品库存键名
            $stockKey = 'goods_stock:' . $goodsId;
            $this->redis->handler()->watch($stockKey); //true
            $this->redis->handler()->multi();
            $this->redis->handler()->decr($stockKey);
            $execResult = $this->redis->handler()->exec();


            // 判断原子减操作是否成功
            if ($execResult === false) {
                $this->error('秒杀失败，请重试');
            } else {
                // 执行购买商品的业务逻辑
                $this->buyGoods($userId, $goodsId, $order_sn);
                $this->success('秒杀成功');
            }
        } else {
            $this->error('商品库存不足');
        }
    }

    // 获取商品库存
    protected function getGoodsStock($goodsId)
    { 
        // 这里使用自定义函数或Model层代码获取商品库存
        // 假设自定义函数名为getGoodsStockByGoodsId，你可以自行修改该函数名
        $stock = $this->goods->getGoodsStockByGoodsId($goodsId);
        return $stock;
    }

    // 购买商品的业务逻辑
    protected function buyGoods($userId, $goodsId, $order_sn)
    {
        // 这里编写购买商品的业务逻辑
        // 假设业务逻辑是将购买记录存入数据库
        // 你可以根据自己的实际需求修改该方法
        $data = [
            'code' => $order_sn,
            'busid' => $userId,
            'product_id' => $goodsId,
            'createtime' => time(),
        ];
        Db::name('order')->insert($data);
    }
}