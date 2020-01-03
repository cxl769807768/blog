<?php

namespace app\api\controller\v1;

use think\Controller;
use think\Db;
use think\Log;
use think\Request;
use wechat\wxpay\WxpayApp;
use wechat\wxpay\WxpayJsapi;
use aliyun\alipay\AlipayApp;
use aliyun\sms\Sms;

class Getpayresult extends Controller
{
    /**
     * [alipay_notify description]
     * @Author   xiaolong
     * @DateTime 2019-01-02T16:20:37+0800
     * @return   接收支付宝回调
     */
    public function alipay_notify(){
       
        $ali = new AlipayApp();
        $str = $ali->createLinkstring($_POST);
        Log::write('阿里云app支付的返回数据为'.$str,'notice');
        $seller = $ali->seller;
        //$_POST['fund_bill_list'] = str_replace('\\', '', $_POST['fund_bill_list']);
        $_POST['version'] = "1.0";
        unset($_POST['controller']);
        unset($_POST['function']);
        $verify = $ali->verifySign($_POST);
        if($verify){
            if ($_POST['trade_status'] == 'TRADE_SUCCESS') {//如果支付成功
                $out_trade_no = $_POST['out_trade_no'];//获取订单号
                $payId = $_POST['trade_no'];
                $paytime = $_POST['notify_time'];
                $payMoney = $_POST['total_amount'];
                $seller_id = $_POST['seller_id'];
                $order = model('BuyOrder')->where('order_no',$out_trade_no)->find();
                if(empty($order)){
                    echo 'failure';
                    exit;
                }
                if($seller_id!==$seller){
                    echo 'failure';
                    exit;
                }
                if(floatval($order['price'])!==floatval($payMoney)){
                    Log::write('阿里云app支付的返回数据为'.$order['price'],'notice');
                    Log::write('阿里云app支付的返回数据为'.$payMoney,'notice');
                    echo 'failure';
                    exit;
                }
                $cmods = array("course"=>'课件','press_card'=>'小记者');
                $code = make_code(6);
                // 启动事务
                Db::startTrans();
                try{
                    $buyOrder = $moneyLog = false;
                    $buyOrders = Db::table('buy_order')->where('order_no',$out_trade_no)->update(['state' => 2,'payid'=>$payId,'paytime'=>$paytime,'confirm_code'=>$code]);
                    if($buyOrders){
                        $buyOrder = true;
                    }
                    $balance = Db::table('purse')->where('id',1)->value('money');
                    $money = floatval($balance) + floatval($payMoney)*config('pay.charge');
                    Db::table('purse')->where('id',1)->update(['money' => $money]);
                    $moneyLogs = Db::table('money_log')->insert(['uid'=>1,'role_id'=>1,'cmod'=>$cmods[$order['cmod']],'xid'=>$order['xid'],'order_no'=>$out_trade_no,'tip'=>2,'outmoney'=>0,'inmoney'=>floatval($payMoney)*config('pay.charge'),'create_time'=>date('Y-m-d H:i:s',time())]);
                    if($moneyLogs){
                        $moneyLog = true;
                    }
                    if($order['cmod'] == 'course'){
                        Db::table('course')->where('id',$order['xid'])->setInc('sales',$order['travel_num']);
                    }
                    if($order['type'] == 1){
                        Db::table('student_info')->where('id',$order['sid'])->update(['isBuy'=>1]);
                    }
                    
                    
                    // 提交事务
                    if($buyOrder && $moneyLog){
                        Db::commit();  
                    }    
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                }
                if($order['cmod'] == 'course'){
                    $goods = model('Course')->where('id',$order['xid'])->find()->toArray();
                }
                $sms  = new Sms();
                if($order['type'] == 4){
                    
                    $phone = model('User')->where('id',$order['uid'])->value('phone');    
                    $sms->send($phone, '研学淘', 'SMS_157280142', ['code' => $code,'title'=>$goods['name'],'order'=>$out_trade_no]);
                    
                }else{
                    $sms->send('13312509319', '研学淘', 'SMS_171111096', ['title'=>$goods['name'],'money'=>$payMoney,"concact"=>$order['contact'],"phone"=>$order['phone']]);
                    if($goods['owner']!==1){
                        $adminId = model('AuthRoleAdmin')->where('role_id',$goods['owner'])->value("admin_id");
                        $admin = model("AuthAdmin")->where("id",$adminId)->find();
                        if(!empty($admin)){
                            $admin = $admin->toArray();
                            $sms->send($admin['phone'], '研学淘', 'SMS_171111096',  ['title'=>$goods['name'],'money'=>$payMoney,"concact"=>$order['contact'],"phone"=>$order['phone']]);
                        }
                    }
                    
                }
                $owner = array(1,$goods['owner']);
                $message = array();
                foreach ($owner as $key => $value) {
                    $message[$key]["owner"] = $value;
                    $message[$key]["cmod"] = "buyOrder";
                    $message[$key]["xid"] = $order['id'];
                    $message[$key]["cmod_tip"] = "buy";
                    $message[$key]["remark"] = "新订单";
                }
                model("Message")->saveAll($message);  
                echo 'success';
                exit;
            }
        }else{
            echo 'failure';
            exit;
        }
        
    
    }
    /**
     * @Author   xiaolong
     * @DateTime 2018-12-28T17:18:31+0800
     * @return   接收微信app支付的回调
     */
    public function wxpayapp_notify(){
        $testxml  = file_get_contents("php://input");
        Log::write('微信app支付的返回数据为'.$testxml,'notice');
        $jsonxml = json_encode(simplexml_load_string($testxml, 'SimpleXMLElement', LIBXML_NOCDATA));
        $result = json_decode($jsonxml, true);//转成数组，
        if(!empty($result)){
            //如果成功返回了
            $out_trade_no = $result['out_trade_no'];
            $payMoney = $result['total_fee']/100;
            $payId = $result['transaction_id'];
            $paytime = $result['time_end'];
            $sign = $result['sign'];
            unset($result['sign']);
            $wx = new WxpayApp();
            $createSigin = $wx->getSign($result);
            if ( $sign == $createSigin) {
                $order = model('BuyOrder')->where('order_no',$out_trade_no)->find();
                Log::write('微信app支付金额0000000000000','notice');
                if(empty($order)){
                    Log::write('微信app支付金额aaaaaaaaa','notice');
                    exit('<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[此订单不存在]]></return_msg></xml>'); 
                }
                //校验返回的订单金额是否与商户侧的订单金额一致。修改订单表中的支付状态。
                if(floatval($order['price'])!==floatval($payMoney)){
                    Log::write('微信app支付金额bbbbbb','notice');
                    exit('<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[支付金额与订单金额不对应]]></return_msg></xml>'); 
                }
            
                if($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS'){
                    Log::write('微信app支付金额dddddddddd','notice');
                    $code = make_code(6);
                    $cmods = array("course"=>'课件','press_card'=>'小记者');
                    // 启动事务
                    Db::startTrans();
                    Log::write('微信app支付金额ccccccccc','notice');
                    try{
                        Log::write('微信app支付金额1111111111111111','notice');
                        $buyOrder = $moneyLog = false;
                        $buyOrders = Db::table('buy_order')->where('order_no',$out_trade_no)->update(['state' => 2,'payid'=>$payId,'paytime'=>$paytime,'confirm_code'=>$code]);
                        if($buyOrders){
                            $buyOrder = true;
                        }
                        
                        Log::write('微信app支付金额222222222222222','notice');
                        $balance = Db::table('purse')->where('id',1)->value('money');
                        Log::write('微信app支付金额33333333333333','notice');
                        $money = floatval($balance) + floatval($payMoney)*config('pay.charge');
                        Log::write('微信app支付金额4444444444444444444'.$money,'notice');
                        Db::table('purse')->where('id',1)->update(['money' => $money]);
                        Log::write('微信app支付金额55555555555555555'.$money,'notice');
                        $moneyLogs = Db::table('money_log')->insert(['uid'=>1,'role_id'=>1,'order_no'=>$out_trade_no,'cmod'=>$cmods[$order['cmod']],'xid'=>$order['xid'],'tip'=>2,'outmoney'=>0,'inmoney'=>floatval($payMoney)*config('pay.charge'),'create_time'=>date('Y-m-d H:i:s',time())]);
                        if($moneyLogs){
                            $moneyLog = true;
                        }
                        Log::write('微信app支付金额666666666666666666'.$money,'notice');
                        
                        if($order['cmod'] == 'course'){
                            Db::table('course')->where('id',$order['xid'])->setInc('sales',$order['travel_num']);
                        }
                        if($order['type'] == 1){
                            Db::table('student_info')->where('id',$order['sid'])->update(['isBuy'=>1]);
                        }    
                        // 提交事务
                        if($buyOrder && $moneyLog){
                            Db::commit();  
                        }
                          
                    } catch (\Exception $e) {
                        // 回滚事务
                        Db::rollback();
                    }
                    if($order['cmod'] == 'course'){
                        $goods = model('Course')->where('id',$order['xid'])->find()->toArray();
                    }
                    $sms  = new Sms();
                    if($order['type'] == 4){
                        
                        $phone = model('User')->where('id',$order['uid'])->value('phone');    
                        $sms->send($phone, '研学淘', 'SMS_157280142', ['code' => $code,'title'=>$goods['name'],'order'=>$out_trade_no]);
                    }else{
                        $sms->send('13312509319', '研学淘', 'SMS_171111096', ['title'=>$goods['name'],'money'=>$payMoney,"concact"=>$order['contact'],"phone"=>$order['phone']]);
                        if($goods['owner']!==1){
                            $adminId = model('AuthRoleAdmin')->where('role_id',$goods['owner'])->value("admin_id");
                            $admin = model("AuthAdmin")->where("id",$adminId)->find();
                            if(!empty($admin)){
                                $admin = $admin->toArray();
                                $sms->send($admin['phone'], '研学淘', 'SMS_171111096',  ['title'=>$goods['name'],'money'=>$payMoney,"concact"=>$order['contact'],"phone"=>$order['phone']]);
                            }
                        }
                        
                    }
                    $owner = array(1,$goods['owner']);
                    $message = array();
                    foreach ($owner as $key => $value) {
                        $message[$key]["owner"] = $value;
                        $message[$key]["cmod"] = "buyOrder";
                        $message[$key]["xid"] = $order['id'];
                        $message[$key]["cmod_tip"] = "buy";
                        $message[$key]["remark"] = "新订单";
                    }
                    model("Message")->saveAll($message);  
                    $return = ['return_code'=>'SUCCESS','return_msg'=>'OK'];
                    $xml = $wx->arrayToXml($return);
                    echo $xml;    
                }else{
                    exit('<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA['.$result['err_code_des'].']]></return_msg></xml>'); 
                }
                    
            }
        }
    }
    /**
     * @Author   xiaolong
     * @DateTime 2018-12-28T17:18:31+0800
     * @return   接收微信小程序支付的回调
     */
    public function wxpayapplet_notify(){
        $testxml  = file_get_contents("php://input");
        Log::write('微信小程序支付的返回数据为'.$testxml,'notice');
        $jsonxml = json_encode(simplexml_load_string($testxml, 'SimpleXMLElement', LIBXML_NOCDATA));
        $result = json_decode($jsonxml, true);//转成数组，
        if(!empty($result)){
            //如果成功返回了
            $out_trade_no = $result['out_trade_no'];
            $payMoney = $result['total_fee']/100;
            $payId = $result['transaction_id'];
            $paytime = $result['time_end'];
            $sign = $result['sign'];
            unset($result['sign']);
            $wx = new WxpayJsapi();
            $createSigin = $wx->getSign($result);
            if ( $sign == $createSigin) {
                $order = model('BuyOrder')->where('order_no',$out_trade_no)->find();
                if(empty($order)){
                    exit('<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[此订单不存在]]></return_msg></xml>'); 
                }
                //校验返回的订单金额是否与商户侧的订单金额一致。修改订单表中的支付状态。
                if(floatval($order['price'])!==floatval($payMoney)){
                
                    exit('<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[支付金额与订单金额不对应]]></return_msg></xml>'); 
                }
            
                if($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS'){
                    $code = make_code(6);
                    $cmods = array("course"=>'课件','press_card'=>'小记者');
                    // 启动事务
                    Db::startTrans();
                    try{
                        $buyOrder = $moneyLog = false;
                        $buyOrders = Db::table('buy_order')->where('order_no',$out_trade_no)->update(['state' => 2,'payid'=>$payId,'paytime'=>$paytime,'source'=>3,'confirm_code'=>$code]);
                        if($buyOrders){
                            $buyOrder = true;
                        }
                        $balance = Db::table('purse')->where('id',1)->value('money');
                        $money = floatval($balance) + floatval($payMoney)*config('pay.charge');
                        Db::table('purse')->where('id',1)->update(['money' => $money]);

                        $moneyLogs =  Db::table('money_log')->insert(['uid'=>1,'role_id'=>1,'order_no'=>$out_trade_no,'cmod'=>$cmods[$order['cmod']],'xid'=>$order['xid'],'tip'=>2,'outmoney'=>0,'inmoney'=>floatval($payMoney)*config('pay.charge'),'create_time'=>date('Y-m-d H:i:s',time())]);
                        if($moneyLogs){
                            $moneyLog = true;
                        }
                        if($order['cmod'] == 'course'){
                            Db::table('course')->where('id',$order['xid'])->setInc('sales',$order['travel_num']);
                        }
                        if($order['type'] == 1){
                            Db::table('student_info')->where('id',$order['sid'])->update(['isBuy'=>1]);
                        }    
                        // 提交事务
                        if($buyOrder && $moneyLog){
                            Db::commit();
                        }
                        
                    } catch (\Exception $e) {
                        // 回滚事务
                        Db::rollback();
                    }
                    if($order['cmod'] == 'course'){
                        $goods = model('Course')->where('id',$order['xid'])->find()->toArray();
                    }
                    $sms  = new Sms();
                    if($order['type'] == 4){

                        $phone = model('User')->where('id',$order['uid'])->value('phone');    
                        $sms->send($phone, '研学淘', 'SMS_157280142', ['code' => $code,'title'=>$goods['name'],'order'=>$out_trade_no]);
                    }else{
                        $sms->send('13312509319', '研学淘', 'SMS_171111096', ['title'=>$goods['name'],'money'=>$payMoney,"concact"=>$order['contact'],"phone"=>$order['phone']]);
                        if($goods['owner']!==1){
                            $adminId = model('AuthRoleAdmin')->where('role_id',$goods['owner'])->value("admin_id");
                            $admin = model("AuthAdmin")->where("id",$adminId)->find();
                            if(!empty($admin)){
                                $admin = $admin->toArray();
                                $sms->send($admin['phone'], '研学淘', 'SMS_171111096',  ['title'=>$goods['name'],'money'=>$payMoney,"concact"=>$order['contact'],"phone"=>$order['phone']]);
                            }
                        }
                    }
                    $owner = array(1,$goods['owner']);
                    $message = array();
                    foreach ($owner as $key => $value) {
                        $message[$key]["owner"] = $value;
                        $message[$key]["cmod"] = "buyOrder";
                        $message[$key]["xid"] = $order['id'];
                        $message[$key]["cmod_tip"] = "buy";
                        $message[$key]["remark"] = "新订单";
                    }
                    model("Message")->saveAll($message);  
                    $return = ['return_code'=>'SUCCESS','return_msg'=>'OK'];
                    $xml = $wx->arrayToXml($return);
                    echo $xml;    
                }else{
                    exit('<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA['.$result['err_code_des'].']]></return_msg></xml>'); 
                }
                    
            }
        }else{
            //查询订单状态
            
        }
    }
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        echo 'index';
    }

    /**
     * 显示创建资源表单页.
     *
     * @return \think\Response
     */
    public function create()
    {
        //
    }

    /**
     * 保存新建的资源
     *
     * @param  \think\Request  $request
     * @return \think\Response
     */
    public function save(Request $request)
    {
        echo 'save';
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        //
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        //
    }
}
