<?php
/**
 * Created by PhpStorm.
 * User: lining
 * Date: 2017/4/25
 * Time: 上午9:55
 */
namespace app\index\controller;
use app\common\controller\Api;
use Service\AlipayService;
use think\Config;
use think\Log;
use think\Request;
use Service\WechatService;
class Orders extends Api{
    /**
     * 订单流程：
     * 1. 充值
     */
    protected $param= array(
        //商品直接下单
        'order'    => array(
            'uid'          => array('name' => 'uid', 'require' => true, 'desc' => '用户ID'),
            'mid'          => array('name' => 'goods_id', 'require' => true, 'desc' => '商品ID'),
            'product_id'          => array('name' => 'product_id', 'require' => false, 'desc' => '产品ID'),
            'number'                  => array('name' => 'number', 'require' => true, 'desc' => '产品数量'),
        ),
        //团购商品下单
        'gbGoOrder'    => array(
            'uid'          => array('name' => 'uid', 'require' => true, 'desc' => '用户ID'),
            'group_buying_goods_id' => array('name' => 'group_buying_goods_id', 'require' => true, 'desc' => '团购商品ID'),
            'coupon_id' => array('name' => 'user_coupon_id', 'require' => false, 'desc' => '团购团长免单券'),
        ),
        'carOrder'    => array(
            'uid'          => array('name' => 'uid', 'require' => true, 'desc' => '用户ID')
        ),
        //提交订单
        'doOrder'    => array(
            'uid'                => array('name' => 'uid', 'require' => true, 'desc' => '用户ID'),
            'goods_info'          => array('name' => 'goods_info', 'require' => true, 'desc' => '商品信息'),
            'address_id'          => array('name' => 'address_id', 'require' => true, 'desc' => '配送地址'),
            'balance'          => array('name' => 'balance', 'require' => false, 'desc' => '使用余额'),
            'coupon_id'          => array('name' => 'coupon_id', 'require' => false, 'desc' => '优惠券ID'),
            'note'                => array('name' => 'note', 'require' => false, 'desc' => '备注信息'),
            //新增销售渠道码
            'source_code'                  => array('name' => 'source_code', 'require' => false, 'desc' => '销售渠道码'),
        ),
        //团购提交订单
        'gbDoOrder'    => array(
            'uid'                => array('name' => 'uid', 'require' => true, 'desc' => '用户ID'),
            'group_buying_goods_id'          => array('name' => 'group_buying_goods_id', 'require' => true, 'desc' => '团购商品ID'),
            'address_id'          => array('name' => 'address_id', 'require' => true, 'desc' => '配送地址'),
            'balance'          => array('name' => 'balance', 'require' => false, 'desc' => '使用余额'),
            'coupon_id'          => array('name' => 'user_coupon_id', 'require' => false, 'desc' => '团长免单券ID'),
            'note'                => array('name' => 'note', 'require' => false, 'desc' => '备注信息'),
            //新增销售渠道码
            'source_code'                  => array('name' => 'source_code', 'require' => true, 'desc' => '销售渠道码'),
        ),
        //收银台
        'cashier'    => array(
            'uid'          => array('name' => 'uid', 'require' => true, 'desc' => '用户ID'),
            'order_id'          => array('name' => 'order_id', 'require' => true, 'desc' => '订单ID')
        ),
        //订单支付
        'pay'    => array(
            'uid'          => array('name' => 'uid', 'require' => true, 'desc' => '用户ID'),
            'order_id'          => array('name' => 'order_id', 'require' => true, 'desc' => '订单ID')
        ),
        //订单列表
        'lists'    => array(
            'uid'          => array('name' => 'uid', 'require' => true, 'desc' => '用户ID'),
            'action'          => array('name' => 'action', 'require' => false, 'desc' => '订单类型')
        ),
        //订单状态
        'change'    => array(
            'uid'          => array('name' => 'uid', 'require' => true, 'desc' => '用户ID'),
            'action'          => array('name' => 'action', 'require' => true, 'desc' => '订单状态'),
            'order_id'          => array('name' => 'order_id', 'require' => true, 'desc' => '订单ID')
        ),
        //订单详情
        'detail'    => array(
            'uid'          => array('name' => 'uid', 'require' => true, 'desc' => '用户ID'),
            'order_id'          => array('name' => 'order_id', 'require' => true, 'desc' => '订单ID')
        ),
        //订单回调
        'notify'    => array(
            'pay_sn'          => array('name' => 'uid', 'require' => false, 'desc' => '订单支付号'),
        ),
        //订单退款
        'refund'    => array(
            'uid'          => array('name' => 'uid', 'require' => true, 'desc' => '用户ID'),
            'order_id'          => array('name' => 'order_id', 'require' => true, 'desc' => '订单ID'),
            'is_get_goods'          => array('name' => 'is_get_goods', 'require' => false, 'desc' => '是否已收货'),
            'reason'          => array('name' => 'reason', 'require' => true, 'desc' => '退款原因'),
            'consignee'          => array('name' => 'consignee', 'require' => false, 'desc' => '联系人'),
            'tel'          => array('name' => 'tel', 'require' => false, 'desc' => '联系方式'),
        ),
    );

    /**
     * 商品直接下单
     * @return array
     */
    public function goOrder()
    {
        if(Request::instance()->isPost())
        {
            $data = $this->params;
            $goods_id = isset($data['goods_id']) && $data['goods_id'] > 0 ? $data['goods_id'] : 0;
            $product_id = isset($data['product_id']) && $data['product_id'] > 0 ? $data['product_id'] : 0;
            //获取下单人收货地址
            $address = $this->getUserDefaultAddress($data['uid']);
            if(!empty($address))
            {
                $response['address'] = $address;
            }
            //获取下单商品
            #TODO 商品有规格
            if($product_id > 0)
            {
                $goods_info = model('Products')->_getGoodsProductInfo($data['product_id']);
                #TODO 商品没规格
            }else if($goods_id > 0){
                $goods_info   = model('Goods')->_getGoodsDetail($data['goods_id']);
            }

            #TODO 检查商品信息
            if(intval($goods_info['is_delete']) === 1){
                json_error_exception(3003);
            }
            if(intval($goods_info['is_sale']) === 0){
                json_error_exception(3004);
            }
            #TODO 检查库存
            if(intval($goods_info['goods_number']) === 0)
            {
                json_error_exception(3005);
            }
            $goods_attr_info = '';
            if(isset($data['product_id']) && $data['product_id'] > 0)
            {
                $goods_attr = explode("|",$goods_info['goods_attr']);
                $goods_attr_info = array();
                foreach ($goods_attr as $k=>$v)
                {
                    $attr_info = model('Goods')->_getGoodsAttrinfo($v);
                    $goods_attr_info[] = $attr_info['attribute_value'];
                }
                $goods_attr_info = implode(",",$goods_attr_info);
            }
            if($product_id > 0){
                $response['goodsList'][0]['productId'] = $product_id;
            }

            $goods_price = $product_id > 0 ? $goods_info['product_price'] : $goods_info['shop_price'];

            $response['goodsList'][0]['goodsId'] =   $goods_id;
            $response['goodsList'][0]['goodsName'] = $goods_info['goods_name'];
            $response['goodsList'][0]['goodsNumber'] = $data['number'];
            $response['goodsList'][0]['goodsPrice'] = $goods_price;
            $response['goodsList'][0]['goodsImage'] = $goods_info['goods_image'];
            if($goods_attr_info != "")
            {
                $response['goodsList'][0]['goodsAttr'] = $goods_attr_info;
            }
            //取运费
            $trafficPrice = 0;
            if(isset($address['province']))
            {
                $shippingGoods = array(
                    (int)$goods_info['shop_id'] => array(
                        'goods_price' => $goods_price * $data['number'],
                        'goods_number' => (int)$data['number'],
                        'goods_heavy' => $goods_info['goods_heavy'],
                    ),
                );
                $trafficPrice = getShippingFeeByGoodsGroup($address['province'], $shippingGoods);
            }

            $orderAmount = $goods_price * $data['number'] + $trafficPrice['fee'];

            //取余额
            $balance = 0;
//            $user =  model('User')->_getOneUser(array('uid='.$data['uid']));
//            if($user['balance'] > 0)
//            {
//                if(intval($user['balance']) >= $orderAmount){
//                    $balance = $orderAmount;
//                    $orderAmount = 0;
//                }else if(intval($user['balance']) < $orderAmount){
//                    $balance = $user['balance'];
//                    $orderAmount = $orderAmount  - $balance;
//                }
//            }
            //去可用优惠券
            $coupon = model('Coupon')->_getUserCanUseCouponCount($data['uid'],$goods_price * $data['number']);

            $response['payInfo']['traffic_note']    =  strval($trafficPrice['note']);
            $response['payInfo']['coupon']    =  $coupon;
            $response['payInfo']['balance']    =  $balance;
            $response['payInfo']['goodsPrice']    =  $goods_price * $data['number'];
            $response['payInfo']['trafficPrice']    = strval($trafficPrice['fee']) ;
            $response['payInfo']['payPrice']    =  $orderAmount ;


            $this->data['data'] = $response;

            return $this->data;
        }else{
            json_error_exception(1001);
        }
    }

