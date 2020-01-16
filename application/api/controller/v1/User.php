<?php

namespace app\api\controller\v1;

use app\api\controller\Common;
use app\common\model\AuthRule;
use think\Request;

class User extends Common
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        // if($this->session['username']=='admin'){
        //     $roles = ['admin'];
        // }else{
        //     $roles = [];
        //     $role = model('AuthRoleAdmin')->where('admin_id',$this->session['id'])->column('role_id');
            
        //     foreach ($role as $key => $val){
        //         $role_name = model('AuthRole')->where('id',$val)->value('alias');
        //         array_push($roles,$role_name);
        //     }
            
        // }
        if(in_array('admin',$this->admin_roles)){
            $rules = model('AuthRule')->where('status',1)->field('id,name,pid,title,status,sorts,icon,path,component,hidden,noCache,alwaysShow')->select()->toArray(); 
            foreach ($rules as $key => $value) {
                $rules[$key]['hidden'] = (boolean)$value['hidden'];
                $rules[$key]['alwaysShow'] = (boolean)$value['alwaysShow'];
                $rules[$key]['meta']['title'] = $value['title'];
                $rules[$key]['meta']['icon'] = $value['icon'];
                $rules[$key]['meta']['noCache'] = (boolean)$value['noCache'];
            }
            $roleRules = AuthRule::cateMerge($rules,'id','pid',0);
            $messageData = [];
        }else{
            $ruleIds = model('AuthRoleRule')->where('role_id','in',$this->admin_roleIds)->column('rule_id'); 
            $rules = model('AuthRule')->where('id','in',$ruleIds)->field('id,name,pid,title,status,sorts,icon,path,component,hidden,noCache,alwaysShow')->select()->toArray(); 
            foreach ($rules as $key => $value) {
                $rules[$key]['hidden'] = (boolean)$value['hidden'];
                $rules[$key]['alwaysShow'] = (boolean)$value['alwaysShow'];
                $rules[$key]['meta']['title'] = $value['title'];
                $rules[$key]['meta']['icon'] = $value['icon'];
                $rules[$key]['meta']['noCache'] = (boolean)$value['noCache'];
            }
            $roleRules = AuthRule::cateMerge($rules,'id','pid',0);
        }

        $return = [
            'name'=> $this->session['username'],
            'avatar'=> !empty($this->session['avatar']) ? $this->request->domain().$this->session['avatar'] : '',
            'introduction'=> '',
            'roles'=>$this->admin_roles,
            'roleRules'=>$roleRules,
        ];
        if(empty($return['roles'])){
            $this->return_msg(400,'对不起，没有权限');
        }else{
            $this->return_msg(200,'获取成功',$return);
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
        
        $data = $this->request->post();
        if(!empty($data['password'])){
            $data['password'] = create_password($this->request->param('password'));
        }
        if(!empty($data['avatar'])){
            $data['avatar'] = strpos($data['avatar'],$this->request->domain())!==false? str_replace($this->request->domain(), '', $data['avatar']) : $data['avatar'];

        }
        if(!empty($data['life_photo'])){
            $data['life_photo'] = strpos($data['life_photo'],$this->request->domain())!==false? str_replace($this->request->domain(), '', $data['life_photo']) : $data['life_photo'];

        }
        if(!empty($data['sex'])){
            $data['sex'] = $data['sex']==1? '男':'女';
        }
        if($this->scene == 'admin'){

        }elseif($this->scene == 'app'){
            $old = model('User')->where('id',$id)->find();
            if(empty($old)){
                $this->return_msg(400,'该用户不存在');
            }
            $res = model('User')->allowField(true)->isUpdate(true)->save($data, ['id' => $id]);
            if(empty($res)){
                $this->return_msg(400,'修改失败');
            }else{
                if(!empty($data['avatar'])){
                    if ($data['avatar'] !== $old['avatar']) {
                        model('ImgManage')->where(['mod' => 'avatar', 'url' => $old['avatar']])->delete();
                        model('ImgManage')->save(['mod' => 'avatar', 'url' => $data['avatar']]);
                        
                    }
                    if ($data['life_photo'] !== $old['life_photo']) {
                        model('ImgManage')->where(['mod' => 'life_photo', 'url' => $old['life_photo']])->delete();
                        model('ImgManage')->save(['mod' => 'life_photo', 'url' => $data['life_photo']]);
                        
                    }
                }
                $return  = model("User")->where("id",$id)->find()->toArray();
                unset($return['password']);
                unset($return['token']);
                unset($return['exceed_time']);
                $return['avatar'] = !empty($return['avatar']) ? $this->request->domain().$return['avatar'] : '';
                $return['life_photo'] = !empty($return['life_photo']) ? $this->request->domain().$return['life_photo'] : '';
                $this->return_msg(200,'修改成功',$return);
            }

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
        //
    }
}
