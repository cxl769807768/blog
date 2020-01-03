<?php

namespace app\api\controller\v1;

use app\api\controller\Common;
use think\Request;

class ThirdpartyLogin extends Common
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
        $openid = $this->request->param('openid',"");
        $unionid = $this->request->param('unionid',"");
        $res = model('User')->where('openid',$openid)->find();
        if(empty($res)){
            $data = [
                'openid'=>$openid,
                'username'=>'yxt_'.generate_username(6),
                'unionid'=>$unionid,
                'realname'=>$result['nickname'],
            ];
            $re = model('User')->allowField(true)->save($data);
            if(empty($re)){
                $this->return_msg(0, '注册失败');
            }else{
                $return  = model('User')->where('openid',$openid)->find();
                model('User')->where('id',$return['id'])->update([
                    'token' => create_token($return['phone'],$return['create_time']),
                    'exceed_time' => time() + 3*24*3600, //过期时间   
                ]);
                $return  = model('User')->where('openid',$openid)->find()->toArray();
                $return['avatar'] = !empty($return['avatar']) ? $this->request->domain().$return['avatar'] : '';
                unset($return['password']);
                $this->return_msg(200, '注册成功',$return);
            }
        }else{
            model('User')->where('openid',$openid)->update(['exceed_time'=>time()+3*24*3600,'token'=>create_token($res['phone'],$res['create_time'])]);
            $return  = model('User')->where('openid',$openid)->find()->toArray();
            $return['avatar'] = !empty($return['avatar']) ? $this->request->domain().$return['avatar'] : '';
            unset($return['password']);
            $this->return_msg(200,'获取用户微信数据成功并登录',$return);
        }
        
    }
    /**
     * [bind description]
     * @Author   xiaolong
     * @DateTime 2019-01-28T17:42:42+0800
     * @return   [type]       微信登录绑定手机号            [description]
     */
    public function bind() {
        $phone = $this->request->param('phone');
        if(empty($phone)){
           $this->return_msg(0, '手机号不能为空');
        }
        if(empty($this->request->param('code'))){
           $this->return_msg(0, '验证码不能为空');
        }
        if(empty($this->request->param('openid'))){
            $this->return_msg(0, 'openid不能为空');
        }
        $checkCode = $this->check_code($phone,$this->request->param('code'));
        if($checkCode['status']){
            $res = model('User')->where('phone',$phone)->find();
            $r = model('User')->where('openid',$this->request->param('openid'))->find();
            if(empty($res)){
                $data = $this->request->param(); 
                $update = model('User')->where('openid',$this->request->param('openid'))->update([
                    'phone'=> $phone,
                    'token'=>create_token($phone,$r['create_time']),
                    'exceed_time'=>time()+3*24*3600,
                ]);

            }else{
                $update = model('User')->where('phone',$phone)->update([
                    'token'=>create_token($phone,$res['create_time']),
                    'exceed_time'=>time()+3*24*3600,
                    'openid'=>$this->request->param('openid'),
                    'unionid'=>$this->request->param('unionid'),
                ]);
                $delete = model('User')->where('openid',$this->request->param('openid'))->delete();
            }
            if(empty($update)){
                $this->return_msg(0, '更新用户信息失败');
            }else{
                $return  = model('User')->where('phone',$phone)->find()->toArray();
                $return['avatar'] = !empty($return['avatar']) ? $this->request->domain().$return['avatar'] : '';
                unset($return['password']);
                $this->return_msg(200, '绑定并登录成功',$return);
            }
        }else{
            $this->return_msg(0, $checkCode['info']);
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
