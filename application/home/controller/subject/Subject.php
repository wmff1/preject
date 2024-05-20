<?php

namespace app\home\controller\subject;

use app\common\controller\Home;

/* 
    课程控制器
*/

class Subject extends Home
{
    //不需要登录的方法
    protected $noNeedLogin = ['search', 'details'];

    //构造函数，继承父类
    public function __construct()
    {
        parent::__construct();

        // 加载模型
        $this->SubjectModel = model('Subject.Subject');
        //章节模型
        $this->ListsModel = model('Subject.Lists');
        //用户模型
        $this->BusinessModel = model('Business.Business');
        //订单模型
        $this->OrderModel = model('Subject.Order');
        //消费模型
        $this->RecordModel = model('Business.Record');
    }

    //搜索
    public function search()
    {

        //获取搜索关键词
        $keywords = $this->request->param('keywords', '', 'trim');

        $where = [];

        //判断$keywords是否为空
        if (!empty($keywords)) {
            //模糊查询
            $where['title|content'] = ["like", "%$keywords%"];
        }

        // 查询课程
        $subject =  $this->SubjectModel->where($where)->select();

        //给模板赋值
        $this->view->assign([
            'subject' => $subject,
            'keywords' => $keywords
        ]);

        //渲染模板
        return $this->view->fetch();
    }

    //课程界面
    public function details($subid = 0)
    {
        //根据课程id去查询课程的信息
        $subject = $this->SubjectModel->find($subid);

        $comments = $this->OrderModel
            ->with('business')
            ->where(['subid' => $subid])->select();

        //默认没点赞
        $likes = false;

        //判断课程是否存在
        if (!$subject) {
            $this->error('课程不存在');
            exit;
        }

        //获取cookie
        $LoginAuth = cookie('LoginAuth') ? cookie('LoginAuth') : [];

        $busid = isset($LoginAuth['id']) ? $LoginAuth['id'] : 0;

        $busmobi = isset($LoginAuth['mobile']) ? $LoginAuth['mobile'] : '';

        //根据id 和 手机号找出这个人
        $where = [
            'id' => $busid,
            'mobile' => $busmobi
        ];

        //单条查询
        $business = $this->BusinessModel->where($where)->find();

        //说明有登录
        if ($business) {
            $str = trim($subject['likes']);
            $arr = explode(',', $str);
            $arr = array_filter($arr);

            //如果在说明点赞过了
            $likes = in_array($busid, $arr) ? true : false;
        }

        //查找课程的章节
        $lists = $this->ListsModel->where(['subid' => $subid])->order('createtime ASC')->select();

        $this->view->assign([
            'comments' => $comments,
            'subject' => $subject,
            'lists' => $lists,
            'likes' => $likes,
        ]);

        return $this->view->fetch();
    }

    //点赞
    public function likes()
    {
        //接收ajax请求
        if ($this->request->isAjax()) {
            //判断用户是否登录
            //获取cookie
            $LoginAuth = cookie('LoginAuth') ? cookie('LoginAuth') : [];

            $busid = isset($LoginAuth['id']) ? $LoginAuth['id'] : 0;

            $busmobi = isset($LoginAuth['mobile']) ? $LoginAuth['mobile'] : '';

            //根据id 和 手机号找出这个人
            $where = [
                'id' => $busid,
                'mobile' => $busmobi
            ];

            //单条查询
            $business = $this->BusinessModel->where($where)->find();

            if (!$business) {
                cookie('LoginAuth', null);
                //code = 0
                $this->error('请先登录');
            }

            //接收课程id
            $subid = $this->request->param('subid', 0);

            //根据课程id查询，查询课程是否存在
            $subject = $this->SubjectModel->find($subid);

            if (!$subject) {
                $this->error('课程不存在');
                exit;
            }

            //将字符串变成数组
            $likes = explode(',', $subject['likes']);

            //去除空元素
            $likes = array_filter($likes);

            //如果用户id不在数组中就增加点赞，如果在就取消点赞
            if (in_array($busid, $likes)) {
                foreach ($likes as $key => $item) {
                    if ($busid == $item) {
                        unset($likes[$key]);
                        break;
                    }
                }
                $msg = '取消点赞';
            } else {
                $likes[] = $busid;
                $msg = '点赞';
            }

            //更新数据
            $likesdata = [
                'id' => $subid,
                //从数组变回字符串
                'likes' => implode(',', $likes),
            ];

            $result = $this->SubjectModel->isUpdate(true)->save($likesdata);

            if ($result) {
                $this->success("{$msg}成功");
                exit;
            } else {
                $this->error("{$msg}失败");
                exit;
            }
        }
    }

