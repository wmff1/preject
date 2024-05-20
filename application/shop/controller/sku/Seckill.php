<?php
namespace app\shop\controller\sku;


use think\Db;
use think\Cache;
use think\Controller;
use think\cache\driver\Redis;
 
class Seckill
{

    public function __construct(){  
        $this->skuModel = model('Skugoods.Goods');
        $this->orderModel = model('Skugoods.Order');
        $this->redisClient = Cache::getHandler();
    }
    public function doSeckill()
    {
        $skuId = '1';
        $userId = mt_rand(1,2);

        // 判断商品是否已经售罄
        $sku = $this->skuModel->getById($skuId);

        if ($sku['stock'] - $sku['sold'] <= 0) {
            return '抢购已结束！';
        }

        // 判断当前用户是否已经超过限购数量
        $orderCount = $this->orderModel->getCountBySkuAndUser($skuId, $userId);

        if ($orderCount >= $sku['max_buy']) {
            return '您已经达到最大购买数量！';
        }

        // 将当前用户加入抢购队列
        $this->redisClient->handler()->lpush('seckill_queue_' . $skuId, $userId);
   
        // 如果队列中的用户数已经超过了商品库存数量，直接报抢购失败
        $queueLength = $this->redisClient->handler()->llen('seckill_queue_' . $skuId);

        if ($queueLength > $sku['stock'] - $sku['sold']) {
            $this->redisClient->handler()->lrem('seckill_queue_' . $skuId, 0, $userId);
            return '抢购失败！';
        }

        return '加入抢购队列成功，请耐心等待！';
    }
}

