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
        $this->assign('cards',profile($id));
        return $this->fetch('publice/report');
    }

    /**
     * 推广概况
     * @param int $id
     * @return mixed
     */
    public function channel($id = 1,$granularity='today'){
        //各项数据表
        $sid = getUserType();
        if($sid == 1){
            $map[] = ['sid','<>',1];
        }else{
            $map[] = ['sid','=',$sid];
        }
        $list = $this->getChartData($id,$granularity,$map);


        //渠道数据概况
        $this->assign('cards',profile($id));
        return $this->fetch();
    }

    public function chart($id=1,$granularity='today'){
        $data = $this->getChartData($id,$granularity);
        return json($data);
    }

    private function card($id){
        $data = profile($id);
        return json($data);
    }

    public function getChartData($id,$dateType,$map=[]){
        $dateformat = getDateMap($dateType);
        $timeFormat = $dateformat[1];
        $map[] = ['mid','=',$id];
        $map[] = $dateformat[0];
        $field = ['STRCMP(`sid`,1)' => 'sid',
            'type', 'remark',
            'IFNULL(COUNT(*),0)' => 'count',
            'DATE_FORMAT( create_timestamp, "' . $timeFormat . '")' => 'date'];
        $group =  'sid,type,date';
        $charts = config('siteinfo.charts');
        foreach ($charts as $chartname=>$chartlabel) {
            if($dateType == 'today'){
                for($i = 0; $i < 24; $i++){
                    $d = str_pad($i,2,"0",STR_PAD_LEFT);
                    $union[]= "SELECT 0,'$chartname','',0,'$d:00:00'";
                    $union[]= "SELECT 1,'$chartname','',0,'$d:00:00'";
                }
                $subsql = db('mini_action_log')
                    ->where($map)
                    ->where('type',$chartname)
                    ->field($field)
                    ->group($group)
                    ->union($union)
                    ->buildSql();
                $$chartname = Db::query('SELECT * FROM'.$subsql.'AS d GROUP BY d.date,d.sid');
            }else {
                $subsql  = db('mini_action_log')
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
            if($item['sid'] == 0){
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
}