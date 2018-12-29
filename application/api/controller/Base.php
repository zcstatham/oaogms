<?php
/**
 * Created by PhpStorm.
 * MiniUser: EDZ
 * Date: 2018/12/7
 * Time: 19:19
 */

namespace app\api\controller;

use app\common\exception\HttpException;
use think\Controller;
use think\facade\Log;

class Base extends Controller
{
    public $uri;
    public $params;
    protected $data;
    protected $middleware = [
        'Check' => ['except' => ['login', 'logout']],
    ];

    protected function initialize()
    {
        $this->uri = $this->request->protocol() . ' ' . $this->request->method() . ' : ' . $this->request->url(true);
        $this->data = array(
            'code' => 1,
            'msg' => '请求成功',
            'time' => time()
        );
        if($this->request->newToken){
            header('Cache-control:no-cache,must-revalidate');
            header('Authorization:'.json_encode($this->request->newToken));
        }
//        Log::init(array(
//            'type' => 'File',
//            'path' => Env::get('runtime_path') . 'log/api'
//        ));

        Log::record('请求信息:'.date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']) . ' ' . $this->uri);
        $this->params = $this->getParams();
        Log::record(['request_params'=>$this->params]);
    }

    private function getParams(){
        $params = $this->request->param();
        if(isset($params['aid'])){
            $params['aid'] = (int)decodeN($params['aid']);
        }else {
            $params['aid'] = 0;
        }
        if(isset($params['mid'])&& is_numeric($mid = decodeN($params['mid']))) {
            $params['mid'] = (int)$mid;
        }else {
            throw new HttpException(array('errorCode'=>2021));
        }
        return $params;
    }
}