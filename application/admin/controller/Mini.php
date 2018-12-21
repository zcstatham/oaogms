<?php
/**
 * Created by PhpStorm.
 * MiniUser: Administrator
 * Date: 2018/12/8
 * Time: 21:35
 */

namespace app\admin\controller;

use app\common\model\ChannelActive;
use think\Db;

/**
 * Class Mini
 * @title 小程序
 * @package app\admin\controller
 */
class Mini extends Base
{

    protected $model;
    protected $miniGroup;

    protected $beforeActionList = ['beforeMethod'];

    protected function beforeMethod()
    {
        $this->model = model('Mini');
        $this->miniGroup = getUserType();
    }

    /**
     * @title 小程序列表
     * @return mixed
     */
    public function index()
    {
        $list = $this->model->order('mid')
            ->paginate(config('siteinfo.list_rows'), false);
        $data = array(
            'group' => $this->miniGroup,
            'list' => $list,
            'page' => $list->render(),
            'keyList' => $this->model->keyList
        );
        $this->setMeta('小程序列表');
        $this->assign($data);
        return $this->fetch('mini/index');
    }

    /**
     * @title 渠道小程序
     * @return mixed
     */
    public function channel()
    {
        $list = Db::view('ChannelActive', ['aid' => 'id', 'name', 'path', 'create_timestamp'])
            ->view('SysAdmin', ['nickname' => 'sname'], 'ChannelActive.sid=SysAdmin.sid')
            ->view('Mini', ['name' => 'mname'], 'ChannelActive.mid=Mini.mid')
            ->paginate(config('siteinfo.list_rows'), false);
        $data = array(
            'list' => $list,
            'page' => $list->render(),
        );
        $this->setMeta('推广活动');
        $this->assign($data);
        return $this->fetch();
    }

    private function miniList($map, $isPaginate = false)
    {
        $order = ['mid' => 'desc'];
        $list = $isPaginate ?
            $this->model->has('bindInfo', '<', 10, 'meid', 'LEFT')->where($map)->order($order)->paginate(config('siteinfo.list_rows'), false) :
            $this->model->has('bindInfo', '<', 10, 'meid', 'LEFT')->where($map)->order($order)->select();
        //绑定列表
        foreach ($list as $minis) {
            $binds = $minis->bindInfo;
            if (!$binds->isEmpty()) {
                $ids = [];
                foreach ($binds as $bind) {
                    $ids[] = abs($bind['bindid']);
                }
                $minis['bindList'] = $this->model->all($ids);
                foreach ($minis['bindList'] as $mini) {
                    if ($mini['sid'] == 1) {
                        $mini['channel'] = 'OAOGMES';
                    } else {
                        $mini['channel'] = $mini->channelInfo->nickname;
                    }
                }
            }
            $minis['bindList'] = [];
        }
        return $list;
    }

    /**
     * @title 新增小程序
     * @author yangweijie <yangweijiester@gmail.com>
     */
    public function addMini()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $data['sid'] = $this->miniGroup;
            $valid = $this->validate($data, 'Mini');
            if ($valid !== true) {
                $this->error('验证失败：' . $valid);
            }
            $result = $this->model->save($data);
            if ($result) {
                $this->success('新增成功', 'admin/mini/index');
            } else {
                $this->error('新增失败');
            }
        } else {
            $data = array(
                'keyList' => $this->model->keyList,
            );
            $this->assign($data);
            $this->setMeta('新增小程序');
            return $this->fetch('public/edit');
        }
        return false;
    }

    /**
     * @title 编辑小程序
     * @param int $id
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function editMini($id = 0)
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            try {
                $this->model->save($data, array('mid' => $data['id']));
                $this->success('更新成功', 'admin/mini/index');
            } catch (\think\Exception\DbException $e) {
                $this->error('更新失败：' . $e->getMessage());
            }
        } else {
            try {
                $info = $this->model->get($id);
            } catch (\think\Exception $e) {
                $this->error('获取后台小程序信息错误：' . $e->getMessage());
            }
            $data = array(
                'info' => $info,
                'keyList' => $this->model->keyList,
            );
            $this->assign($data);
            $this->setMeta('编辑后台小程序');
            return $this->fetch('public/edit');
        }
        return false;
    }

    /**
     * @title 删除小程序
     * @param $id
     */
    public function delMini($id)
    {
        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }
        if ($this->model->where('mid', $id)->delete()) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

    public function addActive()
    {
        if ($this->request->isPost()) {
            $data = $this->request->param();
            $params = getSurveyParam($data['mid'], $data['sid']);
            $data['path'] = $data['path'] ?: 'pages/index/index';
            $data['path'] = stripos($data['path'], '?') === false ? $data['path'] . '&' . $params : $data['path'] . '？' . $params;
            $result = model('ChannelActive')->save($data);
            if ($result) {
                $this->success('新增成功', 'admin/mini/channel');
            } else {
                $this->error('新增失败');
            }
        } else {
            $data = array(
                'keyList' => ChannelActive::getKeyList(),
            );
            $this->assign($data);
            $this->setMeta('新增推广活动');
            return $this->fetch('public/edit');
        }
        return false;
    }

    public function delActive($id)
    {
        if (empty($id)) {
            $this->error('请选择要操作的数据!');
        }
        if (model('ChannelActive')->where('aid', $id)->delete()) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败！');
        }
    }

    /**
     * @title 绑定小程序
     * @param $id
     * @param $status
     * @return bool
     */
    public function bindMini($id, $bindid)
    {
        //绑定小程序
        $result = model('MiniExtend')->save(['mid' => $id, 'bindid' => $bindid]);
        if ($result) {
            $this->success('申请成功', 'admin/mini/index');
        }
        return false;
    }

    /**
     * @title 申请绑定小程序
     * @param $id
     * @return bool
     */
    public function applyBind($id)
    {
        $list = $this->model->get($id);
        $status = session('user_auth.sid');
        $ids = [];
        foreach (explode('|', $list['status']) as $id) {
            $ids[] = abs($id);
        }
        if (!in_array($status, $ids) || empty($ids)) {
            $list['status'] .= '|+' . $status;
        }
        $this->success('申请成功，请等待审核');
        return false;
    }


    /**
     * @title 小程序详细信息
     * @return bool
     */
    public function moreInfo($id)
    {
        //小程序信息
        $list = $this->model->cache(true)->get($id);
        $bind = $list['status'];
        if ($bind) {
            foreach ($bind as $mid) {
                $ids[] = abs($mid);
            }
            $bindInfo = $this->model->all($ids);
            foreach ($bindInfo as $key => $mini) {
                if (in_array('-' . $mini['id'], $mid, true)) {
                    $unbind[] = $mini;
                } else if (in_array('+' . $mini['id'], $mid, true)) {
                    $bind[] = $mini;
                } else if (in_array('' . $mini['id'], $mid, true)) {
                    $binding[] = $mini;
                }
            }
            $data = array(
                'list' => $list,
                'bind' => array(
                    array('title' => '申请中', 'list' => $binding),
                    array('title' => '已绑定', 'list' => $bind),
                    array('title' => '已解绑', 'list' => $unbind)
                )
            );
        } else {
            $data = array(
                'list' => $list,
                'bind' => array(
                    array('title' => '申请中', 'list' => []),
                    array('title' => '已绑定', 'list' => []),
                    array('title' => '已解绑', 'list' => [])
                )
            );
        }
        $this->setMeta('小程序详细信息');
        $this->assign($data);
        return $this->fetch();
    }
}