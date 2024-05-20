<?php

namespace app\rent\controller\business;

use think\Controller;

class Base extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->BusinessModel = model('Business.Business');
        $this->EMSModel = model('Ems');

    }

    // 注册
    public function register()
    {
        if ($this->request->isPost()) {
            $mobile = $this->request->param('mobile', '', 'trim');
            $password = $this->request->param('password', '', 'trim');
            $repass = $this->request->param('repass', '', 'trim');

            // 判断密码是否一致
            if ($password !== $repass) {
                $this->error('密码不一致');
                exit;
            }

            // 生成密码盐
            $salt = build_ranstr();

            // 加密
            $password = md5($password . $salt);

            // 客户来源
            $source = model('Business.Source')->where(['name' => '设备租赁'])->value('id');

            // 生成邀请码
            $invitecode = build_ranstr(6);

            $data = [
                'mobile' => $mobile,
                'password' => $password,
                'salt' => $salt,
                'sourceid' => $source,
                'invitecode' => $invitecode,
                'deal' => 0, //未成交
                'money' => 0 //余额为0
            ];

            $result = $this->BusinessModel->validate('common/Business/Business')->save($data);

            if ($result === FALSE) {
                $this->error($this->BusinessModel->getError());
                exit;
            } else {
                $this->success('注册成功，请登录');
                exit;
            }
        }
    }

    // 登录
    public function login()
    {
        if ($this->request->isPost()) {
            $mobile = $this->request->param('mobile', '', 'trim');
            $password = $this->request->param('password', '', 'trim');

            // 根据手机号查询用户是否存在
            $business = $this->BusinessModel->where(['mobile' => $mobile])->find();

            if (!$business) {
                $this->error('用户不存在');
                exit;
            }

            //验证密码是否正确
            $salt = $business['salt'];

            //密码加密
            $repass = md5($password . $salt);

            // 跟数据库的密码比较，看是否正确
            if ($repass != $business['password']) {
                $this->error('密码错误');
                exit;
            }

            $this->success('登录成功', '/business/base/index', $business);
            exit;
        }
    }

    // 修改资料
    public function profile()
    {
        if ($this->request->isAjax()) {

            $id = $this->request->param('id', 0, 'trim');
            $nickname = $this->request->param('nickname', '', 'trim');
            $email = $this->request->param('email', '', 'trim');
            $gender = $this->request->param('gender', '', 'trim');
            $province = $this->request->param('province', '', 'trim');
            $city = $this->request->param('city', '', 'trim');
            $district = $this->request->param('district', '', 'trim');
            $password = $this->request->param('password', '', 'trim');

            //先确认这个用户是存在
            $business = $this->BusinessModel->find($id);

            if (!$business) {
                $this->error('用户不存在');
                exit;
            }

            //组装数据
            $data = [
                'id' => $id,
                'nickname' => $nickname,
                'email' => $email,
                'gender' => $gender,
                'province' => $province,
                'city' => $city,
                'district' => $district,
            ];

            //判断是否有修改过邮箱，如果有修改过邮箱，那么认证状态也要修改
            if ($email != $business['email']) {
                $data['status'] = 0;
            }

            //判断密码是否要修改
            if (!empty($password)) {
                //重新生成密码盐
                $salt = build_ranstr();

                $password = md5($password . $salt);

                $data['password'] = $password;
                $data['salt'] = $salt;
            }

            //接收文件上传
            if (isset($_FILES['avatar']) && $_FILES['avatar']['size'] > 0) {
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

            //准备更新数据库
            $result = $this->BusinessModel->validate('common/Business/Business.ShopProfile')->isUpdate(true)->save($data);

            if ($result === FALSE) {
                $this->error($this->BusinessModel->getError());
                exit;
            } else {
                //更新资料成功
                //删除旧图片，更新cookie
                if (isset($data['avatar'])) {
                    //判断旧图片是否存在，如果存在就删除掉
                    @is_file("." . $business['avatar']) && unlink("." . $business['avatar']);
                }

                //查询一次最新的数据出来，并返回
                $update = $this->BusinessModel->find($id);

                $this->success('修改成功', '/business/base/index', $update);
                exit;
            }
        }
    }

    /**
     * 发送邮箱验证码
     */
    public function sendems()
    {
        if ($this->request->isAjax()) {
            // 接收id,判断是否存在
            $id = $this->request->param('id', 0, 'trim');

            //判断一下 有没有这个人
            $business = $this->BusinessModel->find($id);

            if (!$business) {
                $this->error('用户不存在');
                exit;
            }

            $email = trim($business['email']);

            //判断邮箱是否为空
            if (empty($email)) {
                $this->error('邮箱为空，无法验证，请先更新邮箱地址');
                exit;
            }

            // 生成验证码
            $code = build_ranstr(6);

            //组装数据
            $data = [
                'event' => 'EmailCheck', //自定义
                'email' => $email,
                'code' => $code,
            ];

            //要将发送成功的验证码插入到数据中
            //开启事务
            $this->EMSModel->startTrans();

            //插入数据
            $result = $this->EMSModel->save($data);

            if ($result === FALSE) {
                $this->error('验证码添加失败');
                exit;
            }

            //调用邮件发送方法
            $success = send_email($email, $code);

            //邮件发送失败
            if (!$success['result']) {
                //回滚插入的验证码记录
                $this->EMSModel->rollback();

                $this->error($success['msg']);
                exit;
            } else {
                //提交事务
                $this->EMSModel->commit();
                $this->success('验证码发送成功，请注意查收');
                exit;
            }
        }
    }

    /**
     * 邮箱验证
     */
    public function checkems()
    {
        if ($this->request->isAjax()) {
            $id = $this->request->param('id', 0, 'trim');
            $code = $this->request->param('code', 0, 'trim');

            //判断用户是否存在
            $business = $this->BusinessModel->find($id);

            if (!$business) {
                $this->error('用户不存在');
                exit;
            }

            if (empty($business['email'])) {
                $this->error('您的邮箱为空，请先修改邮箱');
                exit;
            }

            if ($business['status']) {
                $this->error('您已通过邮箱验证，无须重复验证');
                exit;
            }

            //根据条件查询出验证码记录
            $where = [
                'email' => $business['email'],
                'code' => $code
            ];

            $ems = $this->EMSModel->where($where)->find();

            if (!$ems) {
                $this->error("验证码有误，请重新输入");
                exit;
            }

            // 验证码的时间 验证码失效时间
            $checktime = $ems['createtime'] + 3600 * 24;

            if ($checktime < time()) {
                //直接删除
                $this->EMSModel->destroy($ems['id']);

                $this->error('验证码过期');
                exit;
            }

            //开启事务
            $this->BusinessModel->startTrans();
            $this->EMSModel->startTrans();

            //更新用户表
            $BusessinData = [
                'id' => $business['id'],
                'status' => 1
            ];

            $BusessinStatus = $this->BusinessModel->isUpdate(true)->save($BusessinData);

            if ($BusessinStatus === FALSE) {
                $this->error('更新用户验证状态失败');
                exit;
            }

            //删除验证记录
            $EMStatus = $this->EMSModel->destroy($ems['id']);

            if ($EMStatus === FALSE) {
                $this->BusinessModel->rollback();
                $this->error('验证码删除失败');
                exit;
            }

            if ($BusessinStatus === FALSE || $EMStatus === FALSE) {
                $this->EMSModel->rollback();
                $this->BusinessModel->rollback();
                $this->error('验证失败');
                exit;
            } else {
                //提交事务
                $this->BusinessModel->commit();
                $this->EMSModel->commit();

                //查询一次最新的数据出来，并返回
                $update = $this->BusinessModel->find($id);

                $this->success('邮箱验证成功', null, $update);
                exit;
            }
        }
    }
}
