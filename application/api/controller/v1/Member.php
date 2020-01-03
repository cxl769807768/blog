<?php
 
 namespace app\api\controller\v1;

 use app\api\controller\Common;
 use think\Request;
 class Member extends Common
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
    		$order = 'id DESC';

    		$realname = $this->request->get('realname', '');
    		if (!empty($realname)) {
    		 	$where['realname'] = ['like', '%' .$realname. '%'];
    		 	
    		}

    		$username = $this->request->get('username', '');
    		if (!empty($username)) {
    			$where['username'] = ['like', '%' .$username. '%'];
    			
    		}

    		$phone = $this->request->get('phone', '');
    		if (!empty($phone)) {
    			$where['phone'] = ['=', $phone];
    			
    		}

    		$status = $this->request->get('status', '');
    		if (!empty($status)) {
    			$where['status'] = ['=', $status];
    			
    		}
            // 昨天
            $this_yesterday = $this->request->get('yesterday', '');
            if (!empty($this_yesterday)) {
                $yesterday_start=date("Y-m-d H:i:s", mktime(0,0,0,date('m'),date('d')-1,date('Y')));
                $yesterday_end=date("Y-m-d H:i:s",mktime(23,59,59,date('m'),date('d')-1,date('Y')));
                $where['create_time'] = ['between',[$yesterday_start,$yesterday_end]];
            }
            // 本周
            $this_week = $this->request->get('thisweek', '');
            if (!empty($this_week)) {
                $thisweek_start = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),date("d")-date("w")+1,date("Y"))); 
                $thisweek_end = date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("d")-date("w")+7,date("Y"))); 
                $where['create_time'] = ['between',[$thisweek_start,$thisweek_end]];
               
            }
            // 上周
            $this_lastweek = $this->request->get('lastweek', '');
            if (!empty($this_lastweek)) {
                $lastweek_start = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),date("d")-date("w")+1-7,date("Y")));
                $lastweek_end = date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("d")-date("w")+7-7,date("Y")));
                $where['create_time'] = ['between',[$lastweek_start,$lastweek_end]];
            }
            // 本月
            $this_month = $this->request->get('thismonth', '');
            if (!empty($this_month)) {
                $thismonth_start = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m"),1,date("Y"))); 
                $thismonth_end = date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("t"),date("Y"))); 
                $where['create_time'] = ['between',[$thismonth_start,$thismonth_end]];
                
            }
            // 上月
            $this_lastmonth = $this->request->get('lastmonth', '');
            if (!empty($this_lastmonth)) {
                $this_lastmonth_start = date("Y-m-d H:i:s",mktime(0, 0 , 0,date("m")-1,1,date("Y"))); 
                $this_lastmonth_end = date("Y-m-d H:i:s",mktime(23,59,59,date("m"),0,date("Y"))); 
                $where['create_time'] = ['between',[$this_lastmonth_start,$this_lastmonth_end]];
            }
            // 本季度
            $season = ceil((date('n'))/3);//当月是第几季度
            $thisquarter = $this->request->get('thisquarter', '');
            if (!empty($thisquarter)) {
                $thisquarter_start = date("Y-m-d H:i:s",mktime(0, 0, 0,$season*3-3+1,1,date('Y'))); 
                $thisquarter_end = date("Y-m-d H:i:s",mktime(23,59,59,$season*3,date('t',mktime(0, 0 , 0,$season*3,1,date("Y"))),date('Y'))); 
                $where['create_time'] = ['between',[$thisquarter_start,$thisquarter_end]];
            }
    		$limit = $this->request->get('limit/d', 20);
            $page = $this->request->get('page/d', 1);

            $lists = model('User')->where($where)->order($order)->limit($limit)->page($page)->select()->toArray();

            $res = [];
            $res['total'] = model('User')->where($where)->count();
            $res['list'] = $lists;
            if (empty($res['list'])) {
            	$this -> return_msg(400, '暂时没有数据', $res);
            } else {
            	$this -> return_msg(200, '获取成功', $res);
            }
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

    }

    /**
     * 显示指定的资源
     *
     * @param  int  $id
     * @return \think\Response
     */
    public function read($id)
    {
        if (strtolower($this->request->method()) == 'post') {
        	
        	$data = $this->request->post();
        	$res = model('User')->where('id', $data['id'])->find();
        	if (empty($res)) {
        		$this -> return_msg(400, '暂时没有数据', $res);
        	} else {
                $res = $res->toArray();
                $res['avatar'] = !empty($res['avatar']) ? $this->request->domain().$res['avatar'] : '';
                $res['life_photo'] = !empty($res['life_photo']) ? $this->request->domain().$res['life_photo'] : '';
                unset($res['password']);
                unset($res['exceed_time']);
                unset($res['token']);
        		$this -> return_msg(200, '获取成功', $res);
        	}
        } else {
        	$this -> return_msg(0, '请求方式不正确');
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
    	if (strtolower($this->request->method()) == 'post') {
    		
    		$data = $this->request->post();

    		$old = model('User')->where('id', $data['id'])->find()->toArray();
    		if (intval($data['status']) == 0) {
    			if (intval($data['status']) == $old['status']) {
    				$this -> return_msg(400, '该用户已是禁用状态');
	    		} else {
	    			$res = model('User')->where('id', $data['id'])->update(['status' => intval($data['status'])]);
	    			if ($res) {
	    				$this -> return_msg(200, '编辑用户状态成功');
	    			} else {
	    				$this -> return_msg(400, '编辑用户状态失败');
	    			}
	    		}
    		} elseif (intval($data['status']) == 1) {
    			if (intval($data['status']) == $old['status']) {
    				$this -> return_msg(400, '该用户已是启用状态');
    			} else {
    				$res = model('User')->where('id', $data['id'])->update(['status' => intval($data['status'])]);
    				if ($res) {
    					$this -> return_msg(200, '编辑用户状态成功');
    				} else {
    					$this -> return_msg(400, '编辑用户状态失败');
    				}
    			}
    		}
    	} else {
    		$this -> return_msg(0, '请求方式不正确');
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
       
    }

    /**
     *  重置用户密码
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response 
     */
    public function reset(Request $request, $id)
    {
        if (strtolower($this->request->method()) == 'post') {

            $data = $this->request->post();

            $old = model('user')->where('id', $data['id'])->find()->toArray();

            $password = create_password('123456');
            if ($password === $old['password']) {
                $this -> return_msg(400, '已重置用户密码，密码为123456');
            }

            $res = model('User')->where('id', $data['id'])->update(['password' => $password]);
            if (empty($res)) {
                $this -> return_msg(400, '重置失败');
            } else {
                $this -> return_msg(200, '重置成功，密码为123456');
            }
        } else {
            $this -> return_msg(0, '请求方式不正确');
        }
    }
 }