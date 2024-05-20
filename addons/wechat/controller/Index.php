<?php

namespace addons\wechat\controller;

use addons\wechat\library\Config;
use addons\wechat\model\WechatAutoreply;
use addons\wechat\model\WechatCaptcha;
use addons\wechat\model\WechatContext;
use addons\wechat\model\WechatResponse;
use addons\wechat\model\WechatConfig;

use EasyWeChat\Factory;
use addons\wechat\library\Wechat as WechatService;
use addons\wechat\library\Config as ConfigService;
use think\Log;

// 发图文消息
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;

/**
 * 微信接口
 */
class Index extends \think\addons\Controller
{
    public $app = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->app = Factory::officialAccount(Config::load());
        $this->url = config('site.url');
    }

    /**
     *
     */
    public function index()
    {
        $this->error("当前插件暂无前台页面");
    }

    /**
     * 微信API对接接口
     */
    public function api()
    {
        $this->app->server->push(function ($message) {


            // 本地调试
            // $message = [
            //     // {
            //     //     "ToUserName":"gh_9447146866b6",
            //     //     "FromUserName":"odq7a53pLg5sr1WaYb-qTFTwHU-g",
            //     //     "CreateTime":"1667649453",
            //     //     "MsgType":"location",
            //     //     "Location_X":"23.023619",
            //     //     "Location_Y":"113.320374",
            //     //     "Scale":"15",
            //     //     "Label":"\u756a\u79ba\u533a\u90ed\u5bb6\u574a\u88571\u53f7",
            //     //     "MsgId":"23875001339385687"}

            //     'FromUserName' => 'odq7a53pLg5sr1WaYb-qTFTwHU-g', //微信用户
            //     'ToUserName' => 'gh_9447146866b6', //开发者
            //     'CreateTime' => time(),

            //     'MsgType' => 'text',
            //     // 'Content' => '课程：第一阶段',
            //     'Content' => '模板消息',

            //     //事件参数
            //     // 'MsgType' => 'event',
            //     // 'Event' => 'subscribe',
            //     // 'EventKey' => '',

            //     // 定位
            //     // 'MsgType' => 'location',
            //     // "Location_X" => "23.023619",
            //     // "Location_Y" => "113.320374",
            //     // "Scale" => "15", //地图缩放的级别
            //     // "Label" => "海珠区广州大道南财智大厦",
            // ];

            $wechatService = new WechatService;

            $matches = null;
            $openid = $message['FromUserName'];
            $to_openid = $message['ToUserName'];

            $unknownMessage = WechatConfig::getValue('default.unknown.message');
            $unknownMessage = $unknownMessage ? $unknownMessage : "";

            switch ($message['MsgType']) {
                case 'event': //事件消息
                    $event = $message['Event'];

                    $eventkey = $message['EventKey'] ? $message['EventKey'] : $message['Event'];

                    //验证码消息
                    if (in_array($event, ['subscribe', 'SCAN']) && preg_match("/^captcha_([a-zA-Z0-9]+)_([0-9\.]+)/", $eventkey, $matches)) {
                        return WechatCaptcha::send($openid, $matches[1], $matches[2]);
                    }
                    switch ($event) {
                        case 'subscribe': //添加关注
                            $subscribeMessage = WechatConfig::getValue('default.subscribe.message');

                            $subscribeMessage = $subscribeMessage ? $subscribeMessage : "欢迎关注我们!";

                            return $subscribeMessage;

                        case 'unsubscribe': //取消关注
                            return '';
                        case 'LOCATION': //获取地理位置
                            return '';
                        case 'VIEW': //跳转链接,eventkey为链接
                            return '';
                        case 'SCAN': //扫码
                            return '';
                        default:
                            break;
                    }

                    $wechatResponse = WechatResponse::where(["eventkey" => $eventkey, 'status' => 'normal'])->find();
                    if ($wechatResponse) {
                        $responseContent = (array)json_decode($wechatResponse['content'], true);
                        $wechatContext = WechatContext::where(['openid' => $openid])->order('id', 'desc')->find();
                        $data = ['eventkey' => $eventkey, 'command' => '', 'refreshtime' => time(), 'openid' => $openid];
                        if ($wechatContext) {
                            $wechatContext->save($data);
                        } else {
                            $wechatContext = WechatContext::create($data, true);
                        }
                        $result = $wechatService->response($this, $openid, '', $responseContent, $wechatContext);
                        if ($result) {
                            return $result;
                        }
                    }
                    return $unknownMessage;
                case 'text': //文字消息
                case 'image': //图片消息
                case 'voice': //语音消息
                case 'video': //视频消息
                case 'location': //坐标消息
                case 'link': //链接消息
                default: //其它消息
                    //搜索课程关键字 
                    $content = isset($message['Content']) ? trim($message['Content']) : '';

                    //自动回复处理
                    if ($message['MsgType'] == 'text') {

                        $autoreply = null;
                        // 缓存查询 cache(true)
                        // $autoreplyList = WechatAutoreply::where('status', 'normal')->cache(true)->order('weigh DESC,id DESC')->select();
                        // 不开启缓存查询
                        $autoreplyList = WechatAutoreply::where('status', 'normal')->order('weigh DESC,id DESC')->select();

                        foreach ($autoreplyList as $index => $item) {
                            //完全匹配和正则匹配
                            if ($item['text'] == $message['Content'] || (in_array(mb_substr($item['text'], 0, 1), ['#', '~', '/']) && preg_match($item['text'], $message['Content'], $matches))) {
                                $autoreply = $item;
                                break;
                            }
                        }

                        if ($autoreply) {

                            $wechatResponse = WechatResponse::where(["eventkey" => $autoreply['eventkey'], 'status' => 'normal'])->find();
                            if ($wechatResponse) {

                                //将content 从 json 转换为 php 数组
                                $responseContent = (array)json_decode($wechatResponse['content'], true);

                                //找出要发送给那个微信用户
                                $wechatContext = WechatContext::where(['openid' => $openid])->order('id', 'desc')->find();

                                //回复信息结构
                                $result = $wechatService->response($this, $openid, $message['Content'], $responseContent, $wechatContext, $matches);

                                //返回一个信息字符串
                                if ($result) {
                                    return $result;
                                }
                            }
                        }
                    }

                    // 根据定位信息，返回周边美食
                    if ($message['MsgType'] == 'location') {

                        $params = [
                            'query' => '美食',
                            'location' => $message['Location_X'] . "," . $message['Location_Y'],
                            'ak' => '3WsVgGYtUk30YSBcAs2wwVFm4WkW4Mda',
                            'output' => 'json',
                            'radius' => 1000,
                            'scope' => 2,  //1基本信息 2 详细信息
                            'page_size' => 5,  //这里设置返回条数，我们返回5条少一点，输出多了 公众号不返回内容
                        ];
                        // 将数组转化为字符串
                        $query = http_build_query($params);
                        // 获取地址
                        $url = "https://api.map.baidu.com/place/v2/search?$query";
                        // json字符串
                        $output = file_get_contents($url);
                        // 将json转化为数组
                        $lists = json_decode($output, true);

                        $result = $message['Label'] . " 附近的美食有：\r\n";

                        foreach ($lists['results'] as $item) {
                            $name = $item['name'];

                            $address = $item['address'];
                            // $telephone = $item['telephone'];
                            $url = $item['detail_info']['detail_url'];

                            $result .= "店名：$name \n地址：$address \n地址：<a href='$url'>商家店面地址</a> \r\n\r\n";
                        }

                        return $result;
                    }

                    //搜索 云课堂课程 关键词：【课程：微信小程序】
                    if (stripos($content, '课程') !== FALSE) {

                        //用 : 来做分隔符 分割出课程的名称
                        $arr = explode('：', $content);
                        $title = isset($arr[1]) ? trim($arr[1]) : '';
                      
                        if (empty($title)) {
                            return '请重新输入课程名称';
                        }

                        $where['title|content'] = ['like', "%$title%"];

                        //查询课程
                        $sublist = model('app\common\model\Subject\Subject')->where($where)->select();

                        if (!$sublist) {
                            return "暂无 $title 课程相关信息";
                        }
                 
                        //返回一个图文信息
                        $newlist = [];

                        foreach ($sublist as $item) {
            
                            $newlist[] = new NewsItem([
                                'title' => $item['title'],
                                'description' => $item['content'],
                                'url' => url('/home/subject/subject/details', ['subid' => $item['id']], true, true),
                                'image' => $this->url . $item['thumbs_text']
                            ]);
                        }
                        return new News($newlist);
                    }

                    // 支付成功
                    if ($content == '模板消息') {
                        $createtime = date("Y-m-d H:i", time());

                        //发送模板消息
                        $this->app->template_message->send([
                            //模板消息接收方
                            'touser' => $message['FromUserName'],
                            //模板id
                            'template_id' => 'b7HmjgmLLN4glM4OC2r_HImPdJzAtVr7HB8nrNJnnOE',
                            //跳转地址
                            'url' => 'http://wmfvueshop.k8server.cn',
                            //模板数据
                            'data' => [
                                'title' => '标题：您购买了两件商品',
                                'user' => '用户名：张三',
                                'content' => '描述：您共消费20元',
                                'createtime' => "消费时间：$createtime"
                            ]
                        ]);
                        exit;
                    }

                    //智能聊天接口
                    if (!empty($content)) {
                        $url = "http://api.qingyunke.com/api.php?key=free&appid=0&msg=$content";

                        $json = file_get_contents($url);

                        $result = json_decode($json, true);

                        $res = isset($result['content']) ? $result['content'] : '听不懂你说啥';

                        return $res;
                    }

                    return $unknownMessage;
            }
            return ""; //SUCCESS
        });

        $response = $this->app->server->serve();
        // 将响应输出
        $response->send();
        return;
    }

    /**
     * 登录回调
     */
    public function callback()
    {
    }

    /**
     * 支付回调
     */
    public function notify()
    {
        Log::record(file_get_contents('php://input'), "notify");
        $response = $this->app->handlePaidNotify(function ($message, $fail) {
            // 你的逻辑
            return true;
            // 或者错误消息
            $fail('Order not exists.');
        });

        $response->send();
        return;
    }
}
