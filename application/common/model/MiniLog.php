<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/18
 * Time: 21:30
 */

namespace app\common\model;


class MiniLog extends Base
{
    protected static function init(){
        self::beforeInsert(function($event){
            $data = $event->toArray();
            $tablename = strtolower($data['name']);
            //实例化一个数据库操作类
            $db = new \com\Datatable();
            //检查表是否存在并创建
            if (!$db->CheckTable($tablename)) {
                //创建新表
                return $db->initTable($tablename, $data['title'], 'id')->query();
            }else{
                return false;
            }
        });
        self::afterInsert(function($event){
            $data = $event->toArray();

            $fields = include(APP_PATH.'admin/fields.php');
            if (!empty($fields)) {
                foreach ($fields as $key => $value) {
                    if ($data['is_doc']) {
                        $fields[$key]['model_id'] = $data['id'];
                    }else{
                        if (in_array($key, array('uid', 'status', 'view', 'create_time', 'update_time'))) {
                            $fields[$key]['model_id'] = $data['id'];
                        }else{
                            unset($fields[$key]);
                        }
                    }
                }
                model('Attribute')->saveAll($fields);
            }
            return true;
        });
        self::beforeUpdate(function($event){
            $data = $event->toArray();
            if (isset($data['attribute_sort']) && $data['attribute_sort']) {
                $attribute_sort = json_decode($data['attribute_sort'], true);

                if (!empty($attribute_sort)) {
                    foreach ($attribute_sort as $key => $value) {
                        db('Attribute')->where('id', 'IN', $value)->setField('group_id', $key);
                        foreach ($value as $k => $v) {
                            db('Attribute')->where('id', $v)->setField('sort', $k);
                        }
                    }
                }
            }
            return true;
        });
    }
}