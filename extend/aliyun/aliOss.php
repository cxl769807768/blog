<?php
namespace aliyun;

include_once EXTEND_PATH.'aliyun-openapi-php-sdk'.DIRECTORY_SEPARATOR.'aliyun-php-sdk-core'.DIRECTORY_SEPARATOR.'Config.php';
\Autoloader::addAutoloadPath('aliyun-php-sdk-vod');


use vod\Request\V20170321 as vod;
use OSS\OssClient;
use OSS\Core\OssException;
class aliOss {
    protected $params = array();
    protected $Bucket = '';
    protected $FileName = '';
    protected $videoId = '';
    private  static $client;
    private static $regionId = 'cn-shanghai';


    public function __construct($params=array())
    {
        $this->params = $params;


            $access_key_id     = config('alivod.access_key_id');
            $access_key_secret = config('alivod.access_key_secret');
            $profile = \DefaultProfile::getProfile(self::$regionId, $access_key_id, $access_key_secret);
            self::$client = new \DefaultAcsClient($profile);

    }
//stdClass Object
//(
//[UploadAddress] => eyJFbmRwb2ludCI6Imh0dHBzOi8vb3NzLWNuLXNoYW5naGFpLmFsaXl1bmNzLmNvbSIsIkJ1Y2tldCI6ImluLTZmZmE2MzM4NWU4ZDExZThhODdiMDAxNjNlMWMzNWQ1IiwiRmlsZU5hbWUiOiJjdXN0b21lclRyYW5zLzQ0OGUxN2Q4ZmVlYWU0OWYzMGViMzFhNGNkZDY1NmZiLzE2ODY5NGE3LTE2M2YzYWY1NTg2LTAwMDAtMDAwMC0wMTMtYzlkYWIubW92In0=
//[RequestId] => 25370F3C-05DB-4D0C-9F84-1DC931079114
//[VideoId] => 199813ca90ed48279ac049fe292ab5df
//[UploadAuth] => eyJTZWN1cml0eVRva2VuIjoiQ0FJUzBBUjFxNkZ0NUIyeWZTaklyNG5XZk1yQ3Y0MVU0SlNDWTA3a2dsTWlQdHdacm92OHNUejJJSGhKZVhOdkJPMGV0ZjQrbVdCWTdQY1lscjBwRThBWkZCT2ZQSTV0OXBCUStBK2RaSW5NdlpSdERVM3pYOW4zZDFLSUFqdlhnZVVvQ0llUUZhRTVFWlhBUWxUQWtUQUpDdEdZRVJ5cFExMmlON0NRbEpkamRhNTVkd0trYkQxQWRqVThSRzVsczlSSURXYk5Fdnl2UHhYMjRBelhGMUU2Z2cxbmxVUjE2Nm0wM3EvazdRSEYzblRpMHVJVHE2UDdJSld2YzZzS080eGtBZmU0MWVCUmZLak0yekl5a2g5UjcvVlpnYkJqOHpYS3RjMjREa1ZZZ1dhQktQR0cwOXR1TUE1d2VxVWZCcXBZcmVEMWorRkZvdWpVbm9pVnNCRldKck53VGlERFJaaXAydGY1QU9ldVA5cGJFN0hnSUNid3l0U0lQNVhOdWhrNEF3Z2NMeGdZUnNJbExYWjhNUlUyVmhyZE1yN0ZpMWZSZVZXSVRLMmQxS3dxM0w5b3kwbnA1ZE9RTzJXWFI3S1EzVVJvUFlRblBXb2lNQVFLK0hIbGJxNGVlaFpRVGt0akFMK1pML1YwZHdzTWphTHpvU2pQVWpGaDFuaE5vOERtWS9UZnRydDlESUxrUmNCaTBKRUJYSjFjcjNCUWVqYVJjYisxalZvT2YyRklXS3RmMUxLWFdhV3k4N2lZMnU2ZUU4ZWtYZmtNb1FkZGFpdUQ3WGpQRzNKV2FIUC82TlI5TWdISXBzbUJ3UFdWL3NOcFFGUWt2b2dHVTFxSUtOMDI4bEE3cEtDdjlBaVU5ZUw1Ym1xaitIRi91UHVncHRVUXN4UThJNjM3MmJiQzVtNlA0a2I5Ty9kcHhKM2xQMFIwV2dteWRuQkR4L1NmdTJrS3ZSaHBrUnZ2WTB0Q3NRdk1pRDdySnB4R2dxelJseWxlZm81WG1QWEZUUW1uOGw1cEFNbXkvNjB4WHVkdmJDakgxMHA2V0tjREdvQUJUME53eFZmZlJrWE1Udk14aTI2OW82c0ZvOUxoQ01vVFBmOEdRWWRqNmhlT2Y3WHlTcFRWRllwMnBIVWI5cU9zRkpmNGVwNW9ZOCtZWE0ybVdwcnFObzFHcXN6K1VNQzNSb3hYZk1PQi9LOVVOUURNTCtFS05zdWRoczZFRWNPUWRqc2xNK2NtUVl5SEVXVnpBNmdoRkU5ODVLRjBUT0s1Y1pOZ3NQNGhVL1E9IiwiQWNjZXNzS2V5SWQiOiJTVFMuTkpjN3B2UlJ1V1ZpYWhVZlN3MlM1QU5XUyIsIkFjY2Vzc0tleVNlY3JldCI6IjZCd1I3Z1FjNEdOUUo4ZG5rUlNpYVBTbTZNN2hXS2d0a3JSSDFMNDUycHBFIiwiRXhwaXJhdGlvbiI6IjM2MDAifQ==
//)
    public function create_upload_video() {
        try{

            $request = new vod\CreateUploadVideoRequest();
            $request->setTitle($this->params['title']);        // 视频标题(必填参数)
            $request->setFileName($this->params['filename']); // 视频源文件名称，必须包含扩展名(必填参数)
            $request->setDescription($this->params['message']);  // 视频源文件描述(可选)
            $response = self::$client->getAcsResponse($request);
            $arr = array();
            $arr['UploadAddress'] = json_decode(base64_decode($response->UploadAddress),true);
            $arr['UploadAuth'] = json_decode(base64_decode($response->UploadAuth),true);
            $arr['VideoId'] = $response->VideoId;
            return $arr;

        }catch (Exception $e) {

            return info("Failed:create_upload_video".$e->getMessage(),0);
        }

    }

