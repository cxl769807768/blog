<?php

namespace app\api\controller\v1;

use app\api\controller\Common;
use think\Request;

class ShortVideo extends Common
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
            $order = $this->request->get('order', '');
            $order = empty($order) ? 'id DESC' : 'id ASC';

            $title = $this->request->get('title', '');
            if (!empty($title)) {
                $where['title'] = ['like', '%'. $title .'%'];
            }
            $is_special = $this->request->get('is_special', '');
            if ($is_special !== '') {
                $where['is_special'] = ['=', intval($is_special)];
                
            }
            $xid = $this->request->get('xid', '');
            if (!empty($xid)) {
                $where['xid'] = ['=', intval($xid)];
                
            }
            $uid = $this->request->get('uid', '');
            if (!empty($uid)) {
                $where['uid'] = ['=', intval($uid)];
                
            }
            $status = $this->request->get('status', '');
            if ($status !== '') {
                $where['status'] = ['=', intval($status)];
                
            }
            $state = $this->request->get('state', '');
            if ($state !== '') {
                $where['state'] = ['=', intval($state)];
                
            }
            $type = $this->request->get('type', '');
            if ($type !== '') {
                $where['type'] = ['=', intval($type)];
                
            }
            //获取我点赞的短视频
            $like = $this->request->get('like', '');
            if (!empty($like)) {
                $ids= model('Like')->where(['uid',$this->session['id'],'cmod'=>'shortVideo'])->column('xid');
                if(empty($ids)){
                    $this -> return_msg(200, '暂时没有数据');
                }else{
                    $where['id'] = ['in', $ids];
                }
            }
            //获取我评论的短视频
            $comment = $this->request->get('comment', '');
            if (!empty($comment)) {
                $ids= model('Comment')->where(['uid',$this->session['id'],'cmod'=>'shortVideo'])->column('xid');
                if(empty($ids)){
                    $this -> return_msg(200, '暂时没有数据');
                }else{
                    $where['id'] = ['in', $ids];
                }
            }
            $limit = $this->request->get('limit/d', 20);
            $page = $this->request->get('page/d', 1);
            
            $lists = model('ShortVideo')->where($where)->order($order)->limit($limit)->page($page)->select()->toArray();
            foreach ($lists as $key => $value) {
                
                $lists[$key]['video_url'] = !empty($value['video_url'])?$this->request->domain().$value['video_url'] : '';
                $lists[$key]['cover'] = !empty($value['cover'])?$this->request->domain().$value['cover'] : '';
                $lists[$key]['userInfo'] = model('User')->where('id',$value['uid'])->field('avatar,realname,phone')->find();
                $lists[$key]['userInfo']['phoneHide'] = substr_replace($lists[$key]['userInfo']['phone'],"****", 3,4);
                $lists[$key]['userInfo']['avatar'] = !empty($lists[$key]['userInfo']['avatar'])?$this->request->domain().$lists[$key]['userInfo']['avatar'] : '';
                $lists[$key]['commentNum'] =  model('Comment')->where(['xid'=>$value['id'],'cmod'=>'shortVideo'])->count();
                $lists[$key]['likeNum'] = model('Like')->where(['xid'=>$value['id'],'cmod'=>'shortVideo'])->count();
                $lists[$key]['shareNum'] = model('Share')->where(['xid'=>$value['id'],'cmod'=>'shortVideo'])->count();
                $isLike = model('Like')->where(['xid'=>$value['id'],'cmod'=>'shortVideo','uid'=>$this->session['id']])->count();
                $lists[$key]['isLike'] = !empty($isLike) ? 1 : 0;
                if($this->scene == 'admin'){
                    $lists[$key]['xid'] = model('Special')->where('id', $value['xid'])->value('title');
                    if (strlen($value['title']) > 30) {
                       $lists[$key]['title'] = mb_substr($value['title'], 0, 10).'...';
                    }
                }
            }
            $res = [];
            $res['total'] = model('ShortVideo')->where($where)->count();
            $res['list'] = $lists;
            if (empty($res['list'])) {
                $this -> return_msg(200, '暂时没有数据', $res);
            } else {
                $this -> return_msg(200, '获取成功', $res);
            }
        } else {
            $this -> return_msg(0, '请求方式不正确');
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
            // dump($data); exit;
            if($this->scene == 'app'){
                if(empty($data['uid'])){
                    $this->return_msg(0,'请先登录');
                }
            }
            if(empty($data['title'])){
                $this->return_msg(0,'标题不能为空');
            }
            if(empty($data['video_url'])){
                $this->return_msg(0,'地址不能为空');
            }
            if($data['is_special'] == 1){
                if(empty($data['xid'])){
                    $this->return_msg(0,'专题id不能为空');
                }
            }

            if ($data['cover'] !== 'http://api.ynyxlx.com') {
                $data['cover'] = str_replace($this->request->domain(), '', $data['cover']);
                $data['video_url'] = str_replace($this->request->domain(), '', $data['video_url']);
            } else {
                $data['cover'] = $data['video_url'];
                $data['video_url'] = '';
                $data['cover'] = str_replace($this->request->domain(), '', $data['cover']);
            }
            // dump($data); exit;
            $res = model('ShortVideo')->allowField(true)->save($data);
            if ($res) {
                if (!empty($data['video_url']) ) {
                    $icon= model('ImgManage')->save(['mod' => 'shortVideo', 'url' => $data['video_url']]);
                   
                    if ($icon) {
                        $this -> return_msg(200, '新增短视频成功');
                    } else {
                        $this -> return_msg(400, '新增短视频失败');
                    }
                } else {
                    $this -> return_msg(200, '新增短视频成功');
                }
                if (!empty($data['cover']) ) {
                    $cover= model('ImgManage')->save(['mod' => 'shortVideo', 'url' => $data['cover']]);
                   
                    if ($cover) {
                        $this -> return_msg(200, '新增短视频成功');
                    } else {
                        $this -> return_msg(400, '新增短视频失败');
                    }
                } else {
                    $this -> return_msg(200, '新增短视频成功');
                }
            } else {
                $this -> return_msg(400, '新增短视频失败');
            }
        } else {
            $this -> return_msg(0, '请求方式不正确');
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
        $list = model('ShortVideo')->where('id',$id)->find()->toArray();
        $list['video_url'] = $this->request->domain().$list['video_url'];
        $list['cover'] = $this->request->domain().$list['cover'];
        $list['userInfo'] = model('User')->where('id',$list['uid'])->field('avatar,realname,phone')->find()->toArray();

        $list['userInfo']['phoneHide'] = substr_replace($list['userInfo']['phone'],"****", 3,4);
        $list['userInfo']['avatar'] = !empty($list['userInfo']['avatar'])?$this->request->domain().$list['userInfo']['avatar'] : '';
        $isLike = model('Like')->where(['xid'=>$list['id'],'cmod'=>'shortVideo','uid'=>$this->session['id']])->count();
        $list['isLike'] = !empty($isLike) ? 1 : 0;

        $this -> return_msg(200, '获取成功',$list);
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

            $datas = $this->request->post();
            $data = $datas;

            $old = model('ShortVideo')->where('id', $data['id'])->find();

            if(!empty($data['video_url'])){
                foreach ($this->webroot as $key => $value) {
                    $data['video_url'] = str_replace($value, '', $data['video_url']);
                }
            }

            if(!empty($data['cover'])){
                foreach ($this->webroot as $key => $value) {
                    $data['cover'] = str_replace($value, '', $data['cover']);
                }
            }

            $res = model('ShortVideo')->allowField(true)->isUpdate(true)->save($data);
            if ($res) {
                if ($data['video_url'] !== $old['video_url'] && $data['cover'] !== $old['cover']) {
                    // 视频信息
                    $old_url = model('ImgManage')->where(['mod'=>'shortVideo', 'url'=>$old['video_url']])->find();
                    if (!empty($old_url)) {
                        model('ImgManage')->where(['mod'=>'shortVideo', 'url'=>$old['video_url']])->delete();
                    }
                    $url = model('ImgManage')->save(['mod'=>'shortVideo', 'url'=>$data['video_url'], 'create_time'=>time()]);
                    // 封面信息
                    $old_img = model('ImgManage')->where(['mod'=>'shortVideo', 'url'=>$old['cover']])->find();
                    if (!empty($old_img)) {
                        model('ImgManage')->where(['mod'=>'shortVideo', 'url'=>$old['cover']])->delete();
                    }
                    $img = model('ImgManage')->save(['mod'=>'shortVideo', 'url'=>$data['cover'], 'create_time'=>time()]);
                    if ($url && $img) {
                        $this -> return_msg(200, '更新图片信息管理库成功', $datas);
                    } else {
                        $this -> return_msg(400, '更新图片信息管理库失败', $datas);
                    }
                } else {
                    $this -> return_msg(200, '更新短视频信息成功', $datas);
                }
            } else {
                $this -> return_msg(400, '更新短视频信息失败');
            }
        } else {
            $this -> return_msg(0, '请求方式不正确');
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

            $data = $this->request->post();

            $old = model('ShortVideo')->where('id', $data['id'])->find()->toArray();
            $img = model('ImgManage')->where(['mod'=>'shortVideo', 'url'=>$old['cover']])->find();
            $video = model('ImgManage')->where(['mod'=>'shortVideo', 'url'=>$old['video_url']])->find();
            if (!empty($img)) {
                model('ImgManage')->where(['mod'=>'shortVideo', 'url'=>$old['cover']])->delete();
            }

            if (!empty($video)) {
                model('ImgManage')->where(['mod'=>'shortVideo', 'url'=>$old['video_url']])->delete();
            }

            $res = model('ShortVideo')->where('id', $data['id'])->delete();
            if ($res) {
                $this -> return_msg(200, '删除短视频信息成功');
            } else {
                $this -> return_msg(400, '删除短视频信息失败');
            }
        } else {
            $this -> return_msg(0, '请求方式不正确');
        }
    }
}
