<?php

namespace app\common\model\Subject;

use think\Model;
//引入软删除
use traits\model\SoftDelete;


/* 课程模型 */

class Subject extends Model
{
    //引用软删除
    use SoftDelete;
    //数据库表名
    protected $name = 'subject';

    //自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    //记录时间戳
    protected $createTime = 'createtime';
    protected $updateTime = false;

    //软删除字段
    protected $deleteTime = 'deletetime';

    //忽略不存在的表字段
    protected $field = true;

    //追加自定义字段属性 别名
    protected $append = [
        //课程图片
        'thumbs_text',
        //点赞
        'likes_text'
    ];

    //给追加的新字段赋值
    public function getThumbsTextAttr($value, $data)
    {
        $thumbs = isset($data['thumbs']) ? $data['thumbs'] : '';

        //路径判断 要用相对路径   ./  
        if (!is_file("." . $thumbs)) {
            //给个默认图
            $thumbs = '/assets/home/images/subject.jpg';
        }

        return $thumbs;
    }

    public function getLikesTextAttr($value, $data)
    {
        //获取出likes字段值
        $likes = trim($data['likes']);

        //字符串转换为数组
        $likes = explode(',', $likes);

        //去除空元素
        $likes = array_filter($likes);

        //返回总个数
        return count($likes);
    }
    
    //关联课程分类表
    public function category()
    {
        return $this->belongsTo('app\common\model\Subject\Category', 'cateid', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
