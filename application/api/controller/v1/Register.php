<?php

namespace app\api\controller\v1;

use app\api\controller\Common;
use think\Request;

class Register extends Common
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
            if(empty($this->request->param('phone'))){
                $this->return_msg(0, '手机号不能为空');
            }
            if(empty($this->request->param('code'))){
                $this->return_msg(0, '验证码不能为空');
            }
            if(empty($this->request->param('password'))){
                $this->return_msg(0, '密码不能为空');
            }
            $check = $this->check_username_exist($this->request->param('phone'), 0);
            if ($check['status']) {
                $checkCode = $this->check_code($this->request->param('phone'),$this->request->param('code'));
                if($checkCode['status']){
                    $data = $this->request->param();
                    $data['password'] = create_password($this->request->param('password'));
                    $data['token'] = create_token($this->request->param('phone'), date('Y-m-d H:i:s')); 
                    $data['exceed_time'] = time() + 24*3600; //过期时间    
                    $res = model('User')->allowField(true)->save($data);
                    if(empty($res)){
                        $this->return_msg(0, '注册失败');
                    }else{
                        $return  = model('User')->where('id',model('User')->id)->find()->toArray();
                        // $couponData = array(
                        //     'uid' => $return['id'],
                        //     'type'=> 1,
                        //     'price'=>config('coupon.price'),
                        //     //是否长期有效
                        //     'is_exceed'=>1,
                        //     'type'=>1
                        // );
                        $purseData = array(
                            'uid' => $return['id'],
                            'money'=> 0,
                            'role_id'=>0
                        );
                        model('Purse')->allowField(true)->save($purseData);
                        // model('Coupon')->allowField(true)->save($couponData);
                        unset($return['password']);
                        $this->return_msg(200, '注册成功',$return);
                    }
                }else{
                    $this->return_msg(0, $checkCode['info']);
                }
                
            }else{
                $this->return_msg(0, $check['info']);
            }       
        }else{
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
