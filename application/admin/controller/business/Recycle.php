<?php

namespace app\admin\controller\business;

use app\common\controller\Backend;
use think\Db;

/**
 * 客户管理
 *
 * @icon fa fa-circle-o
 */
class Recycle extends Backend
{

    /**
     * Recycle模型对象
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Business.Business');
    }

    /**
     * 查看
     */
    public function index()
    {
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }

            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            //查询总数
            $total = $this->model
                ->onlyTrashed()  //仅查询软删除的数据
                ->where($where)
                ->count();

            //查询数据
            $list = $this->model
                ->onlyTrashed()  //仅查询软删除的数据
                ->with(['source'])
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 还原
     */
    public function restore($ids = 0){
        //执行更新语句
        $result = Db::name('business')->where(['id' => ['in', $ids]])->update(['deletetime' => NULL]);

        //判断是否还原成功
        if ($result) {
            $this->success();
            exit;
        } else {
            $this->error(__('还原失败'));
            exit;
        }
    }
    
    /**
     * 真实删除
     */
    public function destroy($ids = 0)
    {
        //查询删除的数据
        $rows = $this->model->onlyTrashed()->select($ids);

        // var_dump(collection($rows)->toArray());
        // exit;

        if (!$rows) {
            $this->error('暂无删除的数据');
            exit;
        }

        // 单独的查询图片字段
        $avatar = $this->model->onlyTrashed()->where(['id' => ['in', $ids]])->column('avatar');

        // 去除空元素
        $avatar = array_filter($avatar);

        // 先将数据删除了，然后在去删除图片
        $result = $this->model->destroy($ids, true);

        if ($result === FALSE) {
            $this->error('删除失败');
            exit;
        } else {
            //删除图片
            if (!empty($avatar)) {
                foreach ($avatar as $item) {
                    //先判断文件是否存在， 如果存在 再去做删除
                    @is_file("." . $item) && @unlink('.' . $item);
                }
            }

            $this->success('删除成功');
            exit;
        }
    }
}
