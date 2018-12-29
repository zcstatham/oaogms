<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/26
 * Time: 21:40
 */

namespace app\api\controller\development;



use app\common\exception\HttpException;

class Log extends Base
{

    public function visited(){
        if (!$this->save('login')) {
            throw new HttpException();
        }else{
            $this->data['data'] = 'success';
        }
        return json($this->data);
    }

    public function browsead()
    {
        if (!$this->save('browseAd')) {
            throw new HttpException();
        }else{
            $this->data['data'] = 'success';
        }
        return json($this->data);
    }

    public function save($type)
    {
        $data = array(
            'type' => $type,
            'uid' => $this->params['userInfo']['params']['uid'],
            'action_ip' => get_client_ip(),
            'aid' => $this->params['aid'],
            'mid' => $this->params['mid']
        );
        return model('MiniLog')->save($data);
    }
}