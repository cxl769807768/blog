<?php

namespace app\api\controller\v1;

use app\api\controller\Common;
use think\Request;

class Article extends Common
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
            $order = "id DESC";
            $title = $this->request->get('title', '');
            if (!empty($title)) {
                $where['title'] = ['like', "%" . $title . "%"];
            }
            $mid = $this->request->get('mid', '');
            if ($mid !== '') {
                $where['mid'] = ['=', intval($mid)];
            }
            $state = $this->request->get('state', '');
            if ($state !== '') {
                $where['state'] = ['=', intval($state)];
            }
            $limit = $this->request->get('limit/d', 20);
            $page = $this->request->get('page/d', 1);
            $lists = Model('Article')->where($where) ->order($order)->limit($limit)->page($page)->select()->toArray(); 
            foreach ($lists as $key => $val){
                $lists[$key]['cover'] =  strpos($val['cover'],'/upload/')!==false ? $this->request->domain().$val['cover']:$this->request->domain().DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR.$val['cover'];
                $lists[$key]['url'] =  !empty($val['url']) ? $this->request->domain().$val['url']:'';
                $lists[$key]['commentNum'] =  model('Comment')->where(['cmod'=>'masterArticle','xid'=>$val['id']])->count();
            } 
            $res = [];
            $res['total'] = Model('Article')->where($where)->count();
            $res['list'] = $lists;
            
            if (empty($res['list'])) {
                $this -> return_msg(200, '暂时没有数据',$res);
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
            $datas = $this->request->post();
            $data = $datas;

            $data['cover'] =  str_replace($this->request->domain(), '', $data['cover']);

            if (!empty($data['url'])) {
                $data['url'] = str_replace($this->request->domain(), '', $data['url']);
            }

            $res = model('Article')->allowField(true)->save($data);
            if ($res) {
                $datas['id'] = model('Article')->id;
                $img = model('ImgManage')->save(['mod' => 'article', 'url' => $data['cover'], 'create_time' => time()]);
                if (!empty($data['url'])) {
                   model('ImgManage')->save(['mod' => 'editor', 'url' => $data['url'], 'create_time' => time()]);
                }
                if ($img) {
                    $this -> return_msg(200, '添加图片管理库成功', $datas);
                } else {
                    $this -> return_msg(400, '添加图片管理库失败', $datas);
                }
            } else {
                 $this -> return_msg(400, '添加文章信息失败');
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
        if (strtolower($this->request->method()) == 'get') {
    
            $lists = Model('Article')-> where('id ='.$id)->find()->toArray();
            foreach ($lists as $key => $val){
                if($key == 'cover'){
                    $lists['cover'] =  strpos($lists['cover'],'/upload/')!==false ? $this->request->domain().$lists['cover']:$this->request->domain().DIRECTORY_SEPARATOR."uploads".DIRECTORY_SEPARATOR.$lists['cover'];
                }
                if($key == 'url'){
                    $lists['url'] =  !empty($lists['url']) ? $this->request->domain().$lists['url']:'';
                }
                $lists['masterInfo'] =  Model('Master')-> where('id',$lists['mid'])->find()->toArray();
                $lists['commentNum'] = Model('Comment')-> where(['cmod'=>'masterArticle','xid'=>$id])->count();
                $lists['likeNum'] = Model('Like')-> where(['cmod'=>'article','xid'=>$id])->count();
                $is_like = Model('Like')-> where(['cmod'=>'article','xid'=>$id,'uid'=>$this->session['id']])->count();
                $lists['isLike'] = $is_like>0?1:0;
            }
            if (empty($lists)) {
                $this -> return_msg(0, '暂时没有数据');
            } else {
                $this -> return_msg(200, '获取成功', $lists);
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
            $datas = $this->request->post();
            $data = $datas;
            $old = model('Article')->where('id', $data['id'])->find()->toArray();

            if (!empty($data['url'])) {
                $data['url'] = str_replace($this->request->domain(), '', $data['url']);
            }

            $data['cover'] = str_replace($this->request->domain(), '', $data['cover']);

            $res = model('Article')->where('id', $data['id'])->update($data);
            if ($res) {
                if ($data['cover'] !== $old['cover'] && (!empty($data['url'] && ($data['url'] !== $old['url']))) ) {
                    $ress = model('ImgManage')->where(['mod' => 'article', 'url' => $old['cover']])->delete();
                    $resss = model('ImgManage')->save(['mod' => 'article', 'url' => $data['cover'], 'create_time' => time()]);
                    if (!empty($data['url'])) {
                        if ($data['url'] !== $old['url']) {
                            model('ImgManage')->where(['mod' => 'editor', 'url' => $old['url']])->delete();
                            model('ImgManage')->save(['mod' => 'editor', 'url' => $data['url'], 'create_time' => time()]);
                        }
                    }
                    if ($ress && $resss) {
                        $this -> return_msg(200, '更新图片管理库成功', $datas);
                    } else {
                        $this -> return_msg(400, '更新图片管理库失败', $datas);
                    }
                } else {
                    $this -> return_msg(200, '更新文章信息成功', $datas);
                }
            } else {
                $this -> return_msg(400, '更新文章信息失败');
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

            $old = model('Article')->where('id', $data['id'])->find()->toArray();

            $res = model('Article')->where('id', $data['id'])->delete();
            $ress = model('ImgManage')->where(['mod' => 'article', 'url' => $old['cover']])->delete();
            if (!empty($old['url'])) {
                model('ImgManage')->where(['mod' => 'editor', 'url' => $old['url']])->delete(); 
            }
            if ($res && $ress) {
                $this -> return_msg(200, '删除文章信息成功');
             } else {
                $this -> return_msg(400, '删除文章信息失败');
             }
        } else {
            $this -> return_msg(0, '请求方式不正确');
        }
    }
}
