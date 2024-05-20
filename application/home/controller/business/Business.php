<?php

namespace app\home\controller\business;

use app\common\controller\Home;

/**
 * 用户中心控制器
 */
class Business extends Home
{
    public function __construct()
    {
        //继承父类
        parent::__construct();

        //加载全局模型
        $this->BusinessModel = model('Business.Business');
        $this->RegionModel = model('Region');
    }

    /** 
     * 用户中心
     */
    public function index()
    {
        //渲染模板
        return $this->view->fetch();
    }

    /* 
        用户资料
    */
    public function profile()
    {
        //判断是否有post提交
        if ($this->request->isPost()) {
            //获取提交的值
            $nickname = $this->request->param('nickname', '', 'trim');
            $password = $this->request->param('password', '', 'trim');
            $gender = $this->request->param('gender', '', 'trim');
            $email = $this->request->param('email','','trim');
            $region = $this->request->param('region', '', 'trim');

           
            // 组装数据
            $data = [
                'id' => $this->LoginAuth['id'],
                'nickname' => $nickname,
                'gender' => $gender,
                'email' => $email
            ];
           
            //判断密码是否为空
            if (!empty($password)) {

                //获取数据的密码盐
                $newsalt = $this->LoginAuth['salt'];
                //新输入的密码加密
                $repass = md5($password . $newsalt);
                //判断修改的密码是否与原密码相等
                if ($repass == $this->LoginAuth['password']) {
                    $this->error('新密码不能等于当前密码');
                    exit;
                }

                //重新生成密码盐
                $salt = build_ranstr();
                $data['salt'] = $salt;
                $data['password'] = md5($password . $salt);
            }
            //判断地区是否为空
            if (!empty($region)) {
                //字符串转换为数组
                $region = explode('/', $region);
                // 获取数组最后一个值
                $last = array_pop($region);
                //查找行政编码
                $pathcode = $this->RegionModel->where(['name' => $last])->value('parentpath');
                //将字符串转换为数组
                $region = explode(',', $pathcode);

                $data['province'] = isset($region[0]) ? $region[0] : '';
                $data['city'] = isset($region[1]) ? $region[1] : '';
                $data['district'] = isset($region[2]) ? $region[2] : '';
            }
           
            //判断是否有文件上传
            if (isset($_FILES['avatar']) && $_FILES['avatar']['size']) {

                //调用公共方法
                $success = build_upload('avatar');

                if ($success['result']) {
                    //上传成功
                    $data['avatar'] = $success['data'];
                } else {
                    //上传失败
                    $this->error($success['msg']);
                    exit;
                }
            }

            //准备修改数据
            $result = $this->BusinessModel->isUpdate(true)->save($data);

            if ($result === FALSE) {
                $this->error($this->BusinessModel->getError());
                exit;
            } else {

                //删除旧图片，更新cookie
                if (isset($data['avatar'])) {
                    is_file("." . $this->LoginAuth['avatar']) && unlink("." . $this->LoginAuth['avatar']);
                    $login = [
                        'id' => $this->LoginAuth['id'],
                        'mobile' => $this->LoginAuth['mobile'],
                        'nickname' => $data['nickname'],
                        'avatar' => $data['avatar']
                    ];
                    cookie('LoginAuth', $login);
                }

                $this->success('修改成功', url('/home/business/business/index'));
                exit;
            }
        }

        return $this->view->fetch();
    }
}
