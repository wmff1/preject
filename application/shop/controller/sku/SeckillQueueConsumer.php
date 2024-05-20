<?php
namespace app\shop\controller\sku;

use think\Db;
use think\Cache;
use think\Controller;
use think\cache\driver\Redis;
 
class SeckillQueueConsumer
{
    public function __construct(){  
        $this->skuModel = model('Skugoods.Goods');
        $this->orderModel = model('Skugoods.Order');
        $this->redisClient = Cache::getHandler();
    }
    // 处理抢购队列
    public function consume()
    {
        $skuIds = $this->skuModel->getAllIds();
        foreach ($skuIds as $skuId) {
            $userId = $this->redisClient->handler()->rpop('seckill_queue_' . $skuId);
            if (empty($userId)) {
                continue;
            }

            $sku = $this->skuModel->getById($skuId);
            if ($sku['stock'] - $sku['sold'] <= 0) {
                continue;
            }

            $orderCount = $this->orderModel->getCountBySkuAndUser($skuId, $userId);
            if ($orderCount >= $sku['max_buy']) {
                continue;
            }

            $this->skuModel->sold($skuId);
            $this->orderModel->create($skuId, $userId);
        }
    }
}