    /**
     * 团购商品商品下单
     * @return array
     */
    public function gbGoOrder()
    {
        if(!Request::instance()->isPost())
        {
            json_error_exception(1001);
        }
        $data = $this->params;
        $groupBuyGoodsInfo = model("Groupbuyinggoods")->where(array("group_buying_goods_id" => trim($data['group_buying_goods_id'])))->find();
        if(!isset($groupBuyGoodsInfo['goods_id']))
        {
            json_error_exception(3003);
        }
        $goods_id = (int)$groupBuyGoodsInfo['goods_id'];
        $product_id = (int)$groupBuyGoodsInfo['product_id'];
        //获取下单人收货地址
        $address = $this->getUserDefaultAddress($data['uid']);
        if(!empty($address))
        {
            $response['address'] = $address;
        }
        //获取下单商品
        if($product_id > 0)
        {
            $goods_info = model('Products')->_getGoodsProductInfo($product_id);
        }else{
            $goods_info = model('Goods')->_getGoodsDetail($goods_id);
        }

        #TODO 检查商品信息
        if(intval($goods_info['is_delete']) === 1){
            json_error_exception(3003);
        }
        if(intval($goods_info['is_sale']) === 0){
            json_error_exception(3004);
        }
        #TODO 检查库存
        if(intval($goods_info['goods_number']) === 0)
        {
            json_error_exception(3005);
        }
        $goods_attr_info = '';
        if($product_id > 0)
        {
            $goods_attr = explode("|",$goods_info['goods_attr']);
            $goods_attr_info = array();
            foreach ($goods_attr as $k=>$v)
            {
                $attr_info = model('Goods')->_getGoodsAttrinfo($v);
                $goods_attr_info[] = $attr_info['attribute_value'];
            }
            $goods_attr_info = implode(",",$goods_attr_info);
        }
        if($product_id > 0){
            $response['goodsList'][0]['productId'] = $product_id;
        }
        //如果是团购团长免单券下单，则免费
        if(isset($data['coupon_id']) && (int)$data['coupon_id'] > 0)
        {
            $goods_price = 0.00;
            $trafficPrice = 0.00;
        }
        else
        {
            $goods_price = (float)$groupBuyGoodsInfo['group_buying_price'] > 0 ? (float)$groupBuyGoodsInfo['group_buying_price'] : $goods_info['product_price'];
            #TODO 获取运费
            $trafficPrice = 0.00;
            if(isset($address['province'])){
                $shippingGoods = array(
                    (int)$goods_info['shop_id'] => array(
                        'goods_price' => $goods_price * 1,
                        'goods_number' => 1,
                        'goods_heavy' => $goods_info['goods_heavy'],
                    ),
                );
                $trafficPrice = getShippingFeeByGoodsGroup($address['province'], $shippingGoods);
            }

        }
        $response['goodsList'][0]['goodsId'] =   $goods_id;
        $response['goodsList'][0]['goodsName'] = $goods_info['goods_name'];
        $response['goodsList'][0]['goodsNumber'] = 1;
        $response['goodsList'][0]['goodsPrice'] = $goods_price;
        $response['goodsList'][0]['goodsImage'] = $goods_info['goods_image'];
        if($goods_attr_info != "")
        {
            $response['goodsList'][0]['goodsAttr'] = $goods_attr_info;
        }
        $response['payInfo']['coupon']    =  0;
        $response['payInfo']['balance']    =  0;
        $response['payInfo']['goodsPrice']    =  $goods_price * 1;
        $response['payInfo']['trafficPrice']    = strval($trafficPrice['fee']) ;
        $response['payInfo']['payPrice']    =  strval($trafficPrice['fee']) + $goods_price;
        $this->data['data'] = $response;
        return $this->data;
    }

    /**
     * 购物车下单
     * @return array
     */
    public function carOrder()
    {
        if(Request::instance()->isPost())
        {
            $data = $this->params;
            //获取下单人收货地址
            $address = $this->getUserDefaultAddress($data['uid']);
            if(!empty($address))
            {
                $this->data['data']['address'] = $address;
            }
            //获取下单商品
            $goodsList = array();
            $product_info = model('Car')->_getUserCar($data['uid'],true);
            if(!empty($product_info)){
                $traffic = 0;
                $goodsCount  =$trafficCount= $payCount =0;
                $shopGoods = array();
                foreach ($product_info as $k=>$v)
                {
                    if($v['product_id'] > 0){
                        $goodsList[$k]['productId'] = $v['product_id'];
                    }
                    if($v['goods_attr'] != ''){
                        $goodsList[$k]['goodsAttr'] = $v['goods_attr'];
                    }
                    $goodsList[$k]['goodsId'] = $v['goods_id'];
                    $goodsList[$k]['goodsName'] = $v['goods_name'];
                    $goodsList[$k]['goodsNumber'] = $v['goods_number'];
                    $goodsList[$k]['goodsPrice'] = $v['shop_price']; //shop_price 加购物车时取得product_price
                    $goodsList[$k]['goodsImage'] = $v['goods_image'];

                    $goodsCount += $v['goods_number'] * $v['shop_price'];
                    //算运费商品组
                    if(isset($shopGoods[$v['shop_id']]))
                    {
                        $shopGoods[$v['shop_id']]['goods_number'] += $v['goods_number'];
                        $shopGoods[$v['shop_id']]['goods_price'] += $v['shop_price'];
                        $shopGoods[$v['shop_id']]['goods_heavy'] += $v['goods_heavy'];
                    }
                    else
                    {
                        $shopGoods[$v['shop_id']]['goods_number'] = $v['goods_number'];
                        $shopGoods[$v['shop_id']]['goods_price'] = $v['shop_price'];
                        $shopGoods[$v['shop_id']]['goods_heavy'] = $v['goods_heavy'];
                    }
//                    //运费
//                    if(isset($address['province']) && $address['province']){
//                        $traffic    += getShippingFee($address['province'],$v['goods_number']*$v['goods_heavy']);
//                    }
                }
                //运费
                if(isset($address['province']) && $address['province']){
                    $traffic = getShippingFeeByGoodsGroup($address['province'], $shopGoods);
                }
                $this->data['data']['goodsList'] = $goodsList;

                $orderAmount = $goodsCount + $traffic['fee'];
                //去可用优惠券
                $coupon = model('Coupon')->_getUserCanUseCouponCount($data['uid'],$goodsCount);

                //取余额
                $balance = 0;
//                $user =  model('User')->_getOneUser(array('uid='.$data['uid']));
//                if($user['balance'] > 0)
//                {
//                    if(intval($user['balance']) >= $orderAmount){
//                        $balance = $orderAmount;
//                        $orderAmount = 0;
//                    }else if(intval($user['balance']) < $orderAmount){
//                        $balance = $user['balance'];
//                        $orderAmount = $orderAmount  - $balance;
//                    }
//                }

                $this->data['data']['payInfo']    = array(
                    'traffic_note'=> strval($traffic['note']),
                    'coupon' => $coupon,
                    'balance'=> $balance,
                    'goodsPrice' => $goodsCount,
                    'trafficPrice' => isset($traffic['fee']) && $traffic['fee'] >0 ? $traffic['fee'] : 0 ,
                    'payPrice'   => sprintf("%.2f", $orderAmount)
                );
            }else{
                json_error_exception(3002);
            }
            return $this->data;
        }else{
            json_error_exception(1001);
        }
    }

