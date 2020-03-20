<?php
    
    namespace app\api\controller\v1;
    
    use app\api\controller\Common;
    use think\Request;
    
    class Login extends Common
    {
        /**
         * 显示资源列表
         *
         * @return \think\Response
         */
        public function index()
        {
            echo 'index1';
        }
        
        /**
         * 显示创建资源表单页.
         *
         * @return \think\Response
         */
        public function create()
        {
            echo 'creat';
        }
        
        /**
         * 保存新建的资源
         *
         * @param  \think\Request $request
         * @return \think\Response
         */
        public function save(Request $request)
        {
            
            if (strtolower($this->request->method()) == 'post') {
                if($this->scene == 'admin'){
                    $check = $this->check_username_exist($this->request->param('username'), 1);
                    if ($check['status']) {
                        $res = $this->check_password($this->request->param('password'), $check['data']['password']);
                        if ($res['status']) {
            
                            $time_out = time() + 30*24*3600; //过期时间
                            $token = create_token($check['data']['username'], $check['data']['create_time']);
                            $update = [
                                'token' => $token,
                                'exceed_time' => $time_out,
                            ];
                            $result = model('AuthAdmin')->where('username', $check['data']['username'])->update($update);
                            if ($result) {
                                unset($check['data']['password']);
                                $return = array_merge($check['data'], $update);
                                $return['avatar'] = !empty($return['avatar']) ? $this->request->domain().$return['avatar'] : '';
                                $return['life_photo'] = !empty($return['life_photo']) ? $this->request->domain().$return['life_photo'] : '';
                                $this->return_msg(200, '登录成功', $return);
                            } else {
                                $this->return_msg(0, '更新token失败');
                            }
                        } else {
                            $this->return_msg(0, $res['info']);
                        }
        
                    } else {
                        $this->return_msg(0, $check['info']);
                    }
                }elseif($this->scene == 'app'){
                    //密码登录
                    if($this->request->param('password')){
                        $check = $this->check_username_exist($this->request->param('phone'), 1);
                        if ($check['status']) {
                            $res = $this->check_password($this->request->param('password'), $check['data']['password']);
                            if ($res['status']) {
                
                                $time_out = time() + 30*24*3600; //过期时间
                                $token = create_token($check['data']['phone'], $check['data']['create_time']);
                                $update = [
                                    'token' => $token,
                                    'exceed_time' => $time_out,
                                ];
                                $result = model('User')->where('phone', $this->request->param('phone'))->update($update);
                                if ($result) {
                                    unset($check['data']['password']);
                                    $return = array_merge($check['data'], $update);
                                    $return['avatar'] = !empty($return['avatar']) ? $this->request->domain().$return['avatar'] : '';
                                    $return['life_photo'] = !empty($return['life_photo']) ? $this->request->domain().$return['life_photo'] : '';
                                    $this->return_msg(200, '登录成功', $return);
                                } else {
                                    $this->return_msg(0, '更新token失败');
                                }
                            } else {
                                $this->return_msg(0, $res['info']);
                            }
                        } else {
                            $this->return_msg(0, $check['info']);
                        }    
                    }else{
                        //验证码登录
                        $phone = $this->request->param('phone');
                        if(empty($phone)){
                           $this->return_msg(0, '手机号不能为空');
                        }
                        if(empty($this->request->param('code'))){
                           $this->return_msg(0, '验证码不能为空');
                        }
                        /**
                         *  仅仅是登录
                         * @var [type]
                         
                        $type = $this->check_username($this->request->param('phone'));
                        $check = $this->check_exist($this->request->param('phone'),$type,1); 
                        if ($check['status']) {
                            $checkCode = $this->check_code($this->request->param('phone'),$this->request->param('code'));
                            if($checkCode['status']){
                                $time_out = time() + 3*24*3600; //过期时间
                                $token = create_token($check['data']['phone'], $check['data']['create_time']);
                                $update = [
                                    'token' => $token,
                                    'exceed_time' => $time_out,
                                ];
                                if(!empty($this->request->param('openid'))){
                                    $update['openid'] = $this->request->param('openid');
                                }
                                if(!empty($this->request->param('unionid'))){
                                    $update['unionid'] = $this->request->param('unionid');
                                }
                                $result = model('User')->where('phone', $this->request->param('phone'))->update($update);
                                if ($result) {
                                    unset($check['data']['password']);
                                    $return = array_merge($check['data'], $update);
                                    $return['avatar'] = !empty($return['avatar']) ? $this->request->domain().$return['avatar'] : '';
                                    $return['life_photo'] = !empty($return['life_photo']) ? $this->request->domain().$return['life_photo'] : '';
                                    $this->return_msg(200, '登录成功', $return);
                                } else {
                                    $this->return_msg(0, '更新token失败');
                                }
                            }else{
                                $this->return_msg(0, $checkCode['info']);
                            }
                        }else{
                            $this->return_msg(0, $check['info']);
                        }
                        */  
                        /**
                         * 注册并登录
                         */
                        $checkCode = $this->check_code($phone,$this->request->param('code'));
                        if($checkCode['status']){
                            $res = model('User')->where('phone',$phone)->find();
                            if(empty($res)){
                                $data = $this->request->param(); 
                                $data['password'] = create_password('zhikeyanxuetao');
                                $re = model('User')->allowField(true)->save($data);
                                    if(empty($re)){
                                        $this->return_msg(0, '注册失败');
                                    }else{
                                        $return  = model('User')->where('id',model('User')->id)->find()->toArray();
                                        $update = [
                                            'token' => create_token($phone,$return['create_time']),
                                            'exceed_time' => time() + 30*24*3600, //过期时间   
                                        ];
                                        model('User')->where('id',$return['id'])->update($update);
                                        // $couponData = array(
                                        //     'uid' => $return['id'],
                                        //     'type'=> 1,
                                        //     'price'=>config('coupon.price'),
                                        //     //是否长期有效
                                        //     'is_exceed'=>1,
                                        //     'type'=>1
                                        // );
                                        //model('Coupon')->allowField(true)->save($couponData);
                                        $return['avatar'] = !empty($return['avatar']) ? $this->request->domain().$return['avatar'] : '';
                                        $return['life_photo'] = !empty($return['life_photo']) ? $this->request->domain().$return['life_photo'] : '';
                                        $return['token'] = $update['token'];
                                        $return['exceed_time'] = $update['exceed_time'];
                                        unset($return['password']);
                                        $this->return_msg(200, '注册并登录成功',$return);
                                    }
                            }else{
                                $update = model('User')->where('phone',$phone)->update([
                                    'token'=>create_token($phone,$res['create_time']),
                                    'exceed_time'=>time()+30*24*3600,
                                ]);
                                if(empty($update)){
                                    $this->return_msg(0, '更新Token失败');
                                }else{
                                    $return  = model('User')->where('phone',$phone)->find()->toArray();
                                    $return['avatar'] = !empty($return['avatar']) ? $this->request->domain().$return['avatar'] : '';
                                    $return['life_photo'] = !empty($return['life_photo']) ? $this->request->domain().$return['life_photo'] : '';
                                    unset($return['password']);
                                    $this->return_msg(200, '登录成功',$return);
                                }
                            }
                        }else{
                            $this->return_msg(0, $checkCode['info']);
                        }
                    }
                    
                }
                
                
            } else {
                $this->return_msg(0, '请求方式不正确');
            }
            
            
        }
        
        /**
         * 显示指定的资源
         *
         * @param  int $id
         * @return \think\Response
         */
        public function read($id)
        {
            
        }
        
        /**
         * 显示编辑资源表单页.
         *
         * @param  int $id
         * @return \think\Response
         */
        public function edit($id)
        {
            //
        }
        
        /**
         * 保存更新的资源
         *
         * @param  \think\Request $request
         * @param  int $id
         * @return \think\Response
         */
        public function update(Request $request, $id)
        {
            //
        }
        
        /**
         * 删除指定资源
         *
         * @param  int $id
         * @return \think\Response
         */
        public function delete($id)
        {
            //
        }
    }
