<?php

namespace app\common\model\Admin;

use think\Model;

/**
 * 管理员模型
 */
class Admin extends Model
{
    // 表名
    protected $name = 'admin';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名 在插入语句的时候 会自动写入时间戳
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 忽略数据表不存在的字段
    protected $field = true;

    protected $append = [
        'avatar_text',
        // 分组
        'group_text',

        'createtime_text'
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
    public function getGroupTextAttr($value, $data)
    {
        $AuthGroupModel = model('Admin.AuthGroup');
        $AuthGroupAccessModel = model('Admin.AuthGroupAccess');
        // 从分组权限表中通过管理员id获取group_id
        $gid = $AuthGroupAccessModel->where(['uid' => $data['id']])->value('group_id');
        if(!$gid){
            return '暂无角色组';
        }
        // 从分组表中通过角色组的gid获取角色组名字
        $name = $AuthGroupModel->where(['id' => $gid])->value('name');
        if(!$name){
            return '暂无角色组名称';
        }
        return $name;
    }
    public function getCreatetimeTextAttr($value, $data)
    {
        $createtime = isset($data['createtime']) ? isset($data['createtime']) : 0;

        if($createtime){
            return date('Y-m-d H:i', $createtime);
        }else {
            return date('Y-m-d H:i', time());
        }
    }
}
