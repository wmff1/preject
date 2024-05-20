<?php

namespace app\common\model\Business;

use think\Model;
//引入软删除
use traits\model\SoftDelete;

/**
 * 客户模型表
 */
class Business extends Model
{
    //引用软删除
    use SoftDelete;

    //数据库表名
    protected $name = 'business';

    //自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    //记录时间戳
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = 'deletetime';

    //忽略不存在的表字段
    protected $field = true;

    //追加自定义新字段属性
    protected $append = [
        //头像
        'avatar_text',
        //手机号
        'mobile_text',
        // 性别
        'gender_text',
        // 地区
        'province_text',
        'city_text',
        'district_text'

    ];

    //给追加的新字段赋值
    public function getAvatarTextAttr($value, $data)
    {

        $avatar = isset($data['avatar']) ? $data['avatar'] : '';

        if (!is_file('.' . $avatar)) {
            $avatar = '/assets/home/images/avatar.jpg';
        }

        // 获取系统配置
        $url = config('site.url') ? config('site.url') : '';

        $avatar = trim($avatar, '/');
        $avatar = $url . '/' . $avatar;

        return $avatar;
    }
    // 手机号
    public function getMobileTextAttr($value, $data)
    {
        $mobile = isset($data['mobile']) ? trim($data['mobile']) : '';

        //将手机号码中间4位换成*
        return substr_replace($mobile, '****', 3, 4);
    }

    // 性别
    public function getGenderTextAttr($value, $data)
    {
        $list = ['保密', '男', '女'];

        $gender = $data['gender'];

        return $list[$gender];
    }
    // 地区
    public function getProvinceTextAttr($value, $data)
    {
        $province = $data['province'];

        if (empty($province)) {
            return '';
        }

        return model('Region')->where(['code' => $province])->value('name');
    }

    public function getCityTextAttr($value, $data)
    {
        $province = $data['city'];

        if (empty($province)) {
            return '';
        }

        return model('Region')->where(['code' => $province])->value('name');
    }
    
    public function getDistrictTextAttr($value, $data)
    {
        $province = $data['district'];

        if (empty($province)) {
            return '';
        }

        return model('Region')->where(['code' => $province])->value('name');
    }

    //关联来源表
    public function source()
    {
        return $this->belongsTo('app\common\model\Business\Source', 'sourceid', 'id')->setEagerlyType(0);
    }

    //关联管理员表
    public function adminName()
    {
        return $this->belongsTo('app\admin\model\Admin', 'adminid', 'id')->setEagerlyType(0);
    }

    //关联地址表
    public function address()
    {
        return $this->belongsTo('app\admin\model\Business\Address', 'addressid', 'id')->setEagerlyType(0);
    }
}
