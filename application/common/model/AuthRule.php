<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/13
 * Time: 11:19
 */

namespace app\common\model;


use think\facade\Env;

class AuthRule extends Base
{
    protected $autoWriteTimestamp = false;

    public $filter_method = array('__construct', 'execute', 'login', 'sqlSplit', 'isMobile', 'is_wechat', '_initialize');

    public $keyList = array(
        array('name'=>'module','title'=>'所属模块','type'=>'hidden'),
        array('name'=>'title','title'=>'节点名称','type'=>'text','help'=>''),
        array('name'=>'name','title'=>'节点标识','type'=>'text','help'=>''),
        array('name'=>'group','title'=>'功能组','type'=>'text','help'=>'功能分组'),
        array('name'=>'status','title'=>'状态','type'=>'radio','option'=>array('1'=>'启用','0'=>'禁用'),'help'=>''),
        array('name'=>'condition','title'=>'条件','type'=>'text','help'=>'')
    );

    public function saveNode($data){
        if (isset($data['id']) && $data['id']) {
            $this->save($data, array('id'=>$data['id']));
        }else{
            $this->save($data);
        }
    }

    public function updataNode($type){
        $data = $this->updateRule($type);
        foreach ($data as $value) {
            $id = $this->where('name', $value['name'])->value('id',false);
            if ($id) {
                $value['id'] = $id;
            }
            $list[] = $value;
        }
        $this->saveAll($list);
    }

    public function updateRule($type){
        $path =  Env::get('app_path') . $type . '/controller';
        //控制器类文件
        $classname = $this->scanFile($path);
        foreach ($classname as $value) {
            $class = "\\app\\" . $type . "\\controller\\" . $value;
            if(class_exists($class)){
                $reflection = new \ReflectionClass($class);
                $group_doc = $this->parserDoc($reflection->getDocComment());
                $method = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
                foreach ($method as $key => $v) {
                    if (!in_array($v->name, $this->filter_method)) {
                        $title_doc = $this->parserDoc($v->getDocComment());
                        if (isset($title_doc['title']) && $title_doc['title']) {
                            $list[] = array(
                                'module' => $type,
                                'type' => 2,
                                'name' => $type . '/' . strtolower($value) . '/' . strtolower($v->name),
                                'title' => trim($title_doc['title']),
                                'group' => (isset($group_doc['title']) && $group_doc['title']) ? trim($group_doc['title']) : '',
                                'status' => 1
                            );
                        }
                    }
                }
            }
        }
        return $list;
    }

    protected function scanFile($path){
        $result = array();
        $files = scandir($path);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                if (is_dir($path . '/' . $file)) {
                    $this->scanFile($path . '/' . $file);
                } else {
                    $result[] = substr(basename($file), 0 , -4);
                }
            }
        }
        return $result;
    }

    protected function parserDoc($text){
        $doc = new \doc\Doc();
        return $doc->parse($text);
    }
}