    /**
     * 确认下单
     */
    public function doOrder()
    {
        if(Request::instance()->isPost())
        {
            $data = $this->params;
            $data['source_code'] = isset($data['source_code']) && trim($data['source_code']) ? trim($data['source_code']) : '';
            $data['share_uid'] = isset($data['share_uid']) && trim($data['share_uid']) ? trim($data['share_uid']) : '';
            $data['order_source'] = isset($data['order_source']) && trim($data['order_source']) ? trim($data['order_source']) : '';
            if(!$data['goods_info'] || empty($data['goods_info'])){
                json_error_exception(3002);
            }
            //用户配送地址
            if(!isset($data['address_id']))
            {
                json_error_exception(4010);
            }
            $address = model('Address')->where('address_id','=',$data['address_id'])->find();
            //组装订单商品数据
            $order_goods = $shop_goods =$order_info =  array();

            foreach ($data['goods_info'] as $k=>$v)
            {
                $where = array();
                $where[] = " goods_id=".$v['goods_id'];
                if(isset($v['product_id']) && intval($v['product_id']) > 0)
                {
                    $where[] = ' product_id ='.$v['product_id'];
                    $goods_info  = model('Products')->_getGoodsProductInfoByWhere($where);
                }else{
                    $goods_info   = model('Goods')->_getGoodsDetail($v['goods_id']);
                }

                #TODO 删除购物车
                $delWhere['uid'] = $data['uid'];
                $delWhere['goods_id'] = $v['goods_id'];
                if(isset($v['product_id']) && intval($v['product_id']) > 0)
                {
                    $delWhere['product_id'] = $v['product_id'];
                }
                model('Car')->where($delWhere)->delete();

                $goods_number = isset($goods_info['product_number']) ? $goods_info['product_number'] : $goods_info['goods_number'];
                if(!empty($goods_info)){
                    if(intval($goods_info['is_delete']) > 0){  //删除
                        json_error_exception(3003);
                    }
                    if(intval($goods_info['is_sale']) === 0){  //上架|下架
                        json_error_exception(3004);
                    }
                    if(intval($goods_number) < $v['number']){  //库存
                        json_error_exception(3005);
                    }
                    $goods_info['number'] = $v['number'];
                    $order_goods[$k] = $goods_info;
                }
            }
            foreach ($order_goods as $k=>$v){

                $shop_goods[$v['shop_id']]['shop_id'] = $v['shop_id'];
                $shop_goods[$v['shop_id']]['uid'] = $data['uid'];
                $shop_goods[$v['shop_id']]['goodsList'][] = $v;
            }
            sort($shop_goods);

            $balance_paid =  isset($data['balance']) ? $data['balance'] : 0;
            $coupon_id    =  isset($data['coupon_id']) ? $data['coupon_id'] : 0;

            model('Orders')->startTrans();
            try{
                $order_amount   = $shippint_fee_amount =$pay_amount =0;
                $order_ids = array();
                foreach ($shop_goods as $k=>$v)
                {
                    $shippingGoods = array();
                    $goods_amount = $shippint_amount=0;
                    $orderData['order_sn'] = getSN('ORDER');
                    $orderData['shop_id'] = $v['shop_id'];
                    $orderData['uid'] = $v['uid'];
                    foreach ($v['goodsList'] as $kk=>$vv)
                    {
                        $price = isset($vv['product_price']) ? $vv['product_price'] * $vv['number'] : $vv['shop_price'] * $vv['number'];
                        $goods_amount  += $price;

                        //算运费商品组
                        if(isset($shippingGoods[$v['shop_id']]))
                        {
                            $shippingGoods[$v['shop_id']]['goods_number'] += $vv['goods_number'];
                            $shippingGoods[$v['shop_id']]['goods_price'] += $price;
                            $shippingGoods[$v['shop_id']]['goods_heavy'] += $vv['goods_heavy'];
                        }
                        else
                        {
                            $shippingGoods[$v['shop_id']]['goods_number'] = $vv['goods_number'];
                            $shippingGoods[$v['shop_id']]['goods_price'] = $price;
                            $shippingGoods[$v['shop_id']]['goods_heavy'] = $vv['goods_heavy'];
                        }
//                        $shippint_amount += getShippingFee($address['province'],$vv['number']*$vv['goods_heavy']);
                    }

                    $coupon_paid = 0;
                    if($coupon_id > 0)
                    {
                        $coupon = model('Coupon')->where('coupon_id','=',$coupon_id)->find();
                        $coupon_paid = $coupon['min'] <= $goods_amount ?  $coupon['coupon_money']  :  0 ;
                    }

                    //新的获取运费
                    $shippint_amount = getShippingFeeByGoodsGroup($address['province'], $shippingGoods);
                    $orderData['goods_amount'] = $goods_amount;
                    $orderData['shipping_fee'] = strval($shippint_amount['fee']);
                    $orderData['order_amount'] =  $goods_amount + intval($shippint_amount['fee']);
                    $orderData['consignee'] = $address['consignee'];
                    $orderData['mobile'] = $address['mobile'];
                    $orderData['province'] = $address['province'];
                    $orderData['city'] = $address['city'];
                    $orderData['district'] = $address['district'];
                    $orderData['address'] = $address['address'];
                    $orderData['note'] = isset($data['note']) ? $data['note'] : '';
                    $orderData['source_code'] = trim($data['source_code']);
                    $orderData['order_source'] = trim($data['order_source']) != 2 ? 1 :2;
                    $orderData['share_uid'] = trim($data['share_uid']);
                    if(($orderData['order_amount'] - $balance_paid) == 0)
                    {
                        $orderData['order_status'] = 2;
                        $orderData['pay_status'] = 2;
                    }
                    //团购订单
                    if($orderData['source_code'] != '')
                    {
                        $orderData['order_type'] = 2;
                    }
                    $order_info = model('Orders')->_setOrderInfo($orderData);
                    $order_ids[] = $order_info['order_id'];
                    foreach ($v['goodsList'] as $ok=>$ov)
                    {
                        $orderGoodsData['order_id'] = $order_info['order_id'];
                        $orderGoodsData['goods_id'] = $ov['goods_id'];
                        $orderGoodsData['goods_sn'] = $ov['goods_sn'];
                        $orderGoodsData['goods_name'] = $ov['goods_name'];
                        if(isset($ov['product_id'])){
                            $orderGoodsData['product_id'] = $ov['product_id'];
                        }
                        $orderGoodsData['goods_number'] = $ov['number'];
                        $orderGoodsData['market_price'] = $ov['market_price'];
                        $orderGoodsData['goods_price'] = isset($ov['product_price']) ? $ov['product_price'] : $ov['shop_price'];
                        if(isset($ov['goods_attr']) && $ov['goods_attr'] != ''){
                            $goods_attr = explode("|",$ov['goods_attr']);
                            $checkedAttr = array();
                            foreach ($goods_attr as $gk=>$gv){
                                $att_info = model('Goods')->_getGoodsAttrinfo($gv);
                                $checkedAttr[] = $att_info['attribute_value'];
                            }
                            $orderGoodsData['goods_attr']  = !empty($checkedAttr) ? implode(",",$checkedAttr) : '';
                        }
                        $orderGoodsData['goods_image'] = $ov['goods_image'];
                        model('Ordergoods')->_setOrderGoodsInfo($orderGoodsData);
                    }

                    $order_amount += $orderData['order_amount'];
                    $shippint_fee_amount += $orderData['shipping_fee'];
                }

                $payData['pay_sn'] = getSN('PAYMENT');
                $payData['uid'] = $data['uid'];
                $payData['balance_paid'] = $balance_paid;
                $payData['coupon_id'] = $coupon_id;
                $payData['coupon_paid'] = $coupon_paid;
                $payData['order_amount'] = $order_amount;
                $payData['pay_money'] = $order_amount  - $coupon_paid;
                $payData['pay_orders'] = implode(",",$order_ids);


                if(($order_amount - $balance_paid - $coupon_paid) <= 0)
                {
                    $payData['pay_status'] = 2;
                    $payData['pay_type'] = 'BALANCE';
                    $payData['pay_timestamp'] = date('Y-m-d H:i:s',time());
                    model('Balance')->insert(
                        array(
                            'uid'=>$data['uid'],
                            'calculation_type'=>1,
                            'calculation_type_dataid'=>implode(",",$order_ids),
                            'computing_method'=>'REDUCE',
                            'money'=>$balance_paid,
                            'note'=>'用户'.getUserName($data['uid'])."订单消费",
                            'status'=>1,
                            'create_timestamp'=>date('Y-m-d H:i:s',time())
                        )
                    );
                    //计算达人佣金
                    foreach ($order_ids as $ik=>$iv)
                    {
                        Vip::topLevelVipUser($data['uid'],$iv);
                    }

                }

                $payment_info = model('Payment')->_setPaymentInfo($payData);
                $this->data['data'] =array(
                    'orderId' => $order_ids[0],
                    'orderType' => 1,
                    'paySn' => $payment_info['pay_sn'],
                    'payPrice' => $payData['pay_money'],
                    'payAmount' => $payData['pay_money'],
                    'times'      =>  ((strtotime($payment_info['order_timestamp']) + 24*3600) - time()) >= 0 ? (strtotime($payment_info['order_timestamp']) + 24*3600) - time() : 0
                );
                model('Orders')->commit();

            }catch (\Exception $e){
                model('Orders')->rollback();
                $this->data['msg'] = "下单失败";
            }
            return $this->data;
        }else{
            json_error_exception(1001);
        }
    }

