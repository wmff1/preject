<?php

namespace app\stock\controller;

use fast\Random;
use think\Controller;

class Admin extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->AdminModel = model('Admin.Admin');
    }

    // 授权登录界面
    public function login()
    {
        if ($this->request->isPost()) {
            //临时凭证
            $code = $this->request->param('code', '', 'trim');

            //调用当前控制器下面的方法，用来发起微信接口请求
            $result = $this->code2ession($code);

            $openid = isset($result['openid']) ? trim($result['openid']) : '';

            if (empty($openid)) {
                $this->error('授权失败，无法获取openid');
                exit;
            }

            // 根据openid 去找管理员 是否存在
            $admin = $this->AdminModel->where(['openid' => $openid])->find();

            //如果管理员不存在就说明
            if ($admin) {
                //授权绑定过的
                $this->success('授权成功', null, $admin);
                exit;
            } else {
                // 传递openid，为绑定账号做铺垫
                $url = '/pages/admin/login?openid=' . $openid;

                $this->success('授权成功，请先绑定账号', $url, false);
                exit;
            }
            $this->success('授权成功');
            exit;
        }
    }

    // 绑定账号
    public function bind()
    {
        if ($this->request->isPost()) {
            $username = $this->request->param('username', '', 'trim');
            $password = $this->request->param('password', '', 'trim');
            $openid = $this->request->param('openid', '', 'trim');

            if (empty($openid)) {
                $this->error('授权数据未知，请重新授权');
                exit;
            }

            $admin = $this->AdminModel->where(['username' => $username])->find();

            if (!$admin) {
                $this->error('管理员不存在');
                exit;
            }

            $salt = $admin['salt'];
            $repassword = $admin['password'];

            $password = md5(md5($password) . $salt);

            if ($repassword != $password) {
                $this->error('密码不一致');
                exit;
            }

            if (!empty($admin['openid'])) {
                $this->error('该管理员已经绑定过，不能重复绑定');
                exit;
            }



            $data = [
                'id' => $admin['id'],
                'openid' => $openid
            ];

            $result = $this->AdminModel->isUpdate(true)->save($data);

            if ($result === FALSE) {
                $this->error($this->AdminModel->getError());
                exit;
            }

            // 再次查找管理员得到最新的数据
            $last = $this->AdminModel->find();

            $this->success('绑定管理员成功', '/pages/index/index', $last);
            exit;
        }
    }

    // 账号密码登录 H5和App
    public function signin()
    {
        if ($this->request->isPost()) {
    
            $username = $this->request->param('username', '', 'trim');
            $password = $this->request->param('password', '', 'trim');
 
            $admin = $this->AdminModel->where(['username' => $username])->find();

            if (!$admin) {
                $this->error('管理员不存在');
                exit;
            }

            $salt = $admin['salt'];
            $repass = $admin['password'];

            $password = md5(md5($password) . $salt);

            if ($repass != $password) {
                $this->error('账号的密码错误');
                exit;
            }

            $this->success('登录成功', '/pages/index/index', $admin);
            exit;
        }
    }

    // 解除微信绑定
    public function wechat()
    {
        if ($this->request->isPost()) {
            $adminid = $this->request->param('adminid', 0, 'trim');

            //根据id找一下管理员是否存在
            $admin = $this->AdminModel->find($adminid);

            if (!$admin) {
                $this->error('管理员不存在');
                exit;
            }

            if (empty($admin['openid'])) {
                $this->error('您暂未绑定微信账号');
                exit;
            }

            //解绑的动作
            $data = [
                'id' => $admin['id'],
                'openid' => null
            ];

            $result = $this->AdminModel->isUpdate(true)->save($data);

            if ($result === FALSE) {
                $this->error('解绑失败');
                exit;
            } else {
                //返回最新的数据
                $admin = $this->AdminModel->find($adminid);
                $this->success('解绑成功', null, $admin);
                exit;
            }
        }
    }

    // 修改资料
    public function profile()
    {
        if ($this->request->isPost()) {
            $adminid = $this->request->param('adminid', 0, 'trim');
            $nickname = $this->request->param('nickname', '', 'trim');
            $mobile = $this->request->param('mobile', '', 'trim');
            $email = $this->request->param('email', '', 'trim');
            $password = $this->request->param('password', '', 'trim');

            $admin = $this->AdminModel->where(['id' => $adminid])->find();

            if(!$admin){
                $this->error('管理员不存在');
                exit;
            }

            $data = [
                'id' => $adminid,
                'nickname' => $nickname,
                'mobile' => $mobile,
                'email' => $email,
            ];

            if(!empty($password)){
                $salt = Random::alnum();
                $password = md5(md5($password).$salt);

                if($password === $admin['password']){
                    $this->error('新密码和旧密码不能一致');
                    exit;
                }

                $data['salt'] = $salt;
                $data['password'] = $password;
            }

            $result = $this->AdminModel->isUpdate(true)->save($data);

            if($result === FALSE){
                $this->error('更新失败');
                exit;
            }

            $last = $this->AdminModel->find($adminid);

            $this->success('更新成功', null, $last);
            exit;
        }
    }

    // 上传头像
    public function avatar()
    {
        if ($this->request->isPost()) {
            $adminid = $this->request->param('adminid', 0, 'trim');

            $admin = $this->AdminModel->where(['id' => $adminid])->find();

            if (!$admin) {
                $this->error('管理员不存在');
                exit;
            }

            $data = [
                'id' => $adminid
            ];


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

            $result = $this->AdminModel->isUpdate(true)->save($data);

            if ($result === FALSE) {
                $this->error('更新头像失败');
                exit;
            } else {
                //删除旧图片
                if (isset($data['avatar'])) {
                    //判断旧图片是否存在，如果存在就删除掉
                    @is_file("." . $admin['avatar']) && unlink("." . $admin['avatar']);
                }

                //返回新图片的地址
                //获取系统配置里面的选项
                $url = config('site.url') ? config('site.url') : '';

                //拼上域名信息
                $avatar = trim($data['avatar'], '/');
                $avatar = $url . '/' . $avatar;

                $this->success('头像更新成功', null, $avatar);
                exit;
            }
        }
    }


    // 微信服务端发送GET请求
    public function code2ession($js_code = null)
    {

        if ($js_code) {
            $appid = 'wx7bbcac928d1666ec';

            $appSecret = 'fc972533f9f43475a6abd1289d400196';

            $url = "https://api.weixin.qq.com/sns/jscode2session?appid=$appid&secret=$appSecret&js_code=$js_code&grant_type=authorization_code";

            //发起get请求
            $result = $this->https_request($url);

            //获取结果 将json转化为数组
            $resultArr = json_decode($result, true);

            return $resultArr;
        } else {
            return false;
        }
    }

    //http请求 利用php curl扩展去发送get 或者 post请求 服务器上面一定要开启 php curl扩展
    // https://www.php.net/manual/zh/book.curl.php
    protected function https_request($url, $data = null)
    {
        if (function_exists('curl_init')) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1);
            if (!empty($data)) {
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            // 发送会话，返回结果
            $output = curl_exec($curl);
            curl_close($curl);
            return $output;
        } else {
            return false;
        }
    }
}
