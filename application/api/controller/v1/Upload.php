<?php

namespace app\api\controller\v1;

use app\api\controller\Common;
use think\Request;
use ffmpeg\Mpeg;

class Upload extends Common
{
    /**
     * 显示资源列表
     *
     * @return \think\Response
     */
    public function index()
    {
        //
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
        $file = $this->request->file('upload');
        $type = $this->request->post('type');
        if(empty($file)){
            return $this->return_msg(400,'参数不能为空');
        }else {
            if(in_array($type, array('shortVideo'))){
                $video_path = $this->upload_file($file, $type);
                $file = ROOT_PATH."public".DIRECTORY_SEPARATOR.trim($video_path,DIRECTORY_SEPARATOR);

                $cover_path = $this->getCoverFromVedio($file);
                $return = array(
                    'video'=> $this->request->domain().$video_path,
                    'cover'=> $this->request->domain().$cover_path,
                );  
                $this->return_msg(200, '上传成功', $return);
            }else{
                $img_path = $this->upload_file($file, $type);
                $this->return_msg(200, '上传成功', $this->request->domain().$img_path);
            }
            
            
        }
    }
    /**
     * @param $file
     * @param string $type
     * @return mixed
     */
    public function upload_file($file, $type = 'avatar') {
        
        $filePath = ROOT_PATH."public".DIRECTORY_SEPARATOR."upload".DIRECTORY_SEPARATOR."thumbs".DIRECTORY_SEPARATOR.$type;
        if (false === create_folders($filePath)) {
            return false;
        }
        
        $result =  $file->move($filePath);
        if($result){
            $newFile = $filePath.DIRECTORY_SEPARATOR.$result->getSaveName();
            /*********** 裁剪图片  ***********/
            if ($type=='avatar') {
                $this->image_edit($newFile, $type);
            }
            return str_replace('\\', '/', DIRECTORY_SEPARATOR."upload".DIRECTORY_SEPARATOR."thumbs".DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.$result->getSaveName());
        }else{
            return $this->return_msg(400, $file->getError());
            
        }
    }
    
    /**
     * @param $path
     * @param $type
     */
    public function image_edit($path, $type) {
        $image = \think\Image::open($path);
        switch ($type) {
            case 'avatar':
                $image->thumb(100, 100, \think\Image::THUMB_CENTER)->save($path);
                break;
            case 'icon':
                $image->thumb(100, 100, \think\Image::THUMB_CENTER)->save($path);
                break;
            case 'activity':
                $image->thumb(100, 100, \think\Image::THUMB_CENTER)->save($path);
                break;
            case 'master':
                $image->thumb(100, 100, \think\Image::THUMB_CENTER)->save($path);
                break;

        }
    }
    /**
     * [getCoverFromVedio description]
     * @Author   xiaolong
     * @DateTime 2019-04-22T17:33:41+0800
     * @param    [type]                   $file [description]
     * @return   [type]                   截取视频获取封面
     */
    public function getCoverFromVedio($file){
        $mpeg = new Mpeg($file);
        return $mpeg-> getVideoCover($type = "shortVideo",1);
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