    /**
     * 团购确认下单
     */
    public function gbDoOrder()
    {
        if(!Request::instance()->isPost())
        {
            json_error_exception(1001);
        }
        $data = $this->params;
        $data['source_code'] = isset($data['source_code']) && trim($data['source_code']) ? trim($data['source_code']) : '';
        //用户配送地址
        $address = model('Address')->where('address_id','=',$data['address_id'])->find();
        $groupBuyGoodsInfo = model("Groupbuyinggoods")->where(array("group_buying_goods_id" => trim($data['group_buying_goods_id'])))->find();
        if(!isset($groupBuyGoodsInfo['goods_id']))
        {
            json_error_exception(3003);
        }
        $goodsInfo = model("Goods")->where(array("goods_id" =>(int)$groupBuyGoodsInfo['goods_id']))->find();
        $productInfo = model("Products")->where(array("product_id" =>(int)$groupBuyGoodsInfo['product_id']))->find();
        model('Orders')->startTrans();
        #TODO 验证团长免单券
        $coupon_id    =  isset($data['user_coupon_id']) ? (int)$data['user_coupon_id'] : 0;
        $isGbCoupon = false;
        if($coupon_id > 0)
        {
            $isGbCoupon = $this->_checkUserGroupBuyingCoupon($data['uid'], $coupon_id);
        }
        $goods_amount  = (float)$groupBuyGoodsInfo['group_buying_price'];
        //团长免单券支付
        if($isGbCoupon) {
            $trafficPrice = 0;
            $order_amount = 0;
            $balance_paid =  0;
        }
        else
        {
            #获取运费
            $trafficPrice = 0.00;
            if(isset($address['province'])){
                $shippingGoods = array(
                    (int)$goodsInfo['shop_id'] => array(
                        'goods_price' => $goods_amount * 1,
                        'goods_number' => 1,
                        'goods_heavy' => $goodsInfo['goods_heavy'],
                    ),
                );
                $trafficPrice = getShippingFeeByGoodsGroup($address['province'], $shippingGoods);
                $trafficPrice = isset($trafficPrice['fee']) ? $trafficPrice['fee'] : 0.00;
            }
            $order_amount = $goods_amount + $trafficPrice;
            $balance_paid =  isset($data['balance']) ? $data['balance'] : 0;
        }
        $coupon_paid  =  isset($data['coupon_id']) ? '' : 0;
        try{
            $orderData['order_sn'] = getSN('ORDER');
            $orderData['shop_id'] = $groupBuyGoodsInfo['shop_id'];
            $orderData['uid'] = $data['uid'];
            $orderData['goods_amount'] = $goods_amount;
            $orderData['shipping_fee'] = $trafficPrice;
            $orderData['order_amount'] =  $order_amount;
            $orderData['consignee'] = $address['consignee'];
            $orderData['mobile'] = $address['mobile'];
            $orderData['province'] = $address['province'];
            $orderData['city'] = $address['city'];
            $orderData['district'] = $address['district'];
            $orderData['address'] = $address['address'];
            $orderData['note'] = isset($data['note']) ? $data['note'] : '';
            $orderData['source_code'] = trim($data['source_code']);
            //团购订单
            if($orderData['source_code'] != '')
            {
                $orderData['order_type'] = 2;
            }
            $order_info = model('Orders')->_setOrderInfo($orderData);
            //订单商品
            $order_id = (int)$order_info['order_id'];
            $orderGoodsData['order_id'] = $order_id;
            $orderGoodsData['goods_id'] = (int)$groupBuyGoodsInfo['goods_id'];
            $orderGoodsData['goods_sn'] = $goodsInfo['goods_sn'];
            $orderGoodsData['goods_name'] = $goodsInfo['goods_name'];
            if(isset($groupBuyGoodsInfo['product_id'])){
                $orderGoodsData['product_id'] = $groupBuyGoodsInfo['product_id'];
            }
            $orderGoodsData['goods_number'] = 1;
            $orderGoodsData['market_price'] = $goodsInfo['market_price'];
            $orderGoodsData['goods_price'] = (float)$groupBuyGoodsInfo['group_buying_price'];
            if(isset($productInfo['goods_attr']) && $productInfo['goods_attr'] != ''){
                $goods_attr = explode("|",$productInfo['goods_attr']);
                $checkedAttr = array();
                foreach ($goods_attr as $gk => $gv){
                    $att_info = model('Goods')->_getGoodsAttrinfo($gv);
                    $checkedAttr[] = $att_info['attribute_value'];
                }
                $orderGoodsData['goods_attr']  = !empty($checkedAttr) ? implode(",",$checkedAttr) : '';
            }
            $orderGoodsData['goods_image'] = $goodsInfo['goods_image'];
            model('Ordergoods')->_setOrderGoodsInfo($orderGoodsData);

            $payData = array(
                'pay_sn' => getSN('PAYMENT'),
                'uid'           => $data['uid'],
                'balance_paid'  => $balance_paid,
                'coupon_id'     => 0,
                'coupon_paid'   => $coupon_paid,
                'order_amount'  => $order_amount,
                'pay_money'     => $order_amount,
                'pay_orders'    => $order_id,
            );
            //团长免单券支付，直接支付成功
            if($isGbCoupon)
            {
                $payData['pay_status'] = 2;
                $payData['pay_type'] = 'GB_COUPON';
                $payData['group_buying_coupon_id'] = $coupon_id;
                $payData['pay_timestamp'] = date("Y-m-d H:i:s");
            }
            $payment_info = model('Payment')->_setPaymentInfo($payData);
            if(isset($payData['pay_status']) &&  $payData['pay_status'] == 2)
            {
                $this->data['data'] =array(
                    'orderId' => $order_id,
                    'orderType' => 2,
                    'paySn' => $payment_info['pay_sn'],
                    'payPrice' => $order_amount,
                    'payAmount' => $order_amount,
                    'times'      =>  0,
                    'payStatus' => 2,
                );
                //更新订单状态=已支付
                model("Orders")->where(array("order_id" => $order_id))->update(array("order_status" => 2, "pay_status" => 2));
                //更新用户免单券使用状态
                $groupInfo = model("Groupbuyinggroup")->where(array("group_buying_code" => $orderData['source_code']))->find();
                $saveData = array(
                    "status" => 1,
                    "group_buying_id" => (int)$groupInfo['group_buying_id'],
                    "update_time" => time(),
                );
                model("Groupbuyingusercoupon")->where(array("user_coupon_id" => $coupon_id))->update($saveData);
            }
            else
            {
                $this->data['data'] =array(
                    'orderId' => $order_id,
                    'orderType' => 1,
                    'paySn' => $payment_info['pay_sn'],
                    'payPrice' => $order_amount,
                    'payAmount' => $order_amount,
                    'times'      =>  ((strtotime($payment_info['order_timestamp']) + 1*3600) - time()) >= 0 ? (strtotime($payment_info['order_timestamp']) + 1*3600) - time() : 0,
                    'payStatus' => 1
                );
            }
            model('Orders')->commit();
        }catch (\Exception $e){
            model('Orders')->rollback();
            $this->data['msg'] = "下单失败";
        }
        if(isset($payData['pay_status']) &&  $payData['pay_status'] == 2)
        {
            //处理团购信息
            $this->_groupBuying($orderData['source_code'], $order_id, $data['uid']);
        }
        return $this->data;
    }

