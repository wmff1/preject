<?php

namespace app\common\model\Product;

use think\Model;
//引入软删除
use traits\model\SoftDelete;
use think\Db;

class Product extends Model
{
    //引用软删除
    use SoftDelete;
    
    // 表名
    protected $name = 'product';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    //软删除字段
    protected $deleteTime = 'deletetime';
    protected $hidden = ['createtime', 'status'];
    public static $list_hidden = ['content', 'thumbs'];
    protected $goodsId = null;

    public function getGoodsStockByGoodsId($goodsId){
        $product = $this->where('id',$goodsId)->whereNull('deletetime')->paginate();
        foreach ($product as &$row) {
            return $row['stock'];
        }
    }

    // 追加属性
    protected $append = [
        'flag_text',
        'thumbs_text',
        'status_text',
    ];

    public function getFlagList()
    {
        return ['0' => __('下架'), '1' => __('上架')];
    }

    public function getTypeList()
    {
        return ['normal' => __('Type normal'), 'virtual' => __('Type virtual')];
    }

    public function getStatusList()
    {
        return ['up' => __('Status up'), 'hidden' => __('Status hidden'), 'down' => __('Status down')];
    }

    public function getDispatchTypeList()
    {
        return ['express' => __('Dispatch_type express'), 'selfetch' => __('Dispatch_type selfetch'), 'store' => __('Dispatch_type store'), 'autosend' => __('Dispatch_type autosend')];
    }

    public function getFlagTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['flag']) ? $data['flag'] : '');
        $list = $this->getFlagList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getThumbsTextAttr($value, $data)
    {
        $thumbs = isset($data['thumbs']) ? $data['thumbs'] : '';

        //路径判断 要用相对路径
        if(!is_file(".".$thumbs))
        {
            //给个默认图
           $thumbs = '/assets/home/images/thumb.jpg'; 
        }

        //获取系统配置里面的选项
        $url = config('site.url') ? config('site.url') : '';

        //拼上域名信息
        $thumbs = trim($thumbs, '/');
        $thumbs = $url.'/'.$thumbs;

        return $thumbs;
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');

        $list = [ '1' => __('普通商品'), '2' => __('租赁商品')];
        return isset($list[$value]) ? $list[$value] : '';
    }

    // 时间修改器
    public function setCreatetimeAttr($value,$data)
    {
        return strtotime($value);
    }

    // 分类关联查询
    public function type()
    {
        return $this->belongsTo('app\common\model\Product\Type','typeid','id',[],'LEFT')->setEagerlyType(0);
    }

    // 单位关联查询
    public function unit()
    {
        return $this->belongsTo('app\common\model\Product\Unit','unitid','id',[],'LEFT')->setEagerlyType(0);
    }

    public static function getGoodsList($params, $is_page = true)
    { 
        $is_arrival = 0;//是否查询到货
        extract($params); // 2
        /*
            $goods_ids => '45',
            $per_page => '9999999'
        */
        $type = $type??''; 

        $where = [
            'status' => ['in', ((isset($type) && $type == 'all') ? ['up', 'hidden'] : ['up'])],     // type = all 查询全部
            // 'is_copy'=>0 TODO
        ];

        //排序字段
        if (isset($order)) {
            $order = self::getGoodsListOrder($order);
        }else{
            $order = 'weigh desc';
        }

        if (isset($goods_ids) && $goods_ids !== '') {
            $order = 'field(id, ' . $goods_ids . ')';       // 如果传了 goods_ids 就按照里面的 id 进行排序 结果：field(id, 37)
            $goodsIdsArray = explode(',', $goods_ids);   // 0 => string '37'
            $where['id'] = ['in', $goodsIdsArray];
        }
        if($is_arrival == 1){
            $where['arrival_date'] = ['>', date("Y-m-d H:i:s")];
        }else{
            $where['arrival_date'] = ['<=', date("Y-m-d H:i:s")];
        }   

        $category_ids = [];
        if (isset($category_id) && $category_id != '') {
            // 查询分类所有子分类,包括自己
            $category_ids = Type::getCategoryIds($category_id);
        }

        $goods = (new Product())->where($where)->where(function ($query) use ($category_ids) {
                foreach($category_ids as $key => $category_id) {
                    $query->whereOrRaw("find_in_set($category_id, typeid)");
                }
            });
            // echo $goods->getLastSql();exit;
        // var_dump($goods);exit;
        $goods = $goods->field('*,(sales + show_sales) as total_sales')->order('id desc'); //销售和显示销售

        if(isset($limit) && $limit != ''){
            $goods = $goods->limit($limit);
        }

        if ($is_page) {
            $goods = $goods->paginate($per_page ?? 10);
            $goodsData = $goods->items();
        } else {
            $goods = $goodsData = $goods->select();
        }
        $data = [];
        if ($goodsData) {
            $collection = collection($goodsData);
            $data = $collection->hidden(self::$list_hidden);
            // 处理活动
            // $data->load('skuPrice');        // 延迟预加载

            // foreach ($data as $key => $g) {
            //     $data[$key] = self::operActivity($g, $g['sku_price'],$type);
            // }
        }

        if ($is_page) {
            $goods->data = $data;
        } else {
            $goods = $data;
        }
        return $goods;
    }

    private static function getGoodsListOrder($orderStr)
    {
        $order = 'weigh desc';
        $orderList = json_decode(htmlspecialchars_decode($orderStr), true);
        extract($orderList);
        if (isset($defaultOrder) && $defaultOrder === 1) {
            $order = 'weigh desc';
        }
        if (isset($priceOrder) && $priceOrder === 1) {
            $order = "convert(`price`, DECIMAL(10, 2)) asc";
        }elseif (isset($priceOrder) && $priceOrder === 2) {
            $order = "convert(`price`, DECIMAL(10, 2)) desc";
        }
        if (isset($salesOrder) && $salesOrder === 1){
            $order = 'total_sales desc';
        }
        if (isset($newProdcutOrder) && $newProdcutOrder === 1){
            $order = 'id desc';
        }
        return $order;
    }
}
