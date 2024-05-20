<?php

namespace app\admin\controller\product;

use app\admin\controller\Base;
use app\common\controller\Backend;
use app\common\model\Product\Activity;
use app\common\model\Product\Product;

class Goods extends Base
{ 
    protected $model = null;
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new Product();
        $this->view->assign("typeList", $this->model->getTypeList());
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("dispatchTypeList", $this->model->getDispatchTypeList());
    }

    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = false;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            // list($where, $sort, $order, $offset, $limit) = $this->buildparams('title');
            $sort = $this->request->get("sort", !empty($this->model) && $this->model->getPk() ? $this->model->getPk() : 'id');
            $sort = $sort == 'price' ? 'convert(`price`, DECIMAL(10, 2))' : $sort;
            $order = $this->request->get("order", "DESC");
            $offset = $this->request->get("offset", 0);
            $limit = $this->request->get("limit", 0);
            $activity_type = $this->request->get("activity_type", 'all');   // 活动类型

            $total = $this->buildSearchOrder()->count();

            // 构建查询数据条件
            $list = $this->buildSearchOrder();

            $goodsTableName = $this->model->getQuery()->getTable();

            // 关闭 sql mode 的 ONLY_FULL_GROUP_BY
            $oldModes = closeStrict(['ONLY_FULL_GROUP_BY']);

            $list = $list->field("$goodsTableName.*")
                // ->group('id')
                ->orderRaw($sort . ' ' . $order)
                ->limit($offset, $limit)
                ->select();
            // 恢复 sql mode
            recoverStrict($oldModes);

            foreach ($list as $row) {
                $row->visible(['id', 'type', 'name','price','stock','status']);
            }
            $list = collection($list)->toArray();
            $result = array("total" => $total, "rows" => $list);
            if ($this->request->get("page_type") == 'select') {
                return json($result);
            }
            return $this->success('操作成功', null, $result);
        }
        return $this->view->fetch();
    }
    public function select()
    {
        if ($this->request->isAjax()) {
            return $this->index();
        }
            
        $categoryModel = new \app\common\model\Product\Type;
        $category = $categoryModel->with('children.children.children')->where('pid', 0)->order('weight desc, id asc')->select();
        $this->assignconfig('category', $category);
        return $this->view->fetch();
    }
    private function buildSearchOrder()
    {
        $search = $this->request->get("search", '');        // 关键字
        $status = $this->request->get("status", 'all');
        $activity_type = $this->request->get("activity_type", 'all');
        $app_type = $this->request->get("app_type", 'all');
        $min_price = $this->request->get("min_price", "");
        $max_price = $this->request->get("max_price", "");
        $category_id = $this->request->get('category_id', 0);

        $name = $this->model->getQuery()->getTable(); //  pro_product表名
        $tableName = $name . '.'; //  pro_product.

        $goods = $this->model;
        if ($search) {
            // 模糊搜索字段
            $searcharr = ['name', 'id'];
            foreach ($searcharr as $k => &$v) {
                $v = stripos($v, ".") === false ? $tableName . $v : $v;
            }

            unset($v);
            $goods = $goods->where(function ($query) use ($searcharr, $search, $tableName) {
                $query->where(implode("|", $searcharr), "LIKE", "%{$search}%");
            });
        }

        $goods_ids = [];
        // 活动
        if ($activity_type != 'all') {
            echo 88;exit;
            // 同一请求，会组装两次请求条件,缓存 10 秒
            $activities = Activity::cache(10)->where('type', $activity_type)->column('goods_ids');
            foreach ($activities as $key => $goods_id) {
                $ids = explode(',', $goods_id);
                $goods_ids = array_merge($goods_ids, $ids);
            }
        }
        // 积分
        if ($app_type == 'score') {
            // $score_goods_ids = \app\admin\model\shopro\app\ScoreSkuPrice::cache(10)->group('goods_id')->column('goods_id');
            // $goods_ids = array_merge($goods_ids, $score_goods_ids);
        }

        $goods_ids = array_filter(array_unique($goods_ids));
        if ($goods_ids) {
            $goods = $goods->where($tableName . 'id', 'in', $goods_ids);
        } else if ($activity_type != 'all' || $app_type != 'all') { 
            // 搜了活动，但是 goods_ids 为空，这时候搜索结果应该为空
            $goods = $goods->where($tableName . 'id', 'in', $goods_ids);
        }
        // 价格
        if ($min_price != '') {
            $goods = $goods->where('convert(`price`, DECIMAL(10, 2)) >= ' . round($min_price, 2));
        }
        if ($max_price != '') {
            $goods = $goods->where('convert(`price`, DECIMAL(10, 2)) <= ' . round($max_price, 2));
        }
        // 商品状态
        if ($status != 'all') {
            $goods = $goods->where('status', 'in', $status);
        }

        if(isset($category_id) && $category_id != 0) {
            $category_ids = [];
            // 查询分类所有子分类,包括自己
            $category_ids = \app\common\model\Product\Type::getCategoryIds($category_id);
            $goods = $goods->where(function ($query) use ($category_ids) {
                // 所有子分类使用 find_in_set or 匹配，亲测速度并不慢
                foreach($category_ids as $key => $category_id) {
                    // $category_id = filter_sql($category_id);
                    $query->whereOrRaw("find_in_set($category_id, typeid)");
                }
            });
        }
        return $goods;
    }

    public function lists()
    {
        $params = $this->request->get();
        $data = $this->model->getGoodsList($params);
        $this->success('商品列表',null, $data);
    }

}