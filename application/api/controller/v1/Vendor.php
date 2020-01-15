<?php

namespace app\api\controller\v1;

use app\api\Controller\Common;
use think\Request;

class Vendor extends Common
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
            $name = $this->request->get('name', '');
            if (!empty($name)) {
                $where['name'] = ['like', "%" . $name . "%"];
                $order = '';
            }

            $phone = $this->request->get('phone', '');
            if (!empty($phone)) {
                $where['phone'] = ['=', $phone];
                $order = '';
            }

            $limit = $this->request->get('limit/d', 20);
            $page = $this->request->get('page/d', 1);
            $lists = model('vendor')->where($where)
                ->order($order)
                ->page($page, $limit)->select()->toArray();
            foreach ($lists as $k => $v) {
                $lists[$k]['cover'] = !empty($v['cover']) ? $this->request->domain().$v['cover'] : '';
                $lists[$k]['slideshow'] = !empty($v['slideshow']) ? unserialize($v['slideshow']): '';
                if(!empty($lists[$k]['slideshow'])){
                    foreach ($lists[$k]['slideshow'] as $key => $val) {
                        $lists[$k]['slideshow'][$key] = $this->request->domain().$val;
                    }
                }

            }
            $res = [];
            $res["total"] = model('vendor')->where($where)->count();
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
     * @param  \think\Request  $request
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
