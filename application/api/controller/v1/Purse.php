<?php

namespace app\api\controller\v1;

use app\api\controller\Common;
use think\Request;
use think\Db;
use aliyun\sms\Sms;

class Purse extends Common
{
   /**
    * [getPurse description]
    * @Author   xiaolong
    * @DateTime 2019-03-14T16:25:22+0800
    * @return   [type]                  获取钱包数据
    */
    public function getPurse()
    {
        if (strtolower($this->request->method()) == 'get') {
            $data = $this->request->get();
            if($this->scene == 'admin'){

                if(empty($data['roles'])){
                    $this -> return_msg(0, '所属角色不能为空');
                }
                $roleInfo = model('AuthRole')->where('alias',$data['roles'])->find();
                if(empty($roleInfo)){
                    $this->return_msg(0, '角色不存在');
                }
                if($data['roles']=='admin'){

                }else{
                    
                    $courseIds = model('Course')->where('owner','in',$this->admin_roles)->column('id');
                    $pressCardIds = model('PressCard')->where('owner','in',$this->admin_roles)->column('id');
                    if(!empty($pressCardIds)){
                        $pressApplyIds = model('PressApply')->where('pressid','in',$pressCardIds)->column('id');
                    }else{
                        $pressApplyIds = [];
                    }
                    $mergeIds = array_merge($courseIds,$pressApplyIds);
                    if(empty($mergeIds)){
                       
                    }else{
                        
                        if(!empty($courseIds)){

                            $where['xid'] = ['in', $courseIds]; 
                            $where['cmod'] = ['=', 'course']; 
                            $where['state'] = ['in', array(2,4,5,6)]; 
                            $BuyOrder_course = model('BuyOrder')->where($where)->select()->toArray();
                        }else{
                            $BuyOrder_course = [];
                        }
                        if(!empty($pressApplyIds)){
                            $wherePress['xid'] = ['in',$pressApplyIds];
                            $wherePress['cmod'] = ['=','press_card'];
                            $wherePress['state'] = ['in', array(2,4,5,6)]; 
                            $BuyOrder_press = model('BuyOrder')->where($wherePress)->select()->toArray();
                        }else{
                            $BuyOrder_press = [];
                        }
                        $BuyOrder = array_merge($BuyOrder_course,$BuyOrder_press);
                        if(empty($BuyOrder)){
                                    
                        }else{
                            $money = 0;
                            foreach ($BuyOrder as $key => $value) {
                                if($value['type'] == 4 && $value['state'] == 2){
                                    unset($BuyOrder[$key]);
                                }else{
                                    $money+=floatval($value['price']);
                                }
                            }
                            $divideMoney = floatval($money)*floatval($roleInfo['divide'])*config('pay.charge');    
                            model('Purse')->where('role_id',$roleInfo['id'])->update(['money' => $divideMoney]);
                        }
                    }
                }
                $result = model('Purse')->where('role_id',$roleInfo['id'])->find()->toArray();
                $result['account'] = $roleInfo['account'];
                $result['account_name'] = $roleInfo['account_name'];
                $result['divide'] = $roleInfo['divide'];
                $result['minblance'] = $roleInfo['minblance'];
                

            }else{
                if(empty($data['uid'])){
                    $this -> return_msg(0, '用户id不能为空');
                }
                $result = model('Purse')->where('uid',$data['uid'])->find()->toArray();

            }
            if (empty($result)) {
                $this -> return_msg(0, '暂时没有数据',$result);
            } else {
                $this -> return_msg(200, '获取成功', $result);
            }
            
        } else {
            $this -> return_msg(0, '请求方式不正确');
        }
    }
    /**
     * [accountMoney description]
     * @Author   xiaolong
     * @DateTime 2019-03-27T11:47:07+0800
     * @return   [type]                   结算
     */
    public function accountMoney(){
        $data = $this->request->post();
        if($this->scene == 'admin'){

            if(empty($data['roles'])){
                $this -> return_msg(0, '所属角色不能为空');
            }
            $roleInfo = model('AuthRole')->where('alias',$data['roles'])->find();

            if(empty($roleInfo)){
                $this->return_msg(0, '角色不存在');
            }
            if(empty($data['money'])){
                $this->return_msg(0, '结算金额不能为空');
            }
            $purses = model('purse')->where('role_id',$roleInfo['id'])->value('money');
            if(floatval($data['money']) > floatval($purses)){
                $this->return_msg(0, '结算金额不能大于余额');
            }
            if(floatval($purses) - floatval($data['money']) < floatval($roleInfo['minblance'])){
                $this->return_msg(0, '钱包余额不能小于'.$roleInfo['minblance']);
            }
            //兹有${name}发起${money}的结算通知，开户行为${bank},开户账号为${account},开户人为${account_name}，请尽快与客户联系落实！
            $sms  = new Sms();
            $smsData = $sms->send('13312509319', '研学淘', 'SMS_162195105', ['name' => $roleInfo['name'],'money'=>$data['money'],'bank'=>$roleInfo['bank'],'account'=>$roleInfo['account'],'account_name'=>$roleInfo['account_name']]);
            if ($smsData->Message == "OK" && $smsData->Code == 'OK') {
                // 启动事务
                Db::startTrans();
                try{
                    $purse = $money_log = $yxtMoney_log = $purse_yxt = false;
                    

                    $balance = Db::table('purse')->where('role_id',$roleInfo['id'])->value('money');
                    $money = floatval($balance) - floatval($data['money']);

                    $purses = Db::table('purse')->where('role_id',$roleInfo['id'])->update(['money' => $money]);
                    if($purses){
                        $purse = true;
                    }
                    $order_no = createOrderNum();
                    $money_logs = Db::table('money_log')->insert(['uid'=>0,'role_id'=>$roleInfo['id'],'order_no'=>$order_no,'tip'=>5,'outmoney'=>0,'inmoney'=>$data['money'],'create_time'=>date('Y-m-d H:i:s',time())]);
                    if($money_logs){
                        $money_log = true;
                    }
                    $yxtBalance = Db::table('purse')->where('id',1)->value('money');
                    $yxtMoney = floatval($yxtBalance) - floatval($data['money']);
                    $purse_yxts = Db::table('purse')->where('id',1)->update(['money' => $yxtMoney]);
                    if($purse_yxts){
                        $purse_yxt = true;
                    }
                    $yxtMoney_logs = Db::table('money_log')->insert(['uid'=>1,'role_id'=>1,'order_no'=>$order_no,'tip'=>5,'outmoney'=>$data['money'],'inmoney'=>0,'create_time'=>date('Y-m-d H:i:s',time())]);
                    if(!empty($yxtMoney_logs)){
                        $yxtMoney_log = true;
                    }
                    // 提交事务
                    Db::commit();    
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                }
                if($purse && $money_log && $yxtMoney_log && $purse_yxt){
                    $this->return_msg(200, '订单结算成功');
                }else{
                    $this->return_msg(0, '订单结算失败');
                }
            } 
        }
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
        //
    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        
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
