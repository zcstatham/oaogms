<?php
/**
 * Created by PhpStorm.
 * MiniUser: Administrator
 * Date: 2018/12/8
 * Time: 21:35
 */

namespace app\admin\controller;
use think\Db;

/**
 * Class Publice
 * @title 报表
 * @package app\admin\controller
 */
class Publice extends Base
{
    /**
     * @title 小程序概况
     * @param int $id
     * @return mixed
     */
    public function index($id = 1){
        $list = model('Mini')->field('mid,name')->all();
        $map[] = ['create_timestamp','>=',date('Y-m-d 00:00:00',time())];
        $map[] = ['mid','=',$id];
        $data = array(
            'minis'=> $list,
            'cards'=> profile($map)
        );
        $this->assign($data);
        return $this->fetch('publice/report');
    }

    /**
     * 推广概况
     * @param int $id
     * @return mixed
     */
    public function channel($id = 1,$granularity='today'){
        $this->assign('list',$this->getChannelData($id = 1,$granularity='today'));
        return $this->fetch();
    }

    public function channelData($id = 1,$granularity='today',$type='register'){
        return json($this->getActiveChartData($id,$granularity,$type='register'));
    }

    public function getChannelData($id = 1,$granularity='today'){
        $dateformat = getDateMap($granularity);
        $list = Db::view('SysAdmin','sid,nickname')
            ->view('ChannelActive','aid,mid,name','SysAdmin.sid=ChannelActive.sid')->select();
        foreach ($list as &$item){
            $pmap = array(
                $dateformat[0],
                ['aid','=',$item['aid']]
            );
            $profile = profile($pmap);
            $item = array_merge($item,$profile);
        }
        return $list;
    }

    /**
     * 获取渠道统计
     * @param $id * 小程序id
     * @param $dateType * 统计区间
     * @param $type * 统计类型
     * @param null $sid * 渠道id
     */
    public function getActiveChartData($id,$dateType,$type,$sid = null){
        $dateformat = getDateMap($dateType);
        $timeFormat = $dateformat[1];
        $dateformat[0][0] = 'log.create_timestamp';
        $map[] = $dateformat[0];
        $charts = config('siteinfo.charts');
        foreach ($charts as $chartname=>$chartlabel) {
            $subsql  = Db::view('mini_log log',
                ['type', 'remark','aid',
                    'IFNULL(COUNT(log.id),0)' => 'count',
                    'DATE_FORMAT(log.create_timestamp, "' . $timeFormat . '")' => 'date'])
                ->view('channel_active active','name','log.aid=active.aid')
                ->view('mini','mid','mini.mid=active.mid')
                ->view('sys_admin admin','nickname','admin.sid=active.sid')
                ->where($map)
                ->group('log.aid,log.type')
                ->buildSql();
            if($dateType == 'today') {
                for ($i = 0; $i < 24; $i++) {
                    $d = str_pad($i, 2, "0", STR_PAD_LEFT);
                    $union[] = "SELECT 0,'$type','',0,'$d:00:00'";
                    $union[] = "SELECT 1,'$type','',0,'$d:00:00'";
                }
                $$chartname = Db::query('SELECT * FROM'.$subsql.'AS d GROUP BY d.date,d.aid');
            }else {
                $$chartname = db('calendar')
                    ->field('b.nickname,b.remark,IFNULL(b.count,0) as count,a.date')
                    ->alias('a')
                    ->join([$subsql => 'b'], 'a.date = b.date')
                    ->select();
            }
            $data[$chartname] = $this->formChannelChart($$chartname);
        }
        return $data;
    }

    public function chart($id=1,$granularity='today'){
        $data = $this->getChartData($id,$granularity);
        return json($data);
    }

    public function card($id){
        $data = profile($id);
        return json($data);
    }

    public function getChartData($id,$dateType,$map=[]){
        $dateformat = getDateMap($dateType);
        $timeFormat = $dateformat[1];
        $map[] = ['mid','=',$id];
        $map[] = $dateformat[0];
        $field = ['aid',
            'type', 'remark',
            'IFNULL(COUNT(*),0)' => 'count',
            'DATE_FORMAT( create_timestamp, "' . $timeFormat . '")' => 'date'];
        $group =  'aid,type,date';
        $charts = config('siteinfo.charts');
        foreach ($charts as $chartname=>$chartlabel) {
            if($dateType == 'today'){
                for($i = 0; $i < 24; $i++){
                    $d = str_pad($i,2,"0",STR_PAD_LEFT);
                    $union[]= "SELECT 0,'$chartname','',0,'$d:00:00'";
                    $union[]= "SELECT 1,'$chartname','',0,'$d:00:00'";
                }
                $subsql = db('mini_log')
                    ->where($map)
                    ->where('type',$chartname)
                    ->field($field)
                    ->group($group)
                    ->union($union)
                    ->buildSql();
                $$chartname = Db::query('SELECT * FROM'.$subsql.'AS d GROUP BY d.date,d.aid');
            }else {
                $subsql  = db('mini_log')
                    ->where($map)
                    ->where('type',$chartname)
                    ->field($field)
                    ->group($group)
                    ->buildSql();
                $$chartname = db('calendar')
                    ->field('b.sid,b.remark,IFNULL(b.count,0) as count,a.date')
                    ->alias('a')
                    ->join([$subsql=> 'b'], 'a.date = b.date')
                    ->select();
            }

            $data[$chartname] = $this->formChart($$chartname);
        }
        return $data;
    }

    private function formChart($list)
    {
        $data = array(
            'labels'=>[],
            'datasets'=>array(
                array(
                    'data'=>[],
                    'label'=>'oao',
                    'borderColor'=>'#3e95cd',
                    'fill'=>false,
                ),
                array(
                    'data'=>[],
                    'label'=>'渠道',
                    'borderColor'=>'#8e5ea2',
                    'fill'=>false,
                ),
                array(
                    'data'=>[],
                    'label'=>'总计',
                    'borderColor'=>'#28a745',
                    'fill'=>false,
                )
            ),
        );
        foreach ($list as $item){
            if(!in_array($item['date'], $data['labels'])){
                $data['labels'][] = $item['date'];
                $data['datasets'][2]['data'][$item['date']] = 0;
            }
            if($item['aid'] == 0){
                $data['datasets'][0]['data'][$item['date']] = $item['count'];
            }else{
                $data['datasets'][1]['data'][$item['date']] = $item['count'];
            }
            $data['datasets'][2]['data'][$item['date']] += $item['count'];
        }
        foreach ($data['datasets'] as &$v){
            $v['data'] = array_values($v['data']);
        }
        return $data;
    }
    private function formChannelChart($list)
    {
        $data = array(
            'labels'=>[],
            'datasets'=>array(
            )
        );
        foreach ($list as $item) {
            if(!in_array($item['nickname'], $data['labels'])){
                $data['labels'][$item['aid']] = $item['nickname'];
            }
            if($item['aid'] != 0){
                $data['datasets'][$item['aid']]['data'][$item['date']] = [$item['count'],$item['name']];
            }
        }
        foreach ($data['datasets'] as &$v){
            $v['data'] = array_values($v['data']);
        }
        return $data;
    }
}