    public function init_oss_client() {
        try{
            $result = $this->create_upload_video();

            $uploadAuth = $result['UploadAuth'];
            $uploadAddress = $result['UploadAddress'];
            $this->videoId = $result['VideoId'];
            $ossClient = new OssClient($uploadAuth['AccessKeyId'], $uploadAuth['AccessKeySecret'], $uploadAddress['Endpoint'], false, $uploadAuth['SecurityToken']);
            $ossClient->setTimeout(86400*7);    // 设置请求超时时间，单位秒，默认是5184000秒, 建议不要设置太小，如果上传文件很大，消耗的时间会比较长
            $ossClient->setConnectTimeout(10);  // 设置连接超时时间，单位秒，默认是10秒
            $this->Bucket = $result['UploadAddress']['Bucket'];
            $this->FileName = $result['UploadAddress']['FileName'];
            return $ossClient;
        }catch (Exception $e) {

            return info("Failed:init_oss_client".$e->getMessage(),0);
        }

    }
    public function upload_local_file() {
        try{
            $ossClient = $this-> init_oss_client();

            $result = $ossClient->uploadFile($this->Bucket, $this->FileName, $this->params['localfile']);
            $result['videoId'] = $this->videoId;
            return $result;
        }catch (Exception $e) {

            return info("Failed:upload_local_file".$e->getMessage(),0);
        }

    }
    public function refresh_upload_video() {

        try{

            $result = $this->create_upload_video();
            $videoId = $result['VideoId'];
            $request = new vod\RefreshUploadVideoRequest();
            $request->setVideoId($videoId);
            return self::$client->getAcsResponse($request);
        }catch (Exception $e) {

            return info("Failed:refresh_upload_video".$e->getMessage(),0);
        }
    }
}

