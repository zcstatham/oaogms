<?php
/**
 * Created by PhpStorm.
 * User: lining
 * Date: 2017/4/26
 * Time: 下午1:43
 */
namespace app\index\model;
use think\Model;

class Orders extends Model
{
    protected $table = 'api_orders';

    /**
     * 保存订单
     * @param array $data
     * @return array|static
     */
    public function _setOrderInfo($data = array())
    {
        $response = array();
        if(empty($data))
        {
            return $response;
        }
        $order_id = isset($data['order_id']) && $data['order_id'] > 0 ? $data['order_id'] : 0;
        $model = array();
        if($order_id  > 0)
        {
            $model = Orders::get(array('order_id'=>$order_id));
        }
        if(!$model){
            $model = new Orders;
            $model->order_sn                = isset($data['order_sn']) ? $data['order_sn'] : '';
            $model->order_type                = isset($data['order_type']) ? $data['order_type'] : 1;
            $model->shop_id                 = isset($data['shop_id']) ? $data['shop_id'] : 0;
            $model->uid                     = isset($data['uid']) ? $data['uid'] : 0;
            $model->goods_amount            = isset($data['goods_amount']) ? $data['goods_amount'] : 0;
            $model->shipping_fee            = isset($data['shipping_fee']) ? $data['shipping_fee'] : 0;
            $model->order_amount            = isset($data['order_amount']) ? $data['order_amount'] : 0;
            $model->order_status            = isset($data['order_status']) ? $data['order_status'] : 1;
            $model->pay_status              = isset($data['pay_status'])   ? $data['pay_status'] : 1;;
            $model->consignee               = isset($data['consignee']) ? $data['consignee'] : '';
            $model->mobile                  = isset($data['mobile']) ? $data['mobile'] : '';
            $model->province                = isset($data['province']) ? $data['province'] : 0;
            $model->city                    = isset($data['city']) ? $data['city'] : 0;
            $model->district                = isset($data['district']) ? $data['district'] : 0;
            $model->address                 = isset($data['address']) ? $data['address'] : '';
            $model->note                    = isset($data['note']) ? $data['note'] : '';
            $model->source_code             = isset($data['source_code']) ? $data['source_code'] : 0;
            $model->order_source             = isset($data['order_source']) ? $data['order_source'] : 1;
            $model->share_uid             = isset($data['share_uid']) ? $data['share_uid'] : '';
            $model->create_timestamp        = date('Y-m-d H:i:s',time());
        }else{
            $model->order_status            = isset($data['order_status']) ? $data['order_status'] : $model->order_status;
            $model->pay_status              = isset($data['pay_status']) ? $data['pay_status'] : $model->pay_status;
        }
        if($model->save() !== false){
            return Orders::get(array('order_id'=>$model->order_id));
        }else{
            return $response;
        }

    }

    /**
     * 获取订单列表
     * @param array $data
     * @param bool $is_delete
     * @return array|mixed
     */
    public function _getOrderList($data = array(),$is_delete = true)
    {
        $response = array();
        if(empty($data)){
            return $response;
        }
        $where = '';
        if(!empty($data))
        {
            $where = " and ".implode(" and ",$data);
        }
        if($is_delete)
        {
            $where .= " and  is_delete=0 ".$where;
        }
       return  $this->query("SELECT * FROM `v_orders` WHERE  1=1 ".$where);
    }


    /**
     * 订单总数
     * 全部|待付款|待发货|待收货|待评价
     * @param int $uid
     * @return array|mixed
     */
    public function _getOrderStatusListCount($uid = 0)
    {
        $response  =  array('all'=>0,'wait_pay'=>0,'paid'=>0,'wait_confirm'=>0,'wait_comment'=>0);
        if(!$uid){
            return $response;
        }
        $sql = "SELECT f_getOrderStatusCount(".$uid.") as orderStatusCountList";
        $result = $this->query($sql);
        $result = isset($result[0]) ? json_decode($result[0]['orderStatusCountList'],true) : array();
        return is_array($result) && !empty($result) ? $result : $response;
    }
}