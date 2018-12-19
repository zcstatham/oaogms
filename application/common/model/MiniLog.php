<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/18
 * Time: 21:30
 */

namespace app\common\model;


use think\facade\Request;

class MiniLog extends Base
{

    protected static $namePre = 'mini_log_';

    protected static function init(){
        $mid = decrypt(Request::post('m'),config('siteinfo.mini_salt'));
        if($mid == ''){
            return false;
        }
        $tablename = MiniLog::$namePre = MiniLog::$namePre.$mid;
        //实例化一个数据库操作类
        $db = new \com\Datatable();
        //检查表是否存在并创建
        if (!$db->CheckTable($tablename)) {
            //创建新表
            $db->initTable($tablename, '', 'id')->query();
        }
        return true;
    }

    public function setTable(){
        $this->table = MiniLog::$namePre;
    }
}