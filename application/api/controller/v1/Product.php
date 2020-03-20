<?php

namespace app\api\controller\v1;

use app\api\Controller\Common;
use think\Request;
use think\Loader;

class Product extends Common
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
            $tid = $this->request->get('tid', '');
            if ($tid !== '') {
                $where['tid'] = ['=', intval($tid)];

            }
            $name = $this->request->get('name', '');
            if (!empty($name)) {
                $where['name'] = ['like', "%" . $name . "%"];

            }
            if(!in_array('admin',$this->admin_roles) && $this->scene == "admin"){
                $where['role_id'] = $this->admin_roles[0];
            }
            $phone = $this->request->get('phone', '');
            if (!empty($phone)) {
                $where['phone'] = ['=', $phone];

            }

            $limit = $this->request->get('limit/d', 20);
            $page = $this->request->get('page/d', 1);
            $lists = model('product')->where($where)
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
            $res["total"] = model('product')->where($where)->count();
            $res["list"] = $lists;
            if (empty($res["list"])) {
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
            $validate = Loader::validate('product');
            if(!$validate->check($data)){
                $this->return_msg(0,$validate->getError());
            }
            if($this->scene == "admin"){
                $data['role_id'] = in_array('admin',$this->admin_roles) ? 1 : $this->admin_roles[0];
            }


            $data['cover'] = str_replace($this->request->domain(), '', $data['cover']);

            $temp = [];
            $data['slideshow'] = is_array($data['slideshow']) ? $data['slideshow'] : explode(',',$data['slideshow']);
            foreach ($data['slideshow'] as $key => $value) {
                $temp[$key]['url'] = str_replace($this->request->domain(), '', $value);
                $temp[$key]['mod'] = 'product_slideshow';
            }
            $data['slideshow'] = serialize(array_column($temp,'url'));
            $res = model('product')->allowField(true)->save($data);
            if ($res) {
                $insert_cover = model('ImgManage') -> save(['mod' => 'product_cover', 'url' => $data['cover']]);
                if (empty($insert_cover)) {
                    $this -> return_msg(0, '添加封面信息失败');
                }
                // 处理轮播上传的图片
                $insert_slideshow = model('ImgManage') ->isUpdate(false)->saveAll($temp);
                if (empty($insert_slideshow)) {
                    $this -> return_msg(0, '添加图片管理轮播信息失败');
                }
                if (!empty($data['introduce'])) {
                    $intr = [];
                    $pattern = '/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.jpeg|\.png]))[\'|\"].*?[\/]?>/i';
                    preg_match_all($pattern, $data['introduce'], $resssss);
                    if (!empty($resssss)) {
                        foreach ($resssss[1] as $key => $value) {
                            $intr[$key]['url'] = str_replace($this->request->domain(), '', $value);
                            $intr[$key]['mod'] = 'product_introduce';
                        }
                        $insert_introduce = model('ImgManage') ->isUpdate(false)->saveAll($intr);
                        if (empty($insert_introduce)) {
                            $this -> return_msg(0, '添加图片管理商家介绍中的图片信息失败');
                        }
                    }
                }
                if ($insert_cover && $insert_slideshow && $insert_introduce ) {
                    $this -> return_msg(200, '添加信息完成', $data);
                } else {
                    $this -> return_msg(400, '添加信息失败', $data);
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
        if (strtolower($this->request->method()) == 'get') {
            $list = Model('product')->where('id',$id)->find()->toArray();
            $list['cover'] = !empty($list['cover']) ? $this->request->domain().$list['cover'] : '';

            $list['slideshow'] = !empty($list['slideshow']) ? unserialize($list['slideshow']): '';
            if(!empty($list['slideshow'])){
                foreach ($list['slideshow'] as $key => $val) {
                    $list['slideshow'][$key] = $this->request->domain().$val;
                }
            }


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
            $validate = Loader::validate('product');
            if(!$validate->check($data)){
                $this->return_msg(0,$validate->getError());
            }
            $old = model('product')->where('id',$id)->find()->toArray();
            $data['cover'] = str_replace($this->request->domain(), '', $data['cover']);

            $temp = [];
            foreach ($data['slideshow'] as $key => $value) {
                $temp[$key]['url'] = str_replace($this->request->domain(), '', $value);
                $temp[$key]['mod'] = 'product_slideshow';
            }
            $data['slideshow'] = serialize(array_column($temp,'url'));
            $res = model('product')->isUpdate(true)->allowField(true)->save($data);
            if ($res) {
                if($old['cover']!==$data['cover']){
                    model('ImgManage')->where(['mod'=>'product_cover', 'url'=>$old['cover']])->delete();
                    $insert_cover = model('ImgManage') -> save(['mod' => 'product_cover', 'url' => $data['cover']]);
                    if (empty($insert_cover)) {
                        $this -> return_msg(0, '添加封面信息失败');
                    }
                }

                // 处理轮播上传的图片
                if($old['slideshow']!==$data['slideshow']){
                    $insert_slideshow = model('ImgManage') ->isUpdate(false)->saveAll($temp);
                    $oldSlideshow = unserialize($old['slideshow']);
                    foreach ($oldSlideshow as $key => $val){
                        model('ImgManage')->where(['mod'=>'product_slideshow', 'url'=>$val])->delete();
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
                                model('ImgManage')->where(['mod'=>'product_introduce', 'url'=>str_replace($this->request->domain(), '', $value)])->delete();
                            }
                            $intro = [];
                            foreach ($ress[1] as $key => $value) {  // 新增传递的信息
                                $intro[$key]['url'] = str_replace($this->request->domain(), '', $value);
                                $intro[$key]['mod'] = 'product_introduce';
                            }
                            model('ImgManage')->isUpdate(false)->saveAll($intro);
                        }
                    } else {  // 没有数据就添加传递的信息
                        $intro = [];
                        foreach ($ress[1] as $key => $value) {
                            $intro[$key]['url'] = $value;
                            $intro[$key]['mod'] = 'product_introduce';
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

            $old = model('product')->where('id', $id)->find()->toArray();
            $res = model('product')->where('id', $id)->delete();
            if($res){

                model('ImgManage')->where(['mod'=>'product_cover', 'url'=>$old["cover"]])->delete();
                $oldSlideshow = unserialize($old['slideshow']);
                foreach ($oldSlideshow as $key => $val){
                    model('ImgManage')->where(['mod'=>'product_slideshow', 'url'=>$val])->delete();
                }
                // 删除数据库中的介绍的图片信息
                if (!empty($old['introduce'])) {

                    $shuju_intr = '/<[img|IMG].*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.jpeg|\.png]))[\'|\"].*?[\/]?>/i';
                    preg_match_all($shuju_intr, $old['introduce'], $shu_ress);
                    if (!empty($shu_ress)) {
                        foreach ($shu_ress[1] as $key => $value) {

                            model('ImgManage')->where(['mod'=>'product_introduce', 'url'=>str_replace($this->request->domain(), '', $value)])->delete();
                        }
                    }

                }

                $this -> return_msg(200, '删除信息成功');

            }
        }else{
            $this->return_msg(0, '请求方式不正确');
        }
    }
    public function deleteImage(){
        if (strtolower($this->request->method()) == 'post') {
            $data = $this->request->post();
            $delUrl = str_replace($this->request->domain(), '', $data['url']);
            $del = unlink(PUBLIC_PATH.$delUrl);
            if(!empty($del)){
                $this -> return_msg(200, '删除图片成功');
            }else{
                $this -> return_msg(200, '删除图片失败');
            }

        }else{
            $this->return_msg(0, '请求方式不正确');
        }
    }
}
