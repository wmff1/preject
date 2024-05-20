<?php

namespace app\common\controller;

use think\Controller;

class Home extends Controller
{
    //构造函数
    public function __construct()
    {
        //继承父类
        parent::__construct();

        //全局模型加载
        $this->BusinessModel = model('Business.Business');

        //全局变量身份变量
        $this->LoginAuth = null;

        //自动去调用判断用户是否登录
        $this->IsLogin();
    }

    //判断是否登录
    public function IsLogin()
    {

        
        //获取cookie
        $LoginAuth = cookie('LoginAuth') ? cookie('LoginAuth') : [];
        //获取id和手机号
        $busid = isset($LoginAuth['id']) ? trim($LoginAuth['id']) : 0;
        $busmobile = isset($LoginAuth['mobile']) ? trim($LoginAuth['mobile']) : '';

        //判断id是否存在和手机号是否为空
        if (!$busid || empty($busmobile)) {
            //清空伪造cookie
            cookie('LoginAuth', null);
            $this->error('登录有误，请重新登录', url('/home/index/login'));
            exit;
        }

        //根据id和手机号查询此人是否存在
        $where = [
            'id' => $busid,
            'mobile' => $busmobile
        ];

        //单条查询
        $LoginAuth = $this->BusinessModel->where($where)->find();
        
        //执行该模型的最后一条语句
        // echo $this->BusinessModel->getLastSql();

        if (!$LoginAuth) {
            //清空伪造cookie
            cookie('LoginAuth', null);
            $this->error('非法登录', url('/home/index/login'));
            exit;
        }
            // var_dump($LoginAuth);
            // exit;

        //赋值给模板
        $this->view->assign('LoginAuth', $LoginAuth);

        //赋值一份给全局变量
        $this->LoginAuth = $LoginAuth;
    }
}
