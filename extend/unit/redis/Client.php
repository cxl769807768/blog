<?php
namespace unit\redis;
/**
 * Created by PhpStorm.
 * User: xiaolong
 * Date: 2019/7/9
 * Time: 1:48 PM
 */

class Client
{
    /**
     * @var redis 客户端
     */
    private $client;
    /**
     * @var 当前权限认证
     */
    protected static $auth;
    /**
     * @var array 系统配置
     */
    protected static $config = [
        // 'host' => '103.47.80.156',
        'host' => '127.0.0.1',
        'port' => 6379,
        'auth' => '123.com',
        
    ];
    /**
     * [__construct description]
     * @Author   xiaolong
     * @DateTime 2019-07-09T10:19:55+0800
     */
    public function __construct(){
        $this->client = new \Redis();
        $this->client->connect(self::$config['host'], self::$config['port']);
        if (isset(self::$config['auth']) && !empty(self::$config['auth'])) {
            $this->client->auth(self::$config['auth']);
        }
        return $this->client;
    }
    /**
     * [instance description]
     * @Author   xiaolong
     * @DateTime 2019-07-09T09:39:05+0800
     * @return   [type]                   初始化连接
     */
    public static function instance() {
        

    }
    /**
     * [getKey description]
     * @Author   xiaolong
     * @DateTime 2019-07-09T09:40:13+0800
     * @param    [type]                   $uid        [description]
     * @param    [type]                   $createTime [description]
     * @return   [type]                              加密key
     */
    public static function getKey($uid,$createTime) {
        return md5($uid."yanxuetaoxiaolong".$createTime);
    }
    /**
     * 右边添加
     * @param $value
     */
    public function right_push($key, $value) {
        $data = json_encode($value);
        $this->client->rPush($key, $data);
    }

    /**
     * 左边添加
     * @param $value
     */
    public function left_push($key, $value) {
        $data = json_encode($value);
        $this->client->lPush($key, $data);
    }

    /**
     * 左边移出
     * @param $key
     * @return mixed
     */
    public function left_pop($key) {
        $value = $this->client->lpop($key);
        return json_decode($value, true);
    }

    /**
     * 右边移出
     * @param $key
     * @return mixed
     */
    public function right_pop($key) {
        $value = $this->client->rpop($key);
        return json_decode($value,true);
    }
    /**
     * [addNoRepetition description]
     * @Author   xiaolong
     * @DateTime 2019-07-09T14:23:26+0800
     * @param    [type]                   $key   [description]
     * @param    [type]                   $value 添加无重复的元素
     */
    public function addNoRepetition($key,$value){
        $data = json_encode($value);
        return $this->client->sadd($key,$data);
    }
    /**
     * 
     * @Author   xiaolong
     * @DateTime 2019-07-09T15:17:13+0800
     * @param    [type]                   $key [description]
     * @return   [type]                        获取集合中的元素
     */
    public function getListValue($key){
        return $this->client->smembers($key);
    }
    /**
     * [getListLen description]
     * @Author   xiaolong
     * @DateTime 2019-07-09T14:29:33+0800
     * @param    [type]                   $key [description]
     * @return   [type]                        获取list的长度
     */
    public function getListLen($key){
        return $this->client->llen($key);
    }
    /**
     * 设置值
     * @param $key
     * @param $value
     */
    public function setValue($key, $value) {
        $data = json_encode($value);
        $this->client->set($key, $data);
    }

    /**
     * 获取值
     * @param $key
     * @return mixed
     */
    public function getValue($key) {
        $value = $this->client->get($key);
        return json_decode($value, true);
    }

    /**
     * 获取ttl
     * @param $key
     * @return int
     */
    public function ttl($key) {
        return $this->client->ttl($key);
    }

    /**
     * 删除
     * @param $key
     * @return int
     */
    public function delete($key) {
        return $this->client->delete($key);
    }

    /**
     * 清空
     * @return bool
     */
    public function clean() {
        return $this->client->flushAll();
    }
}