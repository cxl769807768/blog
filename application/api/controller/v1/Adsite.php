<?php

namespace app\api\controller\v1;

use app\api\controller\Common;
use think\Request;

/**
 * Class Adsite  广告位类
 * @package app\api\controller\v1
 */
class Adsite extends Common
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
        $name = $this->request->get('name', '');
        if (!empty($name)){
            $where['name'] = ['like','%'.$name. '%'];
            $order = '';
        }

        $limit = $this->request->get('limit/d', 20);
        $page = $this->request->get('page/d', 1);

        $lists = model('AdSite')->where($where)->order($order)->page($page,$limit)->select()->toArray();

        $res = [];
        $res["total"] = model('AdSite')->where($where)->count();
        $res["list"] = $lists;
        
        if(empty($res["list"])){
            $this->return_msg(400,'暂时没有数据',$res);
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
            if (empty($data['name']) || empty($data['alias'])) {
                $this->return_msg('400', '名称和别名不能为空');
            }
            $res = model('AdSite')->allowField(true)->save($data);
            if ($res) {
                $data['id'] = model('AdSite')->id;
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
        if (strtolower($this->request->method()) == 'post') {
            $data = $this->request->post();
            if (empty($data['name'])) {
                $this->return_msg('400', '名称不能为空');
            }
            $res = model('AdSite')->allowField(true)->isUpdate(true)->save($data);
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
            $res = model('AdSite')->where('id', $id)->delete();
            if($res){
                $this->return_msg(200, '删除成功');
            
            }else{
                $this->return_msg(0, '删除失败');
            }
        }else{
            $this->return_msg(0, '请求方式不正确');
        }
    }
}
