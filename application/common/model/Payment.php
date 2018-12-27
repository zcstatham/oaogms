<?php
/**
 * Created by PhpStorm.
 * User: lining
 * Date: 2017/4/26
 * Time: 下午4:05
 */
namespace app\index\model;
use think\Model;

class Payment extends Model
{
    protected $table = 'api_payment';

    /**
     * 保存支付
     * @param array $data
     * @return array|static
     */
    public function _setPaymentInfo($data = array())
    {
        $response = array();
        if (empty($data)) {
            return $response;
        }
        $pay_sn = isset($data['pay_sn']) && $data['pay_sn'] != '' ? $data['pay_sn'] : '';
        $model = array();
        if ($pay_sn) {
            $model = Payment::get(array('pay_sn' => $pay_sn));
        }
        if (!$model) {
            $model = new Payment;
            $model->pay_sn = $data['pay_sn'];
            $model->pay_status = isset($data['pay_status']) ? $data['pay_status'] : 1;
            $model->uid = $data['uid'];
            $model->order_amount = isset($data['order_amount']) ? $data['order_amount'] : 0;
            $model->balance_paid = isset($data['balance_paid']) ? $data['balance_paid'] : 0;
            $model->coupon_id = isset($data['coupon_id']) ? $data['coupon_id'] : 0;
            $model->group_buying_coupon_id = isset($data['group_buying_coupon_id']) ? $data['group_buying_coupon_id'] : 0;
            $model->coupon_paid = isset($data['coupon_paid']) ? $data['coupon_paid'] : 0;
            $model->vip_coupon_paid = isset($data['vip_coupon_paid']) ? $data['vip_coupon_paid'] : 0;
            $model->pay_money = isset($data['pay_money']) ? $data['pay_money'] : 0;
            $model->pay_orders = isset($data['pay_orders']) ? $data['pay_orders'] : '';
            $model->order_timestamp = date('Y-m-d H:i:s',time());
            $model->pay_type = isset($data['pay_type']) ? $data['pay_type'] : 'UNPAY';
            $model->pay_type_sn = isset($data['pay_type_sn']) ? $data['pay_type_sn'] : '';
            $model->pay_timestamp = isset($data['pay_timestamp']) ? $data['pay_timestamp'] : '';

        } else {
            $model->pay_type = isset($data['pay_type']) ? $data['pay_type'] : $model->pay_type;
            $model->pay_type_sn = isset($data['pay_type_sn']) ? $data['pay_type_sn'] : $model->pay_type_sn;
            $model->pay_status = isset($data['pay_status']) ? $data['pay_status'] : $model->pay_status;
            $model->pay_timestamp = isset($data['pay_timestamp']) ? $data['pay_timestamp'] : $model->pay_timestamp;
        }
        if ($model->save() !== false) {
            return Payment::get(array('pay_sn' => $data['pay_sn']));
        } else {
            return $response;
        }

    }


    /**
     * 获取订单列表
     * @param array $data
     * @return array|mixed
     */
    public function _getPaymentList($data = array())
    {
        $where = '';
        if (empty($data))
            return array();
        $where = " and ".implode(" and ",$data);
        return $this->query("SELECT * FROM `v_payment` WHERE 1=1 ".$where);
    }

    /**
     * 获取订单支付信息
     */
    public function _getPaymentInfoByOrderId($order_id = 0)
    {
        $result = $this->query("SELECT * FROM `v_payment` WHERE FIND_IN_SET(".$order_id.",`pay_orders`)");
        return isset($result[0]) ? $result[0] : array();
    }


    /**
     * 获取单条支付信息
     * @param array $data
     * @return array|mixed
     */
    public function _getOrderPaymentInfo($data = array())
    {
        $where = '';
        if (empty($data))
            return array();
        $where = " and ".implode(" and ",$data);
        $result = $this->query("SELECT * FROM `v_payment` WHERE 1=1 ".$where);
        return isset($result[0]) ? $result[0] : array();
    }

    /**
     * 获取订单支付列表
     * @param array $data
     * @return array|mixed
     */
    public function _getOrderPayment($data = array())
    {
        $response = array();
        if(empty($data)){
            return $response;
        }
        $page        = isset($data['page']) && $data['page'] > 1? $data['page'] -1  : 0;
        $pageSize    = isset($data['pageSize']) ? $data['pageSize'] :10;
        $limit       = ($page*$pageSize).','.$pageSize;
        $where = " and  uid=".$data['uid'];
        if(isset($data['action']) && $data['action'] > 0)
        {
            switch ($data['action'])
            {
                case 1:  //待付款
                    $where = " and  uid=".$data['uid']." and order_status=1";
                    break;
                case 2:  //待发货
                    $where = " and  uid=".$data['uid']." and order_status in (2,6) and pay_status=2 and shipping_status=1";
                    break;
                case 3: //待收货
                    $where = " and  uid=".$data['uid']." and order_status in (2,6) and pay_status=2 and shipping_status=2";
                    break;
                case 4: //待评价
                    $where = " and  order_type =1 and uid=".$data['uid']." and order_status=3 and pay_status=2 and shipping_status=2";
                    break;
            }
        }
        return $this->query("SELECT * FROM `v_payment` WHERE order_type != 2 and 1=1 ".$where. " order by order_timestamp desc limit ".$limit);

    }


    /**
     * 获取订单列表
     * @param array $data
     * @param int $page
     * @param int $pageSize
     * @return array|mixed
     */
    public function _getOrderList($data = array() , $page = 1 ,$pageSize =10)
    {
        $response = array();
        if(empty($data)){
            return $response;
        }
        $page        = isset($page) && $page > 1? $page -1  : 0;
        $pageSize    = isset($pageSize) ? $pageSize :10;
        $limit       = ($page*$pageSize).','.$pageSize;
        $where = '';
        if(!empty($data))
        {
            $where = " and ".implode(" and ",$data);
        }
        return $this->query("SELECT * FROM `v_orders` WHERE is_delete=0 ".$where. " limit ".$limit);
    }
}