<?php

namespace app\api\controller\v1;

use app\api\controller\Common;
use think\Request;

class MoneyLog extends Common
{

    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        if (strtolower($this->request->method()) == 'get') {


            $where = [];
            $order = "id DESC";
            $cmod = $this->request->get('cmod', '');
            if ($cmod !== '') {
                $where['cmod'] = ['=', $cmod]; 
            }
            $xid = $this->request->get('xid', '');
            if ($xid !== '') {
                $where['xid'] = ['=', intval($xid)]; 
            }
            $order_no = $this->request->get('order_no', '');
            if ($order_no !== '') {
                $where['order_no'] = ['like', "%" . $order_no . "%"]; 
            }
           
            
            $data = $this->request->get();
            $limit = $this->request->get('limit/d', 20);
            $page = $this->request->get('page/d', 1);


            if($this->scene == 'admin'){

                if(empty($data['roles'])){
                    $this -> return_msg(0, '所属角色不能为空');
                }
                $roleInfo = model('AuthRole')->where('alias',$data['roles'])->find()->toArray();  
                if(empty($roleInfo)){
                    $this->return_msg(0, '角色不存在');
                }
                $result = model('MoneyLog')->where('role_id',$roleInfo['id'])->where($where)->count('xid')->sum('inmoney')->order($order)->limit($limit)->page($page)->select()->toArray();  
            }else{
                if(empty($data['uid'])){
                    $this -> return_msg(0, '用户id不能为空');
                }
                $result = model('MoneyLog')->where('uid',$data['uid'])->where($where)->order($order)->limit($limit)->page($page)->select()->toArray();  
            }
            $inMoney = $this->scene == 'admin'? model('MoneyLog')->where('role_id',$roleInfo['id'])->where($where)->sum('inmoney') : model('MoneyLog')->where('uid',$data['uid'])->where($where)->sum('inmoney');
            $outMoney = $this->scene == 'admin'? model('MoneyLog')->where('role_id',$roleInfo['id'])->where($where)->sum('outmoney') : model('MoneyLog')->where('uid',$data['uid'])->where($where)->sum('outmoney');

            $res = [];
            $res['total'] = $this->scene == 'admin'? model('MoneyLog')->where('role_id',$roleInfo['id'])->where($where)->count() : model('MoneyLog')->where('uid',$data['uid'])->where($where)->count();
            $res['list'] = $result;
            $res['totalInmoney'] = $inMoney;
            $res['totalOutmoney'] = $outMoney;
            $res['totalIncome'] = floatval($inMoney) - floatval($outMoney);
            if (empty($res['list'])) {
                $this -> return_msg(0, '暂时没有数据',$res);
            } else {
                $this -> return_msg(200, '获取成功', $res);
            }
            
        } else {
            $this -> return_msg(0, '请求方式不正确');
        }
    }

    /**
     * 显示资源列表
     * 根据课程id显示订单流水信息数据
     * @return \think\Response
     */
    public function statistical()
    {
        if (strtolower($this->request->method()) == 'get') {
            
            $where = [];
            $order = 'id DESC';

            $id = $this->request->get('id', '');
            if ($id !== '') {
                $where['id'] = ['=', intval($id)];
            }

            $name = $this->request->get('name', '');
            if ($name !== '') {
                $where['name'] = ['like', '%'.$name.'%'];
            }
            $wheres['state'] = ['in', array(4,5)];
            $data = $this->request->get();
            $limit = $this->request->get('limit/d', 20);
            $page = $this->request->get('page/d', 1);

            $mon = model('MoneyLog')->where('cmod','eq','课件')->where('xid','neq',0)->column('xid');
            
            if($this->scene == 'admin'){
                if(in_array($this->session['username'], array('admin','editor'))){
                    $course = model('Course')->where('id','in',$mon)->where('owner','=','yanxuetao')->where($where)->order($order)->limit($limit)->page($page)->select();
                }else{
                    $course = model('Course')->where('id','in',$mon)->where('owner',$this->admin_roles[0])->where($where)->order($order)->limit($limit)->page($page)->select();
                }
            }

            if (empty($course)) {
                $this -> return_msg(400, '暂无数据');
            } else {
                $lists = [];
                foreach ($course as $key => $value) {
                    $endtime = model('CourseJourney')->where('cid', $value['id'])->value('endtime');
                    if (time() < strtotime($endtime)) {
                        // 课程id
                        $lists[$key]['id'] = $value['id'];
                        // 课程名称
                        $lists[$key]['name'] = $value['name'];
                        // 课程单价
                        $lists[$key]['price'] = $value['prices'];
                        // 课程开始时间
                        $lists[$key]['starttime'] = model('CourseJourney')->where('cid', $value['id'])->value('starttime');
                        // 课程结束时间
                        $lists[$key]['endtime'] = $endtime;
                        // 总进账(未扣除手续费)
                        $lists[$key]['untotalInmoney'] = model('BuyOrder')->where('xid', $value['id'])->where('state', 2)->sum('price');
                        //　总进账(已扣除手续费)
                        $lists[$key]['totalInmoney'] = model('MoneyLog')->where('xid', $value['id'])->where('tip', 2)->sum('inmoney');
                        // 进账手续费用
                        $lists[$key]['in_pcommission_charge'] = $lists[$key]['untotalInmoney'] - $lists[$key]['totalInmoney'];
                        // 总出账
                        $lists[$key]['totalOutmoney'] = model('MoneyLog')->where('xid', $value['id'])->where('tip',4)->sum('outmoney');
                        // 退款造成的手续费用(出账手续费用)
                        $lists[$key]['out_pcommission_charge'] = model('MoneyLog')->where('xid', $value['id'])->where('tip', 6)->sum('outmoney');
                        // 总收入
                        if ($lists[$key]['totalInmoney'] == 0) {  // 如果总进账为0 
                            $lists[$key]['totalIncome'] = 0;
                        } else {
                            $lists[$key]['totalIncome'] = $lists[$key]['totalInmoney'] - $lists[$key]['totalOutmoney'];
                        }
                        // 课程未结束可提取金额(90%)
                        if ($lists[$key]['totalIncome'] < 0) {
                            $lists[$key]['extract'] = 0;
                        } else {
                            $lists[$key]['extract'] = $lists[$key]['totalIncome'] * 0.9;
                        }
                        // 微信支付人数
                        $lists[$key]['buy_wechat'] = model('BuyOrder')->where('xid', $value['id'])->where('state', 2)->where('pay_type', 1)->count('id');
                        // 支付宝支付人数
                        $lists[$key]['buy_alipay'] = model('BuyOrder')->where('xid', $value['id'])->where('state', 2)->where('pay_type', 2)->count('id');
                        // 安卓支付人数
                        $lists[$key]['buy_android'] = model('BuyOrder')->where('xid', $value['id'])->where('state',2)->where('source', 0)->count('id');
                        // 苹果支付人数
                        $lists[$key]['buy_ios'] = model('BuyOrder')->where('xid', $value['id'])->where('state', 2)->where('source', 1)->count('id');
                        // 小程序支付人数
                        $lists[$key]['buy_program'] = model('BuyOrder')->where('xid', $value['id'])->where('state',2)->where('source', 3)->count('id');
                        // 退款中人数
                        $lists[$key]['buy_unrefund'] = model('BuyOrder')->where('xid', $value['id'])->where('state', 6)->count('id');
                        // 退款完成人数
                        $lists[$key]['buy_refund'] = model('BuyOrder')->where('xid', $value['id'])->where('state', 7)->count('id');
                    } 
                    elseif (time() > strtotime($endtime)) 
                    {
                        // 课程id
                        $lists[$key]['id'] = $value['id'];
                        // 课程名称
                        $lists[$key]['name'] = $value['name'];
                        // 课程单价
                        $lists[$key]['price'] = $value['prices'];
                        // 课程开始时间
                        $lists[$key]['starttime'] = model('CourseJourney')->where('cid', $value['id'])->value('starttime');
                        // 课程结束时间
                        $lists[$key]['endtime'] = $endtime;
                        // 总进账(未扣除手续费)
                        $lists[$key]['untotalInmoney'] = model('BuyOrder')->where('xid', $value['id'])->where($wheres)->sum('price');
                        //　总进账(已扣除手续费)
                        $lists[$key]['totalInmoney'] = model('MoneyLog')->where('xid', $value['id'])->where('tip', 2)->sum('inmoney');
                        // 进账手续费用
                        $lists[$key]['in_pcommission_charge'] = $lists[$key]['untotalInmoney'] - $lists[$key]['totalInmoney'];
                        // 总出账
                        $lists[$key]['totalOutmoney'] = model('MoneyLog')->where('xid', $value['id'])->where('tip', 4)->sum('outmoney');
                        // 退款造成的手续费用(出账手续费用)
                        $lists[$key]['out_pcommission_charge'] = model('MoneyLog')->where('xid', $value['id'])->where('tip', 6)->sum('outmoney');
                        // 总收入
                        if ($lists[$key]['totalInmoney'] == 0) {  // 如果总进账为0
                            $lists[$key]['totalIncome'] = 0;
                        } else {
                            $lists[$key]['totalIncome'] = $lists[$key]['totalInmoney'] - $lists[$key]['totalOutmoney'];
                        }
                        
                        // 课程已结束可提取金额(100%)
                        if ($lists[$key]['totalIncome'] < 0) {
                            $lists[$key]['extract'] = 0;
                        } else {
                            $lists[$key]['extract'] = $lists[$key]['totalIncome'];
                        }
                        // 微信支付人数
                        $lists[$key]['buy_wechat'] = model('BuyOrder')->where('xid', $value['id'])->where($wheres)->where('pay_type', 1)->count('id');
                        // 支付宝支付人数
                        $lists[$key]['buy_alipay'] = model('BuyOrder')->where('xid', $value['id'])->where($wheres)->where('pay_type', 2)->count('id');
                        // 安卓支付人数
                        $lists[$key]['buy_android'] = model('BuyOrder')->where('xid', $value['id'])->where($wheres)->where('source', 0)->count('id');
                        // 苹果支付人数
                        $lists[$key]['buy_ios'] = model('BuyOrder')->where('xid', $value['id'])->where($wheres)->where('source', 1)->count('id');
                        // 小程序支付人数
                        $lists[$key]['buy_program'] = model('BuyOrder')->where('xid', $value['id'])->where($wheres)->where('source', 3)->count('id');
                        // 退款中人数
                        $lists[$key]['buy_unrefund'] = model('BuyOrder')->where('xid', $value['id'])->where('state', 6)->count('id');
                        // 退款完成人数
                        $lists[$key]['buy_refund'] = model('BuyOrder')->where('xid', $value['id'])->where('state', 7)->count('id');
                        
                    }
                }
            }

            $res = [];
            $res['list'] = $lists;
            $res['total'] = count($course);
            if (empty($res['list'])) {
                $this -> return_msg(400, '暂无数据');
            } else {
                $this ->return_msg(200, '获取成功', $res);
            }


            //    -------------------------------------------------------------------------------------------------------------------------
            

            // $buy = model('BuyOrder')->column('xid');
            // if($this->scene == 'admin'){
            //     if(in_array($this->session['username'], array('admin','editor'))){
            //         $course = model('Course')->where('id','in',$buy)->where('owner','=','yanxuetao')->where($where)->order($order)->limit($limit)->page($page)->select();
            //     }else{
            //         $course = model('Course')->where('id','in',$buy)->where('owner',$this->admin_roles[0])->where($where)->order($order)->limit($limit)->page($page)->select();
            //     }
            // }

            // if (empty($course)) {
            //     $this -> return_msg(400, '暂无数据');
            // } else {
            //     $lists = [];
            //     foreach ($course as $key => $value) {
            //         //  课程开始时间与结束时间信息
            //         $time = model('CourseJourney')->where('cid','=',$value['id'])->find();
            //         if (time() < strtotime($time['endtime'])) 
            //         {
            //             $lists['courseInfo'] = $course->toArray();
            //             //  微信支付人数
            //             $lists['buy_wechat'] = model('BuyOrder')->where('xid','=',$value['id'])->where('pay_type',1)->where('state',2)->count('id');
            //             //  支付宝支付人数
            //             $lists['buy_alipay'] = model('BuyOrder')->where('xid','=',$value['id'])->where('pay_type',2)->where('state',2)->count('id');
            //             //  退款中人数
            //             $lists['buy_unfinished'] = model('BuyOrder')->where('xid','=',$value['id'])->where('state',6)->count('id');
            //             //  退款完成人数
            //             $lists['buy_finished'] = model('BuyOrder')->where('xid','=',$value['id'])->where('state',7)->count('id');
            //             //  支付宝退款人数
            //             $lists['buy_member'] = model('BuyOrder')->where('xid','=',$value['id'])->where('state',7)->where('pay_type',2)->count('id');
            //             //  安卓支付人数
            //             $lists['buy_android'] = model('BuyOrder')->where('xid','=',$value['id'])->where('source',0)->where('state',2)->count('id');
            //             //  苹果支付人数
            //             $lists['buy_ios '] = model('BuyOrder')->where('xid','=',$value['id'])->where('source',1)->where('state',2)->count('id');
            //             //  小程序支付人数
            //             $lists['buy_program'] = model('BuyOrder')->where('xid','=',$value['id'])->where('source',3)->where('state',2)->count('id');
            //             //  总进账
            //             $lists['buy_totalIncome'] = model('BuyOrder')->where('xid','=',$value['id'])->where('state',2)->sum('price');
            //             // 平台手续费用
            //             $lists['buy_commission_charge'] = ($value['price']*$lists['buy_wechat']*0.006) + ($value['price']*($lists['buy_alipay']+$lists['buy_member'])*0.006);
            //             // 商家实际收入
            //             $lists['buy_income'] = $lists['buy_totalIncome'] * 0.994;
            //             // 商家实际收入 四舍五入精确到分
            //             // $income = sprintf("%.2f", $income);
            //             // 课程活动未结束  可提取90%
            //             $lists['buy_extract'] = $lists['buy_income'] * 0.9;
            //         }
            //         elseif (time() > strtotime($time['endtime']))
            //         {
            //             //  微信支付人数
            //             $lists['buy_wechat'] = model('BuyOrder')->where('xid',$value['id'])->where('pay_type',1)->where('state',4)->count('id');
            //             //  支付宝支付人数
            //             $lists['buy_alipay'] = model('BuyOrder')->where('xid',$value['id'])->where('pay_type',2)->where('state',4)->count('id');
            //             //  退款中人数
            //             $lists['buy_unfinished'] = model('BuyOrder')->where('xid',$value['id'])->where('state',6)->count('id');
            //             //  退款完成人数
            //             $lists['buy_finished'] = model('BuyOrder')->where('xid',$value['id'])->where('state',7)->count('id');
            //             //  支付宝退款人数
            //             $lists['buy_member'] = model('BuyOrder')->where('xid',$value['id'])->where('state',7)->where('pay_type',2)->count('id');
            //             //  安卓支付人数
            //             $lists['buy_android'] = model('BuyOrder')->where('xid',$value['id'])->where('source',0)->where('state',4)->count('id');
            //             //  苹果支付人数
            //             $lists['buy_ios'] = model('BuyOrder')->where('xid',$value['id'])->where('source',1)->where('state',4)->count('id');
            //             //  小程序支付人数
            //             $lists['buy_program'] = model('BuyOrder')->where('xid',$value['id'])->where('source',3)->where('state',4)->count('id');
            //             //  总进账
            //             $lists['buy_totalIncome'] = model('BuyOrder')->where('xid',$value['id'])->where('state',4)->sum('price');
            //             // 平台手续费用
            //             $lists['buy_commission_charge'] = ($value['price']*$lists['buy_wechat']*0.006) + ($value['price']*($lists['buy_alipay']+$lists['buy_member'])*0.006);
            //             // 商家实际收入
            //             $lists['buy_income'] = $lists['buy_totalIncome'] * 0.994;
            //             // // 商家实际收入 四舍五入精确到分
            //             // $income = sprintf("%.2f", $income);
            //             //  课程活动结束  可全部提取
            //             $lists['buy_extract'] = $lists['buy_income'];
            //         }
            //     }
            // }

            // $res = [];
            // $res['list'] = $lists;
            // $res['total'] = count($course);
            // if (empty($res['list'])) {
            //     $this -> return_msg(400, '暂无数据');
            // } else {
            //     $this ->return_msg(200, '获取成功', $res);
            // }
            
        } else {
            $this -> return_msg(0, '请求方式不正确');
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
