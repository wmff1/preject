<?php

namespace app\common\model\Product;

use think\Model;


class Type extends Model
{
    // 表名
    protected $name = 'product_type';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'thumb_text'
    ];

    /**
     * 缓存递归获取子分类 id
     */
    public static function getCategoryIds($id)
    {
        // 判断缓存
        $cacheKey = 'category-' . $id . '-child-ids';
        $categoryIds = cache($cacheKey);

        if (!$categoryIds) {
            $categoryIds = self::recursionGetCategoryIds($id);
            
            // 缓存暂时注释，如果需要，可以打开，请注意后台更新分类记得清除缓存
            // cache($cacheKey, $categoryIds, (600 + mt_rand(0, 300)));     // 加入随机秒数，防止一起全部过期
        }
        return $categoryIds;
    }

    /**
     * 递归获取子分类 id
     */
    public static function recursionGetCategoryIds($id) {
        $ids = [];
        $category_ids = self::where(['pid' => $id])->column('id');
        if ($category_ids) {
            foreach ($category_ids as $k => $v) {
                $childrenCategoryIds = self::recursionGetCategoryIds($v);
                if ($childrenCategoryIds && count($childrenCategoryIds) > 0) $ids = array_merge($ids, $childrenCategoryIds);
            }
        }
        return array_merge($ids, [intval($id)]);
    }

    public function getThumbTextAttr($value, $data)
    {

        $thumb = isset($data['thumb']) ? $data['thumb'] : '';

        //路径判断 要用相对路径
        if(!is_file(".".$thumb))
        {
            //给个默认图
           $thumb = '/assets/home/images/thumb.jpg'; 
        }

        //获取系统配置里面的选项
        $url = config('site.url') ? config('site.url') : '';

        //拼上域名信息
        $thumb = trim($thumb, '/');

        $thumb = $url.'/'.$thumb;
   
        return $thumb;
    }

    public function children () 
    {
        return $this->hasMany(\app\common\model\Product\Type::class, 'pid', 'id')->order('weight desc, id asc');
    }
}
