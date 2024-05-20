<?php

namespace app\admin\controller\subject;

use app\common\controller\Backend;
//引入Tp的数据库类
use think\Db;

/**
 * 课程管理
 *
 * @icon fa fa-circle-o
 */
class Subject extends Backend
{

    /**
     * Subject模型对象
     * @var \app\common\model\Subject\Subject
     */
    protected $model = null;

    // 当前是否为关联查询
    protected $relationSearch = true;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Subject.Subject');
        $this->CategoryModel = model('Subject.Category');
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
                ->where($where)
                ->count();

            //查询数据
            $list = $this->model
                ->with(['category'])
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
     * 添加课程
     */
    public function add()
    {
        //判断是否有post请求
        if ($this->request->isPost()) {
            //获取请求中 row 名称的数组元素
            $params = $this->request->param('row/a');

            //插入语句
            $result = $this->model->validate('common/Subject/Subject')->save($params);

            if ($result === FALSE) {
                $this->error($this->model->getError());
                exit;
            } else {
                $this->success();
                exit;
            }
        }

        //查询所有的课程分类
        $catelist = $this->CategoryModel->order('weight asc')->column('id,name');

        //将生成好的select下拉框赋值到模板
        $this->view->assign('catelist', build_select('row[cateid]', $catelist, [], ['class' => 'form-control selectpicker']));

        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = 0)
    {
        //根据id查询课程存不存在
        $subject = $this->model->find($ids);

        if (!$subject) {
            $this->error('暂时没有课程');
            exit;
        }

        //判断是有post提交
        if ($this->request->isPost()) {
            //获取数据
            $params = $this->request->param('row/a');

            //将id添加到$params
            $params['id'] = $ids;

            //执行更新语句
            $result = $this->model->validate('common/Subject/Subject')->isUpdate(true)->save($params);

            if ($result === false) {
                $this->error($this->model->getError());
                exit;
            } else {
                //判断图片有没有上传，判断图片路劲是否相等
                if ($subject['thumbs'] != $params['thumbs']) {
                    @is_file("." . $subject['thumbs']) && @unlink("." . $subject['thumbs']);
                }
                $this->success();
                exit;
            }
        }

        //查询所有分类课程
        $catelist = $this->CategoryModel->order('weight asc')->column('id,name');

        //赋值到下拉模板
        $this->view->assign('catelist', build_select('row[cateid]', $catelist, $subject['cateid'], ['class' => 'form-control selectpicker']));

        //把课程赋值给模板
        $this->view->assign('subject', $subject);

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

        //有则直接软删除
        $result = $this->model->destroy($ids);

        if ($result === FALSE) {
            $this->error('删除课程失败');
            exit;
        } else {
            $this->success();
            exit;
        }
    }

    /**
     * 回收站
     */
    public function recyclebin()
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
                ->with(['category'])
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
     * 真实删除
     */
    public function destroy($ids = 0)
    {
        //查询删除的数据
        $rows = $this->model->onlyTrashed()->select($ids);

        if (!$rows) {
            $this->error('暂无删除的数据');
            exit;
        }

        //单独的查询图片字段
        $thumbs = $this->model->onlyTrashed()->where(['id' => ['in', $ids]])->column('thumbs');

        //去除空元素
        $thumbs = array_filter($thumbs);

        //先将数据删除了，然后在去删除图片
        $result = $this->model->destroy($ids, true);

        if ($result === FALSE) {
            $this->error('删除失败');
            exit;
        } else {
            //删除图片
            if (!empty($thumbs)) {
                foreach ($thumbs as $item) {
                    //先判断文件是否存在， 如果存在 再去做删除
                    @is_file("." . $item) && @unlink('.' . $item);
                }
            }

            $this->success();
            exit;
        }
    }

    /**
     * 还原
     */
    public function restore($ids = 0)
    {
        //执行更新语句
        $result = Db::name('subject')->where(['id' => ['in', $ids]])->update(['deletetime' => NULL]);

        //判断是否还原成功
        if ($result) {
            $this->success();
            exit;
        } else {
            $this->error(__('还原失败'));
            exit;
        }
    }
}
