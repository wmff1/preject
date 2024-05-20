<?php

namespace app\admin\controller\business;

use app\common\controller\Backend;
use think\Db;

/**
 * 客户管理
 *
 * @icon fa fa-circle-o
 */
class Privatesea extends Backend
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
        $this->RegionModel = model('Region');
        $this->AdminModel = model('Admin');
        $this->SourceModel = model('Business.Source');
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

            $adminid = $this->auth->id;

            $where = [
                'adminid' => $adminid
            ];

            //查询总数
            $total = $this->model
                ->where($where)
                ->count();

            //查询数据
            $list = $this->model
                ->with(['adminName', 'source'])
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
     * 添加客户
     */
    public function add()
    {
        if ($this->request->isPost()) {
            // 获取请求中的数据
            $param = $this->request->param('row/a');

            // 判断密码是否一致
            if ($param['password'] != $param['repass']) {
                $this->error('密码不一致');
                exit;
            }

            // 生成密码盐
            $param['salt'] = build_ranstr();

            // 加密密码
            $param['password'] = md5($param['password'] . $param['salt']);

            // 邀请码
            $param['invitecode'] = $param['salt'];

            //判断地区是否为空
            if (!empty($param['region'])) {
                //字符串转换为数组
                $param['region'] = explode('/', $param['region']);
                // 获取数组最后一个值
                $last = array_pop($param['region']);
                //查找行政编码
                $pathcode = $this->RegionModel->where(['name' => $last])->value('parentpath');
                //将字符串转换为数组
                $param['region'] = explode(',', $pathcode);

                $param['province'] = isset($param['region'][0]) ? $param['region'][0] : '';
                $param['city'] = isset($param['region'][1]) ? $param['region'][1] : '';
                $param['district'] = isset($param['region'][2]) ? $param['region'][2] : '';
            }

            //判断是否有文件上传
            if (isset($_FILES['avatar']) && $_FILES['avatar']['size']) {

                //调用公共方法
                $success = build_upload('avatar');

                if ($success['result']) {
                    //上传成功
                    $param['avatar'] = $success['data'];
                } else {
                    //上传失败
                    $this->error($success['msg']);
                    exit;
                }
            }

            // 插入语句
            $result = $this->model->validate('common/Business/Business')->save($param);

            if ($result === FALSE) {
                $this->error($this->model->getError());
                exit;
            } else {
                $this->success('添加成功');
                exit;
            }
        }

        $adminid = $this->auth->id;
        // 查询当前管理员名称
        $admin = $this->AdminModel->where(['id' => $adminid])->order('id asc')->column('id,nickname');

        $this->view->assign('adminlist', build_select('row[adminid]', $admin, [], ['class' => 'form-control selectpicker']));

        // 查询所有客户来源
        $source = $this->SourceModel->order('id asc')->column('id,name');

        // 将生成好的select下拉框赋值到模板
        $this->view->assign('sourcelist', build_select('row[sourceid]', $source, [], ['class' => 'form-control selectpicker']));


        return $this->view->fetch();
    }

    /**
     * 编辑客户
     */
    public function edit($ids = 0)
    {
        if ($this->request->isPost()) {
        }

        // 获取ids查找数据
        $business = $this->model->find($ids);

        if (!$business) {
            $this->error('用户不存在');
            exit;
        }

        $adminid = $this->auth->id;

        // 查询当前管理员名称
        $admin = $this->AdminModel->where(['id' => $adminid])->order('id asc')->column('id,nickname');

        $this->view->assign('adminlist', build_select('row[adminid]', $admin, [], ['class' => 'form-control selectpicker']));

        // 查询所有客户来源
        $source = $this->SourceModel->order('id asc')->column('id,name');

        // 将生成好的select下拉框赋值到模板
        $this->view->assign('sourcelist', build_select('row[sourceid]', $source, $business['sourceid'], ['class' => 'form-control selectpicker']));

        $this->view->assign('business', $business);

        return $this->view->fetch();
    }

    /**
     * 软删除
     */
    public function del($ids = 0)
    {
        //查询多条要删除的语句
        $rows = $this->model->where(['id' => ['in', $ids]])->select();

        //判断查询语句
        if (!$rows) {
            $this->error('暂无数据');
            exit;
        }

        //软删除
        $result = $this->model->destroy($ids);

        if ($result === FALSE) {
            $this->error('删除用户失败');
            exit;
        } else {
            $this->success('删除成功');
            exit;
        }
    }

    /**
     * 回收
     */
    public function recovery($ids = 0)
    {
        // 判断用户是否存在
        $business = $this->model->where(['id' => ['in', $ids]])->select();

        if (!$business) {
            $this->error('用户不存在');
            exit;
        }

        // 判断管理员是否存在
        $adminid =  $this->auth->id;

        $admin = $this->AdminModel->find($adminid);

        if (!$admin) {
            $this->error('管理员不存在');
            exit;
        }

        // 开启事务
        $this->model->startTrans();
        $this->ReceiveModel->startTrans();

        // 更新用户表
        $result = $this->model->where(['id' => ['in', $ids]])->update(['adminid' => NULL]);

        if ($result === FALSE) {
            $this->error('回收失败');
            exit;
        }

        $ids = explode(',', $ids);

        $BusinessData = [];

        foreach ($ids as $item) {
            $BusinessData[] = [
                'applyid' => $adminid,
                'status' => 'recovery',
                'busid' => $item
            ];
        }

        // 领取表更新
        $ReceiveStatus = $this->ReceiveModel->saveAll($BusinessData);

        if ($ReceiveStatus === FALSE) {
            $this->model->rollback();
            $this->error('回收失败');
            exit;
        }

        if ($result === FALSE || $ReceiveStatus === FALSE) {
            $this->ReceiveModel->rollback();
            $this->model->rollback();
            $this->error('回收失败');
            exit;
        } else {
            $this->model->commit();
            $this->ReceiveModel->commit();
            $this->success('回收成功');
            exit;
        }
    }
}
