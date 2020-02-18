<?php

namespace app\api\controller\v1;

use app\api\controller\Common;
use app\common\model\Toppic as ToppicModel;
use think\Request;

class Toppic extends Common
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

            }
            $name = $this->request->get('name', '');
            if (!empty($name)) {
                $where['name'] = ['like', "%" . $name . "%"];

            }
            $isShow = $this->request->get('isShow', '');
            if ($isShow !== '') {
                $where['isShow'] = ['=', intval($isShow)];

            }

            $limit = $this->request->get('limit/d', 20);
            $page = $this->request->get('page/d', 1);
            $lists = ToppicModel::getLists($where,$order);
            $merge_list = ToppicModel::cateMerge($lists,'id','pid',0);
            $res['list'] = $merge_list;
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
     * [tree description]
     * @Author   xiaolong
     * @DateTime 2019-03-06T14:57:05+0800
     * @return   [type]     获取树形结构
     */
    public function tree()
    {
        $where = [];
        $order = 'id ASC';
        $lists = ToppicModel::getLists($where,$order);
        $tree_list = ToppicModel::cateTree($lists,'id','pid',0);
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

        $name = strtolower(strip_tags($data['name']));
        // 菜单模型
        $info = ToppicModel::where('name',$name)->field('name')->find();
        if ($info){
            $this->return_msg(0, "栏目已经存在");
        }
        $status = !empty($data['status']) ? $data['status'] : 1;
        $pid = !empty($data['pid']) ? $data['pid'] : 0;
        if ($pid){
            $info = ToppicModel::where('id',$pid)->field('id')->find();
            if (!$info){
                $this->return_msg(0, "父级不存在");
            }
            $data['fid'] = !empty($info['fid']) ?  $info['fid']: $info['id'];
        }

        $res = model('Toppic')->allowField(true)->save($data);
        if($res){
            $data['id'] = model('Toppic')->id;
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
        if (empty($data['name'])){
            $this->return_msg(0,'name不能为空');
        }
        $id = $data['id'];
        $name = strtolower(strip_tags($data['name']));
        $idInfo = ToppicModel::where('name',$name)
            ->field('id')
            ->find();
        // 判断名称 是否重名，剔除自己
        if (!empty($idInfo['id']) && $idInfo['id'] != $id){
            $this->return_msg(0, "权限名称已存在");
        }
        $pid = isset($data['pid']) ? $data['pid'] : 0;
        // 判断父级是否存在
        if ($pid){
            $info = ToppicModel::where('id',$pid)->field('id')->find();
            if (!$info){
                $this->return_msg(0, "父级不存在");
            }
            $data['fid'] = !empty($info['fid']) ?  $info['fid']: $info['id'];
        }
        $AuthRuleList = ToppicModel::all();
        // 查找当前选择的父级的所有上级
        $parents = ToppicModel::queryParentAll($AuthRuleList,'id','pid',$pid);
        if (in_array($id,$parents)){
            $this->return_msg(0, "不能把自身/子级作为父级");
        }
        $status = isset($data['status']) ? $data['status'] : 1;

        $res = model('Toppic')->allowField(true)->isUpdate(true)->save($data);
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
        $sub = model('Toppic')->where('pid',$id)->field('id')->find();
        if ($sub){
            $this->return_msg(0,'有子节点不能删除');
        }
        $res = model('Toppic')->where('id',$id)->delete();
        if (!$res){
            $this->return_msg(0,'删除失败');
        }else{

            $this->return_msg(200,'删除成功');
        }
    }
}