    /**
     * 检测用户团长免单券
     * @param $uid
     * @param $coupon_id
     */
    private function _checkUserGroupBuyingCoupon($uid, $coupon_id)
    {
        $couponInfo = model("Groupbuyingusercoupon")->where(array("uid" => $uid, "user_coupon_id" => $coupon_id, "status" => 0))->find();
        if(!isset($couponInfo['user_coupon_id']))
        {
            return false;
        }
        if($couponInfo['end_time'] < time())
        {
            return false;
        }
        return true;
    }

    /**
     * 收银台页面
     */
    public function cashier()
    {
        if(Request::instance()->isPost())
        {
            $data = $this->params;
            $where= array(
                "uid=".$data['uid'],
                $data['order_id']." in (`pay_orders`)"
            );
            $payinfo = model('Payment')->_getOrderPaymentInfo($where);
            if(!$payinfo)
            {
                json_error_exception(4001);
            }
            if($payinfo['order_status'] !== 1)
            {
                json_error_exception(4003);
            }
            $times = ((strtotime($payinfo['order_timestamp']) + 24*3600) - time()) >= 0 ? (strtotime($payinfo['order_timestamp']) + 24*3600) - time() : 0;
            if($times === 0)
            {
                json_error_exception(4004);
            }
            $this->data['data'] =array(
                'orderType' => 1,
                'paySn' => $payinfo['pay_sn'],
                'payPrice' => $payinfo['pay_money'],
                'times'      =>  $times
            );
            return $this->data;
        }else{
            json_error_exception(1001);
        }
    }

    /**
     * 订单支付
     */
    public function pay()
    {
        if(Request::instance()->isPost())
        {
            $param = $this->params;
            $where= array(
                "uid=".$param['uid'],
                $param['order_id']." in (`pay_orders`)"
            );
            $payinfo = model('Payment')->_getOrderPaymentInfo($where);
            if(!$payinfo)
            {
                json_error_exception(4001);
            }
            if($payinfo['order_status'] !== 1)
            {
                json_error_exception(4003);
            }
            
            $wechat = new WechatService();
            $alipay = new AlipayService();
            $wxpayInfo  = $wechat->application($payinfo['pay_sn'],$payinfo['pay_money']);
            $alipayInfo = $alipay->application($payinfo['pay_sn'],$payinfo['pay_money']);
            $this->data['data']['wxpay'] = $wxpayInfo;
            $this->data['data']['alipay'] = $alipayInfo;
            return $this->data;
        }else{
            json_error_exception(1001);
        }
    }
    

    /**
     * 订单列表
        1 待付款
        2 已付款
        3 已确认收货
        4 已取消
        5 已完成
        6 退款中
        7 退款完成
     */
    public function lists()
    {
        if(Request::instance()->isPost())
        {
            $data = $this->params;
            $orderList = array();
            $payOrderList = model('Payment')->_getOrderPayment($data);
            if(!empty($payOrderList)){
                $i = 0;
                foreach ($payOrderList as $k=>$v)
                {
                    if(intval($v['pay_status']) === 1){
                        $order = model('Orders')->_getOrderList(array('pay_id='.$v['pay_id']));
                        if(!empty($order))
                        {
                            $orderList[$i]['orderId'] = $order[0]['order_id'];

                            $orderList[$i]['pay_status'] = $v['pay_status'];
                            $orderList[$i]['pay_id'] = $v['pay_id'];

                            $orderList[$i]['orderSn'] = $order[0]['order_sn'];
                            $orderList[$i]['orderType'] = $order[0]['order_type'];
                            $orderList[$i]['isShowShipping'] = $order[0]['is_show'] == 1 ? true : false;
                            $orderList[$i]['orderStatus'] = $order[0]['order_status'];
                            $orderList[$i]['payStatus'] = $order[0]['pay_status'];
                            $orderList[$i]['shippingStatus'] = $order[0]['shipping_status'];
                            $orderList[$i]['payAmount'] = $v['pay_money'];
                            $orderList[$i]['needPayAmount'] = $v['pay_money'];
                            $goodsList =array();
                            foreach ($order as $kk=>$vv)
                            {
//                                $goodsList[$kk]['goodsId'] = $vv['order_goods_id'];//why ?????
                                $goodsList[$kk]['goodsId'] = $vv['goods_id'];
                                $goodsList[$kk]['goodsName'] = $vv['goods_name'];
                                $goodsList[$kk]['goodsAttr'] = $vv['goods_attr'];
                                $goodsList[$kk]['goodsNumber'] = $vv['order_goods_number'];
                                $goodsList[$kk]['goodsImage'] = trim($vv['goods_image']);
                                $goodsList[$kk]['isComment']  = Comment::checkGoodsComment($order[0]['order_id'],$vv['goods_id']);
                            }
                            $orderList[$i]['goodsList'] = $goodsList;
                            $i++;
                        }
                    }else if(intval($v['pay_status']) === 2)
                    {
                        $list = array();
                        $order = model('Orders')->_getOrderList(array('pay_id='.$v['pay_id']));
                        if(!empty($order)){
                            foreach ($order as $kk=>$vv)
                            {
                                $list[$vv['order_id']][] = $vv;
                            }
                            sort($list);
                            foreach ($list as $lk=>$lv)
                            {
                                $orderList[$i]['orderId'] = $lv[0]['order_id'];

                                $orderList[$i]['pay_status'] = $v['pay_status'];
                                $orderList[$i]['pay_id'] = $v['pay_id'];


                                $orderList[$i]['orderSn'] = $lv[0]['order_sn'];
                                $orderList[$i]['orderType'] = $order[0]['order_type'];
                                $orderList[$i]['isShowShipping'] = $order[0]['is_show'] == 1 ? true : false;
                                $orderList[$i]['orderStatus'] = $lv[0]['order_status'];
                                $orderList[$i]['payStatus']  = $lv[0]['pay_status'];
                                $orderList[$i]['shippingStatus'] = $lv[0]['shipping_status'];
                                $orderList[$i]['payAmount'] =  $v['pay_money'];
                                $orderList[$i]['needPayAmount'] = $v['pay_money'];
                                if(intval($lv[0]['order_status']) === 7)
                                {
                                    $orderList[$i]['refundMsg'] = $lv[0]['pay_type'] == 'WXPAY' ? '微信' : ($lv[0]['pay_type'] == 'ALIPAY' ? '支付宝' : '账户余额');
                                }
                                $goodsList =array();
                                foreach ($lv as $kk=>$vv){
//                                    $goodsList[$kk]['goodsId'] = $vv['order_goods_id'];why ??????
                                    $goodsList[$kk]['goodsId'] = $vv['goods_id'];
                                    $goodsList[$kk]['goodsName'] = $vv['goods_name'];
                                    $goodsList[$kk]['goodsAttr'] = $vv['goods_attr'];
                                    $goodsList[$kk]['goodsNumber'] = $vv['order_goods_number'];
                                    $goodsList[$kk]['goodsImage'] = trim($vv['goods_image']);
                                    $goodsList[$kk]['isComment']  = Comment::checkGoodsComment($lv[0]['order_id'],$vv['goods_id']);
                                }
                                $orderList[$i]['goodsList'] = $goodsList;
                                $i++;
                            }
                        }
                    }
                }
                if(!empty($orderList)){
                    $this->data['data']['orderList'] = $orderList;
                }
            }
            $this->data['data']['orderCount'] = model('Orders')->_getOrderStatusListCount($data['uid']);
            return $this->data;
        }else{
            json_error_exception(1001);
        }
    }

