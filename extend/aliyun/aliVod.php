<?php
namespace aliyun;
/**
 * @Author: 何超
 * @Date:   2018-07-04 13:04:38
 * @Email:  chaodoing@live.com
 * @Create Time:  2018-07-04 13:04:38
 * @File Name:    aliVod.php
 * @Project Name: front
 * @Last Modified by:   Super
 * @Last Modified time: 2018-07-12 15:11:47
 */
include_once EXTEND_PATH.'aliyun-openapi-php-sdk'.DIRECTORY_SEPARATOR.'aliyun-php-sdk-core'.DIRECTORY_SEPARATOR.'Config.php';
\Autoloader::addAutoloadPath('aliyun-php-sdk-vod');
use vod\Request\V20170321 as VODSDK;

class aliVod {
	private static $client;
	private static $regionId = 'cn-hangzhou';
	public function __construct() {
		$access_key_id     = config('alivod.access_key_id');
		$access_key_secret = config('alivod.access_key_secret');
		$profile           = \DefaultProfile::getProfile(self::$regionId, $access_key_id, $access_key_secret);
		self::$client      = new \DefaultAcsClient($profile);
	}
	public function getVideoPlayAuth($videoID = '') {
		try {
			$request = new VODSDK\GetVideoPlayAuthRequest();
			$request->setAcceptFormat('JSON');
			$request->setRegionId(self::$regionId);
			$request->setVideoId($videoID);
			$response = self::$client->getAcsResponse($request);
			return $response;
		} catch (Exception $e) {
			return info($e->getMessage(), 0);
		}
	}
	public function getPlayInfo($videoId) {
		$request = new VODSDK\GetPlayInfoRequest();
		$request->setResultType('Single');
		$request->setVideoId($videoId);
		$request->setAuthTimeout(3600*24);// 播放地址过期时间（只有开启了URL鉴权才生效），默认为3600秒，支持设置最小值为3600秒
		$request->setAcceptFormat('JSON');
		$response = self::$client->getAcsResponse($request);
		return $response;
	}
	public function getPlayPath($videoId) {
	
	}
}
