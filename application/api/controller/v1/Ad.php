<?php

namespace app\api\controller\v1;

use app\api\controller\Common;
use think\Request;

/**
 * Class Adsite  广告位类
 * @package app\api\controller\v1
 */
class Ad extends Common
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        $where = [];
        $order = 'sort ASC';
        $sid = $this->request->get('sid', '');
        if (!empty($sid)){
            
           $ads = model('RelationSiteAd')->where('site_id',$sid)->column('ad_id');
           if(empty($ads)){
               $this->return_msg(0,'暂时没有数据');
           }else{
               $where['id'] = ['in',$ads];
               $order = '';
           }
        }
        $alias = $this->request->get('alias', '');
        if(!empty($alias)){
            $siteId = model('AdSite')->where('alias',$alias)->value('id');
            $ads = model('RelationSiteAd')->where('site_id',$siteId)->column('ad_id');
            if(empty($ads)){
                $this->return_msg(0,'暂时没有数据');
            }else{
                $where['id'] = ['in',$ads];
                $order = '';
            }
        }
        $status = $this->request->get('status', '');
        if ($status !== '') {
            $where['status'] = ['=', intval($status)];
            $order = '';
        }
        $limit = $this->request->get('limit/d', 20);
        $page = $this->request->get('page/d', 1);

        if ($this->scene == 'admin') {
            $lists = model('Ad')->where($where)->order($order)->page($page,$limit)->select()->toArray();
            foreach ($lists as $k => $v) {
                $sites = model('RelationSiteAd')->where('ad_id', $v['id'])->column('site_id');
                $v['sites'] = $sites;
                $lists[$k] = $v;
                $lists[$k]['pic'] = $this->request->domain().$v['pic'];
            }
        } else {
            $lists = model('Ad')->where($where)->where('status', 'eq', 1)->order($order)->page($page,$limit)->select()->toArray();
            foreach ($lists as $k => $v) {
                $sites = model('RelationSiteAd')->where('ad_id', $v['id'])->column('site_id');
                $v['sites'] = $sites;
                $lists[$k] = $v;
                $lists[$k]['pic'] = $this->request->domain().$v['pic'];
            }
        }
        
        $res = [];

        $res["list"] = $lists;
        $res["total"] = count($lists);
        if(empty($res['list'])){
            $this->return_msg(200,'暂时没有数据');
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

            if (empty($data['pic'])) {
                $this->return_msg('400', '图片地址不能为空');
            }

            if (empty($data['sites'])) {
                $this->return_msg('400', '所属广告位不能为空');
            }

            $data['status'] = isset($data['status']) ? $data['status'] : 1;

            $data['pic'] = str_replace($this->request->domain(), '', $data['pic']);

            $res = model('Ad')->allowField(true)->save($data);
            if ($res) {
                $temp = [];
                foreach ($data['sites'] as $key => $value){
                    $temp[$key]['site_id'] = $value;
                    $temp[$key]['ad_id'] = model('Ad')->id;
                }
                $result = model('RelationSiteAd')->saveAll($temp);
                if(empty($result)){
                    $this->return_msg(0,'添加失败');
                }else{
                    $data['id'] = model('Ad')->id;
                    /*********** 存入图片管理数据库  ***********/
                    $res = model('ImgManage')->save(['mod'=>'advert', 'url'=>$data['pic'],'create_time'=>time()]);
                    if($res){
                        $data['pic'] =  $this->request->domain().$data['pic'];
                        $this->return_msg(200,'添加成功',$data);
                    }else{
                        $this->return_msg(400,'添加图片管理失败',$data);
                    }
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
        if (strtolower($this->request->method()) == 'post') {

            $data = $this->request->post();

            if (empty($data['pic'])) {
                $this->return_msg('400', '图片地址不能为空');
            }

            if (empty($data['sites'])) {
                $this->return_msg('400', '所属广告位不能为空');
            }

            $old = model('Ad')->where('id', $id)->find()->toArray();

            $data['status'] = isset($data['status']) ? $data['status'] : 1;

            $data['pic'] = str_replace($this->request->domain(), '', $data['pic']);

            $res = model('Ad')->allowField(true)->isUpdate(true)->save($data);
            model('RelationSiteAd')->where('ad_id',$id)->delete();
            if ($res) {
                $temp = [];
                foreach ($data['sites'] as $key => $value){
                    $temp[$key]['site_id'] = $value;
                    $temp[$key]['ad_id'] = $id;
                }
                $result = model('RelationSiteAd')->saveAll($temp);
                if(empty($result)){
                    $this->return_msg(0,'修改失败');
                }else{
                    if($old['pic'] !== $data['pic']){
                        /*********** 存入图片管理数据库  ***********/
                        $r = model('ImgManage')->where(['mod'=>'advert', 'url'=>$old['pic']])->delete();
                        $res = model('ImgManage')->save(['mod'=>'advert', 'url'=>$data['pic'],'create_time'=>time()]);
                        if($r && $res){
                            $data['pic'] = $this->request->domain().$data['pic'];
                            $this->return_msg(200,'修改成功',$data);
                        }else{
                            $this->return_msg(400,'添加图片管理失败',$data);
                        }
                    }else{
                        $data['pic'] = $this->request->domain().$data['pic'];
                        $this->return_msg(200,'修改成功',$data);
                    }
                }
            } else {
                $this->return_msg(400, '添加失败');
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
            $old = model('Ad')->where('id',$id)->find()->toArray();
            $res = model('Ad')->where('id', $id)->delete();
            if($res){
                $result = model('RelationSiteAd')->where('ad_id',$id)->delete();
                if($result){
                    $re = model('ImgManage')->where(['mod'=>'advert', 'url'=>$old['pic']])->delete();
                    if($re){
                        $this->return_msg(200, '删除成功');
                    }else{
                        $this->return_msg(400, '管理图片删除成功');
                    }
                    
                }else{
                    $this->return_msg(400, '删除失败');
                }
            }
        }else{
            $this->return_msg(0, '请求方式不正确');
        }
    }
}
