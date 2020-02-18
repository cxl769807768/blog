<?php

namespace app\api\controller\v1;

use app\api\Controller\Common;
use ffmpeg\Mpeg;
use think\Request;
use think\Loader;

class Mood extends Common
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
            $school = $this->request->get('school', '');
            if (!empty($school)) {
                $where['school'] = ['like', "%" . $school . "%"];

            }
            $class = $this->request->get('class', '');
            if (!empty($class)) {
                $where['class'] = ['like', "%" . $class . "%"];

            }
            $type = $this->request->get('type', '');
            if (!empty($type)) {
                $where['type'] = ['=', $type];
            }
            $limit = $this->request->get('limit/d', 20);
            $page = $this->request->get('page/d', 1);
            $lists = model('Mood')->where($where)
                ->order($order)
                ->page($page, $limit)->select()->toArray();
            foreach ($lists as $k => $v) {
                if($v["type"]==1){
                    $lists[$k]['cover'] = !empty($v['cover']) ? $this->request->domain().$v['cover'] : '';
                    $lists[$k]['slideshow'] = !empty($v['slideshow']) ? unserialize($v['slideshow']): '';
                    if(!empty($lists[$k]['slideshow'])){
                        foreach ($lists[$k]['slideshow'] as $key => $val) {
                            $lists[$k]['slideshow'][$key] = $this->request->domain().$val;
                        }
                    }
                }elseif($v["type"]==2){
                    $lists[$k]['cover'] = !empty($v['cover']) ? $this->request->domain().$v['cover'] : '';
                    $lists[$k]['video'] = !empty($v['video']) ? $this->request->domain().$v['video'] : '';
                }


            }
            $res = [];
            $res["total"] = model('Mood')->where($where)->count();
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
            $validate = Loader::validate('mood');

            if(!$validate->check($data)){
                $this->return_msg(0,$validate->getError());
            }
            if($data['type']==1){
                $data['cover'] = str_replace($this->request->domain(), '', $data['cover']);
                $temp = [];
                foreach ($data['slideshow'] as $key => $value) {
                    $temp[$key]['url'] = str_replace($this->request->domain(), '', $value);
                    $temp[$key]['mod'] = 'mood_slideshow';
                }
                $data['slideshow'] = serialize(array_column($temp,'url'));
            }elseif($data['type']==2){
                $data['video'] = str_replace($this->request->domain(), '', $data['video']);
                $data['cover'] = str_replace($this->request->domain(), '', $data['cover']);
            }

            $res = model('Mood')->allowField(true)->save($data);
            if ($res) {
                if($data['type']==1) {
                    $insert_cover = model('ImgManage')->save(['mod' => 'mood_cover', 'url' => $data['cover']]);
                    if (empty($insert_cover)) {
                        $this->return_msg(0, '添加封面信息失败');
                    }
                    // 处理轮播上传的图片
                    $insert_slideshow = model('ImgManage')->isUpdate(false)->saveAll($temp);
                    if (empty($insert_slideshow)) {
                        $this->return_msg(0, '添加图片管理轮播信息失败');
                    }
                }elseif($data['type']==2) {
                    $insert_cover = model('ImgManage')->save(['mod' => 'mood_video', 'url' => $data['cover']]);
                    $insert_video = model('ImgManage')->save(['mod' => 'mood_video', 'url' => $data['video']]);

                    if (empty($insert_cover)) {
                        $this->return_msg(0, '添加视频封面信息失败');
                    }
                    if (empty($insert_video)) {
                        $this->return_msg(0, '添加视频信息失败');
                    }
                }

                $this -> return_msg(200, '添加信息完成', $data);


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
            $validate = Loader::validate('Vendor');
            if(!$validate->check($data)){
                $this->return_msg(0,$validate->getError());
            }
            $old = model('Vendor')->where('id',$id)->find()->toArray();
            $data['cover'] = str_replace($this->request->domain(), '', $data['cover']);

            $temp = [];
            foreach ($data['slideshow'] as $key => $value) {
                $temp[$key]['url'] = str_replace($this->request->domain(), '', $value);
                $temp[$key]['mod'] = 'vendor_slideshow';
            }
            $data['slideshow'] = serialize(array_column($temp,'url'));
            $res = model('Vendor')->isUpdate(true)->allowField(true)->save($data);
            if ($res) {
                if($old['cover']!==$data['cover']){
                    model('ImgManage')->where(['mod'=>'vendor_cover', 'url'=>$old['cover']])->delete();
                    $insert_cover = model('ImgManage') -> save(['mod' => 'vendor_cover', 'url' => $data['cover']]);
                    if (empty($insert_cover)) {
                        $this -> return_msg(0, '添加封面信息失败');
                    }
                }

                // 处理轮播上传的图片
                if($old['slideshow']!==$data['slideshow']){
                    $insert_slideshow = model('ImgManage') ->isUpdate(false)->saveAll($temp);
                    $oldSlideshow = unserialize($old['slideshow']);
                    foreach ($oldSlideshow as $key => $val){
                        model('ImgManage')->where(['mod'=>'vendor_slideshow', 'url'=>$val])->delete();
                    }
                    if (empty($insert_slideshow)) {
                        $this -> return_msg(0, '添加图片管理轮播信息失败');
                    }
                }
                // 介绍信息
                $intr = '/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.jpeg|\.png]))[\'|\"].*?[\/]?>/i';
                preg_match_all($intr, $data['introduce'], $ress);
                if (!empty($ress)) {
                    // 判断数据库介绍的图片信息
                    $shuju_intr = '/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.jpeg|\.png]))[\'|\"].*?[\/]?>/i';
                    preg_match_all($shuju_intr, $old['introduce'], $shu_ress);
                    if (!empty($shu_ress)) {
                        // 判断介绍中的图片是否一致
                        $diff = array_diff($ress[1],$shu_ress[1]);
                        if (!empty($diff)) {

                            foreach ($shu_ress[1] as $key => $value) {
                                model('ImgManage')->where(['mod'=>'vendor_introduce', 'url'=>str_replace($this->request->domain(), '', $value)])->delete();
                            }
                            $intro = [];
                            foreach ($ress[1] as $key => $value) {  // 新增传递的信息
                                $intro[$key]['url'] = str_replace($this->request->domain(), '', $value);
                                $intro[$key]['mod'] = 'vendor_introduce';
                            }
                            model('ImgManage')->isUpdate(false)->saveAll($intro);
                        }
                    } else {  // 没有数据就添加传递的信息
                        $intro = [];
                        foreach ($ress[1] as $key => $value) {
                            $intro[$key]['url'] = $value;
                            $intro[$key]['mod'] = 'vendor_introduce';
                        }
                        model('ImgManage') ->isUpdate(false)->saveAll($intro);
                    }
                }

                $this -> return_msg(200, '更新信息完成', $data);

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
     * @param  int  $id
     * @return \think\Response
     */
    public function delete($id)
    {
        if (strtolower($this->request->method()) == 'post') {

            $old = model('Mood')->where('id', $id)->find()->toArray();
            $res = model('Mood')->where('id', $id)->delete();
            if($res){
                if($old['type']==1){
                    model('ImgManage')->where(['mod'=>'mood_cover', 'url'=>$old["cover"]])->delete();
                    $oldSlideshow = unserialize($old['slideshow']);
                    foreach ($oldSlideshow as $key => $val){
                        model('ImgManage')->where(['mod'=>'mood_slideshow', 'url'=>$val])->delete();
                    }
                }elseif($old['type']==2){
                    model('ImgManage')->where(['mod'=>'mood_video', 'url'=>$old["cover"]])->delete();
                    model('ImgManage')->where(['mod'=>'mood_video', 'url'=>$old["video"]])->delete();
                }
                $this -> return_msg(200, '删除信息成功');

            }
        }else{
            $this->return_msg(0, '请求方式不正确');
        }
    }
}
