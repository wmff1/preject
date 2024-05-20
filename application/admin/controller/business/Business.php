<?php

namespace app\admin\controller\business;

use app\common\controller\Backend;

use think\Db;

/**
 * 客户公海管理
 */
class Business extends Backend
{
    /**
     * Business模型对象
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Business.Business');
        $this->AdminModel = model('Admin');
        $this->ReceiveModel = model('Business.Receive');
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

            $where = [
                'adminid' => NULL
            ];

            //查询总数
            $total = $this->model
                ->where($where)
                ->count();

            //查询数据
            $list = $this->model
                ->with('source')
                ->where($where)
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    // 申请
    public function apply($ids = 0)
    {

        // 判断用户存不存在
        $business = $this->model->select($ids);

        if (!$business) {
            $this->error('用户不存在');
            exit;
        }

        // 获取当前登录的管理员id，判断当前管理员是否存在
        $adminid =  $this->auth->id;

        $admin = $this->AdminModel->find($adminid);

        if (!$admin) {
            $this->error('管理员不存在');
            exit;
        }

        //开启事务
        $this->model->startTrans();
        $this->ReceiveModel->startTrans();

        // 执行更新语句
        $result = $this->model->where(['id' => ['in', $ids]])->update(['adminid' => $adminid]);

        if ($result === FALSE) {
            $this->error(__('申请失败'));
            exit;
        }

        $ids = explode(',', $ids);

        $BusinessData = [];

        foreach ($ids as $item) {
            $BusinessData[] = [
                'applyid' => $adminid,
                'status' => 'apply',
                'busid' => $item
            ];
        }

        // 领取表插入
        $ReceiveStatus = $this->ReceiveModel->saveAll($BusinessData);

        if ($ReceiveStatus === FALSE) {
            $this->model->rollback();
            $this->error('插入失败');
            exit;
        }

        if ($result === FALSE || $ReceiveStatus === FALSE) {
            $this->ReceiveModel->rollback();
            $this->model->rollback();
            $this->error('申请失败');
            exit;
        } else {
            $this->model->commit();
            $this->ReceiveModel->commit();
            $this->success('申请成功');
            exit;
        }
    }

    //分配
    public function share($ids = 0)
    {
        // 查找所有管理员
        $admin = $this->AdminModel->column('id,nickname');

        // 生成下拉列表
        $this->view->assign('adminlist', build_select('row[adminid]', $admin, [], ['class' => 'form-control selectpicker']));

        if ($this->request->isPost()) {

            $busid = $this->request->param('ids');
 
            $business = $this->model->where(['id' => ['in', $busid]])->select();

            if (!$business) {
                $this->error('用户不存在');
                exit;
            }

            //获取请求中 row 名称的数组元素
            $adminid = $this->request->param('row/a');

            // 开启操作表的事务
            $this->model->startTrans();
            $this->ReceiveModel->startTrans();

            $BusinessData = [];

            $busid = explode(',', $busid);

            foreach ($busid as $item) {
                $BusinessData[] = [
                    'id' => $item,
                    'adminid' => $adminid['adminid']
                ];
            }

            $result = $this->model->saveAll($BusinessData);

            if (!$result) {
                $this->error('分配失败');
                exit;
            }

            $ReceiveData = [];

            foreach ($busid as $item) {
                $ReceiveData[] = [
                    'applyid' => $adminid['adminid'],
                    'status' => 'allot',
                    'busid' => $item
                ];
            }

            // 执行插入语句
            $ReceiveStatus = $this->ReceiveModel->saveAll($ReceiveData);

            if ($result === FALSE) {
                $this->model->rollback();
                $this->error(__('分配失败'));
                exit;
            }

            if ($result === FALSE || $ReceiveStatus === FALSE) {
                $this->ReceiveModel->rollback();
                $this->model->rollback();
                $this->error('分配失败');
                exit;
            } else {
                $this->model->commit();
                $this->ReceiveModel->commit();
                $this->success('分配成功');
                exit;
            }
        }
        return $this->view->fetch();
    }

    // 软删除
    public function del($ids = 0)
    {
        //查询多条要删除的语句
        $rows = $this->model->where(['id' => ['in', $ids]])->select();

        //判断查询语句
        if (!$rows) {
            $this->error('暂无数据');
            exit;
        }

        //有则直接软删除
        $result = $this->model->destroy($ids);

        if ($result === FALSE) {
            $this->error('删除用户失败');
            exit;
        } else {
            $this->success('删除成功');
            exit;
        }
    }
}
