<?php

namespace app\api\controller\v1;

use app\api\controller\Common;
use think\Request;
use think\Loader;
use think\Db;
use aliyun\sms\Sms;

class Buy extends Common
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
            if($this->scene == 'admin'){
                if(!in_array($this->session['username'], array('admin'))){
                    $courseIds = model('Course')->where('owner','in',$this->admin_roles)->column('id');
                    if(empty($courseIds)){
                        $this->return_msg(200, '暂时没有数据',[]);
                    }else{
                        $where['xid'] = ['in', $courseIds]; 
                        $where['cmod'] = ['=', 'course'];
                    }
                    
                }
            }
            $uid = $this->request->get('uid', '');
            if ($uid !== '') {
                $where['uid'] = ['=', intval($uid)]; 
                $order = $order;
            }
            $ids = $this->request->get('ids', '');
            if ($ids !== '') {
                $idArray = explode(",",$ids);
                $where['id'] = ['in', $idArray]; 
                $order = $order;
            }
            $xid = $this->request->get('xid', '');
            if ($xid !== '') {
                $where['xid'] = ['=', intval($xid)]; 
                $order = $order;
            }
            $course_name = $this->request->get('course_name', '');
            if ($course_name !== '') {
                $courseIds = model('Course')->where('name', 'like', $course_name)->value('id');
                $where['xid'] = ['in',$courseIds];
                $order = $order;
            }
            $contact = $this->request->get('contact', '');
            if (!empty($contact)) {
                $where['contact'] = ['like', "%" . $contact . "%"];
                $order = $order;
            }
            // $cmod = $this->request->get('cmod', 'course');
            $cmod = $this->request->get('cmod', '');
            if (!empty($cmod)) {
                $where['cmod'] = ['=', $cmod];
                $order = $order;
            }
            $phone = $this->request->get('phone', '');
            if (!empty($phone)) {
                $where['phone'] = ['like', "%" . $phone . "%"];
                $order = $order;
            }
            $confirm_code = $this->request->get('confirm_code', '');
            if (!empty($confirm_code)) {
                $where['confirm_code'] = ['like', "%" . $confirm_code . "%"];
                $order = $order;
            }
            $order_no = $this->request->get('order_no', '');
            if (!empty($order_no)) {
                $where['order_no'] = ['like', "%" . $order_no . "%"];
                $order = $order;
            }
            $type = $this->request->get('type', '');
            if ($type !== '') {
                $where['type'] = ['=', intval($type)]; 
                $order = $order;
            }
            $state = $this->request->get('state', '');
            if ($state !== '') {
                $where['state'] = ['=', intval($state)]; 
                $order = $order;
            }
            $pay_type = $this->request->get('pay_type', '');
            if ($pay_type !== '') {
                $where['pay_type'] = ['=', intval($pay_type)]; 
                $order = $order;
            }
            $source = $this->request->get('source', '');
            if ($source !== '') {
                $where['source'] = ['=', intval($source)]; 
                $order = $order;
            }
            $guardian = $this->request->get('guardian', '');
            if ($guardian !== '') {
                $guardians = model('PressApply')->where('guardian', $guardian)->column('id');
                if (empty($guardians)) {
                    $this -> return_msg(0, '暂无数据');
                } else {
                    $where['xid'] = ['in', $guardians];
                    $order = $order;
                }
            }
            $guardian_phone = $this->request->get('guardian_phone', '');
            if ($guardian_phone !== '') {
                $guardian_phones = model('PressApply')->where('guardian_phone', $guardian_phone)->column('id');
                if (empty($guardian_phones)) {
                    $this -> return_msg(0, '暂无数据');
                } else {
                    $where['xid'] = ['in', $guardian_phones];
                    $order = $order;
                }
            }

            $this_yesterday = $this->request->get('yesterday', '');
            if (!empty($this_yesterday)) {
                $yesterday_start=date("Y-m-d H:i:s", mktime(0,0,0,date('m'),date('d')-1,date('Y')));
                $yesterday_end=date("Y-m-d H:i:s",mktime(23,59,59,date('m'),date('d')-1,date('Y')));
                $where['paytime'] = ['between',[$yesterday_start,$yesterday_end]];
            }

            $this_week = $this->request->get('thisweek', '');
            if (!empty($this_week)) {
                $thisweek_start = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),date("d")-date("w")+1,date("Y"))); 
                $thisweek_end = date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("d")-date("w")+7,date("Y"))); 
                $where['ordertime'] = ['between',[$thisweek_start,$thisweek_end]];
                $order = $order;
            }
            $last_week = $this->request->get('lastweek', '');
            if (!empty($last_week)) {
                $lastweek_start = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),date("d")-date("w")+1-7,date("Y")));
                $lastweek_end = date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("d")-date("w")+7-7,date("Y")));
                $where['ordertime'] = ['between',[$lastweek_start,$lastweek_end]];
                $order = $order;
            }
            $this_month = $this->request->get('thismonth', '');
            if (!empty($this_month)) {
                $thismonth_start = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),1,date("Y"))); 
                $thismonth_end = date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("t"),date("Y"))); 
                $where['ordertime'] = ['between',[$thismonth_start,$thismonth_end]];
                $order = $order;
            }
            $last_month = $this->request->get('lastmonth', '');
            if (!empty($last_month)) {
                $lastmonth_start = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m")-1,1,date("Y"))); 
                $lastmonth_end = date("Y-m-d H:i:s",mktime(23,59,59,date("m") ,0,date("Y"))); 
                $where['ordertime'] = ['between',[$lastmonth_start,$lastmonth_end]];
                $order = $order;
            }
            $limit = $this->request->get('limit/d', 20);
            $page = $this->request->get('page/d', 1);
            


            $lists = model('BuyOrder')->where($where)->order($order)->page($page, $limit)->select()->toArray();
            foreach ($lists as $key => $value) {
                $userInfo = model('User')->where('id',$value['uid'])->find();
                if(!empty($userInfo)){
                    $userInfo = $userInfo->toArray(); 
                    $userInfo['phoneHide'] = substr_replace($userInfo['phone'],"****", 3,4);
                }
                
                $lists[$key]['parentName'] = "在线下单";
                if($value['state'] == 0){
                    if(intval(time())>(intval(strtotime($value['create_time']))+24*3600)){
                            model('BuyOrder')->where('id',$value['id'])->update(['state'=>9]);
                    }
                }
                if($value['cmod']=='course'){
                    $journeyEndtime = model('CourseJourney')->where('id',$value['travel_detail'])->value('endtime');
                    if($value['state'] == 2){
                        if((intval(time())>intval(strtotime($journeyEndtime))) || empty($journeyEndtime) ){
                            model('BuyOrder')->where('id',$value['id'])->update(['state'=>4]);
                        }
                    }
                    
                    
                    $goodInfo = model('Course')->where('id',$value['xid'])->find();
                    if(!empty($goodInfo)){
                        $goodInfo = $goodInfo->toArray(); 
                        $goodInfo['cover'] =  strpos($goodInfo['cover'],'/upload/')!==false ? $this->request->domain().$goodInfo['cover']:$this->request->domain().DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR.$goodInfo['cover'];
                    }
                    $lists[$key]['goodInfo'] = $goodInfo;
                    $travelPerson = model('RelationPersonOrder')->where('oid',$value['id'])->column('pid');
                    $travelPerson = array_unique($travelPerson);
                    foreach ($travelPerson as $k => $val) {
                       $lists[$key]['travelpersonInfo'][$k] = model('TravelPerson')->where('id',$val)->find();
                    }
                    $lists[$key]['headlineTitle'] = "手机号为".$userInfo['phoneHide']."的用户购买了课件".$goodInfo['name'];
                }
                if($value['cmod']=='press_card'){
                    $applyInfo = model('PressApply')->where('id',$value['xid'])->find();
                    if(!empty($applyInfo)){
                        $applyInfo = $applyInfo->toArray(); 
                        $applyInfo['photograph'] =  strpos($applyInfo['photograph'],'/upload/')!==false ? $this->request->domain().$applyInfo['photograph']:$this->request->domain().DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR.$applyInfo['photograph'];
                    }
                    $lists[$key]['applyInfo'] = $applyInfo;
                    $goodInfo = model('PressCard')->where('id',$applyInfo['pressid'])->find()->toArray();
                    $lists[$key]['goodInfo'] = $goodInfo;
                }
                
            }
            $res = [];
            $res["total"] = model('BuyOrder')->where($where)->count();
            $res["list"] = $lists;
            if (empty($res["list"])) {
                $this->return_msg(200, '暂时没有数据',$res);
            } else {
                $this->return_msg(200, '获取成功', $res);
            }
        } else {
            $this->return_msg(0, '请求方式不正确');
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
        if (strtolower($this->request->method()) == 'post') {
            $data = $this->request->param();
            $data['cmod'] = isset($data['cmod']) && !empty($data['cmod']) ? $data['cmod'] : 'course';
            
            if($data['cmod'] == 'course'){
                $validate = Loader::validate('Buy');
                if(!$validate->check($data)){
                    $this->return_msg(0,$validate->getError());
                }
                $result = model('Course')->where('id',$data['xid'])->find();
                if(empty($result)){
                    $this->return_msg(0,'课程不存在');
                }
            }elseif($data['cmod'] == 'press_card'){
                if(empty($data['uid'])){
                    $this->return_msg(0,'用户ID不能为空');
                }elseif(empty($data['xid'])){
                    $this->return_msg(0,'报名信息id不能为空');
                }elseif(empty($data['pressid'])){
                    $this->return_msg(0,'商品(小记者)id不能为空');
                }
                $press = model('PressCard')->where('id',$data['pressid'])->find();
                if(empty($press)){
                    $this->return_msg(0,'商品(小记者)招募不存在');
                }
                $result = model('PressApply')->where('id',$data['xid'])->find();
                if(empty($result)){
                    $this->return_msg(0,'该报名不存在');
                }
                $data['type'] = 5;
            }
            if($data['type'] == 0 || $data['type'] == 1){
                //0是私人订单 1是学校订单
                if(empty($data['travelperson_id'])){
                    $this->return_msg(0,'出行人id不能为空');
                }
                $data['travel_num'] = count($data['travelperson_id']);
                $data['original_price'] = $result['prices']*$data['travel_num'];
                
            }elseif($data['type'] == 2){
                //团体
                if(empty($data['travel_num'])){
                    $this->return_msg(0,'出行人人数不能为空');
                }
                $data['original_price'] = $result['prices']*$data['travel_num'];
            }elseif($data['type'] == 3){
                //亲子
                if(empty($data['travelperson_id'])){
                    $this->return_msg(0,'出行人id不能为空');
                }
                if(empty($data['adultNum'])){
                    $this->return_msg(0,'成人数量不能为空');
                }
                if(empty($data['childrenNum'])){
                    $this->return_msg(0,'儿童数量不能为空');
                }
                $data['travel_num'] = count($data['travelperson_id']);
                $data['original_price'] = $result['adult_price']*$data['adultNum']+$result['children_price']*$data['childrenNum'];

            }elseif($data['type'] == 4){
                $data['original_price'] = $result['prices'];
                
            }
            if($data['type'] == 1){
                if(empty($data['sid'])){
                    $this->return_msg(0,'学生ID不能为空');
                }
                foreach ($data['travelperson_id'] as $key => $value) {
                    $orderIds = model('RelationPersonOrder')->where('pid',$value)->column('oid');
                    $travelPerson = model('TravelPerson')->where('id',$value)->find();
                    foreach ($orderIds as $k => $v) {
                        $order = model('BuyOrder')->where(['id'=>$v])->find()->toArray();
                        if($order['state'] == 2 && $order['cmod'] == 'course' && $order['xid'] == $data['xid'] && $order['travel_detail'] == $data['travel_detail']){
                            $this->return_msg(0,'此课件'.$travelPerson['name'].'你已支付过了');
                        }
                    }
                    
                }
                
            }
            if($data['type'] == 5){
                $data['original_price'] = $press['price'];
            }
            
            $data['order_no'] = createOrderNum();
            if(!empty($data['coupon_id'])){
                $coupon = model('Coupon')->where('id',$data['coupon_id'])->find();
                if(empty($coupon)){
                    $this->return_msg(0,'优惠卷不存在');
                }
                if($coupon['is_exceed'] == 0){

                    if(time()>$coupon['end_time']){
                        if($coupon['status']==1){
                            model('Coupon')->isUpdate(true)->save(['status'=>0,'id'=>$data['coupon_id']]);
                        }
                        $this->return_msg(0,'优惠卷已过期');
                    } 
                }
                $data['price'] = $data['original_price']-$coupon['price'];
                if($data['price']<=0){
                    $this->return_msg(0,'优惠卷面值不能大于商品总价格');
                }
                
            }else{
                $data['price'] = $data['original_price'];
            }
            $res = model('BuyOrder')->allowField(true)->save($data);
            if(empty($res)){
                $this->return_msg(0, '订单添加失败');
            }else{
                
                if(!empty($data['travelperson_id'])){
                    $temp = [];
                    foreach ($data['travelperson_id'] as $key => $value) {
                        $temp[$key]['pid'] = $value;
                        $temp[$key]['oid'] = model('BuyOrder')->id;
                    
                        $re = model('RelationPersonOrder')->saveAll($temp);
                        if(empty($re)){
                            $this->return_msg(0, '出行人与订单关系添加失败');
                        }
                    }
                }
                if(!empty($data['coupon_id'])){
                    model('Coupon')->isUpdate(true)->save(['status'=>2,'id'=>$data['coupon_id']]);
                }
                //团体订单发送通知
                if($data['type'] == 2){
                    $code = make_code(6);
                    $update = model('BuyOrder')->isUpdate(true)->save(['confirm_code'=>$code,'id'=>model('BuyOrder')->id]);
                    if(empty($update)){
                        $this->return_msg(0, '更新订单确认码失败');
                    }else{
                        $sms  = new Sms();
                        $smsData = $sms->send('13312509319', '研学淘', 'SMS_156465013', ['code' => $code,'title'=>$result['name'],'money'=>$data['price']]);
                        if ($smsData->Message == "OK" && $smsData->Code == 'OK') {
            
                        } else {
                            $this->return_msg(0, $smsData->Message);
                        }
                    }
                    
                }
                $return  = model('BuyOrder')->where('id',model('BuyOrder')->id)->find()->toArray();
                $return['buy_name'] = $result['name'];
                $this->return_msg(200, '订单添加成功',$return);
            }
        } else {
            $this->return_msg(0, '请求方式不正确');
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
        if (strtolower($this->request->method()) == 'get') {
            $list = Model('BuyOrder')->where('id',$id)->find()->toArray();
            
            if($list['cmod']=='course'){
                $goodInfo = model('Course')->where('id',$list['xid'])->find()->toArray();
                $goodInfo['cover'] =  strpos($goodInfo['cover'],'/upload/')!==false ? $this->request->domain().$goodInfo['cover']:$this->request->domain().DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR.$goodInfo['cover'];
                $travelPerson = model('RelationPersonOrder')->where('oid',$list['id'])->column('pid');
                $travelPerson = array_unique($travelPerson);
                $travelPersons = [];
                foreach ($travelPerson as $k => $val) {
                    $person = model('TravelPerson')->where('id',$val)->find();
                    if(!empty($person)){
                        $travelPersons[$k] = $person->toArray();
                    }
                   
                }
                $travelPersons = array_values($travelPersons);
                $journeyInfo = model('CourseJourney')->where('cid',$list['xid'])->select()->toArray();
                $list['travelInfo'] = $travelPersons;
                $list['journeyInfo'] = $journeyInfo;
                $list['goodInfo'] = $goodInfo;
            }
            

            if(empty($list)){
                $this->return_msg(0, '暂时没有数据');
            }else{
                $this->return_msg(200, '获取成功', $list);
            }
        }else{
            $this->return_msg(0, '请求方式不正确');
        }
    }
    /**
     * [chargeoff description]
     * @Author   xiaolong
     * @DateTime 2019-03-13T11:27:46+0800
     * @return   [type]                   订单核销
     */
    public function chargeoff(){
        $data = $this->request->param();
        if(empty($data['order_no'])){
            $this->return_msg(0, '订单号不能为空');
        }
        if(empty($data['confirm_code'])){
            $this->return_msg(0, '确认码不能为空');
        }
        if(empty($data['roles'])){
            $this->return_msg(0, '角色标识不能为空');
        }
        $roleInfo = model('AuthRole')->where('alias',$data['roles'])->find();
        $result = model('BuyOrder')->where(['order_no'=>$data['order_no'],'confirm_code'=>$data['confirm_code']])->find()->toArray();
        if(empty($result)){
            $this->return_msg(0, '订单不存在');
        }
        if(empty($roleInfo)){
            $this->return_msg(0, '角色不存在');
        }
        if($result['state'] !== 2){
            $this->return_msg(0, '订单未支付，不能核销');
        }
        if($result['state'] == 4){
            $this->return_msg(0, '订单已经核销');
        }
        $state = Db::table('buy_order')->where('order_no',$data['order_no'])->update(['state' => 4]); 
        if($state){
            $this->return_msg(200, '订单核销成功');
        }else{
            $this->return_msg(0, '订单核销失败');
        }
        
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
        $data = $this->request->param();
        $list = model('BuyOrder')->where('id',$id)->find();
        if(empty($list)){
            $this->return_msg(0, '暂时没有订单');
        }
        $res = model('BuyOrder')->allowField(true)->isUpdate(true)->save($data, ['id' => $id]);
        if(empty($res)){
            $this->return_msg(0, '更新失败');
        }else{
            $this->return_msg(200, '更新成功');
        }
    }

    /**
     * 删除指定资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if(empty($id)){
            $this->return_msg(0, '订单Id不能为空');
        }
        $res = model('BuyOrder')->where('id',$id)->delete();
        if(empty($res)){
            $this->return_msg(0, '删除订单失败');
        }else{
            $this->return_msg(200, '删除订单成功');
        }
    }


    /**
     * 根据条件显示相应的数据
     * @return \think\Response
     */
    public function ordergraphlist()
    {
        if (strtolower($this->request->method()) == 'get') {
            $where = [];
            $order = "id ASC";
            if($this->scene == 'admin'){
                if(!in_array($this->session['username'], array('admin'))){
                    $courseIds = model('Course')->where('owner','in',$this->admin_roles)->column('id');
                    if(empty($courseIds)){
                        $this->return_msg(200, '暂时没有数据',[]);
                    }else{
                        $where['xid'] = ['in', $courseIds]; 
                        $where['cmod'] = ['=', 'course'];
                    }
                    
                }
            }

            $cmod = $this->request->get('cmod', '');
            if (!empty($cmod)) {
                $where['cmod'] = ['=', $cmod];
                $order = $order;
            }

            $state = $this->request->get('state', '');
            if (!empty($state)){
                $state = explode(',', $state);
                $where['state'] = ['in',$state];
                $order = $order;
            }

            $time = $this->request->get('time', '');
            if (!empty($time) && $time == 'thisweek') {
                $thisweek_start = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),date("d")-date("w")+1,date("Y"))); 
                $thisweek_end = date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("d")-date("w")+7,date("Y"))); 
                $where['ordertime'] = ['between',[$thisweek_start,$thisweek_end]];
                $order = $order;
            } elseif (!empty($time) && $time == 'lastweek') {
                $lastweek_start = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),date("d")-date("w")+1-7,date("Y")));
                $lastweek_end = date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("d")-date("w")+7-7,date("Y")));
                $where['ordertime'] = ['between',[$lastweek_start,$lastweek_end]];
                $order = $order;
            } elseif (!empty($time) && $time == 'thismonth') {
                $thismonth_start = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),1,date("Y"))); 
                $thismonth_end = date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("t"),date("Y"))); 
                $where['ordertime'] = ['between',[$thismonth_start,$thismonth_end]];
                $order = $order;
            } elseif (!empty($time) && $time == 'lastmonth') {
                $lastmonth_start = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m")-1,1,date("Y"))); 
                $lastmonth_end = date("Y-m-d H:i:s",mktime(23,59,59,date("m") ,0,date("Y"))); 
                $where['ordertime'] = ['between',[$lastmonth_start,$lastmonth_end]];
                $order = $order;
            } elseif (empty($time)) {
                $thisweek_start = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),date("d")-date("w")+1,date("Y"))); 
                $thisweek_end = date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("d")-date("w")+7,date("Y"))); 
                $where['ordertime'] = ['between',[$thisweek_start,$thisweek_end]];
                $order = $order;
            }

            $limit = $this->request->get('limit/d', 20);
            $page = $this->request->get('page/d', 1);
            
           
            $lists = model('BuyOrder')->where($where)->where('paytime', 'neq', '')->order($order)->page($page, $limit)->select()->toArray();
            foreach ($lists as $key => $value) {
                if (date("Ymd His",$value['paytime'])) {
                    $lists[$key]['paytime'] = date('y-m-d', strtotime($value['paytime']));
                    $lists[$key]['orderInfo']['price'] = model('BuyOrder')->where('paytime', 'like', '%'.$value['paytime'].'%')->sum('price');
                }
            }
           
            $res = [];
            $res["total"] = model('BuyOrder')->where($where)->where('paytime', 'neq', '')->count();
            $res["list"] = $lists;
            if (empty($res["list"])) {
                $this->return_msg(200, '暂时没有数据',$res);
            } else {
                $this->return_msg(200, '获取成功', $res);
            }
        } else {
            $this -> return_msg(0, '请求方式不正确');
        }
    }
}
