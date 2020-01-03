<?php
    
    namespace app\api\controller\v1;
    
    use app\api\controller\Common;
    use getspell\GetSpell;
    use think\Request;
    
    class Admin extends Common
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
                $order = 'id DSC';
                $status = $this->request->get('status', '');
                if ($status !== '') {
                    $where['status'] = ['=', intval($status)];
                    $order = '';
                }
                $username = $this->request->get('username', '');
                if (!empty($username)) {
                    $where['username'] = ['like', "%" . $username . "%"];
                    $order = '';
                }
                $company_name = $this->request->get('company_name', '');
                if (!empty($company_name)) {
                    $where['company_name'] = ['like', "%" . $company_name . "%"];
                    $order = '';
                }
                $phone = $this->request->get('phone', '');
                if (!empty($phone)) {
                    $where['phone'] = ['=', $phone];
                    $order = '';
                }
                $email = $this->request->get('email', '');
                if (!empty($email)) {
                    $where['email'] = ['=', $email];
                    $order = '';
                }
                $role_id = $this->request->get('role_id/id', '');
                if ($role_id !== '') {
                    $admin_ids = model('AuthRoleAdmin')->where('role_id', $role_id)->column('admin_id');
                    $where['id'] = ['in', $admin_ids];
                    $order = '';
                }
                $role = $this->request->get('role', '');
                if ($role !== '') {
                    $admin_ids = model('AuthRoleAdmin')->where('role_id', $role)->column('admin_id');
                    $where['id'] = ['in', $admin_ids];
                    $order = '';
                }
                $limit = $this->request->get('limit/d', 20);
                $page = $this->request->get('page/d', 1);
                $lists = model('AuthAdmin')->where($where)
                    ->order($order)
                    ->page($page, $limit)->field("id,username,phone,email,avatar,create_time,status")->select()->toArray();
                foreach ($lists as $k => $v) {
                    $lists[$k]['avatar'] = !empty($v['avatar']) ? $this->request->domain().$v['avatar'] : '';
                    $roles = model('AuthRoleAdmin')->where('admin_id', $v['id'])->column('role_id');
                    $lists[$k]['roles'] = $roles;
                }
                $res = [];
                $res["total"] = model('AuthAdmin')->where($where)->count();
                $res["list"] = $lists;
                if (empty($res)) {
                    $this->return_msg(0, '暂时没有数据');
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
         * @param  \think\Request $request
         * @return \think\Response
         */
        public function save(Request $request)
        {
            if (strtolower($this->request->method()) == 'post') {
                $data = $this->request->post();
                if (empty($data['username'])) {
                    $this->return_msg('400', '用户名不能为空');
                }
                if (empty($data['password'])) {
                    $this->return_msg('400', '密码不能为空');
                }
                if (empty($data['checkPassword'])) {
                    $this->return_msg('400', '确认密码不能为空');
                }
                if ($data['password'] !==$data['checkPassword']) {
                    $this->return_msg('400', '密码与确认密码不一致');
                }
                if (empty($data['phone'])) {
                    $this->return_msg('400', '手机号不能为空');
                }
                if (empty($data['email'])) {
                    $this->return_msg('400', '邮箱不能为空');
                }
                if (empty($data['roles'])) {
                    $this->return_msg('400', '所属角色不能为空');
                }
                $data['avatar'] = str_replace($this->request->domain(), '', $data['avatar']);
                $checkUsername = $this->check_username_exist($data['username'], 0);
                if (!$checkUsername['status']) {
                    $this->return_msg('400', $checkUsername['info']);
                }
                $data['password'] = create_password($data['password']);
                $res = model('AuthAdmin')->allowField(true)->save($data);
                if ($res) {
                    
                    $data['id'] = model('AuthAdmin')->id;
                    $setRoleAdmin= model('AuthRoleAdmin')->setRoleAdminByRole($data['roles'],model('AuthAdmin')->id);
                    if(!empty($setRoleAdmin)){
                        $res = model('ImgManage')->save(['mod'=>'avatar', 'url'=>$data['avatar']]);
                        if($res){
                            $this->return_msg(200,'添加成功',$data);
                        }else{
                            $this->return_msg(400,'添加图片管理失败');
                        }
                    }else{
                        $this->return_msg(400,'添加admin与role关系失败');
                    }
                    
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
         * @param  int $id
         * @return \think\Response
         */
        public function read($id)
        {
            if (strtolower($this->request->method()) == 'get') {
                $list = Model('AuthAdmin')->where('id',$id)->find()->toArray();
                $list['avatar'] = !empty($list['avatar']) ? $this->request->domain().$list['avatar'] : '';
                if(empty($list)){
                    $this->return_msg(0, '暂时没有数据');
                }else{
                    $this->return_msg(200, '获取成功', $list);
                }
            } else {
                $this -> return_msg(0, '请求方式不正确');
            }
        }
        
        /**
         * 显示编辑资源表单页.
         *
         * @param  int $id
         * @return \think\Response
         */
        public function edit($id)
        {
        
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
            if (strtolower($this->request->method()) == 'post') {
                $data = $this->request->post();
                if (empty($data['username'])) {
                    $this->return_msg('400', '公司名称不能为空');
                }
                
                if(!empty($data['password'])){
                    if ($data['password'] !==$data['checkPassword']) {
                        $this->return_msg('400', '密码与确认密码不一致');
                    }
                }
                
                if (empty($data['phone'])) {
                    $this->return_msg('400', '手机号不能为空');
                }
                if (empty($data['roles'])) {
                    $this->return_msg('400', '所属角色不能为空');
                }
//                $getSpell = new GetSpell();
//                $data['username'] = $getSpell->getFirstPY($data['company_name']);
                $old = model('AuthAdmin')->where('id',$id)->find()->toArray();
                if($old['username']!==$data['username']){
                    $check = $this->check_username_exist($data['username'], 0);
                    if (!$check['status']) {
                        $this->return_msg('400', $check['info']);
                    }
                }
                $data['avatar'] = str_replace($this->request->domain(), '', $data['avatar']);
                
                if(empty($data['password'])){
                    unset($data['password']);
                }else{
                    $data['password'] = create_password($data['password']);
                }
                $res = model('AuthAdmin')->allowField(true)->isUpdate(true)->save($data);
                if ($res){
                    model('AuthRoleAdmin')->where('admin_id',$id)->delete();
                    $setRoleAdmin= model('AuthRoleAdmin')->setRoleAdminByRole($data['roles'],model('AuthAdmin')->id);
                    
                    if(!empty($setRoleAdmin)){
                        if($old['avatar']!==$data['avatar']){
                            /*********** 存入图片管理数据库  ***********/
                            $res = model('ImgManage')->save(['mod'=>'avatar', 'url'=>$data['avatar']]);
                            model('ImgManage')->where(['mod'=>'avatar', 'url'=>$old['avatar']])->delete();
                            if($res){
                                $this->return_msg(200,'更新成功',$data);
                            }else{
                                $this->return_msg(400,'添加图片管理失败');
                            }
                        }
                    }else{
                        $this->return_msg(400,'添加admin与role关系失败');
                    }
                    
                    $data["avatar"] = $this->request->domain().$data['avatar'];
                    $this->return_msg(200, '修改成功',$data);
                } else {
                    $this->return_msg(400, '更新失败');
                }
            } else {
                $this->return_msg(0, '请求方式不正确');
            }
        }
        
        /**
         * 删除指定资源
         *
         * @param  int $id
         * @return \think\Response
         */
        public function delete($id)
        {
            if (strtolower($this->request->method()) == 'post') {
                $data = $this->request->post();
                $old = model('AuthAdmin')->where('id', $data['id'])->find()->toArray();
                $res = model('AuthAdmin')->where('id', $data['id'])->delete();
                if($res){
                    $result = model('AuthRoleAdmin')->where('admin_id', $data['id'])->delete();
                    if($result){
                        model('ImgManage')->where(['mod'=>'avatar', 'url'=>$old['avatar']])->delete();
                        
                        $this->return_msg(200, '删除成功');
                        
                    }else{
                        $this->return_msg(0, '删除失败');
                    }
                }
            }else{
                $this->return_msg(0, '请求方式不正确');
            }
        }
    }