    /**
     * 订单详情
     */
    public function detail()
    {
        if(Request::instance()->isPost())
        {
            $data = $this->params;
            $payment = model('Payment')->_getPaymentInfoByOrderId($data['order_id']);
            if(!empty($payment))
            {
                if(intval($payment['pay_status']) === 1){
                    $data['order_id'] = explode(',',$payment['pay_orders']);
                }
                $this->data['data'] = $this->getOrderDetail($data);
            }
            return $this->data;
        }else{
            json_error_exception(1001);
        }
    }


    /**
     * 订单状态的更改
     */
    public function change()
    {
        if(Request::instance()->isPost())
        {
            $data = $this->params;
            $update = array();
            $order_info = model('Orders')->where('order_id','=',$data['order_id'])->find();
            if(empty($order_info))
            {
                json_error_exception(4001);
            }

            $msg = '';
            switch ($data['action'])
            {
                case 'confirm':  //确认收货
                    $update['order_status'] = 3;
                    $update['update_timestamp'] = date('Y-m-d H:i:s',time());
                    $msg = "确认收货";
                    break;
                case 'cancel':
                    if(intval($order_info['pay_status']) === 2)
                    {
                        json_error_exception(4002);
                    }
                    $update['order_status'] = 4;
                    $update['update_timestamp'] = date('Y-m-d H:i:s',time());
                    $msg = "取消订单";
                    break;
                case 'delete':
                    $update['order_status'] = 5;
                    $update['is_delete']    = 1;
                    $update['update_timestamp'] = date('Y-m-d H:i:s',time());
                    $msg = "删除订单";
                    break;
            }
            $result = model('Orders')->where('order_id','=',$data['order_id'])->update($update);


            if($result !== false)
            {
                //获取用户等级
                $user_info = model('User')->_getOneUser(array('uid='.$order_info['uid']));
                $vip_level = model('Vip')->_getUserVipLevelInfo($user_info['level_id']);

                //计算商家收入
                $order_goods = model('Orders')->_getOrderList(array('order_id ='.$data['order_id']));
                $commission = 0;
                $income = 0;
                foreach ($order_goods as $k=>$v)
                {
                    $income     += sprintf("%.2f",$v['cost_price'] * $v['order_goods_number']);
                    $commission +=  sprintf("%.2f", $v['goods_commission'] * $v['order_goods_number'] * ($vip_level['shop_commission']/100));
                }

                if($update['order_status'] == 3)
                {
                    //分享得佣金
                    if($order_info['share_uid'] > 0)
                    {

                        model('Balance')->where(
                            array(
                                'calculation_type'=>3,
                                'calculation_type_dataid'=>$data['order_id']
                            )
                        )->update(array('status'=>1));
                    }

                    if($order_info['shop_id'] > 0)
                    {
                        model('Income')->insert(
                            array(
                                'shop_id'   => $order_info['shop_id'],
                                'type'      => 'PLUS',
                                'order_id'  => $order_info['order_id'],
                                'money'     => $income,
                                'note'      => '卖出商品收入',
                                'create_timestamp' => date('Y-m-d H:i:s',time())
                            )
                        );
                    }

                }


                if(intval($user_info['level_id']) > 0)
                {
                    if($update['order_status'] == 3)
                    {
                        //计算佣金
                        #TODO 获取商品本店佣金
                        model('Balance')->where(
                            array(
                                'calculation_type'=>3,
                                'calculation_type_dataid'=>$data['order_id']
                            )
                        )->update(array('status'=>1));


                        //门槛,晋级,换购直接完成
                        if(in_array($order_info['order_type'],array(3,4,5)))
                        {
                            model('Orders')->where(array('order_id'=>$data['order_id']))->update(array('order_status'=>5,'update_timestamp'=>date('Y-m-d H:i:s',time())));
                        }


                        //获取用户成为达人后的所有订单
                        $all_order = model('Orders')->where(array('order_type'=>1,'create_timestamp'=>array('gt',$user_info['vip_timestamp']),'uid'=>$order_info['uid'],'order_status'=>array('in',array(3,5))))->select();
                        //订单总额
                        $orderAmount = 0;
                        foreach ($all_order as $k=>$v)
                        {
                            $orderAmount  += $v['order_amount'];
                        }

                        if($orderAmount > 0)
                        {
                            $next_level = model('Vip')->where('consumption','<=',$orderAmount)->order("consumption desc")->find();

//                            $true  = false;
                            if(intval($user_info['level_id']) <= intval($next_level['level_id']))
                            {
                                $true = intval($next_level['level_id']) == 7 ? false : true;
                            }else{
                                $true = intval($user_info['level_id']) == 7 ? true : false;
                            }
                            if($true)
                            {
                                model('User')->where('uid','=',$order_info['uid'])->update(array('level_id'=>$next_level['level_id']));

                                $groupList = model('Group')->where('uid','=',$order_info['uid'])->select();
                                if(!empty($groupList))
                                {
                                    model('Group')->where('uid','=',$order_info['uid'])->update(array('group_member_level'=>$next_level['level_id'],'update_timestamp'=>date('Y-m-d H:i:s',time())));

                                    foreach ($groupList as $k=>$v)
                                    {
                                        //获取团队团长
                                        $group_leader = model('Group')->where(array('group_sn'=>$v['group_sn'],'is_group_leader'=>1))->find();
                                        //当前团队级别
                                        $group_info = model('Vipgroup')->where('id','=',$v['group_level_id'])->find();
                                        //团队中资深以上人数
                                        $memberCount = model('Group')->where(array('child_level'=>1,'group_sn'=>$v['group_sn'],'group_member_level'=>array("NOTIN",array(7))))->count();
                                        //当前团队资深以上人数能达到的团队级别
                                        $next_group = model('Vipgroup')->where(array('group_qualifications_vip_level_number'=>array('elt',$memberCount)))->order("group_qualifications_vip_level_number desc")->find();
                                        //团队是否晋级
                                        if(intval($next_group['group_qualifications_vip_level_number']) >= intval($group_info['group_qualifications_vip_level_number']))
                                        {
                                            if($next_group['id'] != $group_info['id'])
                                            {
                                                //升级团队
                                                model('Group')->where('group_sn','=',$v['group_sn'])->update(array('group_level_id'=>$next_group['id'],'update_timestamp'=>date('Y-m-d H:i:s',time())));
                                                //直接晋级的人数
                                                //$zjjj_count = model('Group')->where(array('is_group_leader'=>0,'group_sn'=>$v['group_sn'],'is_zjjj'=>1))->count();
                                                $shengjiren = model('Group')->where(array('is_group_leader'=>0,'is_used'=>0,'child_level'=>1,'group_sn'=>$v['group_sn']))->select();
                                                $zjjj_count = 0;
                                                foreach ($shengjiren as $sk=>$sv)
                                                {
                                                    if(intval($sv['is_zjjj']) == 1)
                                                    {
                                                        //记录使用
                                                        model('Group')->where(array('uid'=>$sv['uid'],'group_sn'=>$v['group_sn']))->update(array('is_used'=>1));
                                                        $zjjj_count++;
                                                    }
                                                }
                                                //判断团队晋级的时间  如果大于三个月 * 0.5
                                                $days = $this->diffBetweenTwoDays(date('Y-m-d',strtotime($v['update_timestamp'])),date('Y-m-d',time()));
                                                $reward = 0;
                                                if($next_group['group_qualifications_vip_level_number'] > 1)
                                                {
                                                    $reward = sprintf("%.2f", $next_group['level_reward'] / 5 * $zjjj_count);

                                                }
                                                if($days > 90)
                                                {
                                                    $reward = sprintf("%.2f", $reward * 0.5);
                                                }
                                                if($reward > 0)
                                                {
                                                    model('Balance')->insert(
                                                        array(
                                                            'uid'=>$group_leader['uid'],
                                                            'calculation_type'=>6,
                                                            'calculation_type_dataid'=>$v['id'],
                                                            'computing_method'=>'PLUS',
                                                            'money'=>$reward >= 3000 ? 3000 : $reward,
                                                            'note'=>'团队晋级',
                                                            'status'=>1,
                                                            'create_timestamp'=>date('Y-m-d H:i:s',time())
                                                        )
                                                    );
                                                }
                                            }
                                        }
                                    }


                                }
                            }
                        }

                    }
                }
                $log = array(
                    'order_id'=>$data['order_id'],
                    'action_user'=>getUserName($data['uid']),
                    'order_status' => $update['order_status'],
                    'action_place'=>1,
                    'action_note'=>$msg,
                    'log_timestamp'=>date('Y-m-d H:i:s',time())
                );
                model('Orderaction')->insert($log);
                $this->data['msg'] = "更改成功";
            }
            return $this->data;
        }else{
            json_error_exception(1001);
        }
    }


