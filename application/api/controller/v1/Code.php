<?php

namespace app\api\controller\v1;

use app\api\controller\Common;
use think\Request;
use aliyun\sms\Sms;

class Code extends Common
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //
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
            $username      = $this->request->post('phone');
            $isLogin      = $this->request->post('isLogin');
            if($isLogin == 0){
                $check = $this->check_username_exist($username, $isLogin?1:0);
                if (!$check['status']) {
                    $this->return_msg('400', $check['info']);
                }
            }
            
            if(isset($username) && !empty($username)){
                $username_type = $this->check_username($username); // 检查用户名, 决定用下面哪那个函数
            }else{
                $this->return_msg(400,'手机号不能为空');
            }
            switch ($username_type) {
                case 'phone':
                    $this->get_code_by_username($username, 'phone', $isLogin?1:0); // 通过手机获取验证码
                    break;
                case 'email':
                    $this->get_code_by_username($username, 'email', $isLogin?1:0); // 通过邮箱获取验证码
                    break;
            }
        } else {
            $this -> return_msg(0, '请求方式不正确');
        }

    }
    protected function get_code_by_username($username, $type, $exist) {
        if ($type == 'phone') {
            $type_name = '手机';
        } else {
            $type_name = '邮箱';
        }
        /*********** 检测手机号/邮箱是否存在  ***********/
        // $re = $this->check_exist($username, $type, $exist);
        // if(!$re['status']){
        //     $this->return_msg(400,$re['info']);
        // }
        /*********** 生成验证码  ***********/
        $code = $this->make_code(6);
        if ($type == 'phone') {
            $this->send_code_to_phone($username, $code,$exist);
        } else {
            //$this->send_code_to_email($username, $code);
        }
    }

    /**
     * @param $num  生成6位或者4位验证码
     * @return int
     */
    protected function make_code($num) {
        $max = pow(10, $num) - 1;
        $min = pow(10, $num - 1);
        return rand($min, $max);
    }
    protected function send_code_to_phone($username, $code,$exist){
        $sms  = new Sms();
        $data = $sms->send($username, '研学淘', $exist ?'SMS_78885159':'SMS_78885157', ['code' => $code]);
        if ($data->Message == "OK" && $data->Code == 'OK') {
            //存储code
            $current_time = time();
            $res = model('SmsCode')->save([
                'phone'=>$username,
                'code'=>$code,
                'send_time'=>$current_time,
                'exceed_time'=>$current_time+2*60,
            ]);
            if($res){
                $this->return_msg(200,'短信发送成功',['phone'=>$username]);
            }else{
                $this->return_msg(400,'短信验证码存储失败');
            }
            
        } else {
            $this->return_msg(0, $data->Message);// 测试环境给客户端显示验证码
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
