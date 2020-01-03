<?php

namespace app\api\controller\v1;

use app\api\controller\Common;
use getspell\GetSpell;
use think\Request;
use aliyun\sms\Sms;

class Role extends Common
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $where = [];
        $order = 'id ASC';
        $status = $this->request->get('status', '');
        if (!empty($status)){
            $status = explode(',', $status);
            $where['status'] = ['in',$status];
            $order = '';
        }
        $name = $this->request->get('name', '');
        if (!empty($name)){
            $where['name'] = ['like','%'.$name. '%'];
            $order = '';
        }
        
        
        $limit = $this->request->get('limit/d', 20);
        $page = $this->request->get('page/d', 1);
        $lists = model('AuthRole')->where($where)->order($order)->page($page,$limit)->select()->toArray();
        
        $res = [];
        $res["total"] = model('AuthRole')->where($where)->count();
        $res["list"] = $lists;
        if(empty($res)){
            $this->return_msg(0,'暂时没有数据');
        }else{
            $this->return_msg(200,'获取成功',$res);
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
            $data = $this->request->post();
            if (empty($data['name'])) {
                $this->return_msg('400', '角色名称不能为空');
            }
            $res = model('AuthRole')->allowField(true)->save($data);
            if ($res) {
                
                $data['id'] = model('AuthRole')->id;
                $this->return_msg(200,'添加成功',$data);
                
            } else {
                $this->return_msg(400, '添加失败');
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
            $list = Model('AuthRole')->where('id',$id)->find()->toArray();
            
            $ruleId = model('AuthRoleRule')->where('role_id', $id)->column('rule_id');
            $list['rules'] = model('AuthRule')->where('id', 'in', $ruleId)->select()->toArray();
            
            foreach ($ruleId as $key => $val){
                $pid = model("AuthRule")->where('id',$val)->value('pid');
                if(empty($pid)){
                    unset($ruleId[$key]);
                }
            }
            $list['ruleIds'] = $ruleId;
            
            if(empty($list)){
                $this->return_msg(0,'暂时没有数据');
            }else{
                $this->return_msg(200,'获取成功',$list);
            }
        } else {
            $this -> return_msg(0, '请求方式不正确');
        }
    }

    /**
     * 显示编辑资源表单页.
     *
     * @param  
     * @return \think\Response
     */
    public function edit($id)
    {
        //
    }

    /*
     *  授权
     */
    public function setRules()
    {
        if (strtolower($this->request->method()) == 'post') {
            
            $data = $this->request->post();
            
            model('AuthRoleRule')->where('role_id', $data['role_id'])->delete();
            $role_id = $data['role_id'];
            $temp = [];
            foreach ($data['auth_rules'] as $key => $value) {
                $temp[$key]['rule_id'] = $value;
                $temp[$key]['role_id'] = $role_id;
            }
            $res = model('AuthRoleRule')->saveAll($temp);
            if ($res) {
                $this -> return_msg(200, '授权成功');
            } else {
                $this -> return_msg(400, '授权失败');
            }
        } else {
            $this -> return_msg(0, '请求方式不正确');
        }
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
            if (empty($data['name'])) {
                $this->return_msg('400', '角色名称不能为空');
            }
            
            $res = model('AuthRole')->allowField(true)->isUpdate(true)->save($data);
            if ($res) {
                $this->return_msg(200,'修改成功',$data);
                
            } else {
                $this->return_msg(400, '修改失败');
            }
        } else {
            $this->return_msg(0, '请求方式不正确');
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
        if (strtolower($this->request->method()) == 'post') {
            
            $res = model('AuthRole')->where('id', $id)->delete();
            if($res){
                $result = model('AuthRoleAdmin')->where('role_id', $id)->delete();
                if($result){
                    $this->return_msg(200, '删除成功');
                }else{
                    $this->return_msg(0, '删除失败');
                }
            }
            
        }else{
            $this->return_msg(0, '请求方式不正确');
        }
    }
/**
 * [audit description]
 * @Author   xiaolong
 * @DateTime 2019-06-19T10:16:51+0800
 * @param    [type]                   $id [description]
 * @return   [type]                   审核
 */
    public function audit($id){
       $data = $this->request->post(); 
       $role = model('AuthRole')->where('id', $id)->find()->toArray();
       if(empty($role)){
            $this->return_msg(0,'机构不存在');
       }
       if(!isset($data['status'])){
            $this->return_msg(0,'审核状态不能为空');
       }
       if($data['status'] == 0 && empty($data['errorTip'])){
            $this->return_msg(0,'审核不通过的原因不能为空');
       }
       $admin_id = model('AuthRoleAdmin')->where('role_id', $id)->value('admin_id'); 
       $admin = model('AuthAdmin')->where('id',$admin_id)->find();
       model('AuthRole')->where('id', $id)->update(['status'=>$data['status']]);
       model('AuthAdmin')->where('id',$admin_id)->update(['status'=>$data['status']]);
       if($data['status'] == 1){
            
       }elseif($data['status'] == 0){
            $sms  = new Sms();
            $data = $sms->send($admin['phone'], '研学淘', 'SMS_167845002', ['ApplicationNumber' => $id,'ErrorTip'=>$data['errorTip']]);
            if ($data->Message == "OK" && $data->Code == 'OK') {
             
                $this->return_msg(200,'短信发送成功',['phone'=>$admin['phone']]);
                
            } else {
                $this->return_msg(0, $data->Message);// 测试环境给客户端显示验证码
            }
       }
    }
}