    //视频播放
    public function play()
    {
        //如果有ajax请求
        if ($this->request->isAjax()) {

            //接收课程id
            $subid = $this->request->param('subid', 0);
            //获取章节id
            $listid = $this->request->param('listid', 0);
            //获取用户id
            $busid = $this->LoginAuth['id'];

            //组装条件
            $where = [
                'subid' => $subid,
                'busid' => $busid
            ];
            // 通过课程id和用户id查询课程
            $order = $this->OrderModel->where($where)->find();

            if (!$order) {
                $this->error('请先购买课程', null, 'buy');
                exit;
            }

            //如果有章节id 就查询指定的章节，如果没有章节id 就默认查询第一个
            $where = [
                'subid' => $subid
            ];

            if ($listid) {
                $where['id'] = $listid;
            }

            $list = $this->ListsModel->where($where)->order('createtime asc')->find();

            if ($list) {
                $this->success('返回章节', null, $list);
                exit;
            } else {
                $this->error('本课程没有章节');
                exit;
            }
        }
    }

    //购买课程
    public function buy()
    {
        //判断是否有ajax
        if ($this->request->isAjax()) {
            //获取当前购买的课程id
            $subid = $this->request->param('subid', 0);

            $busid = $this->LoginAuth['id'];

            //根据id去查询课程是否存在
            $subject = $this->SubjectModel->find($subid);

            if (!$subject) {
                $this->error('课程不存在');
                exit;
            }

            //判断是否已经购买过
            $where = [
                'busid' => $busid,
                'subid' => $subid
            ];

            $order = $this->OrderModel->where($where)->find();

            if ($order) {
                $this->error('该课程已经购买过，无须重复购买');
                exit;
            }

            //课程价格
            $price = isset($subject['price']) ? trim($subject['price']) : 0;

            //个人余额
            $money = isset($this->LoginAuth['money']) ? trim($this->LoginAuth['money']) : 0;

            //个人余额 - 课程价格
            $UpdateMoney = bcsub($money, $price);

            if ($UpdateMoney < 0) {
                $this->error('余额不足，请先充值');
                exit;
            }

            //开启操作表的事务
            $this->OrderModel->startTrans();
            $this->BusinessModel->startTrans();
            $this->RecordModel->startTrans();

            //生成订单号
            $code = build_code("SU");

            //订单表 插入
            $OrderData = [
                'subid' => $subid,
                'busid' => $busid,
                'total' => $price,
                'code' => $code,
            ];

            //插入订单表
            $OrderStatus = $this->OrderModel->validate('common/Subject/Order')->save($OrderData);

            //订单插入失败
            if ($OrderStatus === FALSE) {
                $this->error($this->OrderModel->getError());
                exit;
            }

            //更新用户表
            $BusData = [
                'id' => $busid,
                'money' => $UpdateMoney
            ];

            //判断成交状态,如果是为未成交，就改成已成交
            if (!$this->LoginAuth['deal']) {
                //变成已成交
                $BusData['deal'] = 1;
            }

            //更新
            $BusStatus = $this->BusinessModel->isUpdate(true)->save($BusData);

            //更新用户失败
            if ($BusStatus === FALSE) {
                //订单要回滚
                $this->OrderModel->rollback();
                $this->error($this->BusinessModel->getError());
                exit;
            }

            //插入消费记录表
            $subtitle = $subject['title'];
            $RecordData = [
                'total' => "-{$price}",
                'content' => "购买了【{$subtitle}】课程，花费了 ￥{$price}元",
                'busid' => $busid
            ];

            //插入消费记录表
            $RecordStatus = $this->RecordModel->validate('common/Business/Record')->save($RecordData);

            //消费记录插入失败
            if ($RecordStatus === FALSE) {
                $this->BusinessModel->rollback();
                $this->OrderModel->rollback();
                $this->error($this->RecordModel->getError());
                exit;
            }


            if ($OrderStatus === FALSE || $BusStatus === FALSE || $RecordStatus === FALSE) {
                $this->RecordModel->rollback();
                $this->BusinessModel->rollback();
                $this->OrderModel->rollback();
                $this->error('购买失败');
                exit;
            } else {
                //3个步骤都成功了 3个模拟的步骤都要提交事务 真正执行到数据库中
                $this->OrderModel->commit();
                $this->BusinessModel->commit();
                $this->RecordModel->commit();
                $this->success('购买成功');
                exit;
            }
        }
    }

    //课程购买成功界面
    public function complete($subid = 0)
    {

        //查找课程
        $subject = $this->SubjectModel->find($subid);

        if (!$subject) {
            $this->error('课程不存在', url('/home/subject/subject/search'));
            exit;
        }

        $this->view->assign('subid', $subid);

        return $this->view->fetch();
    }
}
