<?php
/**
 * Created by PhpStorm.
 * MiniUser: Administrator
 * Date: 2018/12/8
 * Time: 21:35
 */

namespace app\admin\controller;

use think\Db;
use think\facade\Log;

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
    public function index($id = 1)
    {
        $list = model('Mini')->field('mid,name')->where('status', '=', 1)->all();
        $map[] = ['create_timestamp', '>=', date('Y-m-d 00:00:00', time())];
        $map[] = ['mid', '=', $id];
        $data = array(
            'minis' => $list,
            'cards' => profile($map)
        );
        $this->assign($data);
        return $this->fetch('publice/report');
    }

    /**
     * @title 推广概况
     * @param int $id
     * @return mixed
     */
    public function channel()
    {
        $this->assign('minis', model('Mini')
            ->field('mid,name')
            ->where('status', '=', 1)->all());
        return $this->fetch();
    }

    /**
     * @title 获取渠道数据
     * @param int $id
     * @param string $dateType
     * @return \think\response\Json
     */
    public function channelData($id = 1, $granularity = 'today')
    {
        return json($this->getChannelData($id, $granularity));
    }

    /**
     * @title 获取渠道列表信息
     * @param int $id
     * @param string $granularity
     * @return \think\response\Json
     */
    public function channelChartData($id = 1, $granularity = 'today')
    {
        return json($this->getActiveChartData($id, $granularity));
    }

    public function getChannelData($id = 1, $granularity = 'today')
    {
        $dateformat = getDateMap($granularity);
        $sid = session('user_auth.sid');
        $map[] = $sid > 10 ? ['SysAdmin.sid', '=', $sid] : ['SysAdmin.sid', '>', 10];
        $list = Db::view('SysAdmin', 'sid,nickname')
            ->view('ChannelActive', 'aid,mid,name', 'SysAdmin.sid=ChannelActive.sid')
            ->where($map)->select();
        foreach ($list as &$item) {
            $pmap = array(
                $dateformat[0],
                ['aid', '=', $item['aid']],
                ['mid', '=', $id]
            );
            $profile = profile($pmap);
            $item = array_merge($item, $profile);
        }
        return $list;
    }

    public function getActiveChartData($id, $dateType)
    {
        $smap = [];
        $sid = session('user_auth.sid');
        if ($sid > 10) {
            $aid = db('channel_active')->field('aid')->where('sid', $sid)->select();
            $map[] = ['log.aid', 'in', $aid[0]];
            $smap = [['active.aid', 'in', $aid[0]]];
        }
        $dateformat = getDateMap($dateType);
        $dateformat[0][0] = 'log.create_timestamp';
        $map[] = $dateformat[0];
        $charts = config('siteinfo.charts');
        foreach ($charts as $chartname => $chartlabel) {
            $subsql = db('mini_log')
                ->alias('log')
                ->field('type,aid,IFNULL(COUNT(id), 0) AS count')
                ->where($map)
                ->where('mid', '=', $id)
                ->where('type', $chartname)
                ->group('aid')->buildSql();

            $$chartname = db('channel_active active')
                ->field("active.aid,active.name 'aname',mini.name 'mname',admin.nickname,IFNULL(b.type,'$chartname') type,IFNULL(b.count,0) count")
                ->join('mini','mini.mid=active.mid')
                ->join('sys_admin admin','admin.sid=active.sid')
                ->join([$subsql=> 'b'],'b.aid = active.aid','LEFT')
                ->where($smap)
                ->where('active.status',1)
                ->select();
            $data[$chartname] = $this->formChannelChart($$chartname);
        }
        return $data;
    }

    public function chart($id = 1, $granularity = 'today')
    {
        $data = $this->getChartData($id, $granularity);
        return json($data);
    }

    public function card($id, $granularity = 'today')
    {
        $dateformat = getDateMap($granularity);
        $map[] = $dateformat[0];
        $map[] = ['mid', '=', $id];
        $data = profile($map);
        return json($data);
    }

    public function getChartData($id, $dateType, $map = [])
    {
        $dateformat = getDateMap($dateType);
        $timeFormat = $dateformat[1];
        $map[] = ['mid', '=', $id];
        $map[] = $dateformat[0];
        $subsqlmap[] = ['mid', '=', $id];
        $calendarmap = $dateformat[0];
        $calendarmap[0] = 'date';
        $field = ['aid',
            'type', 'remark',
            'IFNULL(COUNT(*),0)' => 'count',
            'DATE_FORMAT( create_timestamp, "' . $timeFormat . '")' => 'date'];
        $group = 'aid,type,date';
        $charts = config('siteinfo.charts');
        foreach ($charts as $chartname => $chartlabel) {
            if ($dateType == 'today' || $dateType == 'yesterday') {
                for ($i = 0; $i < 24; $i++) {
                    $d = str_pad($i, 2, "0", STR_PAD_LEFT);
                    $union[] = "SELECT 0,'$chartname','',0,'$d:00:00'";
                    $union[] = "SELECT 1,'$chartname','',0,'$d:00:00'";
                }
                $subsql = db('mini_log')
                    ->where($map)
                    ->where('type', $chartname)
                    ->field($field)
                    ->group($group)
                    ->union($union)
                    ->buildSql();
                $$chartname = Db::query('SELECT * FROM' . $subsql . 'AS d GROUP BY d.date,d.aid ORDER BY d.date');
            } else {
                $subsql = db('mini_log')
                    ->where($subsqlmap)
                    ->where('type', $chartname)
                    ->field($field)
                    ->group($group)
                    ->buildSql();
                $calendar = db('calendar')->where([$calendarmap])->buildSql();
                Log::write([$calendar, $subsql]);
                $$chartname = Db::query('SELECT IFNULL(s.aid,0) as aid,IFNULL(s.remark,\'\') as remark,IFNULL(s.count,0) as count,c.date FROM' . $calendar . 'AS c LEFT JOIN' . $subsql . 'AS s on s.date=c.date GROUP BY s.date,s.aid ORDER BY s.date');
            }

            $data[$chartname] = $this->formChart($$chartname);
        }
        return $data;
    }

    private function formChart($list)
    {
        $data = array(
            'labels' => [],
            'datasets' => array(
                array(
                    'data' => [],
                    'label' => 'oao',
                    'borderColor' => '#3e95cd',
                    'fill' => false,
                ),
                array(
                    'data' => [],
                    'label' => '渠道',
                    'borderColor' => '#8e5ea2',
                    'fill' => false,
                ),
                array(
                    'data' => [],
                    'label' => '总计',
                    'borderColor' => '#28a745',
                    'fill' => false,
                )
            ),
        );
        foreach ($list as $item) {
            if (!in_array($item['date'], $data['labels'])) {
                $data['labels'][] = $item['date'];
                $data['datasets'][2]['data'][$item['date']] = 0;
            }
            if ($item['aid'] == 0) {
                $data['datasets'][0]['data'][$item['date']] = $item['count'];
            } else {
                $data['datasets'][1]['data'][$item['date']] = $item['count'];
            }
            $data['datasets'][2]['data'][$item['date']] += $item['count'];
        }
        foreach ($data['datasets'] as &$v) {
            $v['data'] = array_values($v['data']);
        }
        return $data;
    }

    private function formChannelChart($list)
    {
        $data = array(
            'labels' => [],
            'datasets' => array()
        );
        foreach ($list as $item) {
            if (!in_array($item['nickname'], $data['labels'])) {
                $data['labels'][$item['aid']] = $item['nickname'];
            }
            if ($item['aid'] != 0) {
                $data['datasets'][$item['aid']] = [$item['count'], $item['aname']];
            }
        }
        $data['labels'] = array_values($data['labels']);
        $data['datasets'] = array_values($data['datasets']);
        return $data;
    }
}