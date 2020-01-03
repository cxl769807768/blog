<?php

namespace app\api\controller\v1;

use app\api\controller\Common;
use app\common\model\AuthRule as AuthRuleModel;
use app\common\model\AuthRoleRule;
use think\Request;

class AuthRule extends Common
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
        if ($status !== ''){
            $where[] = ['status','=',intval($status)];
            $order = '';
        }
        $name = $this->request->get('name', '');
        if (!empty($name)){
            $where[] = ['name','like',$name . '%'];
            $order = '';
        }
        $lists = AuthRuleModel::getLists($where,$order);
        $merge_list = AuthRuleModel::cateMerge($lists,'id','pid',0);
        $res['list'] = $merge_list;

        if(empty($res['list'])){
            $this->return_msg(400,'没有数据');
        }else{
            $this->return_msg(200,'获取成功',$res);
        }
        

    }
    /**
     * [tree description]
     * @Author   xiaolong
     * @DateTime 2019-03-06T14:57:05+0800
     * @return   [type]     获取树形结构
     */
    public function tree()
    {
        $where = [];
        $order = 'id ASC';
        $lists = AuthRuleModel::getLists($where,$order);
        $tree_list = AuthRuleModel::cateTree($lists,'id','pid',0);
        $res = [];
        $res['list'] = $tree_list;
        if(empty($res['list'])){
            $this->return_msg(400,'没有数据');
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
        $data = $this->request->post();
        if (empty($data['name'])){
            $this->return_msg(0,'name不能为空');
        }
        if (empty($data['title'])){
            $this->return_msg(0,'title不能为空');
        }
        $name = strtolower(strip_tags($data['name']));
        // 菜单模型
        $info = AuthRuleModel::where('name',$name)->field('name')->find();
        if ($info){
            $this->return_msg(0, "权限已经存在");
        }
        $status = !empty($data['status']) ? $data['status'] : 1;
        $pid = !empty($data['pid']) ? $data['pid'] : 0;
        if ($pid){
            $info = AuthRuleModel::where('id',$pid)->field('id')->find();
            if (!$info){
                $this->return_msg(0, "父级菜单不存在");
            }
        }
        
        $res = model('AuthRule')->allowField(true)->save($data);
        if($res){
            $data['id'] = model('AuthRule')->id;
            $this->return_msg(200,'添加成功',$data);
        }else{
            $this->return_msg(400,'添加失败');
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
     * 保存更新的资源
     *
     * @param  \think\Request  $request
     * @param  int  $id
     * @return \think\Response
     */
    public function update(Request $request, $id)
    {
        $data = $this->request->post();
        if (empty($data['name'])){
            $this->return_msg(0,'name不能为空');
        }
        if (empty($data['title'])){
            $this->return_msg(0,'title不能为空');
        }
        $id = $data['id'];
        $name = strtolower(strip_tags($data['name']));
        $idInfo = AuthRuleModel::where('name',$name)
            ->field('id')
            ->find();
        // 判断名称 是否重名，剔除自己
        if (!empty($idInfo['id']) && $idInfo['id'] != $id){
            $this->return_msg(0, "权限名称已存在");
        }
        $pid = isset($data['pid']) ? $data['pid'] : 0;
        // 判断父级是否存在
        if ($pid){
            if ($pid){
                $info = AuthRuleModel::where('id',$pid)->field('id')->find();
                if (!$info){
                    $this->return_msg(0, "父级菜单不存在");
                }
            }
        
        }
        $AuthRuleList = AuthRuleModel::all();
        // 查找当前选择的父级的所有上级
        $parents = AuthRuleModel::queryParentAll($AuthRuleList,'id','pid',$pid);
        if (in_array($id,$parents)){
            $this->return_msg(0, "不能把自身/子级作为父级");
        }
        $status = isset($data['status']) ? $data['status'] : 1;

        $res = model('AuthRule')->allowField(true)->isUpdate(true)->save($data);
        if($res){
            $this->return_msg(200,'修改成功');
        }else{
            $this->return_msg(400,'修改失败');
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
        if (empty($id)){
            $this->return_msg(0,'id不能为空');
        }
        // 下面有子节点，不能删除
        $sub = model('AuthRule')->where('pid',$id)->field('id')->find();
        if ($sub){
            $this->return_msg(0,'有子节点不能删除');
        }
        $res = model('AuthRule')->where('id',$id)->delete();
        if (!$res){
            $this->return_msg(0,'删除失败');
        }else{
            // 删除授权的权限
            AuthRoleRule::where('rule_id',$id)->delete();
            $this->return_msg(200,'删除成功');
        }
    }
}
