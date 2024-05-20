<?php

namespace app\admin\controller\business;

use app\common\controller\Backend;
use think\Db;

/**
 * 客户管理
 *
 * @icon fa fa-circle-o
 */
class Lists extends Backend
{

    /**
     * Business模型对象
     * @var \app\common\model\app\common\model\Business\Business
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Business.Business');
        $this->VisitModel = model('Business.Visit');
        $this->AdminModel = model('Admin');
        $this->ReceiveModel = model('Business.Receive');
        // $this->RegionModel = model('Region');
        // $this->SourceModel = model('business.source');
    }

    /**
     * 查看资料
     */
    public function info($ids = 0)
    {

        $adminid = $this->auth->id;

        $where = [
            'adminid' => $adminid
        ];

        $business = $this->model
            ->with(['adminName', 'source'])
            ->where($where)
            ->find($ids);

        if (!$business) {
            $this->error('用户不存在');
            exit;
        }

        $this->view->assign('info', $business);

        return $this->view->fetch();
    }


    /**
     * 回访列表
     */
    public function visit($ids = 0)
    {
        if ($this->request->isAjax()) {
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }

            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            //查询总数
            $total = $this->model
                ->where($where)
                ->count();

            //查询数据
            $list = $this->VisitModel
                ->with(['admin', 'business'])
                ->where(['busid' => $ids])
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 申请记录
     */
    public function receive($ids = 0)
    {
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax()) {
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }

            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            //查询总数
            $total = $this->ReceiveModel
                ->where($where)
                ->count();

            //查询数据
            $list = $this->ReceiveModel
                ->with(['apply', 'business'])
                ->where(['busid' => $ids])
                ->order($sort, $order)
                ->limit($offset, $limit)
                ->select();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 回访记录添加
     */
    public function add($ids = 0)
    {
        $busid = $this->model->where(['id' => $ids])->column('id,nickname');

        $this->view->assign('busid', build_select('row[busid]', $busid, [], ['class' => 'form-control selectpicker']));

        $adminid = $this->auth->id;

        $admin = $this->AdminModel->where(['id' => $adminid])->order('id asc')->column('id,nickname');

        $this->view->assign('adminlist', build_select('row[adminid]', $admin, [], ['class' => 'form-control selectpicker']));

        if ($this->request->isPost()) {
            $rows = $this->request->param('row/a');

            // 插入语句
            $result = $this->VisitModel->save($rows);

            if (!$result) {
                $this->error('添加失败');
                exit;
            } else {
                $this->success('添加成功');
                exit;
            }
        }

        return $this->view->fetch();
    }

    /**
     * 回访记录编辑
     */
    public function edit($ids = 0)
    {
        $bus = $this->VisitModel->with('business')->find($ids);

        if (!$bus) {
            $this->error('回访记录不存在');
            exit;
        }
        $business = $this->model
            ->where(['adminid' => $this->auth->id])
            ->order('id asc')
            ->column('id,nickname');

        // 生成下拉框
        $this->view->assign('busid', build_select('row[busid]', $business, $bus['busid'], ['class' => 'form-control selectpicker']));

        if ($this->request->isPost()) {
            $rows = $this->request->param('row/a');

            $rows['content'] = strip_tags($rows['content']);

            // 组装数据
            $rows['adminid'] = $this->auth->id;

            // 更新语句
            $result = $this->VisitModel->where(['id' => $ids])->update($rows);

            if (!$result) {
                $this->error('编辑失败');
                exit;
            } else {
                $this->success('编辑成功');
                exit;
            }
        }

        return $this->view->fetch();
    }

    /**
     * 回访记录真实删除
     */
    public function del($ids = 0)
    {

        //查询多条要删除的语句
        $business = $this->VisitModel->where(['id' => ['in', $ids]])->select();

        //判断查询语句
        if (!$business) {
            $this->error('用户不存在');
            exit;
        }

        //删除
        $result = $this->VisitModel->destroy($ids);

        if ($result === FALSE) {
            $this->error('删除用户失败');
            exit;
        } else {
            $this->success('删除成功');
            exit;
        }
    }

    /**
     * 申请记录真实删除
     */
    public function dels($ids = 0)
    {

        $bus = $this->ReceiveModel->where(['id' => ['in', $ids]])->select($ids);

        if (!$bus) {
            $this->error('申请记录不存在');
            exit;
        }

        $result = $this->ReceiveModel->destroy($ids);

        if ($result === FALSE) {
            $this->error('删除失败');
            exit;
        } else {
            $this->success('删除成功');
            exit;
        }
    }
}
