<?php

namespace app\home\controller;

use think\Controller;

class Index extends Controller
{
    //构造函数
    public function __construct()
    {
        //继承父类
        parent::__construct();

        //加载全局模型
        $this->BusinessModel = model('Business.Business');
    }

    //首页
    public function index()
    {

        //加载模型
        $CategoryModel = model('Subject.Category');
        $SubjectModel = model('Subject.Subject');

        //查询分类
        $cate = $CategoryModel->order('weight ASC')->select();

        //查询课程点赞量
        $toplist = $SubjectModel->order("likes DESC")->limit(8)->select();

        // var_dump(collection($toplist)->toArray());
        // exit;

        $catelist = [];
        foreach($cate as $key=>$item){
            //查询课程
            $subject = $SubjectModel->where(['cateid' => $item['id']])->order('createtime DESC')->limit(8)->select();
            
            if($subject){
                $catelist[] = [
                    'id' => $item['id'],
                    'name' => $item['name'],
                    'subject' => $subject
                ];

            }
        }
    //    var_dump(collection($toplist)->toArray());
    //    exit;
        //赋值
        $this->view->assign([
            'catelist' => $catelist,
            'toplist' => $toplist
        ]);

        return $this->view->fetch();
    }

    //登录
    public function login()
    {
        //临时关闭模板布局
        $this->view->engine->layout(false);

        //判断是否有post提交
        if ($this->request->isPost()) {
            //接收手机号、密码
            $mobile = $this->request->param('mobile', '', 'trim');
            $password = $this->request->param('password', '', 'trim');

            //判断手机号，密码是否为空
            if (empty($mobile)) {
                $this->error('手机号不能为空');
                exit;
            }
            if (empty($password)) {
                $this->error('密码不能为空');
                exit;
            }

            //都不为空，通过手机号唯一值查找数据库是否存在用户
            $business = $this->BusinessModel->where(['mobile' => $mobile])->find();

            if (!$business) {
                $this->error('用户不存在');
                exit;
            }

            //用户存在，判断密码是否正确
            $salt = $business['salt'];
            $repass = md5($password . $salt);

            if ($repass != $business['password']) {
                $this->error('密码不正确');
                exit;
            }

            //将数据存储到cookie中
            $data = [
                'id' => $business['id'],
                'mobile' => $business['mobile'],
                'nickname' => $business['nickname'],
                'avatar' => $business['avatar'],
            ];

            cookie('LoginAuth', $data);

            //登录成功
            $this->success('登录成功', url('/home/business/business/index'));
            exit;
        }
        return $this->view->fetch();
    }

    //注册
    public function register()
    {
        //临时关闭模板布局
        $this->view->engine->layout(false);

        //判断是否有post提交
        if ($this->request->isPost()) {
            // 1、接收手机号、密码和确认密码，判断密码是否一致
            // 2、生成密码盐，对密码进行加密
            // 3、组装数据，插入数据
            $mobile = $this->request->param('mobile', '', 'trim');
            $password = $this->request->param('password', '', 'trim');
            $repass = $this->request->param('repass', '', 'trim');

            // 判断密码是否一致
            if ($password != $repass) {
                $this->error('密码不一致');
                exit;
            }

            // 生成密码盐
            $salt = build_ranstr();

            //加密密码
            $password = md5($password . $salt);

            //查询用户来源数据，将用户的来源变为变量，用到模型
            $sourceid = model('Business.Source')->where(['name' => '云课堂'])->value('id');

            //组装需要数据
            $data = [
                'mobile' => $mobile, //手机号
                'password' => $password, //密码
                'salt' => $salt, //密码盐
                'deal' => 0, //未成交
                'invitecode' => $salt, //邀请码
                'sourceid' => $sourceid, //用户通过什么方式了解平台
            ];

            //插入数据,用到模型,先构造函数
            $result = $this->BusinessModel->validate('common/Business/Business')->save($data);

            //如果插入数据不成功
            if (!$result) {
                $this->error($this->BusinessModel->getError());
                exit;
            } else {
                $this->success('注册成功', url('home/index/login'));
                exit;
            }
        }
        return $this->view->fetch();
    }
    //退出登录
    public function logout(){
        cookie('LoginAuth',null);
        $this->success('退出成功',url('/home/index/login'));
        exit;
    }

}
