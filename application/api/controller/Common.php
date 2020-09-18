<?php
    /**
     * Created by PhpStorm.
     * User: xiaolong
     * Date: 2018/10/9
     * Time: 1:48 PM
     */
    
    namespace app\api\controller;
    
    use think\Controller;
    use think\Validate;
    use think\Request;
    
    
    class Common extends Controller {
        
        protected $request;
        protected $isAuthorized;
        protected $authorized;
        protected $session;
        protected $scene;
        protected $webroot;
        protected $admin_roleIds;
        protected $admin_roles=[];
        public function __construct()
        {

//            parent::__construct();
            $this->request    = Request::instance();
            $this->isAuthorized = $this->request->header('authorized') && strlen($this->request->header('authorized')) == 32;
            $this->authorized   = $this->isAuthorized ? $this->request->header('authorized') : NULL;
            $this->scene = empty($this->request->header('scene')) ? 'app': $this->request->header('scene');
            $this->webroot = ['http://www.blog.com','https://www.blog.com'];

            $this->_initialize();
        }
        /**
         * [_initialize description]
         * @Author   xiaolong
         * @DateTime 2019-04-01T16:54:43+0800
         * @return   [type]                   [description]
         */
        protected function _initialize() {
            
            if(!$this->isAuthorized){
               
                if ($this->filtration()) {
                    $this->return_msg(50008, '请先登录!');
                    
                }
            } else {
                return $this->initSession();
            }
        }
        /**
         * [initSession description]
         * @Author   xiaolong
         * @DateTime 2019-04-01T16:50:46+0800
         * @return   [type]                   初始化session
         */
        protected function initSession() {
            //判断token是否存在
            if($this->scene == 'admin'){
                $res = model('AuthAdmin')->where('token',$this->request->header('authorized'))->find();
            }elseif($this->scene == 'app'){
                $res = model('User')->where('token',$this->request->header('authorized'))->find();
            }
            if($this->filtration()){
                if (empty($res)) {
                    $this->return_msg(50008, '未知的token!');
            
                } else {
                    if(time()>$res['exceed_time']){
                        //token过期
                        $this->return_msg(50014, '请重新登录!'); 
                    }
    
                }
            }
            $this->session = $res->toArray();
            if($this->scene == 'admin'){
                if($this->session['username']!=='admin'){
                    $this->admin_roleIds = model('AuthRoleAdmin')->where('admin_id',$this->session['id'])->column('role_id');
                    foreach ($this->admin_roleIds as $key => $val){
                        // $role_name = model('AuthRole')->where('id',$val)->value('alias');
                        array_push($this->admin_roles,$val);
                    }
                    
                }else{
                    $this->admin_roles = ['admin'];
                }
            }
        }
    
        /**
         * @return bool 过滤不用token验证的控制器
         */
        protected function filtration() {
            $controller = $this->request->param('controller');
            if($this->scene == 'admin'){
                $filtrationArr = ['register', 'login'];
            }elseif($this->scene == 'app'){
                $filtrationArr = ['register','login','product','toppic','Queue'];
            }
            return !in_array($controller,$filtrationArr);
        }
        /**
         * api 数据返回
         * @param  [int] $code [结果码 200:正常/4**数据问题/5**服务器问题]
         * @param  [string] $msg  [接口要返回的提示信息]
         * @param  [array]  $data [接口要返回的数据]
         * @return [string]       [最终的json数据]
         */
        public function return_msg($code, $msg = '', $data = []) {
            /*********** 组合数据  ***********/
            $return_data['code'] = $code;
            $return_data['msg']  = $msg;
            $return_data['data'] = $data;
            /*********** 返回信息并终止脚本  ***********/
            echo json_encode($return_data);die;
        }
        /**
         * 检测用户名是否符合要求
         * @param $username
         * @return string
         *
         */
        public function check_username($username) {
            /*********** 判断是否为邮箱  ***********/
            $is_email = Validate::is($username, 'email') ? 1 : 0;
            /*********** 判断是否为手机  ***********/
            $is_phone = preg_match('/^1[345789]\d{9}$/', $username) ? 4 : 2;
            /*********** 最终结果  ***********/
            $flag = $is_email + $is_phone;
            switch ($flag) {
                /*********** not phone not email  ***********/
                case 2:
                    return '';
                    break;
                /*********** is email not phone  ***********/
                case 3:
                    return 'email';
                    break;
                /*********** is phone not email  ***********/
                case 4:
                    return 'phone';
                    break;
            }
            
        }
    
        /**
         * @param $value
         * @param $type  手机号或者邮箱
         * @param $exist  1是必须存在  0是不用必须存在
         * @throws \think\db\exception\DataNotFoundException
         * @throws \think\db\exception\ModelNotFoundException
         * @throws \think\exception\DbException
         */
        public function check_exist($value, $type, $exist) {
            $type_num  = $type == "phone" ? 2 : 4;
            $flag      = $type_num + $exist;
            $phone_res = model('User')->where('phone', $value)->find();
            $email_res = model('User')->where('email', $value)->find();
            switch ($flag) {
                /*********** 2+0 phone need no exist  ***********/
                case 2:
                    if ($phone_res) {
                        return ['status' => false, 'info'=> '此手机号已注册!'];
                    }
                    break;
                /*********** 2+1 phone need exist  ***********/
                case 3:
                    if (!$phone_res) {
                        return ['status' => false, 'info'=> '此手机号不存在!'];
                    }
                    break;
                /*********** 4+0 email need no exist  ***********/
                case 4:
                    if ($email_res) {
                        return ['status' => false, 'info'=> '此邮箱已被占用!'];
                    }
                    break;
                /*********** 4+1 email need  exist  ***********/
                case 5:
                    if (!$email_res) {
                        return ['status' => false, 'info'=> '此邮箱不存在!'];
                    }
                    break;
            }
            $phone_res = empty($phone_res) ? array() :$phone_res->toArray();
            $email_res = empty($email_res) ? array() :$email_res->toArray();
            return ['status' => true, 'info'=> '','data'=>$type=='phone'?$phone_res:$email_res];
        
        }
        /* $scene  admin是后台
         * $exist  1是必须存在  0是不用必须存在
         */
        public function check_username_exist($value, $exist) {
            if($this->scene == 'admin'){
                $res = model('AuthAdmin')->where('username|phone', $value)->find();
            }elseif($this->scene == 'app'){
                $res = model('User')->where('username|phone', $value)->find();
            }
            $res = empty($res) ? array() :$res->toArray();
            switch ($exist) {
                /*********** 2+0 phone need no exist  ***********/
                case 1:
                    if (!$res) {
                        return ['status' => false, 'info'=> '用户不存在!请先注册'];
                    }
                    break;
                /*********** 2+1 phone need exist  ***********/
                case 0:
                    if ($res) {
                        return ['status' => false, 'info'=> '此账户已注册!'];
                    }
                    break;
            }
            return ['status' => true, 'info'=> '','data'=>$res];
        
        }
        /*
         * 检测密码
         *$password 用户输入的密码
         * $password1 数据库中的密码
         */
        public function check_password($password,$password1) {
           if(empty($password)){
               return ['status' => false, 'info'=> '请输入密码!'];
              
           }
           if(create_password($password) !== $password1){
               return ['status' => false, 'info'=> '请输入正确的密码!'];
              
           }
            return ['status' => true, 'info'=> '检测成功'];
           
        }
        /**
         * 检测code是否存在，是否正确
         * @param $user_name
         * @param $code
         */
        public function check_code($user_name, $code) {
            /*********** 检测是否超时  ***********/
            $check = model('SmsCode')->where(['phone'=>$user_name,'code'=>$code])->find();
            if(empty($check)){
                return ['status' => false, 'info'=> '验证码不正确!'];
            }
            /*********** 检测验证码是否正确  ***********/
            if (time()>$check['exceed_time']) {
                return ['status' => false, 'info'=> '验证超时!请在两分钟内验证!'];
            }
            model('SmsCode')->where(['phone'=>$user_name,'code'=>$code])->delete();
            return ['status' => true, 'info'=> ''];
        }
        
    }
