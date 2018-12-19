<?php
/**
 * Created by PhpStorm.
 * MiniUser: Administrator
 * Date: 2018/12/8
 * Time: 21:35
 */

namespace app\admin\controller;

/**
 * Class Publice
 * @title 报表
 * @package app\admin\controller
 */
class Publice extends Base
{
    public function index(){
//        $data = array(
//            '__Menu__'=> $this->setMenu(),
//            'data'=> ''
//        );
        return $this->fetch('publice/report');
    }

    public function chart($id=1,$granularity='month'){
        $timeFormat = '%Y-%m-%d';
        $map[] = ['mid','=',$id];
        switch ($granularity){
            case 'week':
                $map[] = ['create_timestamp','between',[date('Y-m-d 00:00:00',strtotime("-7 day")),date('Y-m-d H:m:s',time())]];
                break;
            case 'month':
                $map[] = ['create_timestamp','between',[date('Y-m-01 00:00:00',time()),date('Y-m-d H:m:s',time())]];
                break;
            case 'all':
                break;
            default:
                $map[] = ['create_timestamp','between',[date('Y-m-d 00:00:00',time()),date('Y-m-d H:m:s',time())]];
                $timeFormat = '%Y-%m-%d %H:00:00';
        }
        $list = db('mini_action_log')
            ->field(['sid','type','remark','COUNT(*)'=>'count','DATE_FORMAT( create_timestamp, "'.$timeFormat.'")'=>'date'])
            ->group('type,date')
            ->where($map)
            ->select();
        $charts = config('siteinfo.charts');
        foreach ($list as $item){
            foreach ($charts as $chartname){
                $$chartname['labels'][] = $item['date'];
                $flip = array_flip($$chartname['labels']);
                if($item['sid'] == 1 && $item['type'] == $chartname){
                    $$chartname['datasets'][0]['data'][$flip[$item['date']]] = $item['count'];
                    $$chartname['datasets'][0]['label'] = 'oao';
                    $$chartname['datasets'][0]['borderColor'] = '#3e95cd';
                    $$chartname['datasets'][0]['fill'] = false;
                }else if($item['type'] == $chartname){
                    $$chartname['datasets'][1]['data'][$flip[$item['date']]] = $item['count'];
                    $$chartname['datasets'][1]['label'] = '渠道';
                    $$chartname['datasets'][1]['borderColor'] = '#8e5ea2';
                    $$chartname['datasets'][1]['fill'] = false;
                }
            }
        }
        foreach ($charts as $chartname) {
            $$chartname['labels'] = array_values(
                array_flip(array_flip($$chartname['labels'])));
            $data[$chartname] = $$chartname;
        }
        return json($data);
    }

    protected function setMenu()
    {
        $menu       = array(
            'own'  => array(),
            'channel' => array(),
        );
        if (getMiniGroup() != 1) {
            $ownlist = model('Mini')->where('sid',session('user_auth.sid'))->all();
            $channellist = model('Mini')->where('sid',1)->all();
        } else {
            $ownlist = model('Mini')->where('sid',1)->all();
            $channellist = model('Mini')->where('sid','<>',1)->all();
        }
        foreach ($ownlist as $key => $value) {
            //此处用来做权限判断
            if (IS_ROOT || $this->checkRule($value['url'], 2, null)) {
                if ($controller == $value['url']) {
                    $value['style'] = "active";
                }
                $menu['main'][$value['nid']] = $value;
            }
        }
        foreach ($channellist as $key => $value) {
            //此处用来做权限判断
            if (IS_ROOT || $this->checkRule($value['url'], 2, null)) {
                if ($controller == $value['url']) {
                    $value['style'] = "active";
                }
                $menu['main'][$value['nid']] = $value;
            }
        }

        // 查找当前子菜单
        $pid = db('menu')->where("pid !=0 AND url like '%{$hover_url}%'")->value('pid');
        $id  = db('menu')->where("pid = 0 AND url like '%{$hover_url}%'")->value('nid');
        $pid = $pid ? $pid : $id;
        if ($pid) {
            $map['pid']  = $pid;
            $map['hide'] = 0;
            $map['type'] = 'admin';
            $row = db('menu')->field("nid,title,url,icon,`group`,pid,'' as style")->where($map)->order('sort asc')->select();
            foreach ($row as $key => $value) {
                if (IS_ROOT || $this->checkRule($value['url'], 2, null) || 'test') {
                    if ($controller == $value['url']) {
                        $menu['main'][$value['pid']]['style'] = "active";
                        $value['style']                       = "active";
                    }
                    $menu['child'][] = $value;
                }
            }
        }
        Container::get('app')['view']->assign('__menu__', $menu);
    }
}