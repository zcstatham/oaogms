<?php
/**
 * Created by PhpStorm.
 * User: EDZ
 * Date: 2018/12/10
 * Time: 21:04
 */

namespace app\api\controller\development;


use app\common\exception\OrderException;
use wx\WxApp;

class Orders extends Base
{
    public function transfers()
    {
        $valid = $this->validate($this->params, 'app\api\validate\Order');
        if ($valid !== true) {
            throw new OrderException(array(
                'errorCode' => '3021',
                'msg' => '验证失败：' . $valid
            ));
        }
        $money = $this->params['money'] * $this->params['num'];
        if ($money < config('siteinfo.money_limit')) {
            throw new OrderException('3022');
        }
        $mInfo = model('Mini')->get($this->params['mid']);
        $order = array(
            'order_sn' => createOrderId(),
            'openid' => decrypt($this->params['userInfo']['params']['Okey'], $this->params['userInfo']['params']['uid'] . config('siteinfo.mini_salt')),
            'money' => $this->params['money'] * $this->params['num'],
            'desc' => $this->params['desc'],
        );
        $wxApp = new WxApp($mInfo['appid']);
        $result = $wxApp->transfers($order);
        $return_code = trim(strtoupper($result['return_code']));
        $result_code = trim(strtoupper($result['return_code']));
        if ($return_code == 'SUCCESS' && $result_code == 'SUCCESS') {
            $order = array(
                'order_sn' => $order['order_sn'],
                'uid' => $this->params['user_info']['params']['uid'],
                'mid' => $this->params['mid'],
                'goods_num' => $this->params['num'],
                'goods_price' => $this->params['money'],
                'remark' => $order['desc'],
                'type' => 21,
                'status' => 3,
            );
            model('Orders')->save($order);
            $this->data['msg'] = '提现成功';
        } else {
            $this->data['code'] = 0;
            $this->data['msg'] = '提现失败';
        }
        return json($this->data);
    }

    public function recharge($type)
    {

    }

    public function getbalance($type)
    {

    }

    public function pay($type)
    {

    }

    public function refund($type)
    {

    }

    public function reward($type)
    {

    }
}