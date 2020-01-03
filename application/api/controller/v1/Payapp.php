<?php

namespace app\api\controller\v1;

use app\api\controller\Common;
use think\Request;
use think\Db;
/**
 * 微信app支付
 */
use wechat\wxpay\WxpayApp;
/**
 * 支付宝app支付
 */
use aliyun\alipay\AlipayApp;

class Payapp extends Common
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        
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
        //获取订单号
        $data = $this->request->post();
        if(empty($data['id'])){
            $this->return_msg(0,'订单ID不能为空');
        }
        if(empty($data['pay_type'])){
            $this->return_msg(0,'支付方式不能为空');
        }
        //查询订单信息
        $order_info = model('BuyOrder')->where('id',$data['id'])->find();
        if($order_info['state']!==0){
            $this->return_msg(0,'订单已支付过了，亲');
        }
        if($order_info['type'] == 2){
            $this->return_msg(0,'团体订单暂时不支持线上支付，亲');
        }
        if($order_info['type'] == 1){
            $isBuy = model('StudentInfo')->where('id',$order_info['sid'])->value('isBuy');
            if($isBuy){
                $this->return_msg(0,'你已经为该学生购买过了亲!');
            }
        }
        //商品信息
        if($order_info['cmod'] == 'course'){
            $goodInfo = model('Course')->where('id',$order_info['xid'])->find();

        }elseif($order_info['cmod'] == 'press_card'){
            $goodInfo = model('PressApply')->where('id',$order_info['xid'])->find();
        }
        if(empty($goodInfo)){
            $this->return_msg(0,'购买商品不存在');
        }
        if($order_info['cmod'] == 'course'){
            $title = '课件('.$goodInfo['name'].')'.'支付';
        }elseif($order_info['cmod'] == 'press_card'){
            $title = '小记者('.$goodInfo['name'].')报名支付';
        }
        if($data['pay_type']!==$order_info['pay_type']){
            $res = model('BuyOrder')->isUpdate(true)->save(['pay_type'=>$data['pay_type'],'id'=>$data['id']]);
            if(empty($res)){
                $this->return_msg(0,'更新支付结果失败');
            }
        }
        $out_trade_no = $order_info['order_no'];//订单号
        //判断支付方式
        switch ($data['pay_type']) {
            case 2;//如果支付方式为支付宝支付

                $ali = new AlipayApp();
                $total_fee = $order_info['price'];
                $order = $ali->alipay($title, $total_fee,$out_trade_no); 
                if(empty($order)){
                    $this->return_msg(0,'对不起请检查相关参数!@');     
                }else{
                    model('BuyOrder')->where('id',$data['id'])->update(['ordertime'=>date("Y-m-d H:i:s",time())]);
                    $this->return_msg(200,'获取支付宝参数成功',$order);
                }
               
                break;
            //微信支付
            case 1;
                
                $wx = new WxpayApp();//实例化微信支付控制器
                $body = $title;  //支付说明
                $total_fee = $order_info['price'] * 100;//支付金额(乘以100)
                
                $order = $wx->getPrePayOrder($body, $out_trade_no, $total_fee);//调用微信支付的方法
                if ($order['return_code'] == 'SUCCESS' && $order['result_code'] == 'SUCCESS'){//判断返回参数中是否有prepay_id
                    $orderParams = $wx->getOrder($order['prepay_id']);//执行二次签名返回参数
                    model('BuyOrder')->where('id',$data['id'])->update(['ordertime'=>date("Y-m-d H:i:s",time())]);
                    $this->return_msg(200,'获取微信参数成功',$orderParams);
                } else {
                    $this->return_msg(400,$order['err_code_des']);
                }
                break;
        }
    }
    /**
     * [orderQuery description]
     * @Author   xiaolong
     * @DateTime 2019-01-09T14:58:22+0800
     * @param    [type]                   $out_trade_no 商户订单号
     * @return   [type]                                 微信订单查询
     */
    public function wxOrderQuery(){
        $out_trade_no = $this->request->param('order_no');
        if(empty($out_trade_no)){
            $this->return_msg(400,'商户订单号不能为空');
        }
        $res = model('BuyOrder')->where('order_no',$out_trade_no)->find();
        if(empty($res)){
            $this->return_msg(400,'订单不存在');
        }
        if($res['source']==3 ){
            $this->return_msg(400,'此方法只支持App订单');
        }
        if($res['pay_type']==2 ){
            $this->return_msg(400,'此方法只支持微信订单');
        }
        $wx = new WxpayApp();//实例化微信支付控制器

        $result = $wx->orderQuery($out_trade_no);
        if($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS'){
            if($result['trade_state'] == 'SUCCESS'){//支付成功
                // 启动事务
                Db::startTrans();
                try{
                    Db::table('buy_order')->where('order_no',$out_trade_no)->update(['state' => 2,'payid'=>$result['transaction_id'],'paytime'=>$result['time_end']]);
                    if($res['cmod'] == 'course'){
                        Db::table('course')->where('id',$res['xid'])->setInc('sales');
                    }    
                    // 提交事务
                    Db::commit();    
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                }
            }
            $this->return_msg(200,'订单查询成功',$result);
        }else{
            $this->return_msg(400,$result['err_code_des'],$result); 
        }
    }
     /**
     * [orderRefund description]
     * @Author   xiaolong
     * @DateTime 2019-01-11T14:42:47+0800
     * @return   [type]                   微信订单退款
     */
    public function wxOrderRefund(){
        $data = $this->request->param();
        if(empty($data['order_no'])){
            $this->return_msg(400,'商户订单号不能为空');
        }
        if(empty($data['remark'])){
            $this->return_msg(400,'退款说明不能为空');
        }
        $result = model('BuyOrder')->where('order_no',$data['order_no'])->find();
        if(empty($result)){
            $this->return_msg(0,'订单不存在');
        }
        if($result['state']==0){
            $this->return_msg(0,'此订单未支付，不能退款');
        }
        if($result['source']==3 ){
            $this->return_msg(400,'此方法只支持App订单');
        }
        if($result['pay_type']==2 ){
            $this->return_msg(400,'此方法只支持微信订单');
        }
        $data['refund_fee'] = empty($data['refund_fee']) ? $result['price'] : $data['refund_fee'];
        if(floatval($data['refund_fee'])>floatval($result['price'])){
            $this->return_msg(400,'退款金额不能大于订单的支付金额');
        }
        $refundData = [
            'buy_order_no'=>$data['order_no'],
            'order_no'=>createOrderNum(),
            'total_fee' => $result['price'],
            'refund_fee'=> $data['refund_fee'],
            'remark'=>$data['remark'],
            'refund_source'=>'wx'
        ];
        $insert = model('RefundRecord')->allowField(true)->save($refundData);
        if(empty($insert)){
            $this->return_msg(400,'插入退款记录失败');
        }else{
            $wx = new WxpayApp();//实例化微信支付控制器
            $res = $wx->refund($data['order_no'],$refundData['order_no'],$refundData['total_fee']*100,$data['refund_fee']*100);
            if($res['return_code'] == 'SUCCESS' && $res['result_code'] == 'SUCCESS'){
                $refund_fee = $res['refund_fee']/100;
                $cmods = array("course"=>'课件','press_card'=>'小记者','refundLose'=>'退款损失的手续费');
                // 启动事务
                Db::startTrans();
                try{
                    $refund_record = $state = $yxt_purse = $yxt_money_log = $yxt_money_charge_log = false;
                    $refund_records = Db::table('refund_record')->where('order_no',$refundData['order_no'])->update(['state' => 1,'refund_order_no'=>$res['refund_id'],'refund_fee'=>$refund_fee]);
                    if($refund_records){
                        $refund_record = true;
                    }
                    
                    //if(floatval($refund_fee) == floatval($refundData['total_fee'])){
                        $states = Db::table('buy_order')->where('order_no',$refundData['buy_order_no'])->update(['state' =>7]);
                    //}
                    if($states){
                        $state = true;
                    }
                    if($result['type'] == 1){
                        Db::table('student_info')->where('id',$result['sid'])->update(['isBuy' =>0]);
                        
                    }
                    
                    $balance = Db::table('purse')->where('id',1)->value('money');
                    $money = floatval($balance) - floatval($refund_fee)*config('pay.charge');
                    $yxt_purses = Db::table('purse')->where('id',1)->update(['money' => $money]);
                    if($yxt_purses){
                        $yxt_purse = true;
                    }

                    $yxt_money_logs = Db::table('money_log')->insert(['uid'=>1,'role_id'=>1,'order_no'=>$refundData['buy_order_no'],'cmod'=>$cmods[$result['cmod']],'xid'=>$result['xid'],'tip'=>4,'outmoney'=>$refund_fee,'inmoney'=>0,'create_time'=>date('Y-m-d H:i:s',time())]);
                    if($yxt_money_logs){
                        $yxt_money_log = true;
                    }
                    $yxt_money_charge_logs = Db::table('money_log')->insert(['uid'=>1,'role_id'=>1,'order_no'=>$refundData['buy_order_no'],'tip'=>6,'cmod'=>$cmods['refundLose'],'xid'=>$result['xid'],'outmoney'=>0,'inmoney'=>floatval($refund_fee)*config('pay.chargeLose'),'create_time'=>date('Y-m-d H:i:s',time())]);
                    if($yxt_money_charge_logs){
                        $yxt_money_charge_log = true;
                    }
                    // 提交事务
                    Db::commit();    
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                }
                if($state && $refund_record  && $yxt_purse && $yxt_money_log && $yxt_money_charge_log ){
                    $this->return_msg(200,'订单退款成功',$res); 
                }else{
                    $this->return_msg(0,'订单退款失败',$res); 
                }
                
            }else{
                model('RefundRecord')->where('order_no',$refundData['order_no'])->update(['refund_msg'=>$res['err_code_des']]);
                $this->return_msg(400,$res['err_code_des'],$res); 
            }
            
        }
        

    }
    /**
     * [aliOrderQuery description]
     * @Author   xiaolong
     * @DateTime 2019-01-15T10:12:51+0800
     * @param    [type]                   $out_trade_no [description]
     * @return   [type]                   支付宝订单查询              [description]
     */
    public function aliOrderQuery(){
        $out_trade_no = $this->request->param('order_no');
        if(empty($out_trade_no)){
            $this->return_msg(400,'商户订单号不能为空');
        }
        $res = model('BuyOrder')->where('order_no',$out_trade_no)->find();
        if(empty($res)){
            $this->return_msg(400,'订单不存在');
        }
        if($res['pay_type']==1 || $res['source']==3){
            $this->return_msg(400,'此方法只支持支付宝订单');
        }
        $ali = new AlipayApp();
        $result = $ali->orderQuery($out_trade_no);
        if($result->alipay_trade_query_response->code == 10000){
            if($result->alipay_trade_query_response->trade_status == 'TRADE_SUCCESS'){
                // 启动事务
                Db::startTrans();
                try{
                    Db::table('buy_order')->where('order_no',$out_trade_no)->update(['state' => 2,'payid'=>$result->alipay_trade_query_response->trade_no,'paytime'=>$result->alipay_trade_query_response->send_pay_date]);
                    if($res['cmod'] == 'course'){
                        Db::table('course')->where('id',$res['xid'])->setInc('sales');
                    }    
                    // 提交事务
                    Db::commit();    
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                }
            }
            $this->return_msg(200,'订单查询成功',$result);
        }else{
            $this->return_msg(400,$result->alipay_trade_query_response->sub_msg,$result); 
        }
        
    }
    /**
     * [aliOrderRefund description]
     * @Author   xiaolong
     * @DateTime 2019-01-16T14:19:16+0800
     * @return   [type]   支付宝订单退款                [description]
     */
    public function aliOrderRefund(){
        $data = $this->request->param();
        if(empty($data['order_no'])){
            $this->return_msg(400,'商户订单号不能为空');
        }
        if(empty($data['remark'])){
            $this->return_msg(400,'退款说明不能为空');
        }
        $result = model('BuyOrder')->where('order_no',$data['order_no'])->find();
        if(empty($result)){
            $this->return_msg(0,'订单不存在');
        }
        if($result['state']==0){
            $this->return_msg(0,'此订单未支付，不能退款');
        }
        if($result['source']==3 ){
            $this->return_msg(400,'此方法只支持App订单');
        }
        if($result['pay_type']==1 ){
            $this->return_msg(400,'此方法只支持支付宝订单');
        }
        $data['refund_fee'] = empty($data['refund_fee']) ? $result['price'] : $data['refund_fee'];
        if(floatval($data['refund_fee'])>floatval($result['price'])){
            $this->return_msg(400,'退款金额不能大于订单的支付金额');
        }
        $refundData = [
            'buy_order_no'=>$data['order_no'],
            'order_no'=>createOrderNum(),
            'total_fee' => $result['price'],
            'refund_fee'=> $data['refund_fee'],
            'remark'=>$data['remark'],
            'refund_source'=>'ali'
        ];
        $insert = model('RefundRecord')->allowField(true)->save($refundData);
        if(empty($insert)){
            $this->return_msg(400,'插入退款记录失败');
        }else{
            $ali = new AlipayApp();
            $res = $ali->refund($data['order_no'],$refundData['order_no'],$data['refund_fee']);
            if($res->alipay_trade_refund_response->code == 10000){
                $refund_fee = $res->alipay_trade_refund_response->refund_fee;
                $cmods = array("course"=>'课件','press_card'=>'小记者','refundLose'=>'退款损失的手续费');
                // 启动事务
                Db::startTrans();
                try{
                    $refund_record = $state = $yxt_purse = $yxt_money_log = false;
                    $refund_records = Db::table('refund_record')->where('order_no',$refundData['order_no'])->update(['state' => 1,'refund_order_no'=>$res->alipay_trade_refund_response->trade_no,'refund_fee'=>$refund_fee]);
                    if($refund_records){
                        $refund_record = true;
                    }
                    
                    //if(floatval($refund_fee) == floatval($refundData['total_fee'])){
                        $states = Db::table('buy_order')->where('order_no',$refundData['buy_order_no'])->update(['state' =>7]);
                    //}
                    if($states){
                        $state = true;
                    }
                    if($result['type'] == 1){
                        Db::table('student_info')->where('id',$result['sid'])->update(['isBuy' =>0]);  
                    }
                    $balance = Db::table('purse')->where('id',1)->value('money');
                    $money = floatval($balance) - floatval($refund_fee);
                    $yxt_purses = Db::table('purse')->where('id',1)->update(['money' => $money]);
                    if($yxt_purses){
                        $yxt_purse = true;
                    }

                    $yxt_money_logs = Db::table('money_log')->insert(['uid'=>1,'role_id'=>1,'order_no'=>$refundData['buy_order_no'],'tip'=>4,'cmod'=>$cmods[$result['cmod']],'xid'=>$result['xid'],'outmoney'=>$refund_fee,'inmoney'=>0,'create_time'=>date('Y-m-d H:i:s',time())]);
                    if($yxt_money_logs){
                        $yxt_money_log = true;
                    }
                    
                    // 提交事务
                    Db::commit();    
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                } 
                if($state && $refund_record  && $yxt_purse && $yxt_money_log){
                    $this->return_msg(200,'订单退款成功',$res); 
                }else{
                    $this->return_msg(0,'订单退款失败',$res); 
                }
            }else{
                Db::table('refund_record')->where('order_no',$refundData['order_no'])->update(['refund_msg'=>$res->alipay_trade_refund_response->sub_msg]);
                $this->return_msg(400,$res->alipay_trade_refund_response->sub_msg,$res); 
            }
            
        }
        

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