    /**
     * 增删库存
     * @param int $order_id
     * @param string $type
     */
    public static function change_inventory($order_id = 0 , $type = 'PLUS' )
    {
        $order_goods = model('Ordergoods')->where('order_id',"=",$order_id)->select();
        foreach ($order_goods as $k=>$v)
        {
            $where = array();
            $where['goods_id'] = $v['goods_id'];
            $goods_info = model('Goods')->where($where)->find();
            $goods_number = $goods_info['goods_number'];
            if($type == 'PLUS'){
                $goods_number = intval($goods_info['goods_number'] + $v['goods_number']) > 0 ? intval($goods_info['goods_number'] + $v['goods_number']) : 0;
            }
            IF($type == 'REDUCE'){
                $goods_number = intval($goods_info['goods_number'] - $v['goods_number']) > 0 ? intval($goods_info['goods_number'] - $v['goods_number']) : 0;
            }
            model('Goods')->startTrans();
            try{
                model('Goods')->where($where)->update(array('goods_number'=>$goods_number));
                if($v['product_id'] > 0)
                {
                    $where['product_id'] = $v['product_id'];
                }
                $goods_product_info = model('Products')->where($where)->find();
                if($goods_product_info && !empty($goods_product_info)){
                    $product_number = $goods_product_info['product_number'];
                    if($type == 'PLUS'){
                        $product_number = intval($goods_product_info['product_number'] + $v['goods_number']) > 0 ? intval($goods_product_info['product_number'] + $v['goods_number']) : 0;
                    }
                    IF($type == 'REDUCE'){
                        $product_number = intval($goods_product_info['product_number'] - $v['goods_number']) > 0 ? intval($goods_product_info['product_number'] - $v['goods_number']) : 0;
                    }
                    model('Products')->where($where)->update(array('product_number'=>$product_number));
                }
                model('Goods')->commit();
            }catch (\Exception $e){
                model('Goods')->rollback();
            }
        }

    }

    /**
     * 达人商品减库存
     * @param int $order_id
     */
    public static function change_vip_goods_inventory($order_id = 0)
    {
        $order_info = model('Orders')->where('order_id',"=",$order_id)->find();
        //确定关系
        $invitaion = model('Invitation')->where('uid','=',$order_info['uid'])->order("id desc")->find();
        if($invitaion){
            model('Invitation')->where(array('uid'=>$order_info['uid'],'inviter'=>$invitaion['inviter']))->update(array('status'=>1,'create_timestamp'=>date('Y-m-d H:i:s',time())));

            //消除排序
            if($invitaion['inviter'] > 0)
            {
                model('Vip')->_setChecked($invitaion['inviter']);
            }

            //获取上级团队信息
            $group_list = model('Group')->where('uid','=',$invitaion['inviter'])->select();
            if(!empty($group_list))
            {
                foreach ($group_list as $k=>$v)
                {
                    if(intval($v['child_level']) === 1)
                    {
                        $child_level = 2;
                        if(intval($v['is_group_leader']) === 1)
                        {
                            $child_level = 1;
                        }
                        model('Group')->insert(
                            array(
                                'group_level_id'  => $v['group_level_id'],
                                'group_sn'        => $v['group_sn'],
                                'is_group_leader' => 0,
                                'child_level'     => $child_level,
                                'group_member_level'=> 7,
                                'is_zjjj'         => 0,
                                'uid'             => $order_info['uid'],
                                'create_timestamp'=>date('Y-m-d H:i:s',time()),
                                'update_timestamp'=>date('Y-m-d H:i:s',time())
                            )
                        );
                    }
                }
            }
        }


        $order_goods = model('Ordergoods')->where('order_id',"=",$order_id)->select();
        $where = array();
        $level_id = 0;
        
        foreach ($order_goods as $k=>$v)
        {

            $goods_info = model('Vip')->_getUserVipGoodsInfo($v['goods_id']);
            $goods_number = intval($goods_info['goods_number'] - $v['goods_number']) > 0 ? intval($goods_info['goods_number'] - $v['goods_number']) : 0;
            $level_id = 7;  // 达人级别

            model('Uservipcoupon')->startTrans();
            try{
                model('Vipgoods')->where(array('vip_goods_id'=>$v['goods_id']))->update(array('goods_number'=>$goods_number));

                //代品券
                if(intval($goods_info['goods_type']) == 3)
                {
                    $coupon = array(
                        'uid'=>$order_info['uid'],
                        'data_type'=>1,
                        'data_id'=>$order_id,
                        'number' => $v['goods_number'],
                        'create_timestamp'=>date('Y-m-d H:i:s',time())
                    );
                    model('Uservipcoupon')->insert($coupon);
                    model('Orders')->where(array('order_id'=>$order_id))->update(
                        array(
                            'order_status'=>5,
                            'shipping_status'=>2,
                        )
                    );
                }

                model('Uservipcoupon')->commit();
            }catch (\Exception $e){
                model('Uservipcoupon')->rollback();
            }
        }

        $inviter_userinfo = model('User')->where('uid','=',$invitaion['inviter'])->find();

        $company = $inviter_userinfo['company'] > 0 ?  $inviter_userinfo['company'] : $order_info['city'];

        $invitation_code = getSN('USER');
        model("User")->where('uid','=',$order_info['uid'])->update(array('company'=>$company,'level_id'=>$level_id,'invitation_code'=>$invitation_code,'vip_timestamp'=>date('Y-m-d H:i:s',time() - 30)));
    }


    /**
     * 订单退款申请
     */
    public function refund()
    {
        if(Request::instance()->isPost())
        {
            $data = $this->params;
            $order_info = model('Orders')->where(array('uid'=>$data['uid'],'order_id'=>$data['order_id']))->find();
            if($order_info['pay_status'] != 2){
                json_error_exception(4011);
            }
            $data['create_timestamp'] = date('Y-m-d H:i:s',time());
            $id = model('Refund')->insert($data);
            if($id > 0){
               if(model('Orders')->where('order_id','=',$order_info['order_id'])->update(array('order_status'=>6)) !== false){
                   $log = array(
                       'order_id'=>$data['order_id'],
                       'action_user'=>getUserName($data['uid']),
                       'order_status' => 6,
                       'action_place'=>1,
                       'action_note'=>'用户申请退款',
                       'log_timestamp'=>date('Y-m-d H:i:s',time())
                   );
                   model('Orderaction')->insert($log);
                   $this->data['msg'] = "申请成功";
               }
            }
            return $this->data;
        }else{
            json_error_exception(1001);
        }
    }



