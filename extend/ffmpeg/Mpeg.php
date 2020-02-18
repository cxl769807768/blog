<?php
namespace ffmpeg;

class Mpeg
{

    protected $file = '';

    public function __construct($file = '')
    {

        $this->file = $file;

    }

    //获得视频文件的总长度时间和创建时间
    public function getTime()
    {
        $vtime = exec("ffmpeg -i " . $this->file . " 2>&1 | grep 'Duration' | cut -d ' ' -f 4 | sed s/,//");//总长度
        $ctime = date("Y-m-d H:i:s", filectime($this->file));//创建时间
        $duration            = explode(":", $vtime);
        $duration_in_seconds = $duration[0]*3600+$duration[1]*60+round($duration[2]);//转化为秒

        return [
            'vtime' => "$duration_in_seconds",
            "time"  => $vtime,
            'ctime' => $ctime,
        ];
        
    }

    //获得视频文件的缩略图
    public function getVideoCover($type = "mood_video",$time)
    {
        if (empty($time)) $time = '1';//默认截取第一秒第一帧
        $videoCover = date('YmdHis') . createRandomCode(6);
        $videoCoverName = $videoCover . '.jpg';//缩略图命名
        /**linux**/
        $to = DIRECTORY_SEPARATOR.trim(ROOT_PATH,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR."public".DIRECTORY_SEPARATOR."upload".DIRECTORY_SEPARATOR."thumbs".DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.date('Ymd').DIRECTORY_SEPARATOR;
        /**windows**/
//        $to = ROOT_PATH."public".DIRECTORY_SEPARATOR."upload".DIRECTORY_SEPARATOR."thumbs".DIRECTORY_SEPARATOR.$type.DIRECTORY_SEPARATOR.date('Ymd').DIRECTORY_SEPARATOR;
        if (false === create_folders($to)) {
            return false;
        }
        exec("ffmpeg -ss {$time} -i " . $this->file . " -y -f image2 " . $to.$videoCoverName, $out, $status);
        if ($status == 1)  {
            $str = str_replace(ROOT_PATH."public", '', $to.$videoCoverName);
            return str_replace('\\', '/', $str);

        }elseif ($status == 0) return FALSE;
    }


    //裁剪视频
    public function CutterVideo($startTime,$endTime)
    {
        $video = date('YmdHis') . createRandomCode(6);
        $videoName = $video . '.mp4';//
        $duration  = intval($endTime-$startTime);
        $to = config('syc_images.thumb_tmp');
        if (false === create_folders($to)) {
            return false;
        }
//        $file = pathinfo($this->file)['dirname'].DIRECTORY_SEPARATOR.$videoName;
//        exec("ffmpeg -i $this->file -strict -2  -qscale 0 -intra $file");
        exec("ffmpeg -ss {$startTime} -t {$duration} -accurate_seek -i " . $this->file . " -c:v libx264 -c:a aac -strict experimental -b:a 98k ".$to.DIRECTORY_SEPARATOR.$videoName, $out, $status);

        if ($status == 0) return $to.DIRECTORY_SEPARATOR.$videoName;
        elseif ($status == 1) return FALSE;
    }
    //图片转成视频
    public function imgToVideo()
    {
        $video = date('YmdHis') . createRandomCode(6);
        $videoName = $video . '.mp4';//
        $to = config('syc_images.thumb_tmp');
        if (false === create_folders($to)) {
            return false;
        }
        exec("ffmpeg -loop 1 -f image2 -i {$this->file} -t 10 ".$to.DIRECTORY_SEPARATOR.$videoName, $out, $status);

        if ($status == 0) return $to.DIRECTORY_SEPARATOR.$videoName;
        elseif ($status == 1) return FALSE;
    }
    //视频合成
    public function compoundVideo($fileList){

        $video = date('YmdHis') . createRandomCode(6);
        $videoName = $video . '.mp4';//

        $to = config('syc_images.thumb_tmp');
        if (false === create_folders($to)) {
            return false;
        }
        $path = ROOT_PATH."public".trim($to, '.').DIRECTORY_SEPARATOR;

        $str = "concat:";
        foreach ($fileList as $key => $val){
            $k = $key+1;
            exec("ffmpeg -i {$val} -vcodec copy -acodec copy -vbsf h264_mp4toannexb {$path}input{$k}.ts");
            $str.="{$path}input{$k}.ts|";
        }
        $str = rtrim($str,"|");
//        exec("ffmpeg -i '$str' -c copy {$path}output.ts");
        exec("ffmpeg -i  '$str' -c copy -bsf:a aac_adtstoasc -movflags +faststart ".$to.DIRECTORY_SEPARATOR.$videoName, $out, $status);

        if ($status == 0) return $to.DIRECTORY_SEPARATOR.$videoName;
        elseif ($status == 1) return FALSE;
    }

}

