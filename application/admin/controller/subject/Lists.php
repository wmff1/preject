<?php

namespace app\admin\controller\subject;

use app\common\controller\Backend;

//引入Tp的数据库类
use think\Db;

/**
 * 课程章节管理
 *
 * @icon fa fa-circle-o
 */
class Lists extends Backend
{

    /**
     * Lists模型对象
     * @var \app\common\model\Subject\Lists
     */
    protected $model = null;

    // 当前是否为关联查询
    protected $relationSearch = true;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Subject.Lists');
        $this->SubjectModel = model('Subject.Subject');
    }

    /**
     * 查看
     */
    public function index($ids = 0)
    {
        $this->request->filter(['strip_tags']);
        if ($this->request->isAjax())
        {
            if ($this->request->request('keyField'))
            {
                return $this->selectpage();
            }

            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            
            //查询总数
            $total = $this->model
                    ->where($where)
                    ->where(['subid' => $ids])
                    ->count();

            //查询数据
            $list = $this->model
                    ->where($where)
                    ->where(['subid' => $ids])
                    ->order($sort, $order)
                    ->limit($offset, $limit)
                    ->select();

            $result = array("total" => $total, "rows" => $list);

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add($subid = 0)
    {
        //判断是否有post请求
        if($this->request->isPost())
        {
            $params = $this->request->param('row/a');

            //补充课程id
            $params['subid'] = $subid;

            //插入语句
            $result = $this->model->validate('common/Subject/Lists')->save($params);

            if($result === FALSE)
            {
                $this->error($this->model->getError());
                exit;
            }else
            {
                $this->success('添加课程成功');
                exit;
            }
        }

        return $this->view->fetch();
    }

    /**
     * 编辑
     */
    public function edit($ids = 0)
    {
        //一进来，先要根据id查询这个课程是否存在
        $rows = $this->model->find($ids);

        if(!$rows)
        {
            $this->error('课程章节不存在');
            exit;
        }

        //判断是否有post提交
        if($this->request->isPost())
        {
            //接收数据
            $params = $this->request->param('row/a');

            //将id要补录到数据中
            $params['id'] = $ids;
            $params['subid'] = $rows['subid'];

            //更新操作
            $result = $this->model->validate('common/Subject/Lists')->isUpdate(true)->save($params);

            if($result === FALSE)
            {
                $this->error($this->model->getError());
                exit;
            }else
            {
                //判断有没有新图片上传
                //旧的图片路径 和 表单中提交过来的图片路径 不一样就说明换图片了
                if($rows['url'] != $params['url'])
                {
                    @is_file(".".$rows['url']) && @unlink(".".$rows['url']);
                }

                $this->success();
                exit;
            }

            var_dump($params);
            exit;
        }

        //把课程赋值给模板
        $this->view->assign('rows', $rows);

        return $this->view->fetch();
    }

    /**
     * 删除方法
     */
    public function del($ids = 0)
    {
        $rows = $this->model->select($ids);

        if(!$rows)
        {
            $this->error('暂无数据');
            exit;
        }

        //只查询某个字段数据
        $url = $this->model->where(['id' => ['in', $ids]])->column('url');

        //去除空元素
        $url = array_filter($url);

        //直接软删除
        $result = $this->model->destroy($ids);

        if($result === FALSE)
        {
            $this->error('删除课程章节失败');
            exit;
        }else
        {
            if(!empty($url))
            {
                foreach($url as $item)
                {
                    @is_file(".".$item) && @unlink(".".$item);
                }
            }

            $this->success();
            exit;
        }
    }
}