    /**
     * 订单详情数据
     * @param array $where
     * @return mixed
     */
    public static function getOrderDetail($where = array())
    {
        if(is_array($where['order_id'])){
            $orderWhere = "order_id in (". implode(',',$where['order_id']) .")";
        }else{
            $orderWhere = "order_id =".$where['order_id'];
        }
        $pay_type = Config::get("pay_type");
        $order = model('Orders')->_getOrderList(array($orderWhere,"uid=".$where['uid']));
        if(empty($order[0])){
            json_error_exception(4001);
        }
        $response['orderId']        = $order[0]['order_id'];
        $response['orderSn']        = $order[0]['order_sn'];
        $response['orderType']        = $order[0]['order_type'];
        $response['isShowShipping'] = $order[0]['is_show'] == 1 ? true : false;
        $response['orderStatus']    = $order[0]['order_status'];
        $response['payStatus']      = $order[0]['pay_status'];
        $response['shippingStatus'] = $order[0]['shipping_status'];
        $response['consignee']      = $order[0]['consignee'];
        $response['mobile']         = $order[0]['mobile'];

        if(intval($order[0]['order_status']) === 7)
        {
            $response['refundMsg'] = $order[0]['pay_type'] == 'WXPAY' ? '微信' : ($order[0]['pay_type'] == 'ALIPAY' ? '支付宝' : '账户余额');
        }
        if($order[0]['order_type'] == 2 && $order[0]['source_code'] != ''){
            $group = model('Groupbuyinggroup')->_getBGOrderInfo($order[0]['source_code']);
            if(!empty($group)){
                $response['groupStatus'] = $group['group_status'];
                $response['joinedNumber'] = $group['joined_number'];
                $response['userNumber'] = $group['user_number'];
                $remainingNum = $group['user_number'] - $group['joined_number'] > 0 ? $group['user_number'] - $group['joined_number'] : 0;
                $response['share'] = array(
                    'shareTitle' => '我买了"'.$group['group_buying_price'].'元 '.$group['goods_name'].'"，'.$group['user_number'].'人团',
                    'shareDescription' => '【还差'.$remainingNum.'人】拼团！超值好货拼团'.$group['group_buying_price'].'元 '.$group['goods_name'],
                    'shareTitleImage' => $group['goods_image'],
                    'shareUrl' => 'http://m.ssdtt.com/#/myGroups?id='.$group['group_buying_id']."&id2=".$group['group_buying_goods_id'],
                );
            }
        }
        if(in_array($order[0]['province'],array(2,25,27,32))){
            $response['address'] = $order[0]['city_cn'].$order[0]['district_cn'].$order[0]['address'];
         }else{
            $response['address'] = $order[0]['province_cn'].$order[0]['city_cn'].$order[0]['district_cn'].$order[0]['address'];
        }
        $goodsList = array();
        $goods_amount =  $shipping_fee = 0;
        foreach ($order as $k=>$v)
        {
            $goodsList[$k]['goodsId']       = $v['goods_id'];
            $goodsList[$k]['goodsName']     = $v['goods_name'];
            $goodsList[$k]['goodsPrice']    = $v['goods_price'];
            $goodsList[$k]['goodsAttr']     = $v['goods_attr'];
            $goodsList[$k]['goodsNumber']   = $v['order_goods_number'];
            $goodsList[$k]['goodsImage']    = $v['goods_image'];
            $goodsList[$k]['isComment']     = Comment::checkGoodsComment($order[0]['order_id'],$v['goods_id']);
            $goods_amount += ($v['goods_price'] *  $v['order_goods_number']);
            $shipping_fee += $v['shipping_fee'];

        }
        $response['goodsList'] = $goodsList;
        //支付方式
        $response['payType']        = strval($order[0]['pay_type']) ? $pay_type[strval($order[0]['pay_type'])] : "在线支付";
        $response['shippingType']   = strval($order[0]['shipping_name']) ? strval($order[0]['shipping_name']) : "快递运输" ;
        //金额
        $response['goodsAmount']    = $goods_amount;
        $response['shippintAmount'] = $shipping_fee;
        $response['couponAmount']   = $order[0]['coupon_paid'];
        $response['balanceAmount']  = $order[0]['balance_paid'];
        $response['orderAmount']    = $order[0]['pay_money'];
        $response['orderTime']      = $order[0]['create_timestamp'];
        //剩余支付时间
        if($response['payStatus'] == 1) {
            if ($response['orderType'] == 1 || $response['orderType'] == 3)
            {
                //商城&达人订单24小时有效期
                $response['remainingTime'] = (strtotime($response['orderTime']) + 24 * 3600) - time() > 0 ?
                    (strtotime($response['orderTime']) + 24 * 3600) - time() : 0;
            }
            else if ($response['orderType'] == 2)
            {
                //团购订单1小时有效期
                $response['remainingTime'] = (strtotime($response['orderTime']) + 3600) - time() > 0 ?
                    (strtotime($response['orderTime']) + 3600) - time() : 0;
            }
        }

        $response['shop'] = array(
            'serviceId' => '适时达001',
            'groupId' => '0'
        );
        return $response;
    }
    /**
     * 获取用户默认地址
     * @param int $uid
     * @return array
     */
    public static function getUserDefaultAddress($uid = 0)
    {
        $response = array();
        if(!$uid)
            return $response;
        $address = model('Address')->_getUserDefaultAddress($uid);
        if(!empty($address)){
            $response['addressId'] = $address['address_id'];
            $response['consignee'] = strval($address['consignee']);
            $response['mobile'] = strval($address['mobile']);
            $response['province'] = $address['province'];
            $response['addressInfo'] = strval($address['province_name']).strval($address['city_name']).strval($address['district_name']).strval($address['address']);

        }
        return $response;
    }

    /**
     * 处理团购订单
     * @param string $groupBuyingCode
     * @param int $orderId
     * @param int $uid
     * @return bool
     */
    public function _groupBuying($groupBuyingCode = '', $orderId = 0, $uid = 0)
    {
        Log::write(json_encode(array($groupBuyingCode, $orderId, $uid)),'info');
        $condition = array("group_buying_code" => trim($groupBuyingCode));
        $groupInfo = model("Groupbuyinggroup")->where($condition)->find();
        if(empty($groupInfo))
        {
            return false;
        }
        $groupBuyingId = (int)$groupInfo['group_buying_id'];
        //检测团购团明细里是否有当前记录，如果有，则忽略
        $conditionDetail = $condition;
        $conditionDetail['group_buying_id'] = $groupBuyingId;
        $conditionDetail['uid'] = $uid;
        $detailInfo = model("Groupbuyinggroupdetail")->where($conditionDetail)->find();
        if(isset($detailInfo['uid']) && (int)$detailInfo['uid'] > 0)
        {
            return true;
        }
        $insertData = array(
            'group_buying_id' => $groupBuyingId,
            'uid' => $uid,
            'group_buying_code' => $groupBuyingCode,
            'order_id' => $orderId,
            'create_time' => time(),
            'update_time' => time(),
        );
        $result = model("Groupbuyinggroupdetail")->insert($insertData);
        if(!$result)
        {
            return false;
        }
        //更新参团人数
        $updateData = array(
            'joined_number' => (int)$groupInfo['joined_number'] + 1,
            'update_time' => time(),
        );
        //判断用户是不是团长
        if($uid == (int)$groupInfo['uid'])
        {
            //团过期时间24小时后
            $updateData['end_time'] = time() + 1 * 24 * 60 * 60;
        }
        //判断团是不是够人数
        $goodsInfo = model("Groupbuyinggoods")->where(array('group_buying_goods_id' => (int)$groupInfo['group_buying_goods_id']))->find();
        if($updateData['joined_number'] >= (int)$goodsInfo['user_number'] && (int)$groupInfo['status'] != 2)
        {
            //成团状态
            $updateData['status'] = 1;
        }
        $updateResult = model("Groupbuyinggroup")->where($condition)->update($updateData);
        //处理成团的消息
        if(isset($updateData['status']) && $updateData['status'] == 1)
        {
            //TODO 告知用户团成功
        }
        return true;
    }


    /**
     * 计算两日期之间的天数
     * @param $day1
     * @param $day2
     * @return float
     */
    protected  function diffBetweenTwoDays ($day1, $day2)
    {
        $second1 = strtotime($day1);
        $second2 = strtotime($day2);

        if ($second1 < $second2) {
            $tmp = $second2;
            $second2 = $second1;
            $second1 = $tmp;
        }
        return ($second1 - $second2) / 86400;
    